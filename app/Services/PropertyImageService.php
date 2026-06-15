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
            fn (GdImage $image): GdImage => $this->applyCopyrightWatermark($image, $agentCode),
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
            'url' => $assetImage->image?->url,
            'isDefault' => $assetImage->isdefault === 'Y',
            'seq' => $assetImage->seq,
        ];
    }

    private function applyCopyrightWatermark(GdImage $image, string $agentCode): GdImage
    {
        $copyrightPath = public_path('copyright/'.strtoupper($agentCode).'.png');

        if (! is_file($copyrightPath)) {
            return $image;
        }

        $watermark = imagecreatefrompng($copyrightPath);

        if ($watermark === false) {
            return $image;
        }

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        if ($watermarkWidth <= 0 || $watermarkHeight <= 0) {
            imagedestroy($watermark);

            return $image;
        }

        $maxWidth = (int) round($imageWidth * 0.5);
        $targetWidth = min($watermarkWidth, $maxWidth);
        $scale = $targetWidth / $watermarkWidth;
        $targetHeight = (int) round($watermarkHeight * $scale);
        $padding = max(8, (int) round($imageHeight * 0.02));
        $destX = (int) (($imageWidth - $targetWidth) / 2);
        $destY = $imageHeight - $targetHeight - $padding;

        imagealphablending($image, true);
        imagesavealpha($image, true);
        imagealphablending($watermark, true);
        imagesavealpha($watermark, true);

        imagecopyresampled(
            $image,
            $watermark,
            $destX,
            $destY,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $watermarkWidth,
            $watermarkHeight,
        );

        imagedestroy($watermark);

        return $image;
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
