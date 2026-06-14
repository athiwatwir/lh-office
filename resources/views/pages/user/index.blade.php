@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title" />

<div class="space-y-6">
    @if (session('success'))
    <x-ui.alert variant="success" :title="session('success')" />
    @endif

    @if (session('error'))
    <x-ui.alert variant="error" :title="session('error')" />
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    ทั้งหมด {{ number_format($data->total()) }} รายการ
                    @if ($data->total() > 0)
                    (แสดง {{ $data->firstItem() }}–{{ $data->lastItem() }})
                    @endif
                </p>
            </div>

            <a href="{{ route('user.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                เพิ่มตัวแทนขาย
            </a>
        </div>

        @if ($data->isEmpty())
        <div class="px-5 py-16 text-center sm:px-6">
            <p class="text-base font-medium text-gray-800">ยังไม่มีตัวแทนขาย</p>
            <p class="mt-1 text-sm text-gray-500">เริ่มต้นด้วยการเพิ่มตัวแทนขายใหม่</p>
        </div>
        @else
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="min-w-full">
                <thead>
                    <tr class="border-y border-gray-100">
                        <th class="px-5 py-3 text-start font-normal sm:px-6">
                            <span class="text-theme-sm text-gray-500">รูป</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ชื่อ-นามสกุล</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">รหัส / ตำแหน่ง</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ติดต่อ</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">เปิดให้ใช้งานระบบ</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ทรัพย์สิน</span>
                        </th>
                        <th class="px-4 py-3 text-end font-normal">
                            <span class="text-theme-sm text-gray-500">จัดการ</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($data as $item)
                    <tr class="hover:bg-gray-50/60">
                        <td class="px-5 py-4 sm:px-6">
                            <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-50">
                                @if ($item->profile_image_url)
                                <img src="{{ $item->profile_image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                @else
                                <span class="text-sm font-semibold text-gray-400">{{ mb_substr($item->firstname, 0, 1) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-theme-sm font-medium text-gray-800">{{ $item->name }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-theme-sm text-gray-700">{{ $item->usercode ?: '—' }}</p>
                            <p class="mt-0.5 text-theme-xs text-gray-500">{{ $item->position ?: '—' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-theme-sm text-gray-700">{{ $item->email }}</p>
                            <p class="mt-0.5 text-theme-xs text-gray-500">{{ $item->phone ?: '—' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            @if ($item->isActive())
                            <span class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600">ใช้งาน</span>
                            @else
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-500">ปิดใช้งาน</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-theme-sm text-gray-700">{{ number_format($item->assets_count) }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('user.edit', $item) }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50">
                                    แก้ไข
                                </a>

                                @if ($item->assets_count > 0 || $item->id === auth()->id())
                                <span class="inline-flex h-9 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-theme-sm font-medium text-gray-400" title="{{ $item->id === auth()->id() ? 'ไม่สามารถลบบัญชีของตัวเองได้' : 'ไม่สามารถลบได้ เนื่องจากมีทรัพย์สินที่ผูกอยู่' }}">
                                    ลบ
                                </span>
                                @else
                                <form method="POST" action="{{ route('user.destroy', $item) }}" onsubmit="return confirm('ยืนยันการลบตัวแทนขายนี้?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg border border-error-200 bg-error-50 px-3 text-theme-sm font-medium text-error-600 transition hover:bg-error-100">
                                        ลบ
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($data->hasPages())
        <div class="border-t border-gray-200 px-5 py-4 sm:px-6">
            {{ $data->links('vendor.pagination.tailadmin') }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
