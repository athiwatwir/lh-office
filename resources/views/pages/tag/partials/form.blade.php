<form method="POST" action="{{ $action }}" class="px-5 py-6 sm:px-6">
    @csrf
    @method($method)

    <div class="max-w-xl">
        <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อแท็ก <span class="text-error-500">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $item->name) }}"
            required
            autofocus
            placeholder="เช่น ใกล้ BTS, ทำเลทอง, ริมน้ำ"
            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('name') border-error-500 @enderror"
        />
        @error('name')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-theme-xs text-gray-500">ใช้สำหรับจัดกลุ่มทำเลหรือจุดเด่นของทรัพย์สิน</p>
    </div>

    <x-form.actions :cancel-url="route('tag.index')" />
</form>
