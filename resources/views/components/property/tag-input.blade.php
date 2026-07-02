@props([
    'value' => '',
    'tags' => [],
])

@php
    $textareaClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10';
@endphp

<div
    x-data="{
        input: @js(old('tag_names', $value)),
        allTags: @js($tags),
        filter: '',
        get parsedNames() {
            const names = [];
            for (const line of this.input.split(/[\r\n]+/)) {
                for (const part of line.split(/[,;]+/)) {
                    const name = part.trim();
                    if (name !== '' && name !== '-') {
                        names.push(name);
                    }
                }
            }
            return [...new Set(names)];
        },
        get filteredTags() {
            const term = this.filter.trim().toLowerCase();
            const selected = new Set(this.parsedNames.map((name) => name.toLowerCase()));

            return this.allTags
                .filter((name) => !selected.has(name.toLowerCase()))
                .filter((name) => term === '' || name.toLowerCase().includes(term))
                .slice(0, 40);
        },
        isSelected(name) {
            const lower = name.toLowerCase();
            return this.parsedNames.some((item) => item.toLowerCase() === lower);
        },
        toggleTag(name) {
            if (this.isSelected(name)) {
                const lower = name.toLowerCase();
                const next = this.parsedNames.filter((item) => item.toLowerCase() !== lower);
                this.input = next.join(', ');
                return;
            }

            this.input = this.parsedNames.length === 0
                ? name
                : `${this.parsedNames.join(', ')}, ${name}`;
        },
        removeTag(name) {
            const lower = name.toLowerCase();
            this.input = this.parsedNames
                .filter((item) => item.toLowerCase() !== lower)
                .join(', ');
        },
    }"
    class="space-y-3"
>
    <div>
        <label for="tag_names" class="mb-1.5 block text-theme-sm font-medium text-gray-700">แท็กทำเล / กลุ่ม</label>
        <textarea
            id="tag_names"
            name="tag_names"
            rows="3"
            x-model="input"
            placeholder="พิมพ์หรือวางชื่อแท็ก คั่นด้วย comma หรือขึ้นบรรทัดใหม่ เช่น เขตบางนา, ใกล้ BTS"
            class="{{ $textareaClass }} @error('tag_names') border-error-500 @enderror"
        ></textarea>
        @error('tag_names')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
        @enderror
        <p class="mt-1.5 text-theme-xs text-gray-500">พิมพ์ชื่อใหม่ได้เลย ระบบจะสร้างแท็กให้อัตโนมัติ หรือคลิกเลือกจากรายการด้านล่าง</p>
    </div>

    <div
        x-show="parsedNames.length > 0"
        x-cloak
        class="rounded-xl border-2 border-brand-200 bg-brand-50/70 p-4"
    >
        <div class="mb-3 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-brand-800">แท็กที่เลือก</p>
            <span
                class="inline-flex min-h-7 min-w-7 items-center justify-center rounded-full bg-brand-500 px-2.5 text-sm font-semibold text-white"
                x-text="parsedNames.length"
            ></span>
        </div>

        <div class="flex flex-wrap gap-2.5">
            <template x-for="name in parsedNames" :key="name">
                <button
                    type="button"
                    @click="removeTag(name)"
                    class="inline-flex items-center gap-2 rounded-xl border border-brand-300 bg-white px-4 py-2.5 text-sm font-semibold text-brand-800 shadow-theme-xs transition hover:border-error-300 hover:bg-error-50 hover:text-error-700"
                    :title="`ลบ ${name}`"
                >
                    <span x-text="name"></span>
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 text-base leading-none text-brand-700" aria-hidden="true">&times;</span>
                </button>
            </template>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-4">
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-theme-xs font-medium text-gray-600">คลิกเพื่อเพิ่มแท็ก</p>
            <input
                type="search"
                x-model="filter"
                placeholder="ค้นหาแท็ก..."
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 sm:max-w-xs"
            />
        </div>

        <div class="max-h-40 overflow-y-auto">
            <div class="flex flex-wrap gap-2">
                <template x-for="name in filteredTags" :key="name">
                    <button
                        type="button"
                        @click="toggleTag(name)"
                        class="inline-flex rounded-full border border-gray-200 bg-white px-2.5 py-1 text-theme-xs font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
                        x-text="name"
                    ></button>
                </template>
            </div>
            <p x-show="filteredTags.length === 0" x-cloak class="py-4 text-center text-theme-xs text-gray-500">
                ไม่พบแท็กที่ตรงกับคำค้นหา
            </p>
        </div>
    </div>
</div>
