<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Asset;
use App\Models\AssetImage;
use App\Models\AssetOption;
use App\Models\AssetTag;
use App\Models\AssetViewsDaily;
use Illuminate\Support\Facades\File;

class PropertyDeletionService
{
    public function __construct(
        private readonly ImageUploadService $imageUpload,
        private readonly ImageProxyService $imageProxy,
    ) {}

    public function permanentlyDelete(Asset $asset): void
    {
        $asset->loadMissing('asset_images.image');

        $addressId = $asset->address_id;
        $deleteAddress = $addressId !== null
            && Asset::withTrashed()->where('address_id', $addressId)->count() === 1;

        $this->deleteImages($asset);

        AssetViewsDaily::query()->where('asset_id', $asset->id)->delete();
        AssetOption::withTrashed()->where('asset_id', $asset->id)->forceDelete();
        AssetTag::query()->where('asset_id', $asset->id)->delete();

        $asset->forceDelete();

        if ($deleteAddress) {
            Address::withTrashed()->whereKey($addressId)->forceDelete();
        }

        $this->removeAssetUploadDirectory($asset->id);
    }

    private function deleteImages(Asset $asset): void
    {
        $assetImages = AssetImage::withTrashed()
            ->where('asset_id', $asset->id)
            ->with(['image' => fn ($query) => $query->withTrashed()])
            ->get();

        foreach ($assetImages as $assetImage) {
            $image = $assetImage->image;

            if ($image !== null && $this->isManagedPath($image->img_path, $asset->id)) {
                $this->imageUpload->delete(basename($image->img_path), Asset::picDirectory($asset->id));
            }

            $assetImage->forceDelete();

            if ($image !== null) {
                $this->imageProxy->invalidate($image);
                $image->forceDelete();
            }
        }
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

    private function removeAssetUploadDirectory(string $assetId): void
    {
        $directory = public_path(Asset::picDirectory($assetId));

        if (is_dir($directory)) {
            File::deleteDirectory($directory);
        }
    }
}
