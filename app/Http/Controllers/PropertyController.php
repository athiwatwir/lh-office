<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyCheckCodeRequest;
use App\Http\Requests\PropertyIndexRequest;
use App\Http\Requests\PropertyIsactiveRequest;
use App\Http\Requests\PropertyIsrecommendRequest;
use App\Http\Requests\PropertyTransferAgentRequest;
use App\Http\Requests\PropertyRequest;
use App\Models\Agent;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\User;
use App\Models\Zone;
use App\Services\ActiveAgentService;
use App\Services\PropertyAddressService;
use App\Services\PropertyDeletionService;
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
        $activeAgentId = $this->activeAgent->id();

        $data = Asset::query()
            ->forList()
            ->withCount('asset_images')
            ->with([
                'asset_type:id,name',
                'zone:id,name',
                'user:id,firstname,lastname',
            ])
            ->when(
                $activeAgentId,
                fn($query) => $query->where('agent_id', $activeAgentId),
                fn($query) => $query->whereRaw('0 = 1'),
            )
            ->filtered($filters)
            ->latestFirst()
            ->paginate(20)
            ->withQueryString();

        return view('pages.property.index', [
            'title' => $filters['recommend'] ? 'ทรัพย์แนะนำ' : 'รายการทรัพย์สิน',
            'data' => $data,
            'filters' => $filters,
            'hasFilter' => $request->hasFilter(),
            'officeAgents' => Agent::query()->orderBy('name')->get(['id', 'name', 'code']),
            ...$this->formOptions(withAgentPhotos: false),
        ]);
    }

    public function show(string $property): View
    {
        $activeAgentId = $this->activeAgent->id();

        $item = Asset::query()
            ->with(['asset_type', 'zone', 'user', 'address', 'agent', 'asset_images.image'])
            ->when(
                $activeAgentId,
                fn($query) => $query->where('agent_id', $activeAgentId),
                fn($query) => $query->whereRaw('0 = 1'),
            )
            ->findOrFail($property);

        return view('pages.property.partials.detail', compact('item'));
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
        if (! $this->activeAgent->hasAgent()) {
            return back()
                ->withInput()
                ->withErrors(['agent' => 'กรุณาเลือกเอเจนต์ที่ใช้งานก่อนเพิ่มทรัพย์สิน']);
        }

        $addressId = $this->addressService->sync($request->addressData());

        $item = Asset::query()->create([
            ...$request->assetData(),
            'address_id' => $addressId,
            'agent_id' => $this->activeAgent->id(),
            'created' => now(),
            'createdby' => Auth::id(),
            'isactive' => 'Y',
        ]);

        return redirect()
            ->route('property.edit', $item)
            ->with('success', 'เพิ่มทรัพย์สินเรียบร้อยแล้ว สามารถอัปโหลดรูปภาพได้ด้านล่าง');
    }

    public function edit(string $property): View
    {
        $item = Asset::query()
            ->with(['asset_type', 'zone', 'user', 'address', 'agent', 'asset_images.image'])
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
            ->route('property.edit', $item)
            ->with('success', 'บันทึกทรัพย์สินเรียบร้อยแล้ว');
    }

    public function checkCode(PropertyCheckCodeRequest $request): JsonResponse
    {
        $excludeId = $request->excludeId();
        $agentId = $excludeId
            ? Asset::query()->whereKey($excludeId)->value('agent_id')
            : $this->activeAgent->id();

        if ($agentId === null) {
            return response()->json([
                'available' => false,
                'message' => 'กรุณาเลือกเอเจนต์ที่ใช้งานก่อนตรวจสอบรหัส',
            ]);
        }

        $exists = Asset::query()
            ->where('agent_id', $agentId)
            ->where('code', $request->code())
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();

        $agentName = Agent::query()->whereKey($agentId)->value('name');

        return response()->json([
            'available' => ! $exists,
            'agent_name' => $agentName,
            'message' => $exists
                ? 'รหัสทรัพย์นี้ถูกใช้แล้วในเอเจนต์นี้'
                : 'รหัสทรัพย์นี้ใช้ได้',
        ]);
    }

    public function updateIsrecommend(PropertyIsrecommendRequest $request, string $property): JsonResponse
    {
        $item = Asset::query()->findOrFail($property);
        $isrecommend = $request->isrecommendValue();

        $item->update(['isrecommend' => $isrecommend]);

        return response()->json([
            'message' => $isrecommend === 'Y'
                ? 'ตั้งเป็นทรัพย์แนะนำเรียบร้อยแล้ว'
                : 'ยกเลิกทรัพย์แนะนำเรียบร้อยแล้ว',
            'isrecommend' => $isrecommend,
        ]);
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

    public function transferAgent(PropertyTransferAgentRequest $request, string $property): JsonResponse
    {
        $item = Asset::query()->findOrFail($property);
        $activeAgentId = $this->activeAgent->id();
        $targetAgentId = $request->validated('agent_id');

        if ($activeAgentId && $item->agent_id !== $activeAgentId) {
            return response()->json([
                'message' => 'ไม่สามารถย้ายทรัพย์สินที่ไม่อยู่ในเอเจนต์ปัจจุบันได้',
            ], 403);
        }

        if ($item->agent_id === $targetAgentId) {
            return response()->json([
                'message' => 'ทรัพย์สินอยู่ในเอเจนต์นี้อยู่แล้ว',
            ], 422);
        }

        $item->update(['agent_id' => $targetAgentId]);

        return response()->json([
            'message' => 'ย้ายทรัพย์สินไปยังเอเจนต์ใหม่เรียบร้อยแล้ว',
            'agent_id' => $targetAgentId,
        ]);
    }

    public function destroy(string $property): RedirectResponse|JsonResponse
    {
        $item = Asset::query()->findOrFail($property);

        app(PropertyDeletionService::class)->permanentlyDelete($item);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'ลบทรัพย์สินถาวรเรียบร้อยแล้ว',
            ]);
        }

        return redirect()
            ->route('property.index')
            ->with('success', 'ลบทรัพย์สินถาวรเรียบร้อยแล้ว');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(bool $withAgentPhotos = true): array
    {
        $agentsQuery = User::query()
            ->where('isseller', 'Y')
            ->orderByRaw('seq IS NULL')
            ->orderBy('seq')
            ->orderBy('firstname')
            ->orderBy('lastname');

        if ($withAgentPhotos) {
            $agentsQuery->with('image');
        }

        return [
            'assetTypes' => AssetType::query()->orderBy('seq')->orderBy('name')->get(['id', 'name']),
            'zones' => Zone::query()->orderBy('name')->get(['id', 'name']),
            'agents' => $agentsQuery->get(['id', 'firstname', 'lastname', 'usercode']),
            'activeAgent' => $this->activeAgent->agent(),
        ];
    }
}
