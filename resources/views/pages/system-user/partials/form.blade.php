@php
$inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10';
$isActiveInitially = old('isactive', $item->isactive ?? 'N') === 'Y';
@endphp

@csrf
@method($method)

<div class="grid grid-cols-1 gap-5 md:grid-cols-2" x-data="{ isActive: @js($isActiveInitially) }">
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

    <div class="md:col-span-2">
        <label for="email" class="mb-1.5 block text-theme-sm font-medium text-gray-700">อีเมล <span class="text-error-500">*</span></label>
        <input id="email" type="email" name="email" value="{{ old('email', $item->email) }}" required class="{{ $inputClass }} @error('email') border-error-500 @enderror" />
        <p class="mt-1 text-theme-xs text-gray-500">ใช้สำหรับเข้าสู่ระบบ admin</p>
        @error('email')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="isactive" value="1" x-model="isActive" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
            เปิดให้ใช้งานระบบ
        </label>
        <p class="mt-1 text-theme-xs text-gray-500">เปิดใช้งาน = สามารถ login เข้า admin ได้</p>
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

<x-form.actions :cancel-url="route('system-user.index')" />
