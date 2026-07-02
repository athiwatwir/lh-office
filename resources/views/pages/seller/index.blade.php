@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title" />

<div class="space-y-6">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                <p class="mt-1 text-sm text-gray-500">ทั้งหมด {{ number_format($data->count()) }} รายการ · ลากแถวเพื่อเรียงลำดับการแสดงผล</p>
            </div>

            <a href="{{ route('seller.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
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
                        <th class="w-10 px-3 py-3 text-start font-normal sm:px-4"><span class="sr-only">ลากเรียงลำดับ</span></th>
                        <th class="w-16 px-3 py-3 text-start font-normal sm:px-4"><span class="text-theme-sm text-gray-500">ลำดับ</span></th>
                        <th class="px-5 py-3 text-start font-normal sm:px-6"><span class="text-theme-sm text-gray-500">รูป</span></th>
                        <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">ชื่อ-นามสกุล</span></th>
                        <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">ตำแหน่ง</span></th>
                        <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">ติดต่อ</span></th>
                        <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">ทรัพย์สิน</span></th>
                        <th class="px-4 py-3 text-end font-normal"><span class="text-theme-sm text-gray-500">จัดการ</span></th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-gray-100"
                    x-data="userSortable({
                        reorderUrl: @js(route('seller.reorder', [], false)),
                        csrf: document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    })"
                    x-ref="tbody"
                    @dragover.prevent="onDragOver($event)"
                    @drop.prevent="onDrop($event)"
                >
                    @foreach ($data as $item)
                    <tr class="hover:bg-gray-50/60" data-user-id="{{ $item->id }}">
                        <td class="px-3 py-4 sm:px-4">
                            <span data-drag-handle draggable="true" class="flex h-9 w-9 cursor-grab items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 active:cursor-grabbing" title="ลากเพื่อเรียงลำดับ" @dragstart.stop="onDragStart($event)" @dragend="onDragEnd()">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.22 5.22a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1-1.06 1.06L8 6.56 6.78 7.78a.75.75 0 0 1-1.06-1.06l2.25-2.25Zm-1.06 4.5a.75.75 0 0 1 1.06 0L8 12.56l1.22-1.22a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 1.06-1.06l1.22 1.22 1.22-1.22Zm4.5-1.06a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1-1.06 1.06L12 9.56l-1.22 1.22a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l-1.22 1.22 1.22 1.22Zm-1.06 4.5a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1-1.06 1.06L12 14.56l-1.22 1.22a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l-1.22 1.22 1.22-1.22Z" clip-rule="evenodd" /></svg>
                            </span>
                        </td>
                        <td class="px-3 py-4 sm:px-4"><span data-seq-value class="text-theme-sm font-medium text-gray-500">{{ $item->seq ?? '—' }}</span></td>
                        <td class="px-5 py-4 sm:px-6">
                            <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-50">
                                @if ($item->profile_image_url)
                                <img src="{{ $item->profile_image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                @else
                                <span class="text-sm font-semibold text-gray-400">{{ mb_substr($item->firstname, 0, 1) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4"><p class="text-theme-sm font-medium text-gray-800">{{ $item->name }}</p></td>
                        <td class="px-4 py-4"><p class="text-theme-sm text-gray-700">{{ $item->position ?: '—' }}</p></td>
                        <td class="px-4 py-4">
                            <p class="text-theme-sm text-gray-700">{{ $item->email ?: '—' }}</p>
                            <p class="mt-0.5 text-theme-xs text-gray-500">{{ $item->phone ?: '—' }}</p>
                        </td>
                        <td class="px-4 py-4"><span class="text-theme-sm text-gray-700">{{ number_format($item->assets_count) }}</span></td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('seller.edit', $item) }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50">แก้ไข</a>
                                @if ($item->assets_count > 0)
                                <span class="inline-flex h-9 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-theme-sm font-medium text-gray-400" title="ไม่สามารถลบได้ เนื่องจากมีทรัพย์สินที่ผูกอยู่">ลบ</span>
                                @else
                                <form method="POST" action="{{ route('seller.destroy', $item) }}" onsubmit="return confirm('ยืนยันการลบตัวแทนขายนี้?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg border border-error-200 bg-error-50 px-3 text-theme-sm font-medium text-error-600 transition hover:bg-error-100">ลบ</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
