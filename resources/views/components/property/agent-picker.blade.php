@props([
'agents',
'selected' => '',
'withPhotos' => true,
'required' => false,
])

@php
$selectedId = (string) old('user_id', $selected);
$agentsPayload = $agents->map(fn ($agent) => [
'id' => (string) $agent->id,
'name' => $agent->name,
'usercode' => $agent->usercode,
'photo' => $withPhotos ? $agent->profile_image_url : null,
'initial' => mb_substr($agent->firstname ?? '', 0, 1) ?: '?',
])->values();
@endphp

<div x-data="{
        open: false,
        query: '',
        selectedId: @js($selectedId),
        agents: @js($agentsPayload),
        init() {
            this.$watch('open', value => {
                document.body.style.overflow = value ? 'hidden' : 'unset';

                if (value) {
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                } else {
                    this.query = '';
                }
            });
        },
        get selectedAgent() {
            return this.agents.find((agent) => String(agent.id) === String(this.selectedId)) ?? null;
        },
        get filteredAgents() {
            const term = this.query.trim().toLowerCase();

            if (term === '') {
                return this.agents;
            }

            return this.agents.filter((agent) => {
                const name = (agent.name ?? '').toLowerCase();
                const usercode = (agent.usercode ?? '').toLowerCase();

                return name.includes(term) || usercode.includes(term);
            });
        },
        selectAgent(id) {
            this.selectedId = id;
            this.open = false;
            this.$nextTick(() => {
                this.$refs.userIdInput?.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.userIdInput?.dispatchEvent(new Event('change', { bubbles: true }));
            });
        },
        clearAgent() {
            this.selectedId = '';
            this.open = false;
            this.$nextTick(() => {
                this.$refs.userIdInput?.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.userIdInput?.dispatchEvent(new Event('change', { bubbles: true }));
            });
        },
    }" {{ $attributes->merge(['class' => '']) }}>
    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700">
        ตัวแทน
        @if ($required)
            <span class="text-error-500">*</span>
        @endif
    </label>

    <input type="hidden" name="user_id" x-ref="userIdInput" x-model="selectedId" @if ($required) required @endif>

    <button type="button" @click="open = true" class="flex h-11 w-full items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-3 text-left text-sm text-gray-800 shadow-theme-xs transition hover:border-brand-300 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 @error('user_id') border-error-500 @enderror">
        <span class="flex min-w-0 items-center gap-3">
            @if ($withPhotos)
            <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gray-100 text-sm font-semibold text-gray-400">
                <img x-show="selectedAgent?.photo" x-cloak :src="selectedAgent?.photo" alt="" class="h-full w-full object-cover">
                <span x-show="selectedAgent && !selectedAgent.photo" x-cloak x-text="selectedAgent?.initial"></span>
            </span>
            @endif

            <span class="truncate" x-text="selectedAgent ? (selectedAgent.name + (selectedAgent.usercode ? ' (' + selectedAgent.usercode + ')' : '')) : 'เลือกตัวแทน'"></span>
        </span>

        <svg class="shrink-0 text-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5">
        <div @click="open = false" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div @click.stop class="relative flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-3xl bg-white shadow-theme-xl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 sm:px-6">
                <h3 class="text-lg font-semibold text-gray-800">เลือกตัวแทน</h3>
                <button type="button" @click="open = false" class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition hover:bg-gray-200 hover:text-gray-700">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill="currentColor" />
                    </svg>
                </button>
            </div>

            <div class="border-b border-gray-200 px-5 py-3 sm:px-6">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input
                        x-ref="searchInput"
                        type="search"
                        x-model="query"
                        placeholder="ค้นหาชื่อหรือรหัสตัวแทน..."
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10"
                        @keydown.escape.stop="open = false"
                    />
                </div>
            </div>

            <div class="overflow-y-auto p-3 sm:p-4">
                @unless ($required)
                <button
                    type="button"
                    x-show="query.trim() === ''"
                    @click="clearAgent()"
                    class="mb-2 flex w-full items-center gap-3 rounded-xl border border-transparent px-3 py-3 text-left transition hover:border-gray-200 hover:bg-gray-50"
                    :class="selectedId === '' ? 'border-brand-200 bg-brand-50' : ''"
                >
                    @if ($withPhotos)
                    <span class="flex h-12 w-12 shrink-0 rounded-full bg-gray-100"></span>
                    @endif
                    <span class="text-sm font-medium text-gray-800">ทั้งหมด</span>
                </button>
                @endunless

                <p x-show="filteredAgents.length === 0" x-cloak class="px-3 py-8 text-center text-sm text-gray-500">
                    ไม่พบตัวแทนที่ตรงกับ "<span x-text="query"></span>"
                </p>

                <template x-for="agent in filteredAgents" :key="agent.id">
                    <button type="button" @click="selectAgent(agent.id)" class="flex w-full items-center gap-3 rounded-xl border border-transparent px-3 py-3 text-left transition hover:border-gray-200 hover:bg-gray-50" :class="String(selectedId) === String(agent.id) ? 'border-brand-200 bg-brand-50' : ''">
                        @if ($withPhotos)
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gray-100 text-sm font-semibold text-gray-400">
                            <img x-show="agent.photo" x-cloak :src="agent.photo" :alt="agent.name" class="h-full w-full object-cover">
                            <span x-show="!agent.photo" x-cloak x-text="agent.initial"></span>
                        </span>
                        @endif

                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-gray-800" x-text="agent.name"></span>
                            <span x-show="agent.usercode" class="mt-0.5 block truncate text-theme-xs text-gray-500" x-text="agent.usercode"></span>
                        </span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
