@csrf
@method($method)

@php
$inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10';
$selectClass = $inputClass;
$siteFeatures = $siteFeatures ?? ['zone' => true, 'special_price' => false];
$ynFields = [
'issale' => 'ขาย',
'isrent' => 'เช่า',
'issalerent' => 'ขาย/เช่า',
'issellout' => 'เซลล์เอาท์',
'issaledown' => 'ขายดาวน์',
'iscovering' => 'กำลังปรับปรุง',
'isdweller' => 'มีผู้อยู่อาศัย',
];
@endphp

<div class="space-y-8">
    <section>
        <h4 class="mb-4 text-base font-semibold text-gray-800">ข้อมูลหลัก</h4>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <x-property.code-input :value="old('code', $item->code)" :property-id="$item->exists ? $item->id : null" :agent-name="($item->exists ? $item->agent?->name : null) ?? ($activeAgent->name ?? null)" :input-class="$inputClass" />

            <div>
                <label for="asset_type_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ประเภททรัพย์ <span class="text-error-500">*</span></label>
                <select id="asset_type_id" name="asset_type_id" required class="{{ $selectClass }} @error('asset_type_id') border-error-500 @enderror">
                    <option value="">— เลือก —</option>
                    @foreach ($assetTypes as $assetType)
                    <option value="{{ $assetType->id }}" @selected(old('asset_type_id', $item->asset_type_id) === $assetType->id)>{{ $assetType->name }}</option>
                    @endforeach
                </select>
                @error('asset_type_id')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ชื่อทรัพย์ <span class="text-error-500">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $item->name) }}" required class="{{ $inputClass }} @error('name') border-error-500 @enderror" />
                @error('name')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2" x-data="{
                    previewOpen: false,
                    previewHtml: '',
                    openDescriptionPreview() {
                        const textarea = document.getElementById('description');
                        this.previewHtml = (textarea?.value ?? '').trim();
                        this.previewOpen = true;
                        document.body.style.overflow = 'hidden';
                    },
                    closeDescriptionPreview() {
                        this.previewOpen = false;
                        document.body.style.overflow = 'unset';
                    },
                }">
                <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                    <label for="description" class="block text-theme-sm font-medium text-gray-700">รายละเอียด</label>
                    <button type="button" @click="openDescriptionPreview()" class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-lg border border-brand-300 bg-brand-50 px-3 text-theme-xs font-semibold text-brand-700 shadow-theme-xs transition hover:bg-brand-100">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1 1 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        ดูตัวอย่าง
                    </button>
                </div>

                <x-form.wysiwyg-editor name="description" :value="old('description', $item->description)" :height="400" />

                <div x-show="previewOpen" x-cloak @keydown.escape.window="closeDescriptionPreview()" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5">
                    <div @click="closeDescriptionPreview()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

                    <div @click.stop class="relative flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-theme-xl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 sm:px-6">
                            <h3 class="text-lg font-semibold text-gray-800">ตัวอย่างรายละเอียด</h3>
                            <button type="button" @click="closeDescriptionPreview()" class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition hover:bg-gray-200 hover:text-gray-700">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill="currentColor" />
                                </svg>
                            </button>
                        </div>

                        <div class="overflow-y-auto px-5 py-5 sm:px-6 sm:py-6">
                            <div x-show="previewHtml !== ''" x-html="previewHtml" class="prose prose-sm tiptap-editor-content max-w-none text-gray-700"></div>
                            <p x-show="previewHtml === ''" x-cloak class="py-10 text-center text-sm text-gray-500">
                                ยังไม่มีเนื้อหา — กรอกรายละเอียดก่อนแล้วกดดูตัวอย่างอีกครั้ง
                            </p>
                        </div>

                        <div class="border-t border-gray-200 px-5 py-4 sm:px-6">
                            <button type="button" @click="closeDescriptionPreview()" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:w-auto">
                                ปิด
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <label for="youtube_link" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ลิงก์ YouTube</label>
                <input id="youtube_link" type="url" name="youtube_link" value="{{ old('youtube_link', $item->youtube_link) }}" placeholder="https://www.youtube.com/watch?v=..." class="{{ $inputClass }} @error('youtube_link') border-error-500 @enderror" />
                @error('youtube_link')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            @if ($siteFeatures['zone'] ?? true)
            <div>
                <label for="zone_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700">โซน <span class="text-error-500">*</span></label>
                <select id="zone_id" name="zone_id" required class="{{ $selectClass }} @error('zone_id') border-error-500 @enderror">
                    <option value="">— เลือก —</option>
                    @foreach ($zones as $zone)
                    <option value="{{ $zone->id }}" @selected(old('zone_id', $item->zone_id) === $zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
                @error('zone_id')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>
            @endif

            <div @class(['md:col-span-2'=> ! ($siteFeatures['zone'] ?? true)])>
                <x-property.agent-picker :agents="$agents" :selected="old('user_id', $item->user_id)" required />
                @error('user_id')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <x-property.tag-input :value="$item->exists ? \App\Services\PropertyTagService::namesToText($item) : ''" :tags="$tags" />
            </div>

        </div>
    </section>

    <section>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">


            @if ($siteFeatures['special_price'] ?? false)
            <input type="hidden" name="isspecial_marketprice" value="1">

            <div class="md:col-span-3 overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-gray-50/80 shadow-theme-xs">
                <div class="border-b border-gray-100 px-5 py-4">
                    <p class="text-sm font-semibold text-gray-800">ราคาขายและราคาพิเศษ</p>
                    <p class="mt-0.5 text-theme-xs text-gray-500">กำหนดราคาขายและราคาพิเศษสำหรับแสดงบนหน้าเว็บ</p>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-3">
                    <div>
                        <label for="price_amounnt" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ราคาตลาด (บาท)</label>
                        <input id="price_amounnt" type="number" name="price_amounnt" min="0" step="0.01" value="{{ old('price_amounnt', $item->price_amounnt) }}" class="{{ $inputClass }} @error('price_amounnt') border-error-500 @enderror" />
                        @error('price_amounnt')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="price_special" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ราคาพิเศษ (บาท)</label>
                        <input id="price_special" type="number" name="price_special" min="0" step="0.01" value="{{ old('price_special', $item->price_special) }}" class="{{ $inputClass }} @error('price_special') border-error-500 @enderror" />
                        @error('price_special')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="price_per_wah" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ราคาต่อ ตรว. (บาท)</label>
                        <input id="price_per_wah" type="number" name="price_per_wah" min="0" step="0.01" value="{{ old('price_per_wah', $item->price_per_wah) }}" class="{{ $inputClass }} @error('price_per_wah') border-error-500 @enderror" />
                        @error('price_per_wah')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
            @else
            <div>
                <label for="price_amounnt" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ราคาขาย (บาท)</label>
                <input id="price_amounnt" type="number" name="price_amounnt" min="0" step="0.01" value="{{ old('price_amounnt', $item->price_amounnt) }}" class="{{ $inputClass }} @error('price_amounnt') border-error-500 @enderror" />
                @error('price_amounnt')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="price_per_wah" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ราคาต่อ ตรว. (บาท)</label>
                <input id="price_per_wah" type="number" name="price_per_wah" min="0" step="0.01" value="{{ old('price_per_wah', $item->price_per_wah) }}" class="{{ $inputClass }} @error('price_per_wah') border-error-500 @enderror" />
                @error('price_per_wah')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="price_rent" class="mb-1.5 block text-theme-sm font-medium text-gray-700">ราคาเช่า (บาท)</label>
                <input id="price_rent" type="number" name="price_rent" min="0" step="0.01" value="{{ old('price_rent', $item->price_rent) }}" class="{{ $inputClass }} @error('price_rent') border-error-500 @enderror" />
                @error('price_rent')<p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>@enderror
            </div>
            @endif

        </div>
    </section>

    @include('pages.property.partials.address-section', ['item' => $item, 'inputClass' => $inputClass])

    @include('pages.property.partials.images-section', ['item' => $item])

</div>

@php
$guard = $guard ?? false;
@endphp

@if ($guard)
<button type="submit" class="sr-only" tabindex="-1" aria-hidden="true"></button>
<div class="h-40" aria-hidden="true"></div>
@include('pages.property.partials.form-readiness')
@else
<x-form.actions :cancel-url="route('property.index')" />
@endif
