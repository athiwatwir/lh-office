<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\Concerns\FormatsApiImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Asset */
class PropertyDetailResource extends JsonResource
{
    use FormatsApiImageUrls;
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $address = $this->address;
        $createdAt = $this->created ?? $this->created_at;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'asset_type' => $this->whenLoaded('asset_type', fn () => [
                'id' => $this->asset_type?->id,
                'name' => $this->asset_type?->name ?? $this->asset_type_des,
            ]),
            'agent' => $this->whenLoaded('agent', fn () => [
                'id' => $this->agent?->id,
                'name' => $this->agent?->name,
                'code' => $this->agent?->code,
                'logo_url' => $this->apiImageUrl($this->agent?->logo_url),
            ]),
            'user' => $this->whenLoaded('user', fn () => $this->user
                ? new UserResource($this->user)
                : null),
            'zone' => $this->whenLoaded('zone', fn () => [
                'id' => $this->zone?->id,
                'name' => $this->zone?->name,
            ]),
            'address' => $address ? [
                'address1' => $address->address1,
                'address2' => $address->address2,
                'street' => $address->street,
                'soi' => $address->soi,
                'moo' => $address->moo,
                'district' => $address->district,
                'amphur' => $address->amphur,
                'province' => $address->province,
                'zipcode' => $address->zipcode,
            ] : null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'price_amount' => $this->price_amounnt,
            'price_amount_lower' => $this->price_amounnt_lower,
            'price_per_wah' => $this->price_per_wah,
            'price_rent' => $this->price_rent,
            'listing' => [
                'sale' => $this->issale === 'Y',
                'rent' => $this->isrent === 'Y',
                'sale_rent' => $this->issalerent === 'Y',
                'sellout' => $this->issellout === 'Y',
                'sale_down' => $this->issaledown === 'Y',
            ],
            'area' => [
                'rai' => $this->area_rai,
                'ngan' => $this->area_ngan,
                'wah' => $this->area_wah,
                'meter' => $this->area_meter,
                'width' => $this->area_width,
                'long' => $this->area_long,
            ],
            'rooms' => [
                'floor_total' => $this->floor_total,
                'floor' => $this->floor,
                'bedroom' => $this->bedroom,
                'bathroom' => $this->bathroom,
                'kitchen' => $this->kitchen_room,
                'reception' => $this->reception_room,
                'dining' => $this->dining_room,
                'maid' => $this->maid_room,
                'parking' => $this->parking,
            ],
            'flags' => [
                'is_recommend' => $this->isrecommend === 'Y',
                'is_active' => ($this->isactive ?? 'Y') === 'Y',
                'is_covering' => $this->iscovering === 'Y',
                'is_dweller' => $this->isdweller === 'Y',
                'special_market_price' => $this->isspecial_marketprice === 'Y',
                'special_appraised' => $this->isspecial_appraised === 'Y',
            ],
            'direction' => $this->direction,
            'youtube_link' => $this->youtube_link,
            'view_count' => $this->view_count,
            'images' => PropertyImageResource::collection($this->whenLoaded('asset_images')),
            'created_at' => $createdAt?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
