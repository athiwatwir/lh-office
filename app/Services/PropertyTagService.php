<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Tag;
use App\Support\TagNameParser;

class PropertyTagService
{
    public function sync(Asset $asset, ?string $input): void
    {
        $names = TagNameParser::parse($input ?? '');
        $tagIds = [];

        foreach ($names as $name) {
            $tag = Tag::query()->firstOrCreate(['name' => $name]);
            $tagIds[] = $tag->id;
        }

        $asset->tags()->sync($tagIds);
    }

    /**
     * @return string
     */
    public static function namesToText(Asset $asset): string
    {
        if (! $asset->relationLoaded('tags')) {
            $asset->load('tags');
        }

        return $asset->tags
            ->pluck('name')
            ->implode(', ');
    }
}
