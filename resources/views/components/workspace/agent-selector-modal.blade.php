@props([
    'activeAgent' => null,
    'requiresSelection' => null,
    'agents' => null,
])

@php
    use App\Models\Agent;
    use App\Services\ActiveAgentService;

    $service = app(ActiveAgentService::class);
    $activeAgent ??= $service->agent();
    $requiresSelection ??= ! $service->hasAgent();
    $agents ??= view()->shared('workspaceAgents')
        ?? Agent::query()->orderBy('name')->get(['id', 'name', 'code', 'logo']);

    $agentsPayload = $agents->map(fn ($agent) => [
        'id' => $agent->id,
        'name' => $agent->name,
        'code' => $agent->code,
        'logo' => $agent->logo_url,
    ])->values();
@endphp

<div
    x-data="{
        agents: @js($agentsPayload),
        init() {
            if (@js($requiresSelection)) {
                this.$store.activeAgent.setRequired(true);
            }

            this.$watch('$store.activeAgent.modalOpen', value => {
                document.body.style.overflow = value ? 'hidden' : 'unset';
            });
        },
        canClose() {
            return ! this.$store.activeAgent.required;
        },
        tryClose() {
            if (this.canClose()) {
                this.$store.activeAgent.close();
            }
        },
    }"
    x-show="$store.activeAgent.modalOpen"
    x-cloak
    @keydown.escape.window="tryClose()"
    class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5"
>
    <div
        @click="tryClose()"
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
        class="relative flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl bg-white shadow-theme-xl"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        @if ($requiresSelection)
                            กรุณาเลือกเอเจนต์ก่อนใช้งาน
                        @else
                            เปลี่ยนเอเจนต์
                        @endif
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if ($requiresSelection)
                            ระบบจะบันทึกเอเจนต์ที่เลือกไว้ใน session สำหรับการใช้งานครั้งนี้
                        @else
                            เลือกเอเจนต์ที่ต้องการใช้งานในระบบ
                        @endif
                    </p>
                </div>

                <button
                    type="button"
                    x-show="canClose()"
                    @click="tryClose()"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition hover:bg-gray-200 hover:text-gray-700"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill="currentColor"/>
                    </svg>
                </button>
            </div>

            @if ($requiresSelection)
                <div class="mt-4 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3">
                    <p class="text-sm font-medium text-warning-700">ยังไม่ได้เลือกเอเจนต์ — กรุณาเลือกก่อนดำเนินการต่อ</p>
                </div>
            @endif
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4 sm:px-6">
            @if ($agents->isEmpty())
                <div class="py-10 text-center">
                    <p class="text-base font-medium text-gray-800">ยังไม่มีเอเจนต์ในระบบ</p>
                    <p class="mt-1 text-sm text-gray-500">กรุณาเพิ่มเอเจนต์ก่อนเริ่มใช้งาน</p>
                    <a
                        href="{{ route('agent.create') }}"
                        class="mt-4 inline-flex h-10 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600"
                    >
                        เพิ่มเอเจนต์
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($agents as $agent)
                        <form method="POST" action="{{ route('active-agent.store') }}">
                            @csrf
                            <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 text-left transition hover:border-brand-300 hover:bg-brand-50/40 {{ $activeAgent?->id === $agent->id ? 'border-brand-300 bg-brand-50 ring-2 ring-brand-500/10' : '' }}"
                            >
                                <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                    @if ($agent->logo_url)
                                        <img src="{{ $agent->logo_url }}" alt="{{ $agent->name }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-lg font-semibold text-gray-400">{{ mb_substr($agent->name, 0, 1) }}</span>
                                    @endif
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-gray-800">{{ $agent->name }}</span>
                                    @if ($agent->code)
                                        <span class="mt-0.5 block text-theme-xs text-gray-500">{{ $agent->code }}</span>
                                    @endif
                                </span>
                            </button>
                        </form>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none;
    }
</style>
