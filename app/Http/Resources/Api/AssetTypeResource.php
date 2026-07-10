<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\Concerns\FormatsApiImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AssetType */
class AssetTypeResource extends JsonResource
{
    use FormatsApiImageUrls;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'image_url' => $this->apiImageUrl($this->image?->galleryUrl(absolute: false)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
