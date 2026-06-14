<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ImageUploadService
{
    /**
     * Upload an image, convert to WebP, and return the stored filename.
     */
    public function store(UploadedFile $file, ImageUploadOptions $options): string
    {
        $this->assertWebpSupported();

        $image = $this->createImageResource($file);
        $image = $this->applyResize($image, $options);
        $this->preserveAlpha($image);

        $directory = $this->normalizeDirectory($options->directory);
        File::ensureDirectoryExists($directory);

        $filename = $this->generateFilename();
        $path = $directory.DIRECTORY_SEPARATOR.$filename;

        if (! imagewebp($image, $path, $this->normalizeQuality($options->quality))) {
            imagedestroy($image);
            throw new RuntimeException('ไม่สามารถบันทึกไฟล์รูปภาพได้');
        }

        imagedestroy($image);

        return $filename;
    }

    public function delete(?string $filename, string $directory): bool
    {
        if (blank($filename) || ! $this->exists($filename, $directory)) {
            return false;
        }

        return unlink($this->filePath($filename, $directory));
    }

    public function exists(?string $filename, string $directory): bool
    {
        if (blank($filename)) {
            return false;
        }

        return is_file($this->filePath($filename, $directory));
    }

    public function url(?string $filename, string $directory): ?string
    {
        if (! $this->exists($filename, $directory)) {
            return null;
        }

        return asset($this->publicPath($filename, $directory));
    }

    public function replace(
        ?string $currentFilename,
        UploadedFile $file,
        ImageUploadOptions $options,
    ): string {
        $newFilename = $this->store($file, $options);
        $this->delete($currentFilename, $options->directory);

        return $newFilename;
    }

    private function assertWebpSupported(): void
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('เซิร์ฟเวอร์ไม่รองรับการแปลงรูปภาพเป็น WebP');
        }
    }

    private function createImageResource(UploadedFile $file): GdImage
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new InvalidArgumentException('ไม่สามารถอ่านไฟล์รูปภาพได้');
        }

        $image = match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default => false,
        };

        if ($image === false) {
            throw new InvalidArgumentException('รูปแบบไฟล์รูปภาพไม่รองรับ');
        }

        return $image;
    }

    private function applyResize(GdImage $image, ImageUploadOptions $options): GdImage
    {
        if ($options->maxWidth === null && $options->maxHeight === null) {
            return $image;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        [$targetWidth, $targetHeight] = $this->calculateDimensions(
            $width,
            $height,
            $options->maxWidth,
            $options->maxHeight,
            $options->maintainAspectRatio,
        );

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
    private function calculateDimensions(
        int $width,
        int $height,
        ?int $maxWidth,
        ?int $maxHeight,
        bool $maintainAspectRatio,
    ): array {
        if (! $maintainAspectRatio) {
            return [
                $maxWidth ?? $width,
                $maxHeight ?? $height,
            ];
        }

        $ratio = $width / $height;

        if ($maxWidth !== null && ($maxHeight === null || $width > $maxWidth)) {
            $width = min($width, $maxWidth);
            $height = (int) round($width / $ratio);
        }

        if ($maxHeight !== null && $height > $maxHeight) {
            $height = $maxHeight;
            $width = (int) round($height * $ratio);
        }

        return [$width, $height];
    }

    private function preserveAlpha(GdImage $image): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    private function generateFilename(): string
    {
        return time().'_'.Str::lower(Str::random(8)).'.webp';
    }

    private function normalizeQuality(int $quality): int
    {
        return max(0, min(100, $quality));
    }

    private function normalizeDirectory(string $directory): string
    {
        return public_path(trim($directory, '/'));
    }

    private function filePath(string $filename, string $directory): string
    {
        return $this->normalizeDirectory($directory).DIRECTORY_SEPARATOR.basename($filename);
    }

    private function publicPath(string $filename, string $directory): string
    {
        return trim($directory, '/').'/'.basename($filename);
    }
}
