<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CustomerAsset */
class CustomerAssetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $address = $this->address;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->type === 'S' ? 'ฝากขาย' : 'ฝากหา',
            'asset_type' => $this->whenLoaded('asset_type', fn () => [
                'id' => $this->asset_type?->id,
                'name' => $this->asset_type?->name ?? $this->asset_type_des,
            ]),
            'zone' => $this->whenLoaded('zone', fn () => [
                'id' => $this->zone?->id,
                'name' => $this->zone?->name,
                'description' => $this->zone?->description,
            ]),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer?->id,
                'fullname' => $this->customer?->fullname,
                'tel' => $this->customer?->tel,
                'email' => $this->customer?->email,
                'lineid' => $this->customer?->lineid,
            ]),
            'description' => $this->description,
            'asset_type_des' => $this->asset_type_des,
            'area' => [
                'rai' => $this->area_rai,
                'ngan' => $this->area_ngan,
                'wah' => $this->area_wah,
                'meter' => $this->area_meter,
            ],
            'rooms' => [
                'floor_total' => $this->floor_total,
                'bedroom' => $this->bedroom,
                'bathroom' => $this->bathroom,
                'kitchen' => $this->kitchen_room,
                'reception' => $this->reception_room,
                'dining' => $this->dining_room,
                'maid' => $this->maid_room,
                'parking' => $this->parking,
            ],
            'price_amount' => $this->price_amounnt,
            'price_per_wah' => $this->price_per_wah,
            'budgets' => $this->budgets,
            'is_consult_requested' => $this->isreqconsult === 'Y',
            'is_read' => $this->isread === 'Y',
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
                'description' => $address->description,
            ] : null,
            'images' => PropertyImageResource::collection($this->whenLoaded('assetImages')),
            'created_at' => $this->created?->toIso8601String(),
        ];
    }
}
