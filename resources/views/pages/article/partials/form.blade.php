<form method="POST" action="{{ $action }}" class="px-5 py-6 sm:px-6">
    @csrf
    @method($method)

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">หัวข้อบทความ <span class="text-error-500">*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name', $item->name) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('name') border-error-500 @enderror" />
            @error('name')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ประเภทบทความ <span class="text-error-500">*</span></label>
            <select id="category_id" name="category_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('category_id') border-error-500 @enderror">
                <option value="">— เลือก —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="seq" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ลำดับการแสดงผล <span class="text-error-500">*</span></label>
            <input id="seq" type="number" name="seq" min="0" value="{{ old('seq', $item->seq) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('seq') border-error-500 @enderror" />
            @error('seq')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="isactive" class="mb-1.5 block text-theme-sm font-medium text-gray-700">สถานะ <span class="text-error-500">*</span></label>
            <select id="isactive" name="isactive" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('isactive') border-error-500 @enderror">
                <option value="Y" @selected(old('isactive', $item->isactive ?? 'Y') === 'Y')>เปิดใช้งาน</option>
                <option value="N" @selected(old('isactive', $item->isactive ?? 'Y') === 'N')>ปิดใช้งาน</option>
            </select>
            @error('isactive')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <x-form.wysiwyg-editor
                name="text"
                id="article_text"
                label="เนื้อหาบทความ"
                :value="old('text', $item->text)"
                :height="520"
                placeholder="พิมพ์เนื้อหาบทความที่นี่..."
            />
            <p class="mt-1 text-theme-xs text-gray-500">รองรับหัวข้อ รายการ ลิงก์ Emoji และอัปโหลดรูปประกอบบทความ</p>
        </div>
    </div>

    <x-form.actions :cancel-url="route('article.index')" />
</form>
