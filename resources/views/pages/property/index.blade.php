@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title" />

<div class="space-y-6">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                ทั้งหมด {{ number_format($data->total()) }} รายการ
                @if ($data->total() > 0)
                    (แสดง {{ $data->firstItem() }}–{{ $data->lastItem() }})
                @endif
            </p>
        </div>

        <form method="GET" action="{{ route('property.index') }}" class="border-b border-gray-200 bg-gray-50 px-5 py-4 sm:px-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="code" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รหัส</label>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        value="{{ $filters['code'] }}"
                        placeholder="ค้นหารหัสทรัพย์"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10"
                    />
                </div>

                <div>
                    <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อทรัพย์</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ $filters['name'] }}"
                        placeholder="ใช้ % แทนตัวอักษรได้ เช่น %บ้าน%"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10"
                    />
                </div>

                <div>
                    <label for="asset_type_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ประเภท</label>
                    <select
                        id="asset_type_id"
                        name="asset_type_id"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10"
                    >
                        <option value="">ทั้งหมด</option>
                        @foreach ($assetTypes as $assetType)
                            <option value="{{ $assetType->id }}" @selected($filters['asset_type_id'] === $assetType->id)>
                                {{ $assetType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="zone_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">โซน</label>
                    <select
                        id="zone_id"
                        name="zone_id"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10"
                    >
                        <option value="">ทั้งหมด</option>
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}" @selected($filters['zone_id'] === $zone->id)>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-property.agent-picker :agents="$agents" :selected="$filters['user_id']" />
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button
                    type="submit"
                    class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    ค้นหา
                </button>
                @if ($hasFilter)
                    <a
                        href="{{ route('property.index') }}"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50"
                    >
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
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">โซน</span>
                        </th>

                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ราคา</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ประเภทประกาศ</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ตัวแทน</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">วันที่สร้าง</span>
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
                            $agentName = trim(($item->user?->firstname ?? '').' '.($item->user?->lastname ?? ''));
                            $createdAt = $item->created ?? $item->created_at;
                        @endphp
                    <tr class="hover:bg-gray-50/60">
                        <td class="px-5 py-4 sm:px-6">
                            <span class="whitespace-nowrap text-theme-sm font-medium text-gray-800">
                                {{ $item->code }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div>
                                <p class="text-theme-sm font-medium text-gray-800">{{ $item->name }}</p>

                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-theme-sm text-gray-700">{{ $assetTypeName }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-theme-sm text-gray-700">{{ $item->zone?->name ?? '-' }}</span>
                        </td>

                        <td class="px-4 py-4">
                            <div class="whitespace-nowrap text-theme-sm font-medium text-gray-800">
                                @if ($item->price_amounnt)
                                <p>{{ number_format($item->price_amounnt) }} บาท</p>
                                @endif
                                @if ($item->price_rent)
                                <p class="mt-0.5 text-theme-xs font-normal text-gray-500">
                                    เช่า {{ number_format($item->price_rent) }} บาท
                                </p>
                                @endif
                                @if (! $item->price_amounnt && ! $item->price_rent)
                                -
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if ($listingTypes)
                            <div class="flex flex-wrap gap-1">
                                @foreach ($listingTypes as $listingType)
                                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600">
                                    {{ $listingType }}
                                </span>
                                @endforeach
                            </div>
                            @else
                            <span class="text-theme-sm text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-theme-sm text-gray-700">{{ $agentName ?: '-' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <x-ui.date-time-display :datetime="$createdAt" />
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
