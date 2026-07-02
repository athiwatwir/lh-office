<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetImage;
use App\Models\Image;
use GdImage;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class PropertyImageService
{
    public function __construct(
        private readonly ImageUploadService $imageUpload,
        private readonly ActiveAgentService $activeAgent,
        private readonly ImageProxyService $imageProxy,
    ) {}

    public function upload(Asset $asset, UploadedFile $file): AssetImage
    {
        $agent = $this->activeAgent->agent();

        if ($agent === null) {
            throw new RuntimeException('กรุณาเลือกเอเจนต์ที่ใช้งานก่อนอัปโหลดรูปภาพ');
        }

        $directory = Asset::picDirectory($asset->id);
        $agentCode = (string) $agent->code;

        $filename = $this->imageUpload->storeWithProcessor(
            $file,
            $this->uploadOptions($asset),
            fn (GdImage $image): GdImage => $this->applyCopyrightWatermark(
                $image,
                $agentCode,
                $this->resolveSellerPhone($asset),
            ),
        );

        $path = $directory.'/'.$filename;

        $image = Image::query()->create([
            'name' => $filename,
            'type' => 'property',
            'img_path' => $path,
            'created' => now(),
        ]);

        $hasDefault = AssetImage::query()
            ->where('asset_id', $asset->id)
            ->where('isdefault', 'Y')
            ->exists();

        $assetImage = AssetImage::query()->create([
            'asset_id' => $asset->id,
            'image_id' => $image->id,
            'isdefault' => $hasDefault ? 'N' : 'Y',
            'seq' => $this->nextSeq($asset),
            'created' => now(),
        ]);

        $this->imageProxy->warmAllVariants($image, 'property_upload');

        return $assetImage->load('image');
    }

    public function setDefault(Asset $asset, AssetImage $assetImage): void
    {
        if ($assetImage->asset_id !== $asset->id) {
            throw new RuntimeException('รูปภาพไม่ได้อยู่ในทรัพย์สินนี้');
        }

        AssetImage::query()
            ->where('asset_id', $asset->id)
            ->update(['isdefault' => 'N']);

        $assetImage->update(['isdefault' => 'Y']);
    }

    public function delete(Asset $asset, AssetImage $assetImage): ?string
    {
        if ($assetImage->asset_id !== $asset->id) {
            throw new RuntimeException('รูปภาพไม่ได้อยู่ในทรัพย์สินนี้');
        }

        $wasDefault = $assetImage->isdefault === 'Y';
        $image = $assetImage->image;

        if ($image !== null && $this->isManagedPath($image->img_path, $asset->id)) {
            $this->imageUpload->delete(basename($image->img_path), Asset::picDirectory($asset->id));
        }

        $assetImage->delete();

        if ($image !== null) {
            $this->imageProxy->invalidate($image);
            $image->delete();
        }

        $newDefaultId = null;

        if ($wasDefault) {
            $this->promoteFirstAsDefault($asset);

            $newDefaultId = AssetImage::query()
                ->where('asset_id', $asset->id)
                ->where('isdefault', 'Y')
                ->value('id');
        }

        return $newDefaultId;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatForJson(AssetImage $assetImage): array
    {
        $assetImage->loadMissing('image');

        return [
            'id' => $assetImage->id,
            'url' => $assetImage->image?->galleryUrl(),
            'isDefault' => $assetImage->isdefault === 'Y',
            'seq' => $assetImage->seq,
        ];
    }

    private function applyCopyrightWatermark(GdImage $image, string $agentCode, ?string $phone = null): GdImage
    {
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $padding = max(8, (int) round(min($imageWidth, $imageHeight) * 0.02));
        $phoneText = $this->normalizePhone($phone);

        $watermark = $this->loadCopyrightWatermark($agentCode);
        $targetWidth = 0;
        $targetHeight = 0;

        if ($watermark !== null) {
            $watermarkWidth = imagesx($watermark);
            $watermarkHeight = imagesy($watermark);

            if ($watermarkWidth > 0 && $watermarkHeight > 0) {
                $maxWidth = (int) round($imageWidth * 0.35);
                $targetWidth = min($watermarkWidth, $maxWidth);
                $scale = $targetWidth / $watermarkWidth;
                $targetHeight = (int) round($watermarkHeight * $scale);
            }
        }

        $textHeight = 0;
        $textWidth = 0;
        $fontSize = 0;
        $textGap = 0;

        if ($phoneText !== '') {
            $textBoxWidth = $targetWidth > 0
                ? max(24, $targetWidth - max(8, (int) round($targetWidth * 0.1)))
                : max(48, (int) round($imageWidth * 0.28));

            $fontSize = $this->fitFontSizeToWidth($phoneText, $textBoxWidth, $imageWidth);
            $textMetrics = $this->measureText($phoneText, $fontSize);
            $textWidth = $textMetrics['width'];
            $textHeight = $textMetrics['height'];
            $textGap = $targetWidth > 0
                ? max(4, (int) round($targetHeight * 0.05))
                : max(6, (int) round($fontSize * 0.35));
        }

        $blockWidth = max($targetWidth, $textWidth);
        $blockHeight = $targetHeight + ($phoneText !== '' ? $textGap + $textHeight : 0);

        if ($blockWidth <= 0 && $phoneText === '') {
            if ($watermark !== null) {
                imagedestroy($watermark);
            }

            return $image;
        }

        $blockTopY = (int) round(($imageHeight - $blockHeight) / 2);
        $rightEdge = $imageWidth - $padding;
        $destX = $rightEdge - max($targetWidth, $textWidth);

        imagealphablending($image, true);
        imagesavealpha($image, true);

        if ($watermark !== null && $targetWidth > 0 && $targetHeight > 0) {
            $watermarkX = $rightEdge - $targetWidth;
            $watermarkY = $blockTopY;

            imagealphablending($watermark, true);
            imagesavealpha($watermark, true);

            imagecopyresampled(
                $image,
                $watermark,
                $watermarkX,
                $watermarkY,
                0,
                0,
                $targetWidth,
                $targetHeight,
                imagesx($watermark),
                imagesy($watermark),
            );

            imagedestroy($watermark);
        } elseif ($watermark !== null) {
            imagedestroy($watermark);
        }

        if ($phoneText !== '') {
            $anchorWidth = $targetWidth > 0 ? $targetWidth : $textWidth;
            $anchorX = $rightEdge - $anchorWidth;
            $textX = $anchorX + (int) round(($anchorWidth - $textWidth) / 2);
            $textY = $blockTopY + $targetHeight + $textGap + $textHeight;

            $this->drawFadedText($image, $phoneText, $fontSize, $textX, $textY, 50);
        }

        return $image;
    }

    private function resolveSellerPhone(Asset $asset): ?string
    {
        $asset->loadMissing('user:id,phone');

        return $asset->user?->phone;
    }

    private function normalizePhone(?string $phone): string
    {
        $phone = trim((string) $phone);

        return $phone;
    }

    private function loadCopyrightWatermark(string $agentCode): ?GdImage
    {
        $copyrightPath = public_path('copyright/'.strtoupper($agentCode).'.png');

        if (! is_file($copyrightPath)) {
            return null;
        }

        $watermark = imagecreatefrompng($copyrightPath);

        return $watermark === false ? null : $watermark;
    }

    /**
     * @return array{width: int, height: int}
     */
    private function measureText(string $text, int $fontSize): array
    {
        if ($text === '') {
            return ['width' => 0, 'height' => 0];
        }

        $fontPath = $this->watermarkFontPath();

        if ($fontPath === null) {
            $width = (int) (strlen($text) * ($fontSize * 0.55));
            $height = $fontSize;

            return ['width' => $width, 'height' => $height];
        }

        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);

        if ($bbox === false) {
            return ['width' => 0, 'height' => 0];
        }

        return [
            'width' => abs($bbox[2] - $bbox[0]),
            'height' => abs($bbox[7] - $bbox[1]),
        ];
    }

    private function fitFontSizeToWidth(string $text, int $maxWidth, int $imageWidth): int
    {
        $maxFont = max(10, (int) round($imageWidth * 0.035));
        $minFont = 8;

        for ($size = $maxFont; $size >= $minFont; $size--) {
            if ($this->measureText($text, $size)['width'] <= $maxWidth) {
                return $size;
            }
        }

        return $minFont;
    }

    private function drawFadedText(GdImage $image, string $text, int $fontSize, int $x, int $y, int $opacityPercent): void
    {
        $fontPath = $this->watermarkFontPath();
        $opacityPercent = max(0, min(100, $opacityPercent));
        $alpha = (int) round(127 * (1 - ($opacityPercent / 100)));

        if ($fontPath === null) {
            $color = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
            imagestring($image, 5, $x, $y - $fontSize, $text, $color);

            return;
        }

        $textColor = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);
    }

    private function watermarkFontPath(): ?string
    {
        $fontPath = public_path('copyright/Sarabun-Bold.ttf');

        return is_file($fontPath) ? $fontPath : null;
    }

    private function promoteFirstAsDefault(Asset $asset): void
    {
        $next = AssetImage::query()
            ->where('asset_id', $asset->id)
            ->orderBy('seq')
            ->first();

        if ($next === null) {
            return;
        }

        $next->update(['isdefault' => 'Y']);
    }

    private function nextSeq(Asset $asset): int
    {
        $max = AssetImage::query()
            ->where('asset_id', $asset->id)
            ->max('seq');

        return ((int) $max) + 1;
    }

    private function isManagedPath(?string $path, string $assetId): bool
    {
        if (blank($path)) {
            return false;
        }

        $prefix = Asset::picDirectory($assetId).'/';

        return str_starts_with($path, $prefix)
            || str_starts_with($path, '/'.$prefix);
    }

    private function uploadOptions(Asset $asset): ImageUploadOptions
    {
        return new ImageUploadOptions(
            directory: Asset::picDirectory($asset->id),
            quality: 85,
            maxWidth: 1000,
            maxHeight: 1000,
        );
    }
}
