<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\Concerns\FormatsApiImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Asset */
class PropertyListResource extends JsonResource
{
    use FormatsApiImageUrls;
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $defaultImage = $this->asset_images
            ->first(fn($assetImage) => $assetImage->isdefault === 'Y')
            ?? $this->asset_images->first();

        $createdAt = $this->created ?? $this->created_at;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'asset_type' => $this->whenLoaded('asset_type', fn() => [
                'id' => $this->asset_type?->id,
                'name' => $this->asset_type?->name ?? $this->asset_type_des,
                'code' => $this->asset_type?->code,
            ]),
            'agent' => $this->whenLoaded('agent', fn() => [
                'id' => $this->agent?->id,
                'name' => $this->agent?->name,
                'code' => $this->agent?->code,
            ]),
            'zone' => $this->whenLoaded('zone', fn() => [
                'id' => $this->zone?->id,
                'name' => $this->zone?->name,
            ]),
            'price_amount' => $this->price_amounnt,
            'price_special' => $this->price_special,
            'price_rent' => $this->price_rent,
            'is_special_price' => $this->isspecial_marketprice === 'Y',
            'listing' => [
                'sale' => $this->issale === 'Y',
                'rent' => $this->isrent === 'Y',
                'sale_rent' => $this->issalerent === 'Y',
                'sellout' => $this->issellout === 'Y',
                'sale_down' => $this->issaledown === 'Y',
            ],
            'seller' => $this->user ? [
                'name' => trim($this->user->firstname . ' ' . $this->user->lastname),
                'phone' => $this->user->phone,
            ] : null,
            'address' => $this->address ? [
                'amphur' => $this->address->amphur,
            ] : null,
            'is_recommend' => $this->isrecommend === 'Y',
            'thumbnail_url' => $this->apiImageUrl($defaultImage?->image?->thumbnailUrl(absolute: false)),
            'image_path' => $defaultImage?->image?->img_path,
            'images_count' => $this->whenCounted('asset_images'),
            'slug' => $this->addressSlug(),
            //'created_at' => $createdAt?->toIso8601String(),
        ];
    }

    private function addressSlug(): ?string
    {
        if ($this->address === null) {
            return null;
        }

        $parts = array_values(array_filter([
            $this->address->street,
            $this->address->soi,
            $this->address->address1,
        ], filled(...)));

        if ($parts === []) {
            return null;
        }

        $slug = implode('-', array_values(array_filter(
            array_map(
                static fn(string $part): string => (string) preg_replace('/[^\p{L}\p{N}]+/u', '', trim($part)),
                $parts,
            ),
            filled(...),
        )));

        return $slug !== '' ? $slug : null;
    }
}
