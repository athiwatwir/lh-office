@csrf
@method($method)

@php
$inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10';
$fileClass = 'focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden';
@endphp

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อเอเจนต์ <span class="text-error-500">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $item->name) }}" required class="{{ $inputClass }} @error('name') border-error-500 @enderror" />
        @error('name')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="code" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รหัส</label>
        <input id="code" type="text" name="code" value="{{ old('code', $item->code) }}" class="{{ $inputClass }} uppercase @error('code') border-error-500 @enderror" style="text-transform: uppercase" />
        <p class="mt-1.5 text-theme-xs text-gray-500">รหัสจะถูกบันทึกเป็นตัวพิมพ์ใหญ่อัตโนมัติ</p>
        @error('code')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div x-data="{
        generateApiKey() {
            const bytes = new Uint8Array(32);
            crypto.getRandomValues(bytes);
            document.getElementById('api_key').value = Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
        },
    }">
        <div class="mb-1.5 flex items-center gap-2">
            <label for="api_key" class="text-theme-sm font-medium text-gray-700">API Key</label>
            <button
                type="button"
                @click="generateApiKey()"
                class="inline-flex h-7 items-center gap-1 rounded-md border border-gray-300 bg-white px-2 text-theme-xs font-medium text-gray-600 shadow-theme-xs transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600"
            >
                <i class="lni lni-key text-sm" aria-hidden="true"></i>
                สร้างคีย์
            </button>
        </div>
        <input id="api_key" type="text" name="api_key" value="{{ old('api_key', $item->api_key) }}" class="{{ $inputClass }} font-mono @error('api_key') border-error-500 @enderror" />
        @error('api_key')
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2 grid grid-cols-1 gap-5 md:grid-cols-2 md:items-start">
        <div>
            <label for="logo" class="mb-1.5 block text-theme-sm font-medium text-gray-700">โลโก้</label>
            <input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="{{ $fileClass }} @error('logo') border-error-500 @enderror" />
            <p class="mt-1.5 text-theme-xs text-gray-500">รองรับ JPG, PNG, GIF, WebP ขนาดไม่เกิน 5 MB — ระบบจะแปลงเป็น WebP และเก็บที่ upload/agent</p>
            @error('logo')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 p-6 md:min-h-[220px]">
            <p class="mb-4 text-theme-sm font-medium text-gray-700">โลโก้ปัจจุบัน</p>
            <div class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-2xl border-4 border-white bg-white shadow-theme-sm">
                @if ($item->logo_url)
                <img src="{{ $item->logo_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                @else
                <span class="text-3xl font-semibold text-gray-300">{{ $item->name ? mb_substr($item->name, 0, 1) : '?' }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<x-form.actions :cancel-url="route('agent.index')" />
