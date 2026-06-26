@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title" />

<div x-data="{
        open: false,
        loading: false,
        detailHtml: '',
        init() {
            this.$watch('open', value => {
                document.body.style.overflow = value ? 'hidden' : 'unset';
            });
        },
        async openDetail(id) {
            this.open = true;
            this.loading = true;
            this.detailHtml = '';

            try {
                const response = await fetch(`{{ url('property') }}/${id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                });

                if (! response.ok) {
                    throw new Error('Failed to load detail');
                }

                this.detailHtml = await response.text();
            } catch (error) {
                this.detailHtml = '<div class=\'p-8 text-center text-sm text-error-600\'>ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง</div>';
            } finally {
                this.loading = false;
            }
        },
        closeDetail() {
            this.open = false;
            this.detailHtml = '';
        },
    }" class="space-y-6">
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

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                    <a href="{{ route('property.index', request()->except('page', 'recommend')) }}" @class([ 'inline-flex items-center gap-1.5 rounded-md px-4 py-2 text-sm font-medium transition' , 'bg-white text-gray-800 shadow-theme-xs'=> ! $filters['recommend'],
                        'text-gray-500 hover:text-gray-700' => $filters['recommend'],
                        ])
                        >
                        <i class="lni lni-buildings-1 text-base" aria-hidden="true"></i>
                        ทั้งหมด
                    </a>
                    <a href="{{ route('property.index', [...request()->except('page'), 'recommend' => 1]) }}" @class([ 'inline-flex items-center gap-1.5 rounded-md px-4 py-2 text-sm font-medium transition' , 'bg-white text-gray-800 shadow-theme-xs'=> $filters['recommend'],
                        'text-gray-500 hover:text-gray-700' => ! $filters['recommend'],
                        ])
                        >
                        <span class="inline-flex text-warning-500">
                            <i class="lni lni-star-fat text-base" aria-hidden="true"></i>
                        </span>
                        ทรัพย์แนะนำ
                    </a>
                </div>

                <a href="{{ route('property.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    เพิ่มทรัพย์สิน
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('property.index') }}" class="border-b border-gray-200 bg-gray-50 px-5 py-4 sm:px-6">
            @if ($filters['recommend'])
            <input type="hidden" name="recommend" value="1">
            @endif
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="code" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รหัส</label>
                    <input id="code" type="text" name="code" value="{{ $filters['code'] }}" placeholder="ค้นหารหัสทรัพย์" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10" />
                </div>

                <div>
                    <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อทรัพย์</label>
                    <input id="name" type="text" name="name" value="{{ $filters['name'] }}" placeholder="ใช้ % แทนตัวอักษรได้ เช่น %บ้าน%" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10" />
                </div>

                <div>
                    <label for="asset_type_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ประเภท</label>
                    <select id="asset_type_id" name="asset_type_id" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10">
                        <option value="">ทั้งหมด</option>
                        @foreach ($assetTypes as $assetType)
                        <option value="{{ $assetType->id }}" @selected($filters['asset_type_id']===$assetType->id)>
                            {{ $assetType->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="zone_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">โซน</label>
                    <select id="zone_id" name="zone_id" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10">
                        <option value="">ทั้งหมด</option>
                        @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected($filters['zone_id']===$zone->id)>
                            {{ $zone->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <x-property.agent-picker :agents="$agents" :selected="$filters['user_id']" :with-photos="false" />
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    ค้นหา
                </button>
                @if ($hasFilter)
                <a href="{{ route('property.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50">
                    ล้างเงื่อนไข
                </a>
                @endif
                <p class="text-theme-xs text-gray-500">กรอกเงื่อนไขอย่างใดอย่างหนึ่งหรือหลายอย่างร่วมกันได้</p>
            </div>
        </form>

        @if ($data->isEmpty())
        <div class="px-5 py-16 text-center sm:px-6">
            <p class="text-base font-medium text-gray-800">ไม่พบรายการ</p>
            <p class="mt-1 text-sm text-gray-500">
                {{ $hasFilter ? 'ไม่พบข้อมูลตามเงื่อนไขที่ค้นหา' : 'ไม่พบข้อมูลทรัพย์สินในระบบ' }}
            </p>
        </div>
        @else
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="min-w-full">
                <thead>
                    <tr class="border-y border-gray-100">
                        <th class="px-5 py-3 text-start font-normal sm:px-6">
                            <span class="text-theme-sm text-gray-500">รหัส</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ชื่อทรัพย์</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ประเภท</span>
                        </th>

                        <th class="px-4 py-3 text-center font-normal">
                            <span class="text-theme-sm text-gray-500"></span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ราคา</span>
                        </th>

                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ตัวแทน</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">เปิดใช้งาน</span>
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
                    @php
                    $assetTypeName = $item->asset_type?->name ?? $item->asset_type_des ?? '-';
                    $listingTypes = array_filter([
                    $item->issale === 'Y' ? 'ขาย' : null,
                    $item->isrent === 'Y' ? 'เช่า' : null,
                    $item->issalerent === 'Y' ? 'ขาย/เช่า' : null,
                    $item->issellout === 'Y' ? 'เซลล์เอาท์' : null,
                    $item->issaledown === 'Y' ? 'ขายดาวน์' : null,
                    ]);

                    $createdAt = $item->created ?? $item->created_at;
                    @endphp
                    <tr class="hover:bg-gray-50/60">
                        <td class="px-3 py-2 sm:px-6">
                            <span class="whitespace-nowrap text-theme-sm font-medium">{{ $item->code }}</span>
                        </td>
                        <td class="max-w-xs px-3 py-2">
                            <button type="button" @click="openDetail('{{ $item->id }}')" title="{{ $item->name }}" class="flex w-full min-w-0 items-center gap-1.5 text-start text-theme-sm font-medium text-brand-600 transition hover:text-brand-700 hover:underline">
                                @if (($item->isrecommend ?? 'N') === 'Y')
                                <span class="inline-flex shrink-0 text-warning-500" title="ทรัพย์แนะนำ">
                                    <i class="lni lni-star-fat text-base" aria-hidden="true"></i>
                                </span>
                                @endif
                                <span class="truncate">{{ $item->name }}</span>
                            </button>
                        </td>
                        <td class="px-3 py-2">
                            <span class="text-theme-xs text-gray-700">{{ $assetTypeName }}</span>
                        </td>

                        <td class="px-3 py-2 text-center">
                            <div class="inline-flex items-center justify-center gap-2">
                                @if ($item->asset_images_count > 0)
                                <span title="มีรูป {{ number_format($item->asset_images_count) }} รูป" class="inline-flex text-success-600">
                                    <i class="lni lni-photos text-lg" aria-hidden="true"></i>
                                </span>
                                @else
                                <span title="ไม่มีรูป" class="inline-flex text-gray-300">
                                    <i class="lni lni-photos text-lg" aria-hidden="true"></i>
                                </span>
                                @endif

                                @if (filled($item->youtube_link))
                                <a href="{{ $item->youtube_link }}" target="_blank" rel="noopener noreferrer" title="มีวิดีโอ YouTube" class="inline-flex text-error-600 transition hover:text-error-700">
                                    <i class="lni lni-youtube text-lg" aria-hidden="true"></i>
                                </a>
                                @endif
                            </div>
                        </td>

                        <td class="px-3 py-2">
                            <div class="whitespace-nowrap text-theme-sm font-medium text-gray-800">
                                @if ($item->price_amounnt)
                                <p>{{ number_format($item->price_amounnt) }}</p>
                                @endif
                                @if ($item->price_rent)
                                <p class="mt-0.5 text-theme-xs font-normal text-gray-500">
                                    เช่า {{ number_format($item->price_rent) }}
                                </p>
                                @endif
                                @if (! $item->price_amounnt && ! $item->price_rent)
                                -
                                @endif
                            </div>
                        </td>

                        <td class="px-3 py-2">
                            <span class="text-theme-sm text-gray-700">{{ trim(($item->user?->firstname ?? '')) }}</span>
                        </td>
                        <td class="px-3 py-2">
                            <x-property.isactive-switch :property="$item" :active="($item->isactive ?? 'Y') === 'Y'" />
                        </td>
                        <td class="px-3 py-2">
                            <x-ui.date-time-display :datetime="$createdAt" />
                        </td>
                        <td class="px-3 py-2">
                            <x-property.row-actions :property="$item" :agents="$officeAgents" />
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

    <div x-show="open" x-cloak @keydown.escape.window="closeDetail()" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5">
        <div @click="closeDetail()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div @click.stop class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl bg-white shadow-theme-xl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <button @click="closeDetail()" type="button" class="absolute right-3 top-3 z-10 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill="currentColor" />
                </svg>
            </button>

            <div x-show="loading" class="flex min-h-[320px] items-center justify-center p-8">
                <div class="text-center">
                    <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-4 border-gray-200 border-t-brand-500"></div>
                    <p class="text-sm text-gray-500">กำลังโหลดข้อมูล...</p>
                </div>
            </div>

            <div x-show="! loading" x-html="detailHtml"> </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

    </style>
    @endsection
