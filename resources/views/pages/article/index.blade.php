@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        ทั้งหมด {{ number_format($data->count()) }} รายการ · ลากแถวเพื่อเรียงลำดับการแสดงผล
                    </p>
                </div>

                <a href="{{ route('article.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    เพิ่มบทความ
                </a>
            </div>

            <form method="GET" action="{{ route('article.index') }}" class="border-b border-gray-200 bg-gray-50 px-5 py-4 sm:px-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label for="q" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ค้นหาหัวข้อ</label>
                        <input id="q" type="text" name="q" value="{{ $filters['q'] }}" placeholder="พิมพ์ชื่อบทความ..." class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10" />
                    </div>
                    <div>
                        <label for="category_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ประเภทบทความ</label>
                        <select id="category_id" name="category_id" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10">
                            <option value="">ทั้งหมด</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($filters['category_id'] === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="isactive" class="mb-1.5 block text-theme-sm font-medium text-gray-700">สถานะ</label>
                        <select id="isactive" name="isactive" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10">
                            <option value="">ทั้งหมด</option>
                            <option value="Y" @selected($filters['isactive'] === 'Y')>เปิดใช้งาน</option>
                            <option value="N" @selected($filters['isactive'] === 'N')>ปิดใช้งาน</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                        ค้นหา
                    </button>
                    <a href="{{ route('article.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50">
                        ล้างเงื่อนไข
                    </a>
                </div>
            </form>

            @if ($data->isEmpty())
                <div class="px-5 py-16 text-center sm:px-6">
                    <p class="text-base font-medium text-gray-800">ไม่พบบทความ</p>
                    <p class="mt-1 text-sm text-gray-500">ลองปรับเงื่อนไขค้นหา หรือเพิ่มบทความใหม่</p>
                </div>
            @else
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-y border-gray-100">
                                <th class="w-10 px-3 py-3 text-start font-normal sm:px-4"><span class="sr-only">ลากเรียงลำดับ</span></th>
                                <th class="w-16 px-3 py-3 text-start font-normal sm:px-4"><span class="text-theme-sm text-gray-500">ลำดับ</span></th>
                                <th class="w-20 px-3 py-3 text-start font-normal sm:px-4"><span class="text-theme-sm text-gray-500">ปก</span></th>
                                <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">หัวข้อบทความ</span></th>
                                <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">ประเภท</span></th>
                                <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">Agent</span></th>
                                <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">สถานะ</span></th>
                                <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">อัปเดตล่าสุด</span></th>
                                <th class="px-4 py-3 text-end font-normal"><span class="text-theme-sm text-gray-500">จัดการ</span></th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-100"
                            x-data="articleSortable({
                                reorderUrl: @js(route('article.reorder', [], false)),
                                csrf: document.querySelector('meta[name=csrf-token]')?.content ?? '',
                            })"
                            x-ref="tbody"
                            @dragover.prevent="onDragOver($event)"
                            @drop.prevent="onDrop($event)"
                        >
                            @foreach ($data as $item)
                                @php $updatedAt = $item->updated ?? $item->updated_at ?? $item->created; @endphp
                                <tr class="hover:bg-gray-50/60" data-article-id="{{ $item->id }}">
                                    <td class="px-3 py-4 sm:px-4">
                                        <span
                                            data-drag-handle
                                            draggable="true"
                                            class="flex h-9 w-9 cursor-grab items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 active:cursor-grabbing"
                                            title="ลากเพื่อเรียงลำดับ"
                                            @dragstart.stop="onDragStart($event)"
                                            @dragend="onDragEnd()"
                                        >
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M7.22 5.22a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1-1.06 1.06L8 6.56 6.78 7.78a.75.75 0 0 1-1.06-1.06l2.25-2.25Zm-1.06 4.5a.75.75 0 0 1 1.06 0L8 12.56l1.22-1.22a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 1.06-1.06l1.22 1.22 1.22-1.22Zm4.5-1.06a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1-1.06 1.06L12 9.56l-1.22 1.22a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l-1.22 1.22 1.22 1.22Zm-1.06 4.5a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1-1.06 1.06L12 14.56l-1.22 1.22a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l-1.22 1.22 1.22-1.22Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 sm:px-4"><span data-seq-value class="text-theme-sm font-medium text-gray-500">{{ $item->seq ?? '—' }}</span></td>
                                    <td class="px-3 py-4 sm:px-4">
                                        <div class="flex h-12 w-16 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                            @if ($item->cover_image_url)
                                                <img src="{{ $item->cover_image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                            @else
                                                <span class="text-theme-xs text-gray-400">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="max-w-md px-4 py-4">
                                        <a href="{{ route('article.show', $item) }}" class="line-clamp-2 text-theme-sm font-medium text-brand-600 hover:text-brand-700 hover:underline">
                                            {{ $item->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-4"><span class="text-theme-sm text-gray-700">{{ $item->category?->name ?? '-' }}</span></td>
                                    <td class="px-4 py-4">
                                        @if ($item->isVisibleToAllAgents())
                                            <span class="text-theme-sm text-gray-700">ทุก Agent</span>
                                        @else
                                            <span class="text-theme-sm text-gray-700">{{ $item->agent?->name ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @if (($item->isactive ?? 'Y') === 'Y')
                                            <span class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600">เปิดใช้งาน</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600">ปิดใช้งาน</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4"><x-ui.date-time-display :datetime="$updatedAt" /></td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('article.edit', $item) }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50">แก้ไข</a>
                                            <form method="POST" action="{{ route('article.destroy', $item) }}" onsubmit="return confirm('ยืนยันการลบบทความนี้?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg border border-error-200 bg-error-50 px-3 text-theme-sm font-medium text-error-600 transition hover:bg-error-100">ลบ</button>
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
    </div>
@endsection
