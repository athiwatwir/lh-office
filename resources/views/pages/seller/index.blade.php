@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title" />

<div
    x-data="{
        deleteOpen: false,
        deleting: false,
        target: null,
        openDelete(payload) {
            this.target = payload;
            this.deleteOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeDelete() {
            if (this.deleting) return;
            this.deleteOpen = false;
            this.target = null;
            document.body.style.overflow = 'unset';
        },
        submitDelete() {
            if (! this.target?.formId || this.deleting) return;
            this.deleting = true;
            document.getElementById(this.target.formId)?.submit();
        },
    }"
    class="space-y-6"
>
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
                    @php
                        $deleteFormId = 'seller-delete-form-'.$item->id;
                    @endphp
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
                                <img src="{{ $item->profile_image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover" loading="lazy">
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
                                <button
                                    type="button"
                                    class="inline-flex h-9 items-center justify-center rounded-lg border border-error-200 bg-error-50 px-3 text-theme-sm font-medium text-error-600 transition hover:bg-error-100"
                                    @click="openDelete({
                                        formId: @js($deleteFormId),
                                        name: @js($item->name),
                                        assetsCount: {{ (int) $item->assets_count }},
                                        imagesCount: {{ (int) $item->asset_images_count }},
                                        hasProfileImage: {{ $item->image_id ? 'true' : 'false' }},
                                    })"
                                >
                                    ลบ
                                </button>
                                <form id="{{ $deleteFormId }}" method="POST" action="{{ route('seller.destroy', $item) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div x-show="deleteOpen" x-cloak @keydown.escape.window="closeDelete()" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5">
        <div @click="closeDelete()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]"></div>

        <div @click.stop class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-theme-xl">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-800">ยืนยันการลบตัวแทนขาย</h3>
                <p class="mt-1 text-theme-xs text-gray-500" x-text="target?.name || ''"></p>
            </div>

            <div class="space-y-4 px-5 py-4">
                <div class="rounded-xl border border-error-100 bg-error-50 px-4 py-3">
                    <p class="text-sm font-medium text-error-700">การลบนี้ไม่สามารถกู้คืนได้</p>
                    <p class="mt-1 text-theme-xs text-error-600">ระบบจะลบตัวแทนขายและข้อมูลที่ผูกอยู่ทั้งหมดออกจากระบบ</p>
                </div>

                <div>
                    <p class="mb-2 text-theme-sm font-medium text-gray-800">สิ่งที่จะถูกลบ</p>
                    <ul class="space-y-2 text-theme-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-error-500"></span>
                            <span>ข้อมูลตัวแทนขาย</span>
                        </li>
                        <li class="flex items-start gap-2" x-show="target?.hasProfileImage" x-cloak>
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-error-500"></span>
                            <span>รูปโปรไฟล์ของตัวแทนขาย</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-error-500"></span>
                            <span>
                                สินทรัพย์
                                <span class="font-semibold text-error-600" x-text="Number(target?.assetsCount || 0).toLocaleString('th-TH')"></span>
                                รายการ
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-error-500"></span>
                            <span>
                                รูปภาพของสินทรัพย์
                                <span class="font-semibold text-error-600" x-text="Number(target?.imagesCount || 0).toLocaleString('th-TH')"></span>
                                รูป
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-error-500"></span>
                            <span>แท็ก ตัวเลือก ที่อยู่ และสถิติการเข้าชมที่เกี่ยวข้อง</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4">
                <button type="button" @click="closeDelete()" :disabled="deleting" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50">
                    ยกเลิก
                </button>
                <button type="button" @click="submitDelete()" :disabled="deleting" class="inline-flex h-10 items-center justify-center rounded-lg bg-error-500 px-4 text-sm font-medium text-white transition hover:bg-error-600 disabled:opacity-50">
                    <span x-show="! deleting">ยืนยันลบทั้งหมด</span>
                    <span x-show="deleting" x-cloak>กำลังลบ...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endsection
