@csrf
@method($method)

@php
$inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10';
$selectClass = $inputClass;
$ynFields = [
'issale' => 'ขาย',
'isrent' => 'เช่า',
'issalerent' => 'ขาย/เช่า',
'issellout' => 'เซลล์เอาท์',
'issaledown' => 'ขายดาวน์',
'iscovering' => 'กำลังปรับปรุง',
'isdweller' => 'มีผู้อยู่อาศัย',
];
@endphp

<div class="space-y-8">
    <section>
        <h4 class="mb-4 text-base font-semibold text-gray-800">ข้อมูลหลัก</h4>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="code" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รหัสทรัพย์ <span class="text-error-500">*</span></label>
                <input id="code" type="text" name="code" value="{{ old('code', $item->code) }}" required class="{{ $inputClass }} @error('code') border-error-500 @enderror" />
                @error('code')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="asset_type_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ประเภททรัพย์ <span class="text-error-500">*</span></label>
                <select id="asset_type_id" name="asset_type_id" required class="{{ $selectClass }} @error('asset_type_id') border-error-500 @enderror">
                    <option value="">— เลือก —</option>
                    @foreach ($assetTypes as $assetType)
                    <option value="{{ $assetType->id }}" @selected(old('asset_type_id', $item->asset_type_id) === $assetType->id)>{{ $assetType->name }}</option>
                    @endforeach
                </select>
                @error('asset_type_id')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อทรัพย์ <span class="text-error-500">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $item->name) }}" required class="{{ $inputClass }} @error('name') border-error-500 @enderror" />
                @error('name')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <x-form.wysiwyg-editor name="description" label="รายละเอียด" :value="old('description', $item->description)" :height="400" />
            </div>

            <div class="md:col-span-2">
                <label for="youtube_link" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ลิงก์ YouTube</label>
                <input id="youtube_link" type="url" name="youtube_link" value="{{ old('youtube_link', $item->youtube_link) }}" placeholder="https://www.youtube.com/watch?v=..." class="{{ $inputClass }} @error('youtube_link') border-error-500 @enderror" />
                @error('youtube_link')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="zone_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">โซน <span class="text-error-500">*</span></label>
                <select id="zone_id" name="zone_id" required class="{{ $selectClass }} @error('zone_id') border-error-500 @enderror">
                    <option value="">— เลือก —</option>
                    @foreach ($zones as $zone)
                    <option value="{{ $zone->id }}" @selected(old('zone_id', $item->zone_id) === $zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
                @error('zone_id')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="">
                <x-property.agent-picker :agents="$agents" :selected="old('user_id', $item->user_id)" />
                @error('user_id')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="isactive" value="1" @checked(old('isactive', $item->isactive ?? 'Y') === 'Y')
                        class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
                    เปิดใช้งานทรัพย์สิน
                </label>
                @error('isactive')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div>
                <label for="price_amounnt" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ราคาขาย (บาท)</label>
                <input id="price_amounnt" type="number" name="price_amounnt" min="0" step="0.01" value="{{ old('price_amounnt', $item->price_amounnt) }}" class="{{ $inputClass }} @error('price_amounnt') border-error-500 @enderror" />
                @error('price_amounnt')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="price_rent" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ราคาเช่า (บาท)</label>
                <input id="price_rent" type="number" name="price_rent" min="0" step="0.01" value="{{ old('price_rent', $item->price_rent) }}" class="{{ $inputClass }} @error('price_rent') border-error-500 @enderror" />
                @error('price_rent')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="price_amounnt_lower" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ราคาต่ำสุด (บาท)</label>
                <input id="price_amounnt_lower" type="number" name="price_amounnt_lower" min="0" step="0.01" value="{{ old('price_amounnt_lower', $item->price_amounnt_lower) }}" class="{{ $inputClass }} @error('price_amounnt_lower') border-error-500 @enderror" />
                @error('price_amounnt_lower')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    @include('pages.property.partials.address-section', ['item' => $item, 'inputClass' => $inputClass])

    @include('pages.property.partials.images-section', ['item' => $item])

</div>

<div class="mt-6 flex flex-wrap items-center gap-3">
    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
        บันทึก
    </button>
    <a href="{{ route('property.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50">
        ยกเลิก
    </a>
</div>
