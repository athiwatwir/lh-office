<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyIndexRequest;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Zone;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PropertyIndexRequest $request): View
    {
        $filters = $request->filters();

        $data = Asset::query()
            ->with(['asset_type', 'zone', 'user'])
            ->filtered($filters)
            ->latestFirst()
            ->paginate(20)
            ->withQueryString();

        return view('pages.property.index', [
            'title' => 'รายการทรัพย์สิน',
            'data' => $data,
            'filters' => $filters,
            'hasFilter' => $request->hasFilter(),
            'assetTypes' => AssetType::query()->orderBy('seq')->orderBy('name')->get(['id', 'name']),
            'zones' => Zone::query()->orderBy('name')->get(['id', 'name']),
            'agents' => User::query()
                ->with([
                    'useimages' => fn ($query) => $query->latest('created')->limit(1),
                    'useimages.image',
                ])
                ->where('isseller', 'Y')
                ->orderBy('firstname')
                ->orderBy('lastname')
                ->get(['id', 'firstname', 'lastname', 'usercode']),
        ]);
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
        //
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
        //
    }
}
