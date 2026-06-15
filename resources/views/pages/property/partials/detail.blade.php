@php
    $assetTypeName = $item->asset_type?->name ?? $item->asset_type_des ?? '-';

    $listingTypes = array_filter([
        $item->issale === 'Y' ? 'ขาย' : null,
        $item->isrent === 'Y' ? 'เช่า' : null,
        $item->issalerent === 'Y' ? 'ขาย/เช่า' : null,
        $item->issellout === 'Y' ? 'เซลล์เอาท์' : null,
        $item->issaledown === 'Y' ? 'ขายดาวน์' : null,
    ]);

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

    $roomFields = [
        'ชั้น' => $item->floor_total,
        'ห้องนอน' => $item->bedroom,
        'ห้องน้ำ' => $item->bathroom,
        'ห้องครัว' => $item->kitchen_room,
        'ห้องรับแขก' => $item->reception_room,
        'ห้องทานอาหาร' => $item->dining_room,
        'ห้องแม่บ้าน' => $item->maid_room,
        'ที่จอดรถ' => $item->parking,
    ];
    $hasRooms = collect($roomFields)->contains(fn ($value) => $value !== null && $value !== '');

    $address = $item->address;
    $addressParts = array_filter([
        $address?->address1,
        $address?->address2,
        $address?->street,
        $address?->soi ? 'ซ.' . $address->soi : null,
        $address?->moo ? 'ม.' . $address->moo : null,
        $address?->district,
        $address?->amphur,
        $address?->province?->province_name,
        $address?->zipcode,
    ]);

    $images = $item->asset_images
        ->sortByDesc(fn ($assetImage) => $assetImage->isdefault === 'Y')
        ->values();

    $createdAt = $item->created ?? $item->created_at;
    $isActive = ($item->isactive ?? 'Y') === 'Y';
@endphp

