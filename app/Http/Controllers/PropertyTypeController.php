<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyTypeReorderRequest;
use App\Http\Requests\PropertyTypeRequest;
use App\Models\AssetType;
use App\Services\ActiveAgentService;
use App\Services\AssetTypeImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PropertyTypeController extends Controller
{
    public function __construct(
        private readonly ActiveAgentService $activeAgent,
        private readonly AssetTypeImageService $assetTypeImage,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $activeAgentId = $this->activeAgent->id();

        $data = AssetType::query()
            ->with('image')
            ->withCount(['assets', 'customer_assets'])
            ->forAgent($activeAgentId)
            ->orderedForDisplay()
            ->get();

        return view('pages.property-type.index', [
            'title' => 'ประเภทของทรัพย์สิน',
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.property-type.create', [
            'title' => 'เพิ่มประเภททรัพย์สิน',
            'item' => new AssetType,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PropertyTypeRequest $request): RedirectResponse
    {
        if (! $this->activeAgent->hasAgent()) {
            return back()
                ->withInput()
                ->withErrors(['agent' => 'กรุณาเลือกเอเจนต์ที่ใช้งานก่อนเพิ่มประเภททรัพย์สิน']);
        }

        $activeAgentId = $this->activeAgent->id();
        $nextSeq = (int) AssetType::query()->forAgent($activeAgentId)->max('seq') + 10;

        $assetType = AssetType::query()->create([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'seq' => $nextSeq,
            'created' => now(),
            'breatedby' => Auth::id(),
            'agent_id' => $activeAgentId,
        ]);

        $this->storeCoverImage($request, $assetType);

        return redirect()
            ->route('propertyType.index')
            ->with('success', 'เพิ่มประเภททรัพย์สินเรียบร้อยแล้ว');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $propertyType): View
    {
        $item = AssetType::query()
            ->with('image')
            ->withCount(['assets', 'customer_assets'])
            ->forAgent($this->activeAgent->id())
            ->findOrFail($propertyType);

        return view('pages.property-type.edit', [
            'title' => 'แก้ไขประเภททรัพย์สิน',
            'item' => $item,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PropertyTypeRequest $request, string $propertyType): RedirectResponse
    {
        $item = AssetType::query()
            ->with('image')
            ->forAgent($this->activeAgent->id())
            ->findOrFail($propertyType);

        $item->update([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
        ]);

        $this->replaceCoverImage($request, $item);

        return redirect()
            ->route('propertyType.index')
            ->with('success', 'บันทึกประเภททรัพย์สินเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $propertyType): RedirectResponse
    {
        $item = AssetType::query()
            ->forAgent($this->activeAgent->id())
            ->findOrFail($propertyType);

        if ($item->isInUse()) {
            return redirect()
                ->route('propertyType.index')
                ->with('error', 'ไม่สามารถลบได้ เนื่องจากมีข้อมูลทรัพย์สินหรือคำขอที่ใช้งานประเภทนี้อยู่');
        }

        $this->assetTypeImage->deleteLocalImage($item);
        $item->delete();

        return redirect()
            ->route('propertyType.index')
            ->with('success', 'ลบประเภททรัพย์สินเรียบร้อยแล้ว');
    }

    public function reorder(PropertyTypeReorderRequest $request): JsonResponse
    {
        $activeAgentId = $this->activeAgent->id();

        foreach ($request->validated('order') as $index => $propertyTypeId) {
            AssetType::query()
                ->forAgent($activeAgentId)
                ->whereKey($propertyTypeId)
                ->update(['seq' => ($index + 1) * 10]);
        }

        return response()->json([
            'message' => 'บันทึกลำดับเรียบร้อยแล้ว',
        ]);
    }

    private function storeCoverImage(PropertyTypeRequest $request, AssetType $assetType): void
    {
        $file = $this->validatedCoverFile($request);

        if ($file === null) {
            return;
        }

        $this->assetTypeImage->attach($assetType, $file);
    }

    private function replaceCoverImage(PropertyTypeRequest $request, AssetType $assetType): void
    {
        $file = $this->validatedCoverFile($request);


        if ($file === null) {
            return;
        }

        $assetType->loadMissing('image');
        $this->assetTypeImage->replace($assetType, $file);
    }

    private function validatedCoverFile(PropertyTypeRequest $request): ?UploadedFile
    {
        $file = $request->file('pic');

        if ($file === null) {
            return null;
        }

        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'pic' => 'อัปโหลดรูปไม่สำเร็จ กรุณาลองไฟล์ที่เล็กลง (ไม่เกิน ' . ini_get('upload_max_filesize') . ')',
            ]);
        }

        return $file;
    }
}
