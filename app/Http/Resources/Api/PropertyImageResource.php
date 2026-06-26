<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\Concerns\FormatsApiImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AssetImage */
class PropertyImageResource extends JsonResource
{
    use FormatsApiImageUrls;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->apiImageUrl($this->image?->galleryUrl(absolute: false)),
            'is_default' => $this->isdefault === 'Y',
            'seq' => $this->seq,
        ];
    }
}
