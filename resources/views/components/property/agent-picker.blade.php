@props([
    'agents',
    'selected' => '',
])

@php
    $agentsPayload = $agents->map(fn ($agent) => [
        'id' => $agent->id,
        'name' => $agent->name,
        'usercode' => $agent->usercode,
        'photo' => $agent->profile_image_url,
    ])->values();
@endphp

<div
    x-data="{
        open: false,
        selectedId: @js($selected),
        agents: @js($agentsPayload),
        init() {
            this.$watch('open', value => {
                document.body.style.overflow = value ? 'hidden' : 'unset';
            });
        },
        get selectedAgent() {
            return this.agents.find(agent => agent.id === this.selectedId) ?? null;
        },
        selectAgent(id) {
            this.selectedId = id;
            this.open = false;
        },
        clearAgent() {
            this.selectedId = '';
            this.open = false;
        },
    }"
    {{ $attributes->merge(['class' => '']) }}
>
    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700">ตัวแทน</label>

    <input type="hidden" name="user_id" x-model="selectedId">

    <button
        type="button"
        @click="open = true"
        class="flex h-11 w-full items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-3 text-left text-sm text-gray-800 shadow-theme-xs transition hover:border-brand-300 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10"
    >
        <span class="flex min-w-0 items-center gap-3">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gray-100">
                <img
                    x-show="selectedAgent?.photo"
                    :src="selectedAgent?.photo"
                    alt=""
                    class="h-full w-full object-cover"
                >
            </span>

            <span class="truncate" x-text="selectedAgent ? (selectedAgent.name + (selectedAgent.usercode ? ' (' + selectedAgent.usercode + ')' : '')) : 'เลือกตัวแทน'"></span>
        </span>

        <svg class="shrink-0 text-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5"
    >
        <div
            @click="open = false"
            class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        <div
            @click.stop
            class="relative flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-3xl bg-white shadow-theme-xl"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 sm:px-6">
                <h3 class="text-lg font-semibold text-gray-800">เลือกตัวแทน</h3>
                <button
                    type="button"
                    @click="open = false"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition hover:bg-gray-200 hover:text-gray-700"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill="currentColor"/>
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto p-3 sm:p-4">
                <button
                    type="button"
                    @click="clearAgent()"
                    class="mb-2 flex w-full items-center gap-3 rounded-xl border border-transparent px-3 py-3 text-left transition hover:border-gray-200 hover:bg-gray-50"
                    :class="selectedId === '' ? 'border-brand-200 bg-brand-50' : ''"
                >
                    <span class="flex h-12 w-12 shrink-0 rounded-full bg-gray-100"></span>
                    <span class="text-sm font-medium text-gray-800">ทั้งหมด</span>
                </button>

                <template x-for="agent in agents" :key="agent.id">
                    <button
                        type="button"
                        @click="selectAgent(agent.id)"
                        class="flex w-full items-center gap-3 rounded-xl border border-transparent px-3 py-3 text-left transition hover:border-gray-200 hover:bg-gray-50"
                        :class="selectedId === agent.id ? 'border-brand-200 bg-brand-50' : ''"
                    >
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gray-100">
                            <img
                                x-show="agent.photo"
                                :src="agent.photo"
                                :alt="agent.name"
                                class="h-full w-full object-cover"
                            >
                        </span>

                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-gray-800" x-text="agent.name"></span>
                            <span
                                x-show="agent.usercode"
                                class="mt-0.5 block truncate text-theme-xs text-gray-500"
                                x-text="agent.usercode"
                            ></span>
                        </span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
