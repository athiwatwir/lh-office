<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Zone;
use App\Support\TagNameParser;

class TagImportFromZonesService
{
    /**
     * @return array{created: int, skipped: int, total: int}
     */
    public function import(): array
    {
        $names = [];

        Zone::query()
            ->whereNotNull('description')
            ->orderBy('name')
            ->each(function (Zone $zone) use (&$names): void {
                foreach (TagNameParser::parse($zone->description ?? '') as $name) {
                    $names[] = $name;
                }
            });

        $names = array_values(array_unique($names));

        if ($names === []) {
            return ['created' => 0, 'skipped' => 0, 'total' => 0];
        }

        $existing = Tag::query()
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();

        $existingLookup = array_fill_keys($existing, true);
        $created = 0;

        foreach ($names as $name) {
            if (isset($existingLookup[$name])) {
                continue;
            }

            Tag::query()->create(['name' => $name]);
            $existingLookup[$name] = true;
            $created++;
        }

        return [
            'created' => $created,
            'skipped' => count($names) - $created,
            'total' => count($names),
        ];
    }
}
