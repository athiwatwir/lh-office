@php
$address = $item->address;
$addr = fn (string $field, mixed $default = '') => old("address.{$field}", $address?->{$field} ?? $default);
$provinceName = old('address.province', $address?->province ? trim($address->province) : '');
$latitude = old('latitude', $item->latitude);
$longitude = old('longitude', $item->longitude);
$mapsApiKey = config('services.google_maps.key', '');
@endphp

<section>
    <h4 class="mb-4 text-base font-semibold text-gray-800">ที่อยู่</h4>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
        <div class="md:col-span-2">
            <label for="address_address1" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ที่อยู่ / อาคาร / หมู่บ้าน</label>
            <input id="address_address1" type="text" name="address[address1]" value="{{ $addr('address1') }}" class="{{ $inputClass }} @error('address.address1') border-error-500 @enderror" />
            @error('address.address1')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="address_soi" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ซอย</label>
            <input id="address_soi" type="text" name="address[soi]" value="{{ $addr('soi') }}" class="{{ $inputClass }} @error('address.soi') border-error-500 @enderror" />
            @error('address.soi')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="address_street" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ถนน</label>
            <input id="address_street" type="text" name="address[street]" value="{{ $addr('street') }}" class="{{ $inputClass }} @error('address.street') border-error-500 @enderror" />
            @error('address.street')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="address_district" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ตำบล / แขวง</label>
            <input id="address_district" type="text" name="address[district]" value="{{ $addr('district') }}" autocomplete="off" class="{{ $inputClass }} @error('address.district') border-error-500 @enderror" />
            @error('address.district')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="address_amphur" class="mb-1.5 block text-theme-sm font-medium text-gray-700">อำเภอ / เขต</label>
            <input id="address_amphur" type="text" name="address[amphur]" value="{{ $addr('amphur') }}" autocomplete="off" class="{{ $inputClass }} @error('address.amphur') border-error-500 @enderror" />
            @error('address.amphur')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="address_province" class="mb-1.5 block text-theme-sm font-medium text-gray-700">จังหวัด</label>
            <input id="address_province" type="text" name="address[province]" value="{{ $provinceName }}" autocomplete="off" class="{{ $inputClass }} @error('address.province') border-error-500 @enderror" />
            @error('address.province')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="address_zipcode" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รหัสไปรษณีย์</label>
            <input id="address_zipcode" type="text" name="address[zipcode]" value="{{ $addr('zipcode') }}" autocomplete="off" maxlength="10" class="{{ $inputClass }} @error('address.zipcode') border-error-500 @enderror" />
            @error('address.zipcode')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
        </div>


    </div>

    <p class="mt-2 text-theme-xs text-gray-500">พิมพ์ตำบล อำเภอ จังหวัด หรือรหัสไปรษณีย์ ระบบจะช่วยเติมที่อยู่ให้อัตโนมัติ</p>
</section>

<section>
    <h4 class="mb-1 text-base font-semibold text-gray-800">ปักหมุดบนแผนที่</h4>
    <p class="mb-2 text-theme-xs text-gray-500">คลิกบนแผนที่ ลากหมุด หรือกรอกพิกัดละติจูด/ลองจิจูดเพื่อกำหนดตำแหน่งทรัพย์</p>

    <div id="property-map" data-api-key="{{ $mapsApiKey }}" data-latitude="{{ $latitude }}" data-longitude="{{ $longitude }}" class="w-full overflow-hidden rounded-xl border border-gray-300 bg-gray-50"></div>

    @if (! $mapsApiKey)
    <p class="mt-2 text-theme-xs text-warning-600">ตั้งค่า <code class="text-xs">GOOGLE_MAPS_API_KEY</code> ในไฟล์ .env เพื่อใช้งานแผนที่</p>
    @endif

    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label for="latitude" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ละติจูด (Latitude)</label>
            <input id="latitude" type="text" name="latitude" value="{{ $latitude }}" inputmode="decimal" class="{{ $inputClass }} @error('latitude') border-error-500 @enderror" />
            @error('latitude')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="longitude" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ลองจิจูด (Longitude)</label>
            <input id="longitude" type="text" name="longitude" value="{{ $longitude }}" inputmode="decimal" class="{{ $inputClass }} @error('longitude') border-error-500 @enderror" />
            @error('longitude')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
        </div>
    </div>
</section>
