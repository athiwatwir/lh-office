@csrf
@method('PUT')

@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10';
@endphp

<div class="grid grid-cols-1 gap-5 md:max-w-xl">
    <div>
        <label for="password" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รหัสผ่านใหม่ <span class="text-error-500">*</span></label>
        <input id="password" type="password" name="password" required class="{{ $inputClass }} @error('password') border-error-500 @enderror" />
        @error('password')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ยืนยันรหัสผ่านใหม่ <span class="text-error-500">*</span></label>
        <input id="password_confirmation" type="password" name="password_confirmation" required class="{{ $inputClass }}" />
    </div>
</div>

<div class="mt-6">
    <button
        type="submit"
        class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
    >
        ตั้งรหัสผ่านใหม่
    </button>
</div>
