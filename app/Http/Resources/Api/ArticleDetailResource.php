<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\Concerns\FormatsApiImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Article */
class ArticleDetailResource extends JsonResource
{
    use FormatsApiImageUrls;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $updatedAt = $this->updated ?? $this->updated_at;
        $createdAt = $this->created ?? $this->created_at;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'text' => $this->text,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'cover_image_url' => $this->apiImageUrl($this->cover_image_url),
            'seq' => $this->seq,
            'is_global' => $this->isVisibleToAllAgents(),
            'agent' => $this->when(
                ! $this->isVisibleToAllAgents() && $this->relationLoaded('agent'),
                fn () => [
                    'id' => $this->agent?->id,
                    'name' => $this->agent?->name,
                    'code' => $this->agent?->code,
                ],
            ),
            'created_at' => $createdAt?->toIso8601String(),
            'updated_at' => $updatedAt?->toIso8601String(),
        ];
    }
}
