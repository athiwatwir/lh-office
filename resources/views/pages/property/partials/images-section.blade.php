@if ($item->exists)
    @php
        $initialImages = $item->asset_images
            ->map(fn ($assetImage) => [
                'id' => $assetImage->id,
                'url' => $assetImage->image?->url,
                'isDefault' => $assetImage->isdefault === 'Y',
                'seq' => $assetImage->seq,
            ])
            ->values();
    @endphp

    <section
        x-data="propertyImagesManager({
            initialImages: @js($initialImages),
            uploadUrl: @js(route('property.images.store', $item)),
            defaultUrl: @js(route('property.images.default', ['property' => $item->id, 'assetImage' => '__ID__'])),
            deleteUrl: @js(route('property.images.destroy', ['property' => $item->id, 'assetImage' => '__ID__'])),
            csrf: document.querySelector('meta[name=csrf-token]')?.content ?? '',
        })"
        class="border-t border-gray-200 pt-8"
    >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h4 class="text-base font-semibold text-gray-800">รูปภาพทรัพย์สิน</h4>
                <p class="mt-1 text-sm text-gray-500">อัปโหลดได้ไม่จำกัด ระบบจะปรับขนาดไม่เกิน 1000px และใส่ลายน้ำ copyright อัตโนมัติ</p>
            </div>
            <label class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600" :class="uploading ? 'pointer-events-none opacity-60' : ''">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span x-text="uploading ? 'กำลังอัปโหลด...' : 'เพิ่มรูปภาพ'"></span>
                <input type="file" class="hidden" accept="image/jpeg,image/png,image/gif,image/webp" multiple @change="uploadFiles($event)" :disabled="uploading" />
            </label>
        </div>

        <div
            class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-4 py-8 text-center transition"
            :class="dragOver ? 'border-brand-300 bg-brand-50/50' : ''"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="dropFiles($event)"
        >
            <template x-if="images.length === 0 && !uploading">
                <div>
                    <p class="text-sm text-gray-500">ลากรูปภาพมาวางที่นี่ หรือกดปุ่ม "เพิ่มรูปภาพ"</p>
                    <p class="mt-1 text-xs text-gray-400">รองรับ JPEG, PNG, GIF, WebP สูงสุด 10 MB ต่อไฟล์ (ปรับขนาดไม่เกิน 1000px)</p>
                </div>
            </template>

            @if ($initialImages->isNotEmpty())
                <noscript>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach ($initialImages as $image)
                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                                <img src="{{ $image['url'] }}" alt="รูปทรัพย์" class="aspect-[4/3] w-full object-cover" loading="lazy" />
                            </div>
                        @endforeach
                    </div>
                </noscript>
            @endif

            <template x-if="images.length > 0">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    <template x-for="image in images" :key="image.id">
                        <div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs">
                            <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                                <img :src="image.url" :alt="'รูปทรัพย์ ' + image.seq" class="h-full w-full object-cover" loading="lazy" />
                            </div>

                            <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-1 p-2">
                                <span
                                    x-show="image.isDefault"
                                    class="inline-flex rounded-md bg-brand-500 px-2 py-0.5 text-xs font-medium text-white"
                                >รูปหลัก</span>
                                <span x-show="!image.isDefault" class="inline-flex rounded-md bg-black/50 px-2 py-0.5 text-xs text-white" x-text="'#' + image.seq"></span>

                                <div class="ms-auto flex gap-1 opacity-0 transition group-hover:opacity-100">
                                    <button
                                        type="button"
                                        x-show="!image.isDefault"
                                        @click="setDefault(image)"
                                        :disabled="loadingId === image.id"
                                        class="rounded-md bg-white/90 p-1.5 text-gray-700 shadow-theme-xs transition hover:bg-white disabled:opacity-50"
                                        title="ตั้งเป็นรูปหลัก"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click="remove(image)"
                                        :disabled="loadingId === image.id"
                                        class="rounded-md bg-white/90 p-1.5 text-error-500 shadow-theme-xs transition hover:bg-white disabled:opacity-50"
                                        title="ลบรูป"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </section>
@else
    <section class="border-t border-gray-200 pt-8">
        <h4 class="mb-2 text-base font-semibold text-gray-800">รูปภาพทรัพย์สิน</h4>
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-6 text-sm text-gray-500">
            บันทึกทรัพย์สินก่อน จึงจะสามารถอัปโหลดและจัดการรูปภาพได้
        </div>
    </section>
@endif
