<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetViewsDaily;
use Illuminate\Support\Facades\DB;

class AssetViewService
{
    /**
     * @return array{asset_id: string, view_date: string, total_views: int, view_count: int}
     */
    public function record(Asset $asset): array
    {
        $viewDate = now()->toDateString();

        return DB::transaction(function () use ($asset, $viewDate): array {
            $daily = AssetViewsDaily::query()
                ->where('asset_id', $asset->id)
                ->whereDate('view_date', $viewDate)
                ->lockForUpdate()
                ->first();

            if ($daily === null) {
                $daily = AssetViewsDaily::query()->create([
                    'asset_id' => $asset->id,
                    'view_date' => $viewDate,
                    'total_views' => 1,
                ]);
            } else {
                $daily->increment('total_views');
                $daily->refresh();
            }

            Asset::query()
                ->whereKey($asset->id)
                ->increment('view_count');

            $asset->refresh();

            return [
                'asset_id' => $asset->id,
                'view_date' => $viewDate,
                'total_views' => (int) $daily->total_views,
                'view_count' => (int) ($asset->view_count ?? 0),
            ];
        });
    }
}
