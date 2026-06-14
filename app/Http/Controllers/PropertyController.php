<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyIndexRequest;
use App\Http\Requests\PropertyIsactiveRequest;
use App\Http\Requests\PropertyRequest;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\User;
use App\Models\Zone;
use App\Services\ActiveAgentService;
use App\Services\PropertyAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function __construct(
        private readonly ActiveAgentService $activeAgent,
        private readonly PropertyAddressService $addressService,
    ) {}

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
            ...$this->formOptions(),
        ]);
    }

    public function create(): View
    {
        return view('pages.property.create', [
            'title' => 'เพิ่มทรัพย์สิน',
            'item' => new Asset([
                'issale' => 'Y',
                'isrent' => 'N',
                'issalerent' => 'N',
                'issellout' => 'N',
                'issaledown' => 'N',
                'iscovering' => 'N',
                'isdweller' => 'N',
                'isactive' => 'Y',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(PropertyRequest $request): RedirectResponse
    {
        $addressId = $this->addressService->sync($request->addressData());

        Asset::query()->create([
            ...$request->assetData(),
            'address_id' => $addressId,
            'agent_id' => $this->activeAgent->id(),
            'created' => now(),
            'createdby' => Auth::id(),
        ]);

        return redirect()
            ->route('property.index')
            ->with('success', 'เพิ่มทรัพย์สินเรียบร้อยแล้ว');
    }

    public function edit(string $property): View
    {
        $item = Asset::query()
            ->with(['asset_type', 'zone', 'user', 'address.province'])
            ->findOrFail($property);

        return view('pages.property.edit', [
            'title' => 'แก้ไขทรัพย์สิน',
            'item' => $item,
            ...$this->formOptions(),
        ]);
    }

    public function update(PropertyRequest $request, string $property): RedirectResponse
    {
        $item = Asset::query()->findOrFail($property);

        $addressId = $this->addressService->sync($request->addressData(), $item->address_id);

        $item->update([
            ...$request->assetData(),
            'address_id' => $addressId,
        ]);

        return redirect()
            ->route('property.index')
            ->with('success', 'บันทึกทรัพย์สินเรียบร้อยแล้ว');
    }

    public function updateIsactive(PropertyIsactiveRequest $request, string $property): JsonResponse
    {
        $item = Asset::query()->findOrFail($property);
        $isactive = $request->isactiveValue();

        $item->update(['isactive' => $isactive]);

        return response()->json([
            'message' => 'อัปเดตสถานะเรียบร้อยแล้ว',
            'isactive' => $isactive,
        ]);
    }

    public function destroy(string $property): RedirectResponse
    {
        $item = Asset::query()->findOrFail($property);
        $item->delete();

        return redirect()
            ->route('property.index')
            ->with('success', 'ลบทรัพย์สินเรียบร้อยแล้ว');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
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
        ];
    }
}
