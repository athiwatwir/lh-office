<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetImage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SellerDeletionService
{
    public function __construct(
        private readonly PropertyDeletionService $propertyDeletion,
        private readonly UserImageService $userImage,
    ) {}

    /**
     * @return array{
     *     seller_name: string,
     *     assets_count: int,
     *     images_count: int,
     *     has_profile_image: bool,
     * }
     */
    public function summarize(User $seller): array
    {
        $assetIds = Asset::withTrashed()
            ->where('user_id', $seller->id)
            ->pluck('id');

        return [
            'seller_name' => $seller->name,
            'assets_count' => $assetIds->count(),
            'images_count' => $assetIds->isEmpty()
                ? 0
                : AssetImage::withTrashed()->whereIn('asset_id', $assetIds)->count(),
            'has_profile_image' => filled($seller->image_id),
        ];
    }

    public function delete(User $seller): void
    {
        DB::transaction(function () use ($seller): void {
            $assets = Asset::withTrashed()
                ->where('user_id', $seller->id)
                ->get();

            foreach ($assets as $asset) {
                $this->propertyDeletion->permanentlyDelete($asset);
            }

            $seller->loadMissing('image');
            $this->userImage->deleteLocalProfileImage($seller);

            if ($seller->addresses()->exists()) {
                $seller->addresses()->detach();
            }

            $seller->delete();
        });
    }
}
