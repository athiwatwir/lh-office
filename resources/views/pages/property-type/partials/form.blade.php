@csrf
@method($method)

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อประเภททรัพย์สิน <span class="text-error-500">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $item->name) }}"
            required
            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('name') border-error-500 @enderror"
        />
        @error('name')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="seq" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ลำดับการแสดงผล <span class="text-error-500">*</span></label>
        <input
            id="seq"
            type="number"
            name="seq"
            min="0"
            value="{{ old('seq', $item->seq) }}"
            required
            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('seq') border-error-500 @enderror"
        />
        @error('seq')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="pic" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รูปไอคอน</label>
        <input
            id="pic"
            type="file"
            name="pic"
            accept="image/jpeg,image/png,image/gif,image/webp"
            class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden @error('pic') border-error-500 @enderror"
        />
        <p class="mt-1.5 text-theme-xs text-gray-500">รองรับ JPG, PNG, GIF, WebP ขนาดไม่เกิน 5 MB — ระบบจะแปลงเป็น WebP อัตโนมัติ</p>
        @error('pic')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
        @if ($item->pic_url)
            <div class="mt-3">
                <p class="mb-1.5 text-theme-xs text-gray-500">รูปปัจจุบัน</p>
                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                    <img src="{{ $item->pic_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                </div>
            </div>
        @endif
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center gap-3">
    <button
        type="submit"
        class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
    >
        บันทึก
    </button>
    <a
        href="{{ route('propertyType.index') }}"
        class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50"
    >
        ยกเลิก
    </a>
</div>
