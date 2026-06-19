@csrf
@method($method)

@php
$inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10';
$fileClass = 'focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden';
$isActiveInitially = old('isactive', $item->isactive ?? 'N') === 'Y';
@endphp

<div class="grid grid-cols-1 gap-5 md:grid-cols-2" x-data="{ isActive: @js($isActiveInitially) }">
    <!--
    <div>
        <label for="usercode" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รหัสพนักงาน</label>
        <input id="usercode" type="text" name="usercode" value="{{ old('usercode', $item->usercode) }}" class="{{ $inputClass }} @error('usercode') border-error-500 @enderror" />
        @error('usercode')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>
    -->

    <div>
        <label for="firstname" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อ <span class="text-error-500">*</span></label>
        <input id="firstname" type="text" name="firstname" value="{{ old('firstname', $item->firstname) }}" required class="{{ $inputClass }} @error('firstname') border-error-500 @enderror" />
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
        <label for="email" class="mb-1.5 block text-theme-sm font-medium text-gray-700">อีเมล </label>
        <input id="email" type="email" name="email" value="{{ old('email', $item->email) }}" required class="{{ $inputClass }} @error('email') border-error-500 @enderror" />
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
        @error('position')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2 flex flex-wrap gap-6">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="isactive" value="1" x-model="isActive"
            class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20"
            />
            เปิดให้ใช้งานระบบ
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="isseller" value="1" @checked(old('isseller', $item->isseller) === 'Y')
            class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20"
            />
            เป็นตัวแทนขาย
        </label>
    </div>

    <div class="md:col-span-2 grid grid-cols-1 gap-5 md:grid-cols-2 md:items-start">
        <div>
            <label for="pic" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รูปโปรไฟล์</label>
            <input id="pic" type="file" name="pic" accept="image/jpeg,image/png,image/gif,image/webp" class="{{ $fileClass }} @error('pic') border-error-500 @enderror" />
            <p class="mt-1.5 text-theme-xs text-gray-500">รองรับ JPG, PNG, GIF, WebP ขนาดไม่เกิน 5 MB — ระบบจะแปลงเป็น WebP และเก็บที่ upload/user</p>
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
                <span class="text-4xl font-semibold text-gray-300">
                    {{ $item->firstname ? mb_substr($item->firstname, 0, 1) : '?' }}
                </span>
                @endif
            </div>
        </div>
    </div>

    @if ($showPassword)
    <div x-show="isActive" x-cloak class="contents">
    <div>
        <label for="password" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รหัสผ่าน <span class="text-error-500">*</span></label>
        <input id="password" type="password" name="password" :required="isActive" :disabled="!isActive" class="{{ $inputClass }} @error('password') border-error-500 @enderror" />
        @error('password')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ยืนยันรหัสผ่าน <span class="text-error-500">*</span></label>
        <input id="password_confirmation" type="password" name="password_confirmation" :required="isActive" :disabled="!isActive" class="{{ $inputClass }}" />
    </div>
    </div>
    @endif
</div>

<div class="mt-6 flex flex-wrap items-center gap-3">
    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
        บันทึก
    </button>
    <a href="{{ route('user.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50">
        ยกเลิก
    </a>
</div>
