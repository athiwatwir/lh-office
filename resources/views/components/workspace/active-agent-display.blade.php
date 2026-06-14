@if ($activeAgent)
    <div class="flex items-center gap-3">
        <button
            type="button"
            @click="$store.activeAgent.open()"
            class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-3 py-2 text-left shadow-theme-xs transition hover:border-brand-300 hover:bg-gray-50"
            title="เปลี่ยนเอเจนต์"
        >
            <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                @if ($activeAgent->logo_url)
                    <img src="{{ $activeAgent->logo_url }}" alt="{{ $activeAgent->name }}" class="h-full w-full object-cover">
                @else
                    <span class="text-sm font-semibold text-gray-400">{{ mb_substr($activeAgent->name, 0, 1) }}</span>
                @endif
            </span>

            <span class="min-w-0">
                <span class="block text-theme-xs text-gray-500">เอเจนต์ที่ใช้งาน</span>
                <span class="block max-w-[160px] truncate text-sm font-semibold text-gray-800">{{ $activeAgent->name }}</span>
            </span>

            <svg class="shrink-0 text-gray-400" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
@endif
