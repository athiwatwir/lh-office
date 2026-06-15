@props([
'property',
'agents' => collect(),
])

@php
    use App\Models\Agent;

    $agentOptions = $agents->isNotEmpty()
        ? $agents
        : Agent::query()->orderBy('name')->get(['id', 'name', 'code']);

    $agentsPayload = $agentOptions
        ->map(fn ($agent) => [
            'id' => $agent->id,
            'name' => $agent->name,
            'code' => $agent->code,
        ])
        ->values();
@endphp

<div class="flex justify-end" x-data="{
        moveOpen: false,
        loading: false,
        agents: @js($agentsPayload),
        currentAgentId: @js($property->agent_id),
        get targetAgents() {
            return this.agents.filter((agent) => String(agent.id) !== String(this.currentAgentId));
        },
        openMove() {
            this.moveOpen = true;
        },
        closeMove() {
            if (! this.loading) {
                this.moveOpen = false;
            }
        },
        async transfer(agentId) {
            if (this.loading || String(agentId) === String(this.currentAgentId)) {
                return;
            }

            if (! window.confirm('ย้ายทรัพย์สินนี้ไปยังเอเจนต์ที่เลือก?')) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(@js(route('property.agent.update', $property)), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                    body: JSON.stringify({ agent_id: agentId }),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    throw new Error(data.message ?? 'ย้ายเอเจนต์ไม่สำเร็จ');
                }

                Alpine.store('notify').success(data.message ?? 'ย้ายทรัพย์สินเรียบร้อยแล้ว');
                this.moveOpen = false;
                this.$el.closest('tr')?.remove();
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'ย้ายเอเจนต์ไม่สำเร็จ');
            } finally {
                this.loading = false;
            }
        },
    }">
    <x-common.table-dropdown menu-width="min-w-44">
        <x-slot name="button">
            <button type="button" aria-haspopup="true" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-50 hover:text-gray-700">
                <svg class="size-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" />
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <a href="{{ route('property.edit', $property) }}" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-700 hover:bg-gray-100" role="menuitem">
                <i class="lni lni-pencil text-base text-gray-500"></i>
                แก้ไข
            </a>

            <button type="button" @click="openMove()" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-700 hover:bg-gray-100" role="menuitem">
                <i class="lni lni-arrow-right text-base text-gray-500"></i>
                ย้าย Agent
            </button>

            <form method="POST" action="{{ route('property.destroy', $property) }}" onsubmit="return confirm('ยืนยันการลบทรัพย์สินนี้?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-error-600 hover:bg-error-50" role="menuitem">
                    <i class="lni lni-trash-can text-base"></i>
                    ลบ
                </button>
            </form>
        </x-slot>
    </x-common.table-dropdown>

    <div x-show="moveOpen" x-cloak @keydown.escape.window="closeMove()" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5">
        <div @click="closeMove()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]"></div>

        <div @click.stop class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-theme-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">ย้าย Agent</h3>
                    <p class="mt-0.5 text-theme-xs text-gray-500">{{ $property->code }} — {{ $property->name }}</p>
                </div>
                <button type="button" @click="closeMove()" :disabled="loading" class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition hover:bg-gray-200 hover:text-gray-700 disabled:opacity-50">
                    <i class="lni lni-close"></i>
                </button>
            </div>

            <div class="max-h-72 overflow-y-auto p-3">
                <template x-if="targetAgents.length === 0">
                    <p class="px-2 py-6 text-center text-sm text-gray-500">ไม่มีเอเจนต์ปลายทางให้เลือก</p>
                </template>

                <template x-for="agent in targetAgents" :key="agent.id">
                    <button type="button" @click="transfer(agent.id)" :disabled="loading" class="flex w-full items-center gap-3 rounded-xl border border-transparent px-3 py-3 text-left transition hover:border-gray-200 hover:bg-gray-50 disabled:opacity-50">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-sm font-semibold text-brand-600" x-text="agent.name?.charAt(0) ?? '?'"></span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-gray-800" x-text="agent.name"></span>
                            <span class="mt-0.5 block text-theme-xs text-gray-500" x-text="agent.code ?? ''"></span>
                        </span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
