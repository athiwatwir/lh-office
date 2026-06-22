<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\CustomerAsset;
use App\Models\User;
use App\Services\ActiveAgentService;
use App\Services\AssetViewRankingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(ActiveAgentService $activeAgent): View
    {
        return view('pages.dashboard.index', [
            'title' => 'Dashboard',
            'activeAgent' => $activeAgent->agent(),
        ]);
    }

    public function summary(ActiveAgentService $activeAgent): JsonResponse
    {
        $agent = $activeAgent->agent();

        if ($agent === null) {
            return response()->json([
                'stats' => [
                    'total' => 0,
                    'active' => 0,
                    'inactive' => 0,
                ],
                'top_sellers' => [],
                'unread_sell_requests' => [],
            ]);
        }

        $counts = Asset::query()
            ->where('agent_id', $agent->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN COALESCE(isactive, 'Y') = 'Y' THEN 1 ELSE 0 END) as active_count")
            ->first();

        $total = (int) ($counts->total ?? 0);
        $active = (int) ($counts->active_count ?? 0);

        $topSellers = User::query()
            ->where('isseller', 'Y')
            ->whereHas('assets', fn ($query) => $query->where('agent_id', $agent->id))
            ->withCount(['assets as assets_count' => fn ($query) => $query->where('agent_id', $agent->id)])
            ->with('image')
            ->orderByDesc('assets_count')
            ->limit(5)
            ->get(['id', 'firstname', 'lastname', 'usercode'])
            ->values()
            ->map(fn (User $user, int $index) => [
                'rank' => $index + 1,
                'id' => $user->id,
                'name' => trim($user->firstname.' '.$user->lastname),
                'usercode' => $user->usercode,
                'photo_url' => $user->profile_image_url,
                'initial' => mb_substr($user->firstname, 0, 1),
                'assets_count' => (int) $user->assets_count,
            ]);

        $unreadSellRequests = CustomerAsset::query()
            ->with([
                'customer:id,fullname,tel',
                'asset_type:id,name',
                'zone:id,name',
            ])
            ->where('type', 'S')
            ->where('agent_id', $agent->id)
            ->where('isread', 'N')
            ->orderByDesc('created')
            ->limit(10)
            ->get()
            ->map(fn (CustomerAsset $item) => [
                'id' => $item->id,
                'customer_name' => $item->customer?->fullname,
                'customer_tel' => $item->customer?->tel,
                'asset_type' => $item->asset_type?->name ?? $item->asset_type_des,
                'zone' => $item->zone?->name,
                'price' => $item->price_amounnt !== null
                    ? number_format($item->price_amounnt)
                    : null,
                'created' => optional($item->created)->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'stats' => [
                'total' => $total,
                'active' => $active,
                'inactive' => max(0, $total - $active),
            ],
            'top_sellers' => $topSellers,
            'unread_sell_requests' => $unreadSellRequests,
        ]);
    }

    public function topViews(Request $request, ActiveAgentService $activeAgent, AssetViewRankingService $ranking): JsonResponse
    {
        $days = (int) $request->query('days', 7);
        $limit = (int) $request->query('limit', 5);
        $page = (int) $request->query('page', 1);

        return response()->json(
            $ranking->paginate($activeAgent->id(), $days, $limit, $page),
        );
    }
}