<div class="p-6 sm:p-8">
    <div class="mb-6 pr-10">
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                {{ $item->code }}
            </span>
            @foreach ($listingTypes as $type)
                <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-600">
                    {{ $type }}
                </span>
            @endforeach
            <span @class([
                'rounded-full px-3 py-1 text-xs font-semibold',
                'bg-success-50 text-success-600' => $isActive,
                'bg-gray-100 text-gray-500' => ! $isActive,
            ])>
                {{ $isActive ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
            </span>
            @if ($item->iscovering === 'Y')
                <span class="rounded-full bg-warning-50 px-3 py-1 text-xs font-semibold text-warning-600">
                    กำลังปรับปรุง
                </span>
            @endif
            @if ($item->isdweller === 'Y')
                <span class="rounded-full bg-blue-light-50 px-3 py-1 text-xs font-semibold text-blue-light-600">
                    มีผู้อยู่อาศัย
                </span>
            @endif
        </div>

        <h2 class="text-xl font-semibold text-gray-800 sm:text-2xl">{{ $item->name }}</h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ $assetTypeName }}
            · {{ $item->zone?->name ?? 'ไม่ระบุโซน' }}
            <x-ui.date-time-display
                :datetime="$createdAt"
                layout="inline"
                prefix="· สร้างเมื่อ"
                class="inline-flex"
            />
        </p>
    </div>

    @if ($images->isNotEmpty())
        <section class="mb-5">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($images as $assetImage)
                    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                        <img
                            src="{{ $assetImage->image?->url }}"
                            alt="รูปทรัพย์ {{ $item->code }}"
                            class="aspect-[4/3] w-full object-cover"
                            loading="lazy"
                        />
                        @if ($assetImage->isdefault === 'Y')
                            <span class="absolute left-2 top-2 rounded-md bg-brand-500 px-2 py-0.5 text-xs font-medium text-white">
                                รูปหลัก
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 p-5">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                <i class="lni lni-apartment text-lg text-brand-500"></i>
                ข้อมูลทรัพย์
            </h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="mb-1 text-xs text-gray-500">รหัสทรัพย์</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $item->code }}</dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs text-gray-500">ประเภททรัพย์</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $assetTypeName }}</dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs text-gray-500">โซน</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $item->zone?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs text-gray-500">ตัวแทน</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $item->user?->name ?: '-' }}</dd>
                </div>
                @if ($item->direction)
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">ทิศ</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $item->direction }}</dd>
                    </div>
                @endif
                @if ($item->floor !== null)
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">ชั้นที่</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $item->floor }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="rounded-2xl border border-gray-200 p-5">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                <i class="lni lni-money-protection text-lg text-brand-500"></i>
                ราคา
            </h3>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="mb-1 text-xs text-gray-500">ราคาขาย</dt>
                    <dd class="text-lg font-semibold text-brand-600">
                        {{ $item->price_amounnt ? number_format($item->price_amounnt) . ' บาท' : '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs text-gray-500">ราคาเช่า</dt>
                    <dd class="text-sm font-medium text-gray-800">
                        {{ $item->price_rent ? number_format($item->price_rent) . ' บาท/เดือน' : '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs text-gray-500">ราคาต่ำสุด</dt>
                    <dd class="text-sm font-medium text-gray-800">
                        {{ $item->price_amounnt_lower ? number_format($item->price_amounnt_lower) . ' บาท' : '-' }}
                    </dd>
                </div>
                @if ($item->price_per_wah)
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">ราคาต่อตารางวา</dt>
                        <dd class="text-sm font-medium text-gray-800">
                            {{ number_format($item->price_per_wah) }} บาท/ตร.ว.
                        </dd>
                    </div>
                @endif
            </dl>
        </section>

        @if ($areaParts || $item->area_width || $item->area_long)
            <section class="rounded-2xl border border-gray-200 p-5">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <i class="lni lni-ruler-pencil text-lg text-brand-500"></i>
                    พื้นที่
                </h3>
                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">ไร่</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $item->area_rai !== null ? number_format($item->area_rai, 2) : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">งาน</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $item->area_ngan !== null ? number_format($item->area_ngan, 2) : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">ตร.ว.</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $item->area_wah !== null ? number_format($item->area_wah, 2) : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">ตร.ม.</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $item->area_meter !== null ? number_format($item->area_meter, 2) : '-' }}</dd>
                    </div>
                </dl>
                @if ($areaParts)
                    <p class="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        รวม {{ implode(' ', $areaParts) }}
                    </p>
                @endif
                @if ($item->area_width || $item->area_long)
                    <p class="mt-3 text-sm text-gray-600">
                        กว้าง {{ $item->area_width ? number_format($item->area_width, 2) : '-' }}
                        × ยาว {{ $item->area_long ? number_format($item->area_long, 2) : '-' }}
                    </p>
                @endif
            </section>
        @endif

        @if ($hasRooms)
            <section class="rounded-2xl border border-gray-200 p-5">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <i class="lni lni-home text-lg text-brand-500"></i>
                    ข้อมูลห้อง
                </h3>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($roomFields as $label => $value)
                        @if ($value !== null && $value !== '')
                            <div class="rounded-xl bg-gray-50 px-4 py-3 text-center">
                                <p class="text-lg font-semibold text-gray-800">{{ $value }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $label }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @if ($addressParts)
            <section class="rounded-2xl border border-gray-200 p-5 lg:col-span-2">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <i class="lni lni-map-marker text-lg text-brand-500"></i>
                    ที่อยู่
                </h3>
                <p class="text-sm leading-relaxed text-gray-700">{{ implode(' ', $addressParts) }}</p>
                @if ($address?->description)
                    <p class="mt-3 text-sm text-gray-500">{{ $address->description }}</p>
                @endif
                @if ($item->latitude && $item->longitude)
                    <p class="mt-3 text-xs text-gray-500">
                        พิกัด {{ $item->latitude }}, {{ $item->longitude }}
                    </p>
                @endif
            </section>
        @endif

        @if ($item->youtube_link)
            <section class="rounded-2xl border border-gray-200 p-5 lg:col-span-2">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <i class="lni lni-youtube text-lg text-brand-500"></i>
                    YouTube
                </h3>
                <a
                    href="{{ $item->youtube_link }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm font-medium text-brand-600 hover:text-brand-700 hover:underline"
                >
                    {{ $item->youtube_link }}
                </a>
            </section>
        @endif

        @if ($item->description)
            <section class="rounded-2xl border border-gray-200 p-5 lg:col-span-2">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <i class="lni lni-text-format text-lg text-brand-500"></i>
                    รายละเอียด
                </h3>
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! $item->description !!}
                </div>
            </section>
        @endif
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-5">
        <a
            href="{{ route('property.edit', $item) }}"
            class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
        >
            แก้ไขทรัพย์สิน
        </a>
    </div>
</div>
