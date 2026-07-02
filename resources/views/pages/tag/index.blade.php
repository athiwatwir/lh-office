@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
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

                <a
                    href="{{ route('tag.create') }}"
                    class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50"
                >
                    เพิ่มทีละรายการ
                </a>
            </div>

            <div class="border-b border-gray-200 bg-gray-50/60 px-5 py-5 sm:px-6" x-data="{ open: true }">
                <button
                    type="button"
                    @click="open = !open"
                    class="flex w-full items-center justify-between gap-3 text-start"
                >
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800">เพิ่มแท็กหลายรายการ</h4>
                        <p class="mt-0.5 text-theme-xs text-gray-500">วางรายชื่อทีละบรรทัด หรือคั่นด้วย comma — ข้ามชื่อที่ซ้ำอัตโนมัติ</p>
                    </div>
                    <svg
                        class="h-5 w-5 shrink-0 text-gray-400 transition"
                        :class="open ? 'rotate-180' : ''"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>

                <form
                    x-show="open"
                    x-cloak
                    method="POST"
                    action="{{ route('tag.bulk-store') }}"
                    class="mt-4 space-y-4"
                >
                    @csrf
                    <div>
                        <label for="names" class="sr-only">รายชื่อแท็ก</label>
                        <textarea
                            id="names"
                            name="names"
                            rows="8"
                            required
                            placeholder="ใกล้ BTS อโศก&#10;ทำเลทอง&#10;ริมน้ำ, วิวเมือง&#10;..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 font-mono text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('names') border-error-500 @enderror"
                        >{{ old('names') }}</textarea>
                        @error('names')
                            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                        >
                            เพิ่มทั้งหมด
                        </button>
                        <p class="text-theme-xs text-gray-500">รองรับ copy จาก Excel / Google Sheets วางตรงๆ ได้</p>
                    </div>
                </form>

                <form
                    x-show="open"
                    x-cloak
                    method="POST"
                    action="{{ route('tag.import-from-zones') }}"
                    class="mt-3"
                    onsubmit="return confirm('นำเข้าแท็กจากรายชื่อใน description ของทุกโซน?')"
                >
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-brand-300 bg-brand-50 px-5 text-sm font-medium text-brand-700 shadow-theme-xs transition hover:bg-brand-100"
                    >
                        นำเข้าจากโซน (แยกจาก description คั่นด้วย comma)
                    </button>
                </form>
            </div>

            @if ($data->isEmpty())
                <div class="px-5 py-12 text-center sm:px-6">
                    <p class="text-base font-medium text-gray-800">ยังไม่มีแท็กในรายการ</p>
                    <p class="mt-1 text-sm text-gray-500">วางรายชื่อด้านบนแล้วกดเพิ่มทั้งหมด</p>
                </div>
            @else
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-y border-gray-100">
                                <th class="px-5 py-3 text-start font-normal sm:px-6">
                                    <span class="text-theme-sm text-gray-500">ชื่อแท็ก</span>
                                </th>
                                <th class="px-4 py-3 text-start font-normal">
                                    <span class="text-theme-sm text-gray-500">ทรัพย์สิน</span>
                                </th>
                                <th class="px-4 py-3 text-start font-normal">
                                    <span class="text-theme-sm text-gray-500">วันที่สร้าง</span>
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
                                        <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-theme-sm font-medium text-brand-700">
                                            {{ $item->name }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="text-theme-sm text-gray-700">{{ number_format($item->assets_count) }}</span>
                                        @if ($item->assets_count > 0)
                                            <span class="ms-2 inline-flex rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600">
                                                ใช้งานอยู่
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-ui.date-time-display :datetime="$item->created_at" />
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a
                                                href="{{ route('tag.edit', $item) }}"
                                                class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                            >
                                                แก้ไข
                                            </a>

                                            @if ($item->assets_count > 0)
                                                <span
                                                    class="inline-flex h-9 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-theme-sm font-medium text-gray-400"
                                                    title="ไม่สามารถลบได้ เนื่องจากมีทรัพย์สินที่ใช้แท็กนี้อยู่"
                                                >
                                                    ลบ
                                                </span>
                                            @else
                                                <form
                                                    method="POST"
                                                    action="{{ route('tag.destroy', $item) }}"
                                                    onsubmit="return confirm('ยืนยันการลบแท็กนี้?')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-error-200 bg-error-50 px-3 text-theme-sm font-medium text-error-600 transition hover:bg-error-100"
                                                    >
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
