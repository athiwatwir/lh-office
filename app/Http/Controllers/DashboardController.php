<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\ActiveAgentService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(ActiveAgentService $activeAgent): View
    {
        $activeAgentModel = $activeAgent->agent();
        $stats = [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
        ];

        if ($activeAgentModel !== null) {
            $counts = Asset::query()
                ->where('agent_id', $activeAgentModel->id)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN COALESCE(isactive, 'Y') = 'Y' THEN 1 ELSE 0 END) as active_count")
                ->first();

            $stats['total'] = (int) ($counts->total ?? 0);
            $stats['active'] = (int) ($counts->active_count ?? 0);
            $stats['inactive'] = max(0, $stats['total'] - $stats['active']);
        }

        return view('pages.dashboard.index', [
            'title' => 'Dashboard',
            'activeAgent' => $activeAgentModel,
            'stats' => $stats,
        ]);
    }
}
