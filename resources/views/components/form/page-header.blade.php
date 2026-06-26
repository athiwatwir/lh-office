@props([
    'title',
    'subtitle' => null,
    'backUrl',
    'backLabel' => 'กลับรายการ',
])

<div class="border-b border-gray-200 px-5 py-4 sm:px-6">
    <a
        href="{{ $backUrl }}"
        class="mb-4 inline-flex items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-3.5 py-2 text-sm font-semibold text-brand-700 transition hover:border-brand-300 hover:bg-brand-100"
    >
        <i class="lni lni-arrow-left text-base" aria-hidden="true"></i>
        {{ $backLabel }}
    </a>

    <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>

    @if ($subtitle)
        <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
    @endif
</div>
