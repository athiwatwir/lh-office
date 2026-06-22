@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div
        x-data="{
            loading: true,
            error: null,
            days: (() => {
                const value = Number(new URLSearchParams(window.location.search).get('days'));
                return [1, 7, 30, 60].includes(value) ? value : 7;
            })(),
            items: [],
            meta: { total: 0, per_page: 20, current_page: 1, last_page: 1 },
            async init() {
                await this.load();
            },
            async load(page = 1) {
                this.loading = true;
                this.error = null;

                try {
                    const params = new URLSearchParams({
                        days: String(this.days),
                        limit: '20',
                        page: String(page),
                    });

                    const response = await fetch(`{{ route('dashboard.api.top-views', [], false) }}?${params.toString()}`, {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (! response.ok) {
                        throw new Error('Failed to load ranking');
                    }

                    const data = await response.json();
                    this.items = data.data ?? [];
                    this.meta = data.meta ?? this.meta;
                    this.days = data.days ?? this.days;

                    const url = new URL(window.location.href);
                    url.searchParams.set('days', String(this.days));
                    url.searchParams.set('page', String(this.meta.current_page));
                    window.history.replaceState({}, '', url);
                } catch (error) {
                    this.error = 'ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง';
                    this.items = [];
                } finally {
                    this.loading = false;
                }
            },
            async changeDays(days) {
                this.days = days;
                await this.load(1);
            },
            formatNumber(value) {
                return new Intl.NumberFormat('th-TH').format(value ?? 0);
            },
            periodClass(value) {
                return this.days === value
                    ? 'bg-white text-gray-800 shadow-theme-xs'
                    : 'text-gray-500 hover:text-gray-700';
            },
            pages() {
                const total = this.meta.last_page ?? 1;
                return Array.from({ length: total }, (_, index) => index + 1);
            },
        }"
        class="space-y-6"
    >
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if ($activeAgent)
                            เอเจนต์: <span class="font-medium text-gray-700">{{ $activeAgent->name }}</span>
                        @else
                            กรุณาเลือกเอเจนต์เพื่อดูอันดับยอดวิว
                        @endif
                    </p>
                </div>

                <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                    <template x-for="value in [1, 7, 30, 60]" :key="value">
                        <button
                            type="button"
                            @click="changeDays(value)"
                            class="rounded-md px-4 py-2 text-sm font-medium transition"
                            :class="periodClass(value)"
                            x-text="value + ' วัน'"
                        ></button>
                    </template>
                </div>
            </div>

            <template x-if="error">
                <div class="border-b border-error-100 bg-error-50 px-5 py-4 text-sm text-error-600 sm:px-6" x-text="error"></div>
            </template>

            <template x-if="loading">
                <div class="space-y-3 px-5 py-6 sm:px-6">
                    <template x-for="i in 8" :key="i">
                        <div class="h-14 animate-pulse rounded-xl bg-gray-100"></div>
                    </template>
                </div>
            </template>

            <template x-if="! loading && ! error && items.length === 0">
                <div class="px-5 py-16 text-center sm:px-6">
                    <p class="text-base font-medium text-gray-800">ไม่พบข้อมูลยอดวิว</p>
                    <p class="mt-1 text-sm text-gray-500">ลองเปลี่ยนช่วงเวลาที่ต้องการดู</p>
                </div>
            </template>

            <div x-show="! loading && ! error && items.length > 0" class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full table-fixed">
                    <colgroup>
                        <col class="w-16">
                        <col class="w-16">
                        <col>
                        <col class="w-32">
                        <col class="w-28">
                        <col class="w-28">
                        <col class="w-24">
                    </colgroup>
                    <thead>
                        <tr class="border-y border-gray-100">
                            <th class="w-16 px-5 py-3 text-start font-normal sm:px-6"><span class="text-theme-sm text-gray-500">อันดับ</span></th>
                            <th class="w-16 px-4 py-3 text-center font-normal"><span class="text-theme-sm text-gray-500">รูป</span></th>
                            <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">ชื่อทรัพย์สิน</span></th>
                            <th class="w-32 px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">ประเภท</span></th>
                            <th class="w-28 px-4 py-3 text-end font-normal"><span class="text-theme-sm text-gray-500">ยอดวิวช่วงนี้</span></th>
                            <th class="w-28 px-4 py-3 text-end font-normal"><span class="text-theme-sm text-gray-500">ยอดรวมทั้งหมด</span></th>
                            <th class="w-24 px-4 py-3 text-end font-normal"><span class="text-theme-sm text-gray-500">จัดการ</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-600" x-text="item.rank"></span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                        <img x-show="item.thumbnail_url" :src="item.thumbnail_url" :alt="item.name" class="h-full w-full object-cover">
                                        <i x-show="! item.thumbnail_url" class="lni lni-buildings-1 text-lg text-gray-300" aria-hidden="true"></i>
                                    </span>
                                </td>
                                <td class="overflow-hidden px-4 py-4">
                                    <p class="line-clamp-2 break-words text-theme-sm font-medium text-gray-800" x-text="item.name" :title="item.name"></p>
                                    <p class="mt-0.5 truncate text-theme-xs text-gray-500" x-text="item.code"></p>
                                </td>
                                <td class="overflow-hidden px-4 py-4">
                                    <span class="line-clamp-2 break-words text-theme-sm text-gray-700" x-text="item.asset_type || '-'" :title="item.asset_type || '-'"></span>
                                </td>
                                <td class="px-4 py-4 text-end">
                                    <span class="text-theme-sm font-semibold text-brand-600" x-text="formatNumber(item.period_views)"></span>
                                </td>
                                <td class="px-4 py-4 text-end">
                                    <span class="text-theme-sm text-gray-700" x-text="formatNumber(item.view_count)"></span>
                                </td>
                                <td class="px-4 py-4 text-end">
                                    <a :href="item.edit_url" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-50">
                                        แก้ไข
                                    </a>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="! loading && meta.last_page > 1" class="border-t border-gray-200 px-5 py-4 sm:px-6">
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <button
                        type="button"
                        @click="load(meta.current_page - 1)"
                        :disabled="meta.current_page <= 1"
                        class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-theme-sm font-medium text-gray-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        ก่อนหน้า
                    </button>
                    <template x-for="page in pages()" :key="page">
                        <button
                            type="button"
                            @click="load(page)"
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-theme-sm font-medium transition"
                            :class="meta.current_page === page ? 'bg-brand-500 text-white' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                            x-text="page"
                        ></button>
                    </template>
                    <button
                        type="button"
                        @click="load(meta.current_page + 1)"
                        :disabled="meta.current_page >= meta.last_page"
                        class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-theme-sm font-medium text-gray-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        ถัดไป
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
