<div
    class="fixed bottom-0 z-40 transition-[left] duration-300 ease-in-out"
    :class="{
        'left-0 right-0': true,
        'xl:left-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
        'xl:left-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
    }"
>
    <div
        class="h-1.5 bg-gray-100"
        role="progressbar"
        :aria-valuenow="progressPercent"
        aria-valuemin="0"
        aria-valuemax="100"
    >
        <div
            class="h-full rounded-e-full transition-all duration-500 ease-out"
            :class="canSubmit
                ? 'bg-gradient-to-r from-success-400 via-success-500 to-success-600'
                : 'bg-gradient-to-r from-brand-400 via-brand-500 to-brand-600'"
            :style="`width: ${progressPercent}%`"
        ></div>
    </div>

    <div
        class="border-t shadow-[0_-8px_32px_rgba(70,95,255,0.12)] backdrop-blur-md"
        :class="canSubmit
            ? 'border-success-200 bg-gradient-to-r from-success-50/95 via-white to-emerald-50/80'
            : 'border-brand-200 bg-gradient-to-r from-brand-50/95 via-white to-indigo-50/60'"
    >
        <div class="mx-auto max-w-(--breakpoint-2xl) px-4 py-4 md:px-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:gap-5">
                <div class="flex min-w-0 flex-1 items-start gap-3 sm:items-center sm:gap-4">
                    <div class="relative flex h-14 w-14 shrink-0 items-center justify-center">
                        <svg class="h-14 w-14 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                            <circle
                                cx="18"
                                cy="18"
                                r="15.5"
                                fill="none"
                                class="stroke-gray-200"
                                stroke-width="3"
                            />
                            <circle
                                cx="18"
                                cy="18"
                                r="15.5"
                                fill="none"
                                class="transition-all duration-500 ease-out"
                                :class="canSubmit ? 'stroke-success-500' : 'stroke-brand-500'"
                                stroke-width="3"
                                stroke-linecap="round"
                                pathLength="100"
                                :stroke-dasharray="`${progressPercent} 100`"
                            />
                        </svg>
                        <span
                            class="absolute text-xs font-bold tabular-nums"
                            :class="canSubmit ? 'text-success-700' : 'text-brand-700'"
                            x-text="`${progressPercent}%`"
                        ></span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="text-sm font-bold text-gray-900">ความพร้อมก่อนบันทึก</h4>
                            <span
                                x-show="canSubmit"
                                x-cloak
                                class="inline-flex items-center gap-1 rounded-full bg-success-500 px-2.5 py-0.5 text-theme-xs font-semibold text-white shadow-sm"
                            >
                                <i class="lni lni-check-circle-1 text-sm" aria-hidden="true"></i>
                                พร้อมบันทึก
                            </span>
                            <span
                                x-show="!canSubmit"
                                x-cloak
                                class="inline-flex items-center gap-1 rounded-full bg-warning-100 px-2.5 py-0.5 text-theme-xs font-semibold text-warning-700 ring-1 ring-warning-200"
                            >
                                <i class="lni lni-alarm-1 text-sm" aria-hidden="true"></i>
                                เหลืออีก <span x-text="totalCount - completedCount"></span> รายการ
                            </span>
                        </div>
                        <p class="mt-0.5 text-theme-xs text-gray-500">
                            กรอกข้อมูลที่จำเป็นให้ครบ — ครบแล้ว <span class="font-semibold text-brand-600" x-text="completedCount"></span>/<span x-text="totalCount"></span> รายการ
                        </p>

                        <ul class="mt-3 flex gap-2 overflow-x-auto pb-0.5 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                            <template x-for="check in checks" :key="check.key">
                                <li
                                    class="inline-flex shrink-0 items-center gap-2 rounded-xl border px-3 py-1.5 text-theme-xs font-semibold shadow-theme-xs transition-colors"
                                    :class="check.done
                                        ? 'border-success-300 bg-success-100 text-success-800'
                                        : 'border-warning-200 bg-warning-50 text-warning-800'"
                                    :title="check.hint && !check.done ? check.hint : ''"
                                >
                                    <span
                                        class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-bold"
                                        :class="check.done ? 'bg-success-500 text-white' : 'bg-warning-400 text-white'"
                                    >
                                        <span x-show="check.done">✓</span>
                                        <span x-show="!check.done">!</span>
                                    </span>
                                    <span x-text="check.label"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div class="flex shrink-0 flex-col gap-2 border-gray-200 sm:flex-row sm:items-center lg:border-s lg:ps-5">
                    <a
                        href="{{ route('property.index') }}"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border-2 border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:border-gray-400 hover:bg-gray-50"
                    >
                        <i class="lni lni-close text-base" aria-hidden="true"></i>
                        ยกเลิก
                    </a>

                    <button
                        type="button"
                        @click="submitForm()"
                        :class="canSubmit
                            ? 'inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-6 text-sm font-bold text-white shadow-lg shadow-brand-500/30 transition hover:from-brand-600 hover:to-brand-700 hover:shadow-brand-500/40 focus:outline-none focus:ring-2 focus:ring-brand-500/40'
                            : 'inline-flex h-11 cursor-not-allowed items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-100 px-6 text-sm font-semibold text-gray-400'"
                    >
                        <i class="lni lni-check-circle-1 text-base" aria-hidden="true"></i>
                        บันทึกทรัพย์สิน
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
