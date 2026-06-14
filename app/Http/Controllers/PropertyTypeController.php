<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyTypeRequest;
use App\Models\AssetType;
use App\Services\ImageUploadOptions;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PropertyTypeController extends Controller
{
    private const PIC_DIRECTORY = AssetType::PIC_DIRECTORY;

    public function __construct(
        private readonly ImageUploadService $imageUpload,
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $data = AssetType::query()
            ->withCount(['assets', 'customer_assets'])
            ->orderBy('seq')
            ->orderBy('name')
            ->paginate(20);

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
        $nextSeq = (int) AssetType::query()->max('seq') + 10;

        return view('pages.property-type.create', [
            'title' => 'เพิ่มประเภททรัพย์สิน',
            'item' => new AssetType([
                'seq' => $nextSeq,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PropertyTypeRequest $request): RedirectResponse
    {
        AssetType::query()->create([
            'name' => $request->validated('name'),
            'seq' => $request->validated('seq'),
            'pic' => $this->resolveUploadedPic($request),
            'created' => now(),
            'breatedby' => Auth::id(),
        ]);

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
            ->withCount(['assets', 'customer_assets'])
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
        $item = AssetType::query()->findOrFail($propertyType);

        $data = [
            'name' => $request->validated('name'),
            'seq' => $request->validated('seq'),
        ];

        if ($request->hasFile('pic')) {
            $data['pic'] = $this->imageUpload->replace(
                $item->pic,
                $request->file('pic'),
                $this->picUploadOptions(),
            );
        }

        $item->update($data);

        return redirect()
            ->route('propertyType.index')
            ->with('success', 'บันทึกประเภททรัพย์สินเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $propertyType): RedirectResponse
    {
        $item = AssetType::query()->findOrFail($propertyType);

        if ($item->isInUse()) {
            return redirect()
                ->route('propertyType.index')
                ->with('error', 'ไม่สามารถลบได้ เนื่องจากมีข้อมูลทรัพย์สินหรือคำขอที่ใช้งานประเภทนี้อยู่');
        }

        $this->imageUpload->delete($item->pic, self::PIC_DIRECTORY);
        $item->delete();

        return redirect()
            ->route('propertyType.index')
            ->with('success', 'ลบประเภททรัพย์สินเรียบร้อยแล้ว');
    }

    private function resolveUploadedPic(PropertyTypeRequest $request): ?string
    {
        if (! $request->hasFile('pic')) {
            return null;
        }

        return $this->imageUpload->store(
            $request->file('pic'),
            $this->picUploadOptions(),
        );
    }

    private function picUploadOptions(): ImageUploadOptions
    {
        return new ImageUploadOptions(
            directory: self::PIC_DIRECTORY,
            quality: 85,
            maxWidth: 800,
            maxHeight: 800,
        );
    }
}
