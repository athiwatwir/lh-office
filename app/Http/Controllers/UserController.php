<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserPasswordRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserImageService $userImage,
    ) {}

    public function index(): View
    {
        $data = User::query()
            ->with('image')
            ->withCount('assets')
            // ->where('isseller', 'Y')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->paginate(20);

        return view('pages.user.index', [
            'title' => 'ตัวแทนขาย/ผู้ใช้งานระบบ',
            'data' => $data,
        ]);
    }

    public function create(): View
    {
        return view('pages.user.create', [
            'title' => 'เพิ่มตัวแทนขาย/ผู้ใช้งานระบบ',
            'item' => new User([
                'isactive' => 'N',
                'isseller' => 'Y',
            ]),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $payload = [
            ...$request->profileData(),
            'isverify' => 'Y',
            'created' => now(),
            'updated' => now(),
        ];

        if ($request->input('isactive') === 'Y') {
            $payload['password'] = Hash::make($request->validated('password'));
        }

        $user = User::query()->create($payload);

        if ($request->hasFile('pic')) {
            $this->userImage->attach($user, $request->file('pic'));
        }

        return redirect()
            ->route('user.index')
            ->with('success', 'เพิ่มตัวแทนขาย/ผู้ใช้งานระบบเรียบร้อยแล้ว');
    }

    public function edit(string $user): View
    {
        $item = User::query()
            ->with('image')
            ->withCount('assets')
            ->findOrFail($user);

        return view('pages.user.edit', [
            'title' => 'แก้ไขตัวแทนขาย/ผู้ใช้งานระบบ',
            'item' => $item,
        ]);
    }

    public function update(UserRequest $request, string $user): RedirectResponse
    {
        $item = User::query()->findOrFail($user);

        $item->update([
            ...$request->profileData(),
            'updated' => now(),
        ]);

        if ($request->hasFile('pic')) {
            $this->userImage->replace($item, $request->file('pic'));
        }

        return redirect()
            ->route('user.index')
            ->with('success', 'บันทึกข้อมูลตัวแทนขาย/ผู้ใช้งานระบบเรียบร้อยแล้ว');
    }

    public function updatePassword(UserPasswordRequest $request, string $user): RedirectResponse
    {
        $item = User::query()->findOrFail($user);

        $item->update([
            'password' => Hash::make($request->validated('password')),
            'updated' => now(),
        ]);

        return redirect()
            ->route('user.edit', $item)
            ->with('success', 'ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว');
    }

    public function destroy(string $user): RedirectResponse
    {
        $item = User::query()->findOrFail($user);

        if ($item->id === Auth::id()) {
            return redirect()
                ->route('user.index')
                ->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        if ($item->isInUse()) {
            return redirect()
                ->route('user.index')
                ->with('error', 'ไม่สามารถลบได้ เนื่องจากมีทรัพย์สินที่ผูกกับตัวแทนนี้อยู่');
        }

        $this->userImage->deleteLocalProfileImage($item);
        $item->delete();

        return redirect()
            ->route('user.index')
            ->with('success', 'ลบตัวแทนขาย/ผู้ใช้งานระบบเรียบร้อยแล้ว');
    }
}
