<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetViewsDaily;
use Illuminate\Support\Collection;

class AssetViewRankingService
{
    public const ALLOWED_DAYS = [1, 7, 30, 60];

    /**
     * @return array{
     *     days: int,
     *     start_date: string,
     *     data: list<array<string, mixed>>,
     *     meta: array{total: int, per_page: int, current_page: int, last_page: int}
     * }
     */
    public function paginate(?string $agentId, int $days, int $perPage = 5, int $page = 1): array
    {
        $days = $this->normalizeDays($days);
        $perPage = min(max($perPage, 1), 100);
        $page = max($page, 1);
        $startDate = now()->subDays($days - 1)->toDateString();

        if ($agentId === null) {
            return $this->emptyResult($days, $startDate, $perPage, $page);
        }

        $ranked = $this->rankedRows($agentId, $startDate);
        $total = $ranked->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $pageRows = $ranked
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return [
            'days' => $days,
            'start_date' => $startDate,
            'data' => $this->formatItems($pageRows, ($page - 1) * $perPage + 1),
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
            ],
        ];
    }

    private function rankedRows(string $agentId, string $startDate): Collection
    {
        $assetIds = Asset::query()
            ->where('agent_id', $agentId)
            ->active()
            ->pluck('id');

        if ($assetIds->isEmpty()) {
            return collect();
        }

        return AssetViewsDaily::query()
            ->selectRaw('asset_id, SUM(total_views) as period_views')
            ->whereIn('asset_id', $assetIds)
            ->whereDate('view_date', '>=', $startDate)
            ->groupBy('asset_id')
            ->orderByDesc('period_views')
            ->get();
    }

    /**
     * @param  Collection<int, object{asset_id: string, period_views: int|string}>  $rankedRows
     * @return list<array<string, mixed>>
     */
    private function formatItems(Collection $rankedRows, int $startRank = 1): array
    {
        if ($rankedRows->isEmpty()) {
            return [];
        }

        $assets = Asset::query()
            ->whereIn('id', $rankedRows->pluck('asset_id'))
            ->with([
                'asset_type:id,name',
                'asset_images' => fn ($query) => $query
                    ->orderByRaw("CASE WHEN isdefault = 'Y' THEN 0 ELSE 1 END")
                    ->orderBy('seq')
                    ->limit(1)
                    ->with('image'),
            ])
            ->get()
            ->keyBy('id');

        return $rankedRows
            ->map(function ($row, int $index) use ($assets, $startRank) {
                $asset = $assets->get($row->asset_id);

                if ($asset === null) {
                    return null;
                }

                $defaultImage = $asset->asset_images->first();

                return [
                    'rank' => $startRank + $index,
                    'id' => $asset->id,
                    'code' => $asset->code,
                    'name' => $asset->name,
                    'asset_type' => $asset->asset_type?->name ?? $asset->asset_type_des,
                    'period_views' => (int) $row->period_views,
                    'view_count' => (int) ($asset->view_count ?? 0),
                    'thumbnail_url' => $defaultImage?->image?->thumbnailUrl(),
                    'edit_url' => route('property.edit', $asset),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeDays(int $days): int
    {
        return in_array($days, self::ALLOWED_DAYS, true) ? $days : 7;
    }

    /**
     * @return array{
     *     days: int,
     *     start_date: string,
     *     data: list<array<string, mixed>>,
     *     meta: array{total: int, per_page: int, current_page: int, last_page: int}
     * }
     */
    private function emptyResult(int $days, string $startDate, int $perPage, int $page): array
    {
        return [
            'days' => $days,
            'start_date' => $startDate,
            'data' => [],
            'meta' => [
                'total' => 0,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => 1,
            ],
        ];
    }
}
