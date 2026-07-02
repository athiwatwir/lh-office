<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetImage;
use App\Models\Image;

class PropertyApiImageWarmer
{
    public function __construct(
        private readonly ImageProxyService $imageProxy,
    ) {}

    /**
     * @param  iterable<int, Asset>  $assets
     */
    public function warmListThumbnails(iterable $assets): void
    {
        foreach ($assets as $asset) {
            $image = $this->defaultImage($asset)?->image;

            if ($image !== null) {
                $this->warmThumbnail($image, 'api_list');
            }
        }
    }

    public function warmDetailImages(Asset $asset): void
    {
        if (! $asset->relationLoaded('asset_images')) {
            return;
        }

        foreach ($asset->asset_images as $assetImage) {
            $image = $assetImage->image;

            if ($image === null) {
                continue;
            }

            $this->warmThumbnail($image, 'api_detail');
            $this->warmGallery($image, 'api_detail');
        }
    }

    private function defaultImage(Asset $asset): ?AssetImage
    {
        if (! $asset->relationLoaded('asset_images')) {
            return null;
        }

        return $asset->asset_images
            ->first(fn (AssetImage $assetImage) => $assetImage->isdefault === 'Y')
            ?? $asset->asset_images->first();
    }

    private function warmThumbnail(Image $image, string $context): void
    {
        if ($this->imageProxy->isCached($image, ImageProxyService::VARIANT_THUMB)) {
            return;
        }

        $this->imageProxy->warm($image, ImageProxyService::VARIANT_THUMB, $context);
    }

    private function warmGallery(Image $image, string $context): void
    {
        if ($this->imageProxy->isCached($image, ImageProxyService::VARIANT_GALLERY)) {
            return;
        }

        $this->imageProxy->warm($image, ImageProxyService::VARIANT_GALLERY, $context);
    }
}
