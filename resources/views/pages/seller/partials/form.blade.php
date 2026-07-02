@php
$inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10';
$fileClass = 'focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden';
@endphp

@csrf
@method($method)

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="firstname" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อ <span class="text-error-500">*</span></label>
        <input id="firstname" type="text" name="firstname" value="{{ old('firstname', $item->firstname) }}" required class="{{ $inputClass }} @error('firstname') border-error-500 @enderror" />
        <p class="mt-1 text-theme-xs text-gray-500">ชื่อที่แสดงในหน้าทรัพย์สินและ API</p>
        @error('firstname')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="lastname" class="mb-1.5 block text-theme-sm font-medium text-gray-700">นามสกุล <span class="text-error-500">*</span></label>
        <input id="lastname" type="text" name="lastname" value="{{ old('lastname', $item->lastname) }}" required class="{{ $inputClass }} @error('lastname') border-error-500 @enderror" />
        @error('lastname')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-theme-sm font-medium text-gray-700">อีเมล</label>
        <input id="email" type="email" name="email" value="{{ old('email', $item->email) }}" class="{{ $inputClass }} @error('email') border-error-500 @enderror" />
        <p class="mt-1 text-theme-xs text-gray-500">ใช้แสดงข้อมูลติดต่อ (ไม่ใช่บัญชีเข้าระบบ)</p>
        @error('email')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700">เบอร์โทรศัพท์</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $item->phone) }}" class="{{ $inputClass }} @error('phone') border-error-500 @enderror" />
        @error('phone')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="lineid" class="mb-1.5 block text-theme-sm font-medium text-gray-700">Line ID</label>
        <input id="lineid" type="text" name="lineid" value="{{ old('lineid', $item->lineid) }}" class="{{ $inputClass }} @error('lineid') border-error-500 @enderror" />
        @error('lineid')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="position" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ตำแหน่ง</label>
        <input id="position" type="text" name="position" value="{{ old('position', $item->position) }}" class="{{ $inputClass }} @error('position') border-error-500 @enderror" />
        <p class="mt-1 text-theme-xs text-gray-500">เช่น ที่ปรึกษาอสังหาริมทรัพย์</p>
        @error('position')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2 grid grid-cols-1 gap-5 md:grid-cols-2 md:items-start">
        <div>
            <label for="pic" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รูปโปรไฟล์</label>
            <input id="pic" type="file" name="pic" accept="image/jpeg,image/png,image/gif,image/webp" class="{{ $fileClass }} @error('pic') border-error-500 @enderror" />
            <p class="mt-1.5 text-theme-xs text-gray-500">รองรับ JPG, PNG, GIF, WebP ไม่เกิน 5 MB</p>
            @error('pic')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 p-6 md:min-h-[220px]">
            <p class="mb-4 text-theme-sm font-medium text-gray-700">รูปปัจจุบัน</p>
            <div class="flex h-40 w-40 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-white shadow-theme-sm">
                @if ($item->profile_image_url)
                <img src="{{ $item->profile_image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                @else
                <span class="text-4xl font-semibold text-gray-300">{{ $item->firstname ? mb_substr($item->firstname, 0, 1) : '?' }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<x-form.actions :cancel-url="route('seller.index')" />
