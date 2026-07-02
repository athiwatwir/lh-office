<?php

namespace App\Http\Controllers;

use App\Http\Requests\SystemUserRequest;
use App\Http\Requests\UserPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SystemUserController extends Controller
{
    public function index(): View
    {
        $data = User::query()
            ->systemUsers()
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('pages.system-user.index', [
            'title' => 'ผู้ใช้งานระบบ',
            'data' => $data,
        ]);
    }

    public function create(): View
    {
        return view('pages.system-user.create', [
            'title' => 'เพิ่มผู้ใช้งานระบบ',
            'item' => new User([
                'isseller' => 'N',
                'isactive' => 'N',
            ]),
        ]);
    }

    public function store(SystemUserRequest $request): RedirectResponse
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

        User::query()->create($payload);

        return redirect()
            ->route('system-user.index')
            ->with('success', 'เพิ่มผู้ใช้งานระบบเรียบร้อยแล้ว');
    }

    public function edit(string $system_user): View
    {
        $item = User::query()
            ->systemUsers()
            ->findOrFail($system_user);

        return view('pages.system-user.edit', [
            'title' => 'แก้ไขผู้ใช้งานระบบ',
            'item' => $item,
        ]);
    }

    public function update(SystemUserRequest $request, string $system_user): RedirectResponse
    {
        $item = User::query()->systemUsers()->findOrFail($system_user);

        $item->update([
            ...$request->profileData(),
            'updated' => now(),
        ]);

        return redirect()
            ->route('system-user.index')
            ->with('success', 'บันทึกข้อมูลผู้ใช้งานระบบเรียบร้อยแล้ว');
    }

    public function updatePassword(UserPasswordRequest $request, string $system_user): RedirectResponse
    {
        $item = User::query()->systemUsers()->findOrFail($system_user);

        $item->update([
            'password' => Hash::make($request->validated('password')),
            'updated' => now(),
        ]);

        return redirect()
            ->route('system-user.edit', $item)
            ->with('success', 'ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว');
    }

    public function destroy(string $system_user): RedirectResponse
    {
        $item = User::query()->systemUsers()->findOrFail($system_user);

        if ($item->id === Auth::id()) {
            return redirect()
                ->route('system-user.index')
                ->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $item->delete();

        return redirect()
            ->route('system-user.index')
            ->with('success', 'ลบผู้ใช้งานระบบเรียบร้อยแล้ว');
    }
}
