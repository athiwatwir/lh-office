<?php

namespace App\Services;

use App\Models\Image;
use GdImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ImageProxyService
{
    public function render(Image $image, ?int $width = null, ?int $height = null, ?int $quality = null): ?string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('Server does not support WebP conversion.');
        }

        $quality = max(1, min(100, $quality ?? (int) config('image.default_quality', 80)));
        $sourceKey = $this->sourceKey($image);
        $cachePath = $this->cachePath($image->id, $width, $height, $quality, $sourceKey);

        if (is_file($cachePath)) {
            return file_get_contents($cachePath) ?: null;
        }

        $binary = $this->fetchSourceBinary($image);

        if ($binary === null) {
            return null;
        }

        $webp = $this->transform($binary, $width, $height, $quality);

        if ($webp === null) {
            return null;
        }

        File::ensureDirectoryExists(dirname($cachePath));
        file_put_contents($cachePath, $webp);

        return $webp;
    }

    private function sourceKey(Image $image): string
    {
        $storagePath = $image->img_path ?: ($image->getAttributes()['path'] ?? null);

        if (blank($storagePath)) {
            return 'missing';
        }

        if (str_starts_with($storagePath, 'http://') || str_starts_with($storagePath, 'https://')) {
            return md5($storagePath);
        }

        $localPath = public_path(ltrim($storagePath, '/'));

        if (is_file($localPath)) {
            return md5($localPath.'|'.filemtime($localPath));
        }

        return md5($storagePath);
    }

    private function fetchSourceBinary(Image $image): ?string
    {
        $storagePath = $image->img_path ?: ($image->getAttributes()['path'] ?? null);

        if (blank($storagePath)) {
            return null;
        }

        if (str_starts_with($storagePath, 'http://') || str_starts_with($storagePath, 'https://')) {
            return $this->fetchRemote($storagePath);
        }

        $localPath = public_path(ltrim($storagePath, '/'));

        if (is_file($localPath)) {
            return file_get_contents($localPath) ?: null;
        }

        foreach ($this->legacyUrlCandidates(ltrim($storagePath, '/')) as $url) {
            $binary = $this->fetchRemote($url);

            if ($binary !== null) {
                return $binary;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function legacyUrlCandidates(string $normalized): array
    {
        $baseUrl = rtrim((string) config('image.legacy_base_url', ''), '/');

        if ($baseUrl === '') {
            return [];
        }

        $candidates = [];

        if (str_starts_with($normalized, 'upload/')) {
            $relative = substr($normalized, strlen('upload/'));
            $candidates[] = $baseUrl.'/'.$relative;
        }

        $candidates[] = $baseUrl.'/'.basename($normalized);

        return array_values(array_unique(array_filter($candidates)));
    }

    private function fetchRemote(string $url): ?string
    {
        try {
            $response = Http::timeout((int) config('image.remote_timeout', 15))
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();

            return $body !== '' ? $body : null;
        } catch (\Throwable) {
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

    private function cachePath(string $imageId, ?int $width, ?int $height, int $quality, string $sourceKey): string
    {
        $filename = sprintf(
            '%s_%sx%s_q%d_%s.webp',
            $imageId,
            $width ?? 'auto',
            $height ?? 'auto',
            $quality,
            substr($sourceKey, 0, 12),
        );

        return rtrim((string) config('image.cache_path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;
    }
}
