@props([
    'cancelUrl',
    'submitLabel' => 'บันทึก',
    'cancelLabel' => 'ยกเลิก',
])

<div class="-mx-5 mt-8 flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-5 sm:-mx-6 sm:flex-row sm:items-center sm:justify-end sm:px-6">
    <a
        href="{{ $cancelUrl }}"
        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border-2 border-gray-300 bg-white px-6 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:border-gray-400 hover:bg-gray-100"
    >
        <i class="lni lni-close text-base" aria-hidden="true"></i>
        {{ $cancelLabel }}
    </a>

    <button
        type="submit"
        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
    >
        <i class="lni lni-check-circle-1 text-base" aria-hidden="true"></i>
        {{ $submitLabel }}
    </button>
</div>
