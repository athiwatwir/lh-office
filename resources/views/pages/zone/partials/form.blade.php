<form method="POST" action="{{ $action }}" class="px-5 py-6 sm:px-6">
    @csrf
    @method($method)

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อโซน <span class="text-error-500">*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name', $item->name) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('name') border-error-500 @enderror" />
            @error('name')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รายละเอียด</label>
            <textarea id="description" name="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('description') border-error-500 @enderror">{{ old('description', $item->description) }}</textarea>
            @error('description')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
            บันทึก
        </button>
        <a href="{{ route('zone.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50">
            ยกเลิก
        </a>
    </div>
</form>
