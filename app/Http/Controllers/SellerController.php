<?php

namespace App\Http\Controllers;

use App\Http\Requests\SellerReorderRequest;
use App\Http\Requests\SellerRequest;
use App\Models\User;
use App\Services\UserImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SellerController extends Controller
{
    public function __construct(
        private readonly UserImageService $userImage,
    ) {}

    public function index(): View
    {
        $data = User::query()
            ->sellers()
            ->with('image')
            ->withCount('assets')
            ->orderByRaw('seq IS NULL')
            ->orderBy('seq')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('pages.seller.index', [
            'title' => 'ตัวแทนขาย',
            'data' => $data,
        ]);
    }

    public function create(): View
    {
        return view('pages.seller.create', [
            'title' => 'เพิ่มตัวแทนขาย',
            'item' => new User([
                'isseller' => 'Y',
                'isactive' => 'N',
            ]),
        ]);
    }

    public function store(SellerRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            ...$request->profileData(),
            'isverify' => 'Y',
            'created' => now(),
            'updated' => now(),
            'seq' => (int) User::query()->sellers()->max('seq') + 10,
        ]);

        if ($request->hasFile('pic')) {
            $this->userImage->attach($user, $request->file('pic'));
        }

        return redirect()
            ->route('seller.index')
            ->with('success', 'เพิ่มตัวแทนขายเรียบร้อยแล้ว');
    }

    public function edit(string $seller): View
    {
        $item = User::query()
            ->sellers()
            ->with('image')
            ->withCount('assets')
            ->findOrFail($seller);

        return view('pages.seller.edit', [
            'title' => 'แก้ไขตัวแทนขาย',
            'item' => $item,
        ]);
    }

    public function update(SellerRequest $request, string $seller): RedirectResponse
    {
        $item = User::query()->sellers()->findOrFail($seller);

        $item->update([
            ...$request->profileData(),
            'updated' => now(),
        ]);

        if ($request->hasFile('pic')) {
            $this->userImage->replace($item, $request->file('pic'));
        }

        return redirect()
            ->route('seller.index')
            ->with('success', 'บันทึกข้อมูลตัวแทนขายเรียบร้อยแล้ว');
    }

    public function reorder(SellerReorderRequest $request): JsonResponse
    {
        foreach ($request->validated('order') as $index => $sellerId) {
            User::query()
                ->sellers()
                ->whereKey($sellerId)
                ->update(['seq' => ($index + 1) * 10]);
        }

        return response()->json([
            'message' => 'บันทึกลำดับเรียบร้อยแล้ว',
        ]);
    }

    public function destroy(string $seller): RedirectResponse
    {
        $item = User::query()->sellers()->findOrFail($seller);

        if ($item->isInUse()) {
            return redirect()
                ->route('seller.index')
                ->with('error', 'ไม่สามารถลบได้ เนื่องจากมีทรัพย์สินที่ผูกกับตัวแทนนี้อยู่');
        }

        $this->userImage->deleteLocalProfileImage($item);
        $item->delete();

        return redirect()
            ->route('seller.index')
            ->with('success', 'ลบตัวแทนขายเรียบร้อยแล้ว');
    }
}
