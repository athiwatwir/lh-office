@php
    $isSell = $item->type === 'S';
    $typeLabel = $isSell ? 'ฝากขาย' : 'ฝากหา';
    $assetTypeName = $item->asset_type?->name ?? $item->asset_type_des ?? '-';

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
        $address?->province,
        $address?->zipcode,
    ]);
@endphp

<div class="p-6 sm:p-8">
    <div class="mb-6 pr-10">
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <span @class([
                'rounded-full px-3 py-1 text-xs font-semibold',
                'bg-brand-50 text-brand-600' => $isSell,
                'bg-blue-light-50 text-blue-light-600' => ! $isSell,
            ])>
                {{ $typeLabel }}
            </span>
            @if ($item->isreqconsult === 'Y')
                <span class="rounded-full bg-warning-50 px-3 py-1 text-xs font-semibold text-warning-600">
                    ต้องการปรึกษา
                </span>
            @endif
        </div>

        <h2 class="text-xl font-semibold text-gray-800 sm:text-2xl">{{ $assetTypeName }}</h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ $item->zone?->name ?? 'ไม่ระบุโซน' }}
            <x-ui.date-time-display
                :datetime="$item->created"
                layout="inline"
                prefix="· สร้างเมื่อ"
                class="inline-flex"
            />
        </p>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 p-5">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                <i class="lni lni-user text-lg text-brand-500"></i>
                ข้อมูลลูกค้า
            </h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="mb-1 text-xs text-gray-500">ชื่อ-นามสกุล</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $item->customer?->fullname ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs text-gray-500">เบอร์โทร</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $item->customer?->tel ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs text-gray-500">อีเมล</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $item->customer?->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="mb-1 text-xs text-gray-500">Line ID</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $item->customer?->lineid ?? '-' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-gray-200 p-5">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                <i class="lni lni-apartment text-lg text-brand-500"></i>
                ข้อมูลทรัพย์
            </h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="mb-1 text-xs text-gray-500">ประเภททรัพย์</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $assetTypeName }}</dd>
                </div>
                @if ($item->asset_type_des)
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">รายละเอียดประเภท</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $item->asset_type_des }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="mb-1 text-xs text-gray-500">โซน</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $item->zone?->name ?? '-' }}</dd>
                </div>
                @if ($item->zone?->description)
                    <div class="sm:col-span-2">
                        <dt class="mb-1 text-xs text-gray-500">คำอธิบายโซน</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $item->zone->description }}</dd>
                    </div>
                @endif
            </dl>
        </section>

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
        </section>

        <section class="rounded-2xl border border-gray-200 p-5">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                <i class="lni lni-money-protection text-lg text-brand-500"></i>
                {{ $isSell ? 'ราคา' : 'งบประมาณ' }}
            </h3>
            <dl class="grid grid-cols-1 gap-4">
                @if ($isSell)
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">ราคาขาย</dt>
                        <dd class="text-lg font-semibold text-brand-600">
                            {{ $item->price_amounnt ? number_format($item->price_amounnt) . ' บาท' : '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">ราคาต่อตารางวา</dt>
                        <dd class="text-sm font-medium text-gray-800">
                            {{ $item->price_per_wah ? number_format($item->price_per_wah) . ' บาท/ตร.ว.' : '-' }}
                        </dd>
                    </div>
                @else
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">งบประมาณ</dt>
                        <dd class="text-lg font-semibold text-brand-600">{{ $item->budgets ?: '-' }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        @if ($hasRooms)
            <section class="rounded-2xl border border-gray-200 p-5 lg:col-span-2">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <i class="lni lni-home text-lg text-brand-500"></i>
                    ข้อมูลห้อง / สิ่งอำนวยความสะดวก
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
            </section>
        @endif

        @if ($item->description)
            <section class="rounded-2xl border border-gray-200 p-5 lg:col-span-2">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <i class="lni lni-text-format text-lg text-brand-500"></i>
                    รายละเอียดเพิ่มเติม
                </h3>
                <p class="whitespace-pre-line text-sm leading-relaxed text-gray-700">{{ $item->description }}</p>
            </section>
        @endif
    </div>
</div>
