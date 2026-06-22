<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Asset */
class PropertyListResource extends JsonResource
{
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
            'price_rent' => $this->price_rent,
            'listing' => [
                'sale' => $this->issale === 'Y',
                'rent' => $this->isrent === 'Y',
                'sale_rent' => $this->issalerent === 'Y',
                'sellout' => $this->issellout === 'Y',
                'sale_down' => $this->issaledown === 'Y',
            ],
            'seller' => [
                'name' => $this->user->firstname . ' ' . $this->user->lastname,
                'phone' => $this->user->phone,

            ],
            'address' => [
                'amphur' => $this->address->amphur,
            ],
            'is_recommend' => $this->isrecommend === 'Y',
            'thumbnail_url' => $defaultImage?->image?->thumbnailUrl(absolute: true),
            'images_count' => $this->whenCounted('asset_images'),
            'created_at' => $createdAt?->toIso8601String(),
        ];
    }
}
