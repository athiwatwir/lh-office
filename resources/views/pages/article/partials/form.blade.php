@php
    $fileClass = 'focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="px-5 py-6 sm:px-6">
    @csrf
    @method($method)

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">หัวข้อบทความ <span class="text-error-500">*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name', $item->name) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('name') border-error-500 @enderror" />
            <p class="mt-1 text-theme-xs text-gray-500">ชื่อที่แสดงในรายการและหน้าบทความ</p>
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
            <p class="mt-1 text-theme-xs text-gray-500">จัดกลุ่มบทความตามหมวดหมู่</p>
            @error('category_id')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="agent_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">Agent</label>
            <select id="agent_id" name="agent_id" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('agent_id') border-error-500 @enderror">
                <option value="">ทุก Agent</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" @selected(old('agent_id', $item->agent_id) === $agent->id)>
                        {{ $agent->name }}@if ($agent->code) ({{ $agent->code }})@endif
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-theme-xs text-gray-500">ไม่เลือก = แสดงทุก Agent · เลือก = แสดงเฉพาะ Agent นั้น</p>
            @error('agent_id')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="isactive" class="mb-1.5 block text-theme-sm font-medium text-gray-700">สถานะ <span class="text-error-500">*</span></label>
            <select id="isactive" name="isactive" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('isactive') border-error-500 @enderror">
                <option value="Y" @selected(old('isactive', $item->isactive ?? 'Y') === 'Y')>เปิดใช้งาน</option>
                <option value="N" @selected(old('isactive', $item->isactive ?? 'Y') === 'N')>ปิดใช้งาน</option>
            </select>
            <p class="mt-1 text-theme-xs text-gray-500">ปิดใช้งาน = ไม่แสดงในระบบ</p>
            @error('isactive')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="cover" class="mb-1.5 block text-theme-sm font-medium text-gray-700">รูปหน้าปก</label>
            <input id="cover" type="file" name="cover" accept="image/jpeg,image/png,image/gif,image/webp" class="{{ $fileClass }} @error('cover') border-error-500 @enderror" />
            <p class="mt-1 text-theme-xs text-gray-500">รูปปกในรายการและหน้าบทความ — JPG/PNG/WebP ไม่เกิน {{ number_format(\App\Http\Requests\ArticleRequest::imageMaxKilobytes() / 1024, 1) }} MB</p>
            @error('cover')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
            @if ($item->cover_image_url)
            <div class="mt-3">
                <p class="mb-1.5 text-theme-xs text-gray-500">รูปปัจจุบัน</p>
                <div class="flex h-24 w-40 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                    <img src="{{ $item->cover_image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                </div>
            </div>
            @endif
        </div>

        <div class="md:col-span-2">
            <x-form.wysiwyg-editor
                name="text"
                id="article_text"
                label="เนื้อหาบทความ"
                :value="old('text', $item->text)"
                :height="520"
                :enable-youtube="true"
                placeholder="พิมพ์เนื้อหาบทความที่นี่..."
            />
            <p class="mt-1 text-theme-xs text-gray-500">เนื้อหาเต็ม — รองรับหัวข้อ รูป YouTube และจัดรูปแบบข้อความ · เรียงลำดับทำได้ที่หน้ารายการ</p>
        </div>
    </div>

    <x-form.actions :cancel-url="route('article.index')" />
</form>
