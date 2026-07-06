<?php

namespace App\Http\Controllers;

use App\Models\CustomerAsset;
use App\Services\ActiveAgentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyRequestController extends Controller
{
    public function __construct(
        private readonly ActiveAgentService $activeAgent,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $type = $request->query('type', 'buy');
        $isSell = $type === 'sell';
        $activeAgentId = $this->activeAgent->id();

        $title = $isSell ? 'ฝากขายบ้าน-ที่ดิน' : 'ฝากหาบ้าน-ที่ดิน';

        $data = CustomerAsset::query()
            ->with(['customer', 'asset_type', 'zone'])
            ->when(
                $activeAgentId,
                fn ($query) => $query->where('agent_id', $activeAgentId),
                fn ($query) => $query->whereRaw('0 = 1'),
            )
            ->where('type', $isSell ? 'S' : 'P')
            ->orderByDesc('created')
            ->paginate(20)
            ->withQueryString();

        return view('pages.property-request.index', compact('title', 'type', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $activeAgentId = $this->activeAgent->id();

        $item = CustomerAsset::query()
            ->with(['customer', 'asset_type', 'zone', 'address'])
            ->when(
                $activeAgentId,
                fn ($query) => $query->where('agent_id', $activeAgentId),
                fn ($query) => $query->whereRaw('0 = 1'),
            )
            ->findOrFail($id);

        return view('pages.property-request.partials.detail', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $activeAgentId = $this->activeAgent->id();

        $item = CustomerAsset::query()
            ->when(
                $activeAgentId,
                fn ($query) => $query->where('agent_id', $activeAgentId),
                fn ($query) => $query->whereRaw('0 = 1'),
            )
            ->findOrFail($id);

        $type = $item->type === 'S' ? 'sell' : 'buy';
        CustomerAsset::query()
            ->whereKey($item->id)
            ->delete();

        return to_route('propertyRequest.index', ['type' => $type])
            ->with('success', 'ลบคำขอเรียบร้อยแล้ว');
    }
}
