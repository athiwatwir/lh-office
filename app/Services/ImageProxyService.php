<?php

namespace App\Services;

use App\Models\Image;
use GdImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ImageProxyService
{
    public const VARIANT_THUMB = 'thumb';

    public const VARIANT_GALLERY = 'gallery';

    /**
     * @return list<string>
     */
    public function variants(): array
    {
        return [self::VARIANT_THUMB, self::VARIANT_GALLERY];
    }

    public function publicCacheFilename(Image $image, string $variant): string
    {
        return sprintf('%s_%s.webp', $image->id, $variant);
    }

    public function publicCachePath(Image $image, string $variant): string
    {
        return $this->publicCacheDirectory().DIRECTORY_SEPARATOR.$this->publicCacheFilename($image, $variant);
    }

    public function publicCacheUrl(Image $image, string $variant, bool $absolute = false): ?string
    {
        if (blank($image->id)) {
            return null;
        }

        $path = '/img-cache/'.$this->publicCacheFilename($image, $variant);

        if ($absolute) {
            return url($path);
        }

        return $path;
    }

    public function isCached(Image $image, string $variant): bool
    {
        $path = $this->publicCachePath($image, $variant);

        return is_file($path) && $this->cacheIsFresh($image, $path);
    }

    public function resolveSourceFilePath(?string $imgPath): ?string
    {
        if (blank($imgPath)) {
            return null;
        }

        if (str_starts_with($imgPath, 'http://') || str_starts_with($imgPath, 'https://')) {
            return null;
        }

        if (str_starts_with($imgPath, '/') && is_file($imgPath)) {
            return $imgPath;
        }

        $publicPath = public_path(ltrim($imgPath, '/'));

        if (is_file($publicPath)) {
            return $publicPath;
        }

        return null;
    }

    public function warm(Image $image, string $variant, ?string $context = null): bool
    {
        [$width, $height] = $this->variantDimensions($variant);
        $path = $this->publicCachePath($image, $variant);

        if (is_file($path) && $this->cacheIsFresh($image, $path)) {
            return true;
        }

        $logContext = $this->warmLogContext($image, $variant, $path, $context);

        if (! function_exists('imagewebp')) {
            $this->logWarmFailure($logContext, 'webp_not_supported');

            return false;
        }

        $source = $this->resolveSourceBinary($image);

        if ($source['binary'] === null) {
            $this->logWarmFailure($logContext, $source['reason'], $source['details']);

            return false;
        }

        $quality = (int) config('image.default_quality', 80);
        $webp = $this->transform($source['binary'], $width, $height, $quality);

        if ($webp === null) {
            $this->logWarmFailure($logContext, 'transform_failed');

            return false;
        }

        if (! $this->ensurePublicCacheDirectory($logContext)) {
            return false;
        }

        if (file_put_contents($path, $webp) === false) {
            $this->logWarmFailure($logContext, 'cache_write_failed', [
                'cache_path' => $path,
                'cache_dir_writable' => is_writable(dirname($path)),
            ]);

            return false;
        }

        return true;
    }

    public function ensurePublicCacheDirectory(?array $logContext = null): bool
    {
        $directory = $this->publicCacheDirectory();

        if (is_dir($directory)) {
            if (is_writable($directory)) {
                return true;
            }

            if ($logContext !== null) {
                $this->logWarmFailure($logContext, 'cache_directory_not_writable', [
                    'cache_directory' => $directory,
                ]);
            }

            return false;
        }

        try {
            File::ensureDirectoryExists($directory, 0775);

            if (is_dir($directory) && is_writable($directory)) {
                return true;
            }

            if ($logContext !== null) {
                $this->logWarmFailure($logContext, 'cache_directory_not_writable', [
                    'cache_directory' => $directory,
                    'created' => is_dir($directory),
                ]);
            }

            return false;
        } catch (Throwable $exception) {
            if ($logContext !== null) {
                $this->logWarmFailure($logContext, 'cache_directory_create_failed', [
                    'cache_directory' => $directory,
                    'exception' => $exception->getMessage(),
                ]);
            }

            return false;
        }
    }

    public function warmAllVariants(Image $image, ?string $context = null): void
    {
        foreach ($this->variants() as $variant) {
            $this->warm($image, $variant, $context);
        }
    }

    public function invalidate(Image $image): void
    {
        foreach ($this->variants() as $variant) {
            $path = $this->publicCachePath($image, $variant);

            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function serveVariant(Image $image, string $variant): ?BinaryFileResponse
    {
        if (! $this->warm($image, $variant, 'image_serve')) {
            return null;
        }

        return response()->file($this->publicCachePath($image, $variant), $this->responseHeaders());
    }

    /**
     * @return array<string, string>
     */
    public function responseHeaders(): array
    {
        return [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ];
    }

    public function render(Image $image, ?int $width = null, ?int $height = null, ?int $quality = null): ?string
    {
        $variant = $this->matchVariant($width, $height);

        if ($variant !== null) {
            if ($this->warm($image, $variant, 'image_proxy')) {
                return file_get_contents($this->publicCachePath($image, $variant)) ?: null;
            }

            return null;
        }

        return $this->generate($image, $width, $height, max(1, min(100, $quality ?? (int) config('image.default_quality', 80))));
    }

    public function redirectUrlForDimensions(Image $image, ?int $width, ?int $height): ?string
    {
        $variant = $this->matchVariant($width, $height);

        if ($variant === null) {
            return null;
        }

        if (! $this->warm($image, $variant, 'image_proxy_redirect')) {
            return null;
        }

        return $this->publicCacheUrl($image, $variant);
    }

    private function matchVariant(?int $width, ?int $height): ?string
    {
        $thumb = config('image.sizes.thumb', ['w' => 400, 'h' => 300]);
        $gallery = config('image.sizes.gallery', ['w' => 1200, 'h' => null]);

        if ($width === ($thumb['w'] ?? null) && $height === ($thumb['h'] ?? null)) {
            return self::VARIANT_THUMB;
        }

        if ($width === ($gallery['w'] ?? null) && $height === ($gallery['h'] ?? null)) {
            return self::VARIANT_GALLERY;
        }

        return null;
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function variantDimensions(string $variant): array
    {
        $sizes = config('image.sizes.'.$variant, []);

        return [$sizes['w'] ?? null, $sizes['h'] ?? null];
    }

    private function generate(Image $image, ?int $width, ?int $height, int $quality): ?string
    {
        $source = $this->resolveSourceBinary($image);

        if ($source['binary'] === null) {
            return null;
        }

        return $this->transform($source['binary'], $width, $height, $quality);
    }

    /**
     * @return array{binary: ?string, reason: string, details: array<string, mixed>}
     */
    private function resolveSourceBinary(Image $image): array
    {
        $imgPath = $image->img_path;

        if (blank($imgPath)) {
            return [
                'binary' => null,
                'reason' => 'missing_img_path',
                'details' => [],
            ];
        }

        if (str_starts_with($imgPath, 'http://') || str_starts_with($imgPath, 'https://')) {
            $binary = $this->fetchRemote($imgPath, $status, $error);

            if ($binary !== null) {
                return ['binary' => $binary, 'reason' => '', 'details' => []];
            }

            return [
                'binary' => null,
                'reason' => 'remote_fetch_failed',
                'details' => [
                    'img_path' => $imgPath,
                    'http_status' => $status,
                    'error' => $error,
                ],
            ];
        }

        $filePath = $this->resolveSourceFilePath($imgPath);

        if ($filePath === null) {
            $legacyBinary = $this->fetchLegacySource($imgPath, $legacyStatus, $legacyError);

            if ($legacyBinary !== null) {
                return ['binary' => $legacyBinary, 'reason' => '', 'details' => []];
            }

            return [
                'binary' => null,
                'reason' => $legacyError !== null ? 'legacy_fetch_failed' : 'source_file_not_found',
                'details' => [
                    'img_path' => $imgPath,
                    'checked_paths' => $this->checkedSourcePaths($imgPath),
                    'legacy_url' => Image::resolveLegacyUrl($imgPath),
                    'http_status' => $legacyStatus,
                    'error' => $legacyError,
                ],
            ];
        }

        $binary = file_get_contents($filePath);

        if ($binary === false || $binary === '') {
            return [
                'binary' => null,
                'reason' => 'source_file_unreadable',
                'details' => [
                    'img_path' => $imgPath,
                    'resolved_path' => $filePath,
                ],
            ];
        }

        return ['binary' => $binary, 'reason' => '', 'details' => []];
    }

    /**
     * @return array<string, mixed>
     */
    private function warmLogContext(Image $image, string $variant, string $cachePath, ?string $context): array
    {
        return [
            'image_id' => $image->id,
            'variant' => $variant,
            'img_path' => $image->img_path,
            'cache_path' => $cachePath,
            'context' => $context,
            'php_user' => get_current_user(),
        ];
    }

    /**
     * @param  array<string, mixed>  $logContext
     * @param  array<string, mixed>  $details
     */
    private function logWarmFailure(array $logContext, string $reason, array $details = []): void
    {
        Log::warning('Image cache warm failed: '.$reason, array_merge($logContext, [
            'reason' => $reason,
        ], $details));
    }

    private function cacheIsFresh(Image $image, string $cachePath): bool
    {
        if (! is_file($cachePath)) {
            return false;
        }

        $cacheMtime = filemtime($cachePath) ?: 0;
        $localPath = $this->resolveLocalPath($image);

        if ($localPath !== null && is_file($localPath)) {
            return $cacheMtime >= (filemtime($localPath) ?: 0);
        }

        $imageMtime = $image->updated_at?->getTimestamp()
            ?? $image->created_at?->getTimestamp()
            ?? 0;

        return $cacheMtime >= $imageMtime;
    }

    private function resolveLocalPath(Image $image): ?string
    {
        return $this->resolveSourceFilePath($image->img_path);
    }

    /**
     * @return array<string, string>
     */
    private function checkedSourcePaths(string $imgPath): array
    {
        $paths = [
            'public' => public_path(ltrim($imgPath, '/')),
        ];

        if (str_starts_with($imgPath, '/')) {
            $paths['absolute'] = $imgPath;
        }

        return $paths;
    }

    private function fetchLegacySource(string $imgPath, ?int &$status = null, ?string &$error = null): ?string
    {
        $status = null;
        $error = null;

        if (! config('image.mirror_legacy_sources', true)) {
            return null;
        }

        $legacyUrl = Image::resolveLegacyUrl($imgPath);

        if ($legacyUrl === null) {
            return null;
        }

        return $this->fetchRemote($legacyUrl, $status, $error);
    }

    private function fetchRemote(string $url, ?int &$status = null, ?string &$error = null): ?string
    {
        $status = null;
        $error = null;

        try {
            $response = Http::timeout((int) config('image.remote_timeout', 15))
                ->get($url);

            $status = $response->status();

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();

            return $body !== '' ? $body : null;
        } catch (Throwable $exception) {
            $error = $exception->getMessage();

            return null;
        }
    }

    private function transform(string $binary, ?int $width, ?int $height, int $quality): ?string
    {
        $image = @imagecreatefromstring($binary);

        if ($image === false) {
            return null;
        }

        $image = $this->resize($image, $width, $height);
        $this->preserveAlpha($image);

        ob_start();
        $saved = imagewebp($image, null, $quality);
        $output = ob_get_clean();
        imagedestroy($image);

        if (! $saved || $output === false || $output === '') {
            return null;
        }

        return $output;
    }

    private function resize(GdImage $image, ?int $maxWidth, ?int $maxHeight): GdImage
    {
        if ($maxWidth === null && $maxHeight === null) {
            $maxWidth = (int) config('image.sizes.gallery.w', 1200);
        }

        $width = imagesx($image);
        $height = imagesy($image);
        [$targetWidth, $targetHeight] = $this->calculateDimensions($width, $height, $maxWidth, $maxHeight);

        if ($targetWidth === $width && $targetHeight === $height) {
            return $image;
        }

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        $this->preserveAlpha($resized);

        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height,
        );

        imagedestroy($image);

        return $resized;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function calculateDimensions(int $width, int $height, ?int $maxWidth, ?int $maxHeight): array
    {
        if ($maxWidth === null && $maxHeight === null) {
            return [$width, $height];
        }

        $ratio = $width / max($height, 1);

        if ($maxWidth !== null && ($maxHeight === null || $width > $maxWidth)) {
            $width = min($width, $maxWidth);
            $height = (int) round($width / $ratio);
        }

        if ($maxHeight !== null && $height > $maxHeight) {
            $height = $maxHeight;
            $width = (int) round($height * $ratio);
        }

        return [max(1, $width), max(1, $height)];
    }

    private function preserveAlpha(GdImage $image): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    private function publicCacheDirectory(): string
    {
        return rtrim((string) config('image.public_cache_path'), DIRECTORY_SEPARATOR);
    }
}
