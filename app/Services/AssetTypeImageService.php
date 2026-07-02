<?php

namespace App\Services;

use App\Models\AssetType;
use App\Models\Image;
use Illuminate\Http\UploadedFile;

class AssetTypeImageService
{
    public function __construct(
        private readonly ImageUploadService $imageUpload,
        private readonly ImageProxyService $imageProxy,
    ) {}

    public function attach(AssetType $assetType, UploadedFile $file): void
    {
        $filename = $this->imageUpload->store($file, $this->uploadOptions());
        $path = AssetType::PIC_DIRECTORY.'/'.$filename;

        $image = Image::query()->create([
            'name' => $filename,
            'type' => 'property-type',
            'img_path' => $path,
            'created' => now(),
        ]);

        $assetType->update(['image_id' => $image->id]);

        $this->imageProxy->warmAllVariants($image, 'property_type_upload');
    }

    public function replace(AssetType $assetType, UploadedFile $file): void
    {
        $this->deleteLocalImage($assetType);
        $this->attach($assetType, $file);
    }

    public function deleteLocalImage(AssetType $assetType): void
    {
        $image = $assetType->image;

        if ($image === null) {
            return;
        }

        $this->deleteManagedFile($image->img_path);
        $assetType->update(['image_id' => null]);
        $image->delete();
    }

    private function deleteManagedFile(?string $path): void
    {
        if (blank($path) || ! $this->isManagedPath($path)) {
            return;
        }

        $this->imageUpload->delete(basename($path), AssetType::PIC_DIRECTORY);
    }

    private function isManagedPath(string $path): bool
    {
        return str_starts_with($path, AssetType::PIC_DIRECTORY.'/')
            || str_starts_with($path, '/'.AssetType::PIC_DIRECTORY.'/');
    }

    private function uploadOptions(): ImageUploadOptions
    {
        return new ImageUploadOptions(
            directory: AssetType::PIC_DIRECTORY,
            quality: 85,
            maxWidth: 800,
            maxHeight: 800,
        );
    }
}
