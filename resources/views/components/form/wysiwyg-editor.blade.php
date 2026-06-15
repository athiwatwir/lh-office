@props([
    'name' => '',
    'id' => '',
    'label' => null,
    'value' => null,
    'uploadUrl' => null,
    'height' => 360,
    'required' => false,
    'placeholder' => 'พิมพ์รายละเอียด...',
])

@php
    use Illuminate\Support\Str;

    if ($id === '') {
        $id = $name;
    }

    $editorId = 'hs-editor-' . Str::slug($id, '-');
    $uploadUrl ??= route('editor.upload-image');

    $toolbarBtn = 'inline-flex size-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 focus:outline-none focus:bg-gray-100';
    $emojis = ['😀', '😊', '😍', '🥰', '😎', '🤩', '😢', '😭', '😡', '👍', '👎', '🙏', '👏', '💪', '❤️', '🔥', '⭐', '✅', '❌', '⚠️', '🏠', '🏢', '📍', '📞', '💰', '📷', '🎉', '🌟'];
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-theme-sm font-medium text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-error-500">*</span>
            @endif
        </label>
    @endif

    <div
        id="{{ $editorId }}"
        data-hs-editor
        data-textarea-id="{{ $id }}"
        data-field-name="{{ $name }}"
        data-upload-url="{{ $uploadUrl }}"
        data-min-height="{{ $height }}"
        data-placeholder="{{ $placeholder }}"
        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs"
        x-data="{ emojiOpen: false }"
        @click.outside="emojiOpen = false"
    >
        <div class="relative flex flex-wrap items-center gap-1 border-b border-gray-200 bg-gray-50 px-2 py-2">
            <button type="button" data-hs-editor-bold class="{{ $toolbarBtn }}" title="ตัวหนา">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>
            </button>
            <button type="button" data-hs-editor-italic class="{{ $toolbarBtn }}" title="ตัวเอียง">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 4h-9m4 16H5m4-8h6"/></svg>
            </button>
            <button type="button" data-hs-editor-underline class="{{ $toolbarBtn }}" title="ขีดเส้นใต้">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 4v6a6 6 0 0 0 12 0V4"/><path stroke-linecap="round" d="M4 20h16"/></svg>
            </button>
            <button type="button" data-hs-editor-strike class="{{ $toolbarBtn }}" title="ขีดฆ่า">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M16 4H9a3 3 0 0 0-2.83 4"/><path stroke-linecap="round" d="M14 12a4 4 0 0 1 0 8H6"/><path stroke-linecap="round" d="M4 12h16"/></svg>
            </button>

            <span class="mx-1 h-6 w-px bg-gray-200"></span>

            <button type="button" data-hs-editor-h2 class="{{ $toolbarBtn }}" title="หัวข้อ">
                <span class="text-xs font-bold">H2</span>
            </button>
            <button type="button" data-hs-editor-h3 class="{{ $toolbarBtn }}" title="หัวข้อย่อย">
                <span class="text-xs font-bold">H3</span>
            </button>

            <span class="mx-1 h-6 w-px bg-gray-200"></span>

            <button type="button" data-hs-editor-ul class="{{ $toolbarBtn }}" title="รายการแบบจุด">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M8 6h13"/><path stroke-linecap="round" d="M8 12h13"/><path stroke-linecap="round" d="M8 18h13"/><circle cx="4" cy="6" r="1" fill="currentColor"/><circle cx="4" cy="12" r="1" fill="currentColor"/><circle cx="4" cy="18" r="1" fill="currentColor"/></svg>
            </button>
            <button type="button" data-hs-editor-ol class="{{ $toolbarBtn }}" title="รายการแบบตัวเลข">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M10 6h11"/><path stroke-linecap="round" d="M10 12h11"/><path stroke-linecap="round" d="M10 18h11"/><path stroke-linecap="round" d="M4 6h1v4"/><path stroke-linecap="round" d="M4 10h2"/><path stroke-linecap="round" d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
            </button>
            <button type="button" data-hs-editor-blockquote class="{{ $toolbarBtn }}" title="อ้างอิง">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M3 10h4v7H3z"/><path stroke-linecap="round" d="M10 10h4v7h-4z"/></svg>
            </button>
            <button type="button" data-hs-editor-code class="{{ $toolbarBtn }}" title="โค้ด">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16 18 6-6-6-6"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 6-6 6 6 6"/></svg>
            </button>

            <span class="mx-1 h-6 w-px bg-gray-200"></span>

            <button type="button" data-hs-editor-align-left class="{{ $toolbarBtn }}" title="ชิดซ้าย">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M3 6h18"/><path stroke-linecap="round" d="M3 12h12"/><path stroke-linecap="round" d="M3 18h16"/></svg>
            </button>
            <button type="button" data-hs-editor-align-center class="{{ $toolbarBtn }}" title="กึ่งกลาง">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M3 6h18"/><path stroke-linecap="round" d="M6 12h12"/><path stroke-linecap="round" d="M4 18h16"/></svg>
            </button>
            <button type="button" data-hs-editor-align-right class="{{ $toolbarBtn }}" title="ชิดขวา">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M3 6h18"/><path stroke-linecap="round" d="M9 12h12"/><path stroke-linecap="round" d="M5 18h16"/></svg>
            </button>

            <span class="mx-1 h-6 w-px bg-gray-200"></span>

            <button type="button" data-hs-editor-link class="{{ $toolbarBtn }}" title="ลิงก์">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path stroke-linecap="round" d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            </button>
            <button type="button" data-hs-editor-image class="{{ $toolbarBtn }}" title="แทรกรูป">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path stroke-linecap="round" d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
            </button>
            <button type="button" data-hs-editor-emoji @click="emojiOpen = !emojiOpen" class="{{ $toolbarBtn }}" title="Emoji">
                <span class="text-base leading-none">😊</span>
            </button>
            <button type="button" data-hs-editor-undo class="{{ $toolbarBtn }}" title="ย้อนกลับ">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v6h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
            </button>
            <button type="button" data-hs-editor-redo class="{{ $toolbarBtn }}" title="ทำซ้ำ">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7v6h-6"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3l3 2.7"/></svg>
            </button>

            <div
                x-show="emojiOpen"
                x-cloak
                class="absolute end-2 top-full z-50 mt-1 grid max-w-xs grid-cols-8 gap-1 rounded-xl border border-gray-200 bg-white p-2 shadow-theme-lg"
            >
                @foreach ($emojis as $emoji)
                    <button
                        type="button"
                        data-hs-editor-emoji-item="{{ $emoji }}"
                        class="flex size-8 items-center justify-center rounded-lg text-lg hover:bg-gray-100"
                    >{{ $emoji }}</button>
                @endforeach
            </div>
        </div>

        <div
            data-hs-editor-field
            class="tiptap-editor-field relative bg-white"
            style="min-height: {{ $height }}px"
        ></div>

        <input type="file" data-hs-editor-image-input class="hidden" accept="image/jpeg,image/png,image/gif,image/webp" />
    </div>

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($required) required @endif
        class="hidden"
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
    @enderror

    <p class="mt-1.5 text-theme-xs text-gray-500">Preline UI Text Editor — รองรับจัดรูปแบบ แทรก emoji และอัปโหลดรูปภาพ</p>
</div>
