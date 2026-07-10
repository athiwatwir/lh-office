@extends('layouts.app')

@section('content')
@php
    $readByNames = collect($data->items())
        ->filter(fn ($item) => ($item->isread ?? 'N') === 'Y' && $item->readByUser)
        ->mapWithKeys(fn ($item) => [
            $item->id => $item->readByUser->name,
        ]);
@endphp

<div x-data="{
            open: false,
            loading: false,
            detailHtml: '',
            unreadIds: @js(collect($data->items())->filter(fn ($item) => ($item->isread ?? 'N') === 'N')->pluck('id')->values()),
            readByNames: @js($readByNames),
            currentReaderName: @js(auth()->user()?->name),
            init() {
                this.$watch('open', value => {
                    document.body.style.overflow = value ? 'hidden' : 'unset';
                });
            },
            isUnread(id) {
                return this.unreadIds.includes(String(id));
            },
            readerName(id) {
                return this.readByNames[String(id)] || this.readByNames[id] || '-';
            },
            async openDetail(id) {
                this.open = true;
                this.loading = true;
                this.detailHtml = '';

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
                    this.unreadIds = this.unreadIds.filter((itemId) => String(itemId) !== String(id));

                    if (this.currentReaderName) {
                        this.readByNames[String(id)] = this.currentReaderName;
                    }
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
    <x-common.page-breadcrumb :pageTitle="$title" />

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

            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                <a href="{{ route('propertyRequest.index', ['type' => 'buy']) }}" @class([ 'rounded-md px-4 py-2 text-sm font-medium transition' , 'bg-white text-gray-800 shadow-theme-xs'=> $type === 'buy',
                    'text-gray-500 hover:text-gray-700' => $type !== 'buy',
                    ])
                    >
                    ฝากหา
                </a>
                <a href="{{ route('propertyRequest.index', ['type' => 'sell']) }}" @class([ 'rounded-md px-4 py-2 text-sm font-medium transition' , 'bg-white text-gray-800 shadow-theme-xs'=> $type === 'sell',
                    'text-gray-500 hover:text-gray-700' => $type !== 'sell',
                    ])
                    >
                    ฝากขาย
                </a>
            </div>
        </div>

        @if ($data->isEmpty())
        <div class="px-5 py-16 text-center sm:px-6">
            <p class="text-base font-medium text-gray-800">ยังไม่มีรายการ</p>
            <p class="mt-1 text-sm text-gray-500">ไม่พบข้อมูล{{ $type === 'sell' ? 'ฝากขาย' : 'ฝากหา' }}บ้าน-ที่ดิน</p>
        </div>
        @else
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="min-w-full">
                <thead>
                    <tr class="border-y border-gray-100">
                        <th class="px-4 py-3 text-center font-normal sm:px-6">
                            <span class="text-theme-sm text-gray-500">รูป</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ลูกค้า</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ประเภททรัพย์</span>
                        </th>

                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">พื้นที่</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ที่อยู่</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">
                                {{ $type === 'sell' ? 'ราคา' : 'งบประมาณ' }}
                            </span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">รายละเอียด</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">วันที่สร้าง</span>
                        </th>
                        <th class="px-4 py-3 text-start font-normal">
                            <span class="text-theme-sm text-gray-500">ผู้อ่าน</span>
                        </th>
                        <th class="px-4 py-3 text-end font-normal">
                            <span class="text-theme-sm text-gray-500">จัดการ</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($data as $item)
                    @php
                    $areaParts = [];
                    if ($item->area_rai) {
                    $areaParts[] = number_format($item->area_rai, 2) . ' ไร่';
                    }
                    if ($item->area_ngan) {
                    $areaParts[] = number_format($item->area_ngan, 2) . ' งาน';
                    }
                    if ($item->area_wah) {
                    $areaParts[] = number_format($item->area_wah, 2) . ' ตร.ว.';
                    }
                    if ($item->area_meter) {
                    $areaParts[] = number_format($item->area_meter, 2) . ' ตร.ม.';
                    }
                    $areaText = $areaParts ? implode(' ', $areaParts) : '-';

                    $priceText = $type === 'sell'
                    ? ($item->price_amounnt ? number_format($item->price_amounnt) . ' บาท' : '-')
                    : ($item->budgets ?: '-');

                    $assetTypeName = $item->asset_type?->name ?? $item->asset_type_des ?? '-';

                    $defaultAssetImage = $item->assetImages
                        ->first(fn ($assetImage) => $assetImage->isdefault === 'Y')
                        ?? $item->assetImages->first();
                    $thumbnailUrl = $defaultAssetImage?->image?->thumbnailUrl()
                        ?? $defaultAssetImage?->image?->galleryUrl();
                    $imagesCount = $item->assetImages->count();

                    $address = $item->address;
                    $addressParts = array_filter([
                        $address?->address1,
                        $address?->address2,
                        $address?->street,
                        $address?->soi ? 'ซ.'.$address->soi : null,
                        $address?->moo ? 'ม.'.$address->moo : null,
                        $address?->district,
                        $address?->amphur,
                        $address?->province,
                        $address?->zipcode,
                    ]);
                    $addressText = $addressParts ? implode(' ', $addressParts) : '-';
                    @endphp
                    <tr
                        @click="openDetail('{{ $item->id }}')"
                        :class="isUnread('{{ $item->id }}')
                            ? 'cursor-pointer border-l-4 border-brand-500 bg-brand-50/70 transition hover:bg-brand-50'
                            : 'cursor-pointer border-l-4 border-transparent transition hover:bg-gray-50/60'"
                        role="button"
                        tabindex="0"
                        @keydown.enter="openDetail('{{ $item->id }}')"
                    >
                        <td class="px-4 py-4 text-center sm:px-6">
                            <span class="relative mx-auto flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                @if ($thumbnailUrl)
                                    <img src="{{ $thumbnailUrl }}" alt="รูปคำขอ" class="h-full w-full object-cover" loading="lazy">
                                    @if ($imagesCount > 1)
                                        <span class="absolute bottom-0.5 end-0.5 rounded bg-black/60 px-1.5 py-0.5 text-[10px] font-semibold text-white">
                                            +{{ $imagesCount - 1 }}
                                        </span>
                                    @endif
                                @else
                                    <i class="lni lni-image text-lg text-gray-300" aria-hidden="true"></i>
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-start gap-2">
                                <span
                                    x-show="isUnread('{{ $item->id }}')"
                                    x-cloak
                                    class="mt-1 inline-flex shrink-0 rounded-full bg-brand-500 px-2 py-0.5 text-theme-xs font-semibold text-white"
                                    title="ยังไม่ได้อ่าน"
                                >
                                    ใหม่
                                </span>
                                <div class="min-w-0">
                                <p
                                    class="text-theme-sm text-gray-800"
                                    :class="isUnread('{{ $item->id }}') ? 'font-semibold' : 'font-medium'"
                                >
                                    {{ $item->customer?->fullname ?? '-' }}
                                </p>
                                @if ($item->customer?->tel)
                                <p class="mt-0.5 text-theme-xs text-gray-500">
                                    {{ $item->customer->tel }}
                                </p>
                                @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span
                                class="text-theme-sm text-gray-700"
                                :class="isUnread('{{ $item->id }}') ? 'font-medium text-gray-900' : ''"
                            >{{ $assetTypeName }}</span>
                        </td>

                        <td class="px-4 py-4">
                            <span
                                class="whitespace-nowrap text-theme-sm text-gray-700"
                                :class="isUnread('{{ $item->id }}') ? 'font-medium text-gray-900' : ''"
                            >{{ $areaText }}</span>
                        </td>
                        <td class="max-w-xs px-4 py-4">
                            <span
                                class="line-clamp-2 text-theme-sm text-gray-700"
                                :class="isUnread('{{ $item->id }}') ? 'font-medium text-gray-900' : ''"
                                title="{{ $addressText }}"
                            >{{ $addressText }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span
                                class="whitespace-nowrap text-theme-sm text-gray-800"
                                :class="isUnread('{{ $item->id }}') ? 'font-semibold text-brand-700' : 'font-medium'"
                            >
                                {{ $priceText }}
                            </span>
                        </td>
                        <td class="max-w-xs px-4 py-4">
                            <span class="line-clamp-2 text-theme-sm text-gray-500">
                                {{ $item->description ?: '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <x-ui.date-time-display :datetime="$item->created" />
                        </td>
                        <td class="px-4 py-4">
                            <span
                                x-show="isUnread('{{ $item->id }}')"
                                x-cloak
                                class="text-theme-xs text-gray-400"
                            >-</span>
                            <span
                                x-show="! isUnread('{{ $item->id }}')"
                                x-cloak
                                class="text-theme-sm text-gray-700"
                                x-text="readerName('{{ $item->id }}')"
                            ></span>
                        </td>
                        <td class="px-4 py-4 text-end">
                            <form method="POST" action="{{ route('propertyRequest.destroy', $item) }}" onsubmit="event.stopPropagation(); return confirm('ยืนยันการลบรายการนี้?');" class="inline-flex">
                                @csrf
                                @method('DELETE')
                                <button type="submit" @click.stop class="inline-flex h-9 items-center justify-center rounded-lg border border-error-200 bg-error-50 px-3 text-theme-sm font-medium text-error-600 transition hover:bg-error-100">
                                    ลบ
                                </button>
                            </form>
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

            <div x-show="! loading" x-html="detailHtml"></div>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

</style>
@endsection
