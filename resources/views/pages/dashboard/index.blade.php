@extends('layouts.app')

@section('content')
<div x-data="{
            loading: true,
            error: null,
            stats: { total: 0, active: 0, inactive: 0 },
            topSellers: [],
            unreadSellRequests: [],
            viewsDays: 7,
            topViews: [],
            viewsLoading: true,
            viewsError: null,
            detailOpen: false,
            detailLoading: false,
            detailHtml: '',
            async init() {
                await Promise.all([
                    this.loadSummary(),
                    this.loadTopViews(this.viewsDays),
                ]);
            },
            async loadSummary() {
                this.loading = true;
                this.error = null;

                try {
                    const response = await fetch('{{ route('dashboard.api.summary', [], false) }}', {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (! response.ok) {
                        throw new Error('Failed to load dashboard data');
                    }

                    const data = await response.json();
                    this.stats = data.stats ?? this.stats;
                    this.topSellers = data.top_sellers ?? [];
                    this.unreadSellRequests = data.unread_sell_requests ?? [];
                } catch (error) {
                    this.error = 'ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง';
                } finally {
                    this.loading = false;
                }
            },
            async loadTopViews(days) {
                this.viewsDays = days;
                this.viewsLoading = true;
                this.viewsError = null;

                try {
                    const response = await fetch(`{{ route('dashboard.api.top-views', [], false) }}?${new URLSearchParams({
                        days: String(days),
                        limit: '5',
                    }).toString()}`, {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (! response.ok) {
                        throw new Error('Failed to load top views');
                    }

                    const data = await response.json();
                    this.topViews = data.data ?? [];
                } catch (error) {
                    this.viewsError = 'ไม่สามารถโหลดอันดับยอดวิวได้';
                    this.topViews = [];
                } finally {
                    this.viewsLoading = false;
                }
            },
            viewsPeriodClass(days) {
                return this.viewsDays === days
                    ? 'bg-white text-gray-800 shadow-theme-xs'
                    : 'text-gray-500 hover:text-gray-700';
            },
            formatNumber(value) {
                return new Intl.NumberFormat('th-TH').format(value ?? 0);
            },
            formatDate(value) {
                if (! value) {
                    return '-';
                }

                return new Intl.DateTimeFormat('th-TH', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                }).format(new Date(value));
            },
            async openDetail(id) {
                this.detailOpen = true;
                this.detailLoading = true;
                this.detailHtml = '';
                document.body.style.overflow = 'hidden';

                try {
                    const response = await fetch(`{{ url('propertyRequest') }}/${id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                    });

                    if (! response.ok) {
                        throw new Error('Failed to load detail');
                    }

                    this.detailHtml = await response.text();
                    this.unreadSellRequests = this.unreadSellRequests.filter(
                        (item) => String(item.id) !== String(id),
                    );
                } catch (error) {
                    this.detailHtml = '<div class=\'p-8 text-center text-sm text-error-600\'>ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง</div>';
                } finally {
                    this.detailLoading = false;
                }
            },
            closeDetail() {
                this.detailOpen = false;
                this.detailHtml = '';
                document.body.style.overflow = 'unset';
            },
        }" class="min-w-0 space-y-6 overflow-x-hidden">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-500">
                    <i class="lni lni-dashboard-square-1 text-2xl" aria-hidden="true"></i>
                </span>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 sm:text-2xl">ยินดีต้อนรับ</h2>
                    <p class="mt-0.5 text-sm text-gray-500">
                        @if ($activeAgent)
                        กำลังใช้งาน: <span class="font-medium text-gray-700">{{ $activeAgent->name }}</span>
                        @else
                        กรุณาเลือกเอเจนต์ก่อนเริ่มใช้งานระบบ
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <template x-if="error">
        <div class="rounded-2xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-600" x-text="error"></div>
    </template>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <template x-if="loading">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="mt-3 h-8 w-16 animate-pulse rounded bg-gray-200"></div>
                    </div>
                    <div class="h-12 w-12 animate-pulse rounded-xl bg-gray-100"></div>
                </div>
            </template>
            <template x-if="! loading">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500">ทรัพย์สินทั้งหมด</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-800" x-text="formatNumber(stats.total)"></p>
                    </div>
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500">
                        <i class="lni lni-buildings-1 text-xl" aria-hidden="true"></i>
                    </span>
                </div>
            </template>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <template x-if="loading">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="mt-3 h-8 w-16 animate-pulse rounded bg-gray-200"></div>
                    </div>
                    <div class="h-12 w-12 animate-pulse rounded-xl bg-gray-100"></div>
                </div>
            </template>
            <template x-if="! loading">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500">เปิดใช้งาน</p>
                        <p class="mt-2 text-2xl font-semibold text-success-600" x-text="formatNumber(stats.active)"></p>
                    </div>
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600">
                        <i class="lni lni-check-circle-1 text-xl" aria-hidden="true"></i>
                    </span>
                </div>
            </template>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <template x-if="loading">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="mt-3 h-8 w-16 animate-pulse rounded bg-gray-200"></div>
                    </div>
                    <div class="h-12 w-12 animate-pulse rounded-xl bg-gray-100"></div>
                </div>
            </template>
            <template x-if="! loading">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500">ปิดใช้งาน</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-500" x-text="formatNumber(stats.inactive)"></p>
                    </div>
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-500">
                        <i class="lni lni-xmark-circle text-xl" aria-hidden="true"></i>
                    </span>
                </div>
            </template>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-warning-600">
                        <i class="lni lni-trophy-1 text-lg" aria-hidden="true"></i>
                    </span>
                    <h3 class="text-base font-semibold text-gray-800">ตัวแทนขายที่มีทรัพย์มากสุด 5 อันดับ</h3>
                </div>
                <a href="{{ route('seller.index') }}" class="inline-flex items-center gap-1 text-theme-xs font-medium text-brand-600 hover:text-brand-700">
                    ดูทั้งหมด
                    <i class="lni lni-arrow-right text-sm" aria-hidden="true"></i>
                </a>
            </div>

            <template x-if="loading">
                <div class="space-y-3">
                    <template x-for="i in 5" :key="i">
                        <div class="h-12 animate-pulse rounded-xl bg-gray-100"></div>
                    </template>
                </div>
            </template>

            <template x-if="! loading && topSellers.length === 0">
                <div class="py-10 text-center">
                    <span class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 text-gray-300">
                        <i class="lni lni-user-multiple-4 text-2xl" aria-hidden="true"></i>
                    </span>
                    <p class="text-sm text-gray-500">ไม่พบข้อมูลตัวแทนขาย</p>
                </div>
            </template>

            <div x-show="! loading && topSellers.length > 0" class="space-y-2">
                <template x-for="seller in topSellers" :key="seller.id">
                    <div class="flex items-center gap-3 rounded-xl border border-gray-100 px-4 py-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold" :class="{
                                    'bg-warning-50 text-warning-600': seller.rank === 1,
                                    'bg-brand-50 text-brand-600': seller.rank === 2,
                                    'bg-blue-light-50 text-blue-light-600': seller.rank === 3,
                                    'bg-gray-100 text-gray-600': seller.rank > 3,
                                }">
                            <i x-show="seller.rank === 1" class="lni lni-trophy-1 text-base" aria-hidden="true"></i>
                            <i x-show="seller.rank === 2" class="lni lni-star-fat text-base" aria-hidden="true"></i>
                            <i x-show="seller.rank === 3" class="lni lni-star-fat text-base" aria-hidden="true"></i>
                            <span x-show="seller.rank > 3" x-text="seller.rank"></span>
                        </span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-50">
                            <img x-show="seller.photo_url" :src="seller.photo_url" :alt="seller.name" class="h-full w-full object-cover">
                            <span x-show="! seller.photo_url" class="text-sm font-semibold text-gray-400" x-text="seller.initial"></span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-800" x-text="seller.name"></p>
                            <p class="text-theme-xs text-gray-500" x-show="seller.usercode" x-text="seller.usercode"></p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-gray-50 px-2.5 py-1 text-sm font-semibold text-gray-800">
                            <i class="lni lni-buildings-1 text-sm text-brand-500" aria-hidden="true"></i>
                            <span x-text="formatNumber(seller.assets_count)"></span>
                        </span>
                    </div>
                </template>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-error-50 text-error-600">
                        <i class="lni lni-bell-1 text-lg" aria-hidden="true"></i>
                        <span x-show="! loading && unreadSellRequests.length > 0" class="absolute -end-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-error-500 px-1 text-[10px] font-bold text-white" x-text="unreadSellRequests.length"></span>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">ฝากขายที่ยังไม่ได้อ่าน</h3>
                        <p class="mt-0.5 text-theme-xs text-gray-500">เฉพาะเอเจนต์ที่กำลังใช้งาน</p>
                    </div>
                </div>
                <a href="{{ route('propertyRequest.index', ['type' => 'sell']) }}" class="inline-flex items-center gap-1 text-theme-xs font-medium text-brand-600 hover:text-brand-700">
                    ดูทั้งหมด
                    <i class="lni lni-arrow-right text-sm" aria-hidden="true"></i>
                </a>
            </div>

            <template x-if="loading">
                <div class="space-y-3">
                    <template x-for="i in 4" :key="i">
                        <div class="h-16 animate-pulse rounded-xl bg-gray-100"></div>
                    </template>
                </div>
            </template>

            <template x-if="! loading && unreadSellRequests.length === 0">
                <div class="py-10 text-center">
                    <span class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-success-50 text-success-600">
                        <i class="lni lni-check-circle-1 text-2xl" aria-hidden="true"></i>
                    </span>
                    <p class="text-sm text-gray-500">ไม่มีรายการฝากขายที่ยังไม่ได้อ่าน</p>
                </div>
            </template>

            <div x-show="! loading && unreadSellRequests.length > 0" class="space-y-2">
                <template x-for="item in unreadSellRequests" :key="item.id">
                    <button type="button" @click="openDetail(item.id)" class="flex w-full items-start gap-3 rounded-xl border border-gray-100 px-4 py-3 text-left transition hover:border-brand-200 hover:bg-brand-50/40">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500">
                            <i class="lni lni-home-2 text-lg" aria-hidden="true"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-medium text-gray-800" x-text="item.customer_name || '-'"></p>
                                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600" x-text="item.asset_type || '-'"></span>
                            </div>
                            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-theme-xs text-gray-500">
                                <span class="inline-flex items-center gap-1">
                                    <i class="lni lni-map-marker-1 text-sm text-gray-400" aria-hidden="true"></i>
                                    <span x-text="item.zone || 'ไม่ระบุโซน'"></span>
                                </span>
                                <span x-show="item.price" class="inline-flex items-center gap-1">
                                    <i class="lni lni-wallet-1 text-sm text-gray-400" aria-hidden="true"></i>
                                    <span x-text="item.price + ' บาท'"></span>
                                </span>
                            </p>
                            <p class="mt-1 text-theme-xs text-gray-400" x-text="formatDate(item.created)"></p>
                        </div>
                        <i class="lni lni-arrow-right mt-1 shrink-0 text-sm text-gray-300" aria-hidden="true"></i>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500">
                    <i class="lni lni-bar-chart-4 text-lg" aria-hidden="true"></i>
                </span>
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-gray-800">ทรัพย์สินยอดวิวสูงสุด</h3>
                    <p class="mt-0.5 text-theme-xs text-gray-500">โหลดแยกหลังเปิดหน้าเพื่อความเร็ว</p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                    <template x-for="days in [1, 7, 30, 60]" :key="days">
                        <button
                            type="button"
                            @click="loadTopViews(days)"
                            class="rounded-md px-3 py-1.5 text-theme-xs font-medium transition"
                            :class="viewsPeriodClass(days)"
                            x-text="days + ' วัน'"
                        ></button>
                    </template>
                </div>
                <a href="{{ route('property.views.index') }}" class="inline-flex items-center gap-1 text-theme-xs font-medium text-brand-600 hover:text-brand-700">
                    ดูทั้งหมด
                    <i class="lni lni-arrow-right text-sm" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <template x-if="viewsError">
            <div class="border-b border-error-100 bg-error-50 px-5 py-4 text-sm text-error-600 sm:px-6" x-text="viewsError"></div>
        </template>

        <template x-if="viewsLoading">
            <div class="space-y-3 px-5 py-6 sm:px-6">
                <template x-for="i in 5" :key="i">
                    <div class="h-14 animate-pulse rounded-xl bg-gray-100"></div>
                </template>
            </div>
        </template>

        <template x-if="! viewsLoading && ! viewsError && topViews.length === 0">
            <div class="px-5 py-16 text-center sm:px-6">
                <span class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 text-gray-300">
                    <i class="lni lni-eye text-2xl" aria-hidden="true"></i>
                </span>
                <p class="text-sm text-gray-500">ไม่พบข้อมูลยอดวิวในช่วงเวลานี้</p>
            </div>
        </template>

        <div x-show="! viewsLoading && ! viewsError && topViews.length > 0" class="max-w-full overflow-x-auto custom-scrollbar">
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
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-start font-normal sm:px-6"><span class="text-theme-sm text-gray-500">อันดับ</span></th>
                        <th class="px-4 py-3 text-center font-normal"><span class="text-theme-sm text-gray-500">รูป</span></th>
                        <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">ชื่อทรัพย์สิน</span></th>
                        <th class="px-4 py-3 text-start font-normal"><span class="text-theme-sm text-gray-500">ประเภท</span></th>
                        <th class="px-4 py-3 text-end font-normal"><span class="text-theme-sm text-gray-500">ยอดวิวช่วงนี้</span></th>
                        <th class="px-4 py-3 text-end font-normal"><span class="text-theme-sm text-gray-500">ยอดรวมทั้งหมด</span></th>
                        <th class="px-4 py-3 text-end font-normal"><span class="text-theme-sm text-gray-500">จัดการ</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="item in topViews" :key="item.id">
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
    </div>

    <div x-show="detailOpen" x-cloak @keydown.escape.window="closeDetail()" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5">
        <div @click="closeDetail()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]"></div>

        <div @click.stop class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl bg-white shadow-theme-xl">
            <button @click="closeDetail()" type="button" class="absolute right-3 top-3 z-10 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill="currentColor" />
                </svg>
            </button>

            <div x-show="detailLoading" class="flex min-h-[320px] items-center justify-center p-8">
                <div class="text-center">
                    <div class="mx-auto mb-4 h-10 w-10 animate-pulse rounded-full bg-gray-200"></div>
                    <p class="text-sm text-gray-500">กำลังโหลดข้อมูล...</p>
                </div>
            </div>

            <div x-show="! detailLoading" x-html="detailHtml"></div>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

</style>
@endsection
