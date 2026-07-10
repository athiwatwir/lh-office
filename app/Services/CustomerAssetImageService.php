<?php

namespace App\Services;

use App\Models\AssetImage;
use App\Models\CustomerAsset;
use App\Models\Image;
use Illuminate\Http\UploadedFile;

class CustomerAssetImageService
{
    public function __construct(
        private readonly ImageUploadService $imageUpload,
        private readonly ImageProxyService $imageProxy,
    ) {}

    public function upload(CustomerAsset $customerAsset, UploadedFile $file): AssetImage
    {
        $filename = $this->imageUpload->store($file, $this->uploadOptions($customerAsset));
        $path = CustomerAsset::picDirectory($customerAsset->id).'/'.$filename;

        $image = Image::query()->create([
            'name' => $filename,
            'type' => 'customer_asset',
            'img_path' => $path,
            'created' => now(),
        ]);

        $hasDefault = AssetImage::query()
            ->where('customer_asset_id', $customerAsset->id)
            ->where('isdefault', 'Y')
            ->exists();

        $assetImage = AssetImage::query()->create([
            'customer_asset_id' => $customerAsset->id,
            'image_id' => $image->id,
            'isdefault' => $hasDefault ? 'N' : 'Y',
            'seq' => $this->nextSeq($customerAsset),
            'created' => now(),
        ]);

        $this->imageProxy->warmAllVariants($image, 'customer_asset_upload');

        return $assetImage->load('image');
    }

    private function nextSeq(CustomerAsset $customerAsset): int
    {
        $max = AssetImage::query()
            ->where('customer_asset_id', $customerAsset->id)
            ->max('seq');

        return ((int) $max) + 1;
    }

    private function uploadOptions(CustomerAsset $customerAsset): ImageUploadOptions
    {
        return new ImageUploadOptions(
            directory: CustomerAsset::picDirectory($customerAsset->id),
            quality: 85,
            maxWidth: 1000,
            maxHeight: 1000,
        );
    }
}
