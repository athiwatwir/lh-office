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

    $sourceAssetTypeName = $property->asset_type?->name ?? $property->asset_type_des ?? '';
@endphp

<div class="flex justify-end" x-data="{
        moveOpen: false,
        copyOpen: false,
        deleteOpen: false,
        confirmOpen: false,
        confirmKind: null,
        loading: false,
        isRecommended: @js(($property->isrecommend ?? 'N') === 'Y'),
        agents: @js($agentsPayload),
        currentAgentId: @js($property->agent_id),
        sourceAssetTypeName: @js($sourceAssetTypeName),
        selectedAgent: null,
        targetAssetTypes: [],
        selectedAssetTypeId: null,
        loadingAssetTypes: false,
        get canSubmitAgentAction() {
            if (this.confirmKind !== 'transfer' && this.confirmKind !== 'copy') {
                return true;
            }

            return ! this.loadingAssetTypes && this.selectedAssetTypeId;
        },
        get targetAgents() {
            return this.agents.filter((agent) => String(agent.id) !== String(this.currentAgentId));
        },
        get confirmTitle() {
            if (this.confirmKind === 'transfer') {
                return 'ยืนยันย้าย Agent';
            }

            if (this.confirmKind === 'copy') {
                return 'ยืนยันคัดลอกทรัพย์สิน';
            }

            if (this.confirmKind === 'recommend') {
                return this.isRecommended ? 'ยืนยันยกเลิกทรัพย์แนะนำ' : 'ยืนยันตั้งเป็นทรัพย์แนะนำ';
            }

            return 'ยืนยัน';
        },
        get confirmMessage() {
            if (this.confirmKind === 'transfer') {
                return `ย้ายทรัพย์สินนี้ไปยัง ${this.selectedAgent?.name ?? 'เอเจนต์ที่เลือก'}? ทรัพย์จะหายจากรายการเอเจนต์ปัจจุบัน กรุณาเลือกประเภททรัพย์ในเอเจนต์ปลายทาง`;
            }

            if (this.confirmKind === 'copy') {
                return `คัดลอกข้อมูล รูปภาพ แท็ก และที่อยู่ทั้งหมดไปยัง ${this.selectedAgent?.name ?? 'เอเจนต์ที่เลือก'}? ทรัพย์ต้นฉบับจะยังอยู่ กรุณาเลือกประเภททรัพย์ในเอเจนต์ปลายทาง`;
            }

            if (this.confirmKind === 'recommend') {
                return this.isRecommended
                    ? 'ยกเลิกสถานะทรัพย์แนะนำสำหรับทรัพย์สินนี้?'
                    : 'ตั้งทรัพย์สินนี้เป็นทรัพย์แนะนำ?';
            }

            return '';
        },
        get confirmButtonLabel() {
            if (this.confirmKind === 'transfer') {
                return 'ย้าย';
            }

            if (this.confirmKind === 'copy') {
                return 'คัดลอก';
            }

            if (this.confirmKind === 'recommend') {
                return this.isRecommended ? 'ยกเลิกแนะนำ' : 'ตั้งเป็นแนะนำ';
            }

            return 'ยืนยัน';
        },
        get confirmButtonClass() {
            if (this.confirmKind === 'recommend' && ! this.isRecommended) {
                return 'bg-warning-500 hover:bg-warning-600';
            }

            if (this.confirmKind === 'transfer' || this.confirmKind === 'copy') {
                return 'bg-brand-500 hover:bg-brand-600';
            }

            return 'bg-brand-500 hover:bg-brand-600';
        },
        openMove() {
            this.moveOpen = true;
        },
        closeMove() {
            if (! this.loading) {
                this.moveOpen = false;
            }
        },
        openCopy() {
            this.copyOpen = true;
        },
        closeCopy() {
            if (! this.loading) {
                this.copyOpen = false;
            }
        },
        openDelete() {
            this.deleteOpen = true;
        },
        closeDelete() {
            if (! this.loading) {
                this.deleteOpen = false;
            }
        },
        openConfirm(kind) {
            this.confirmKind = kind;
            this.confirmOpen = true;
        },
        closeConfirm() {
            if (! this.loading) {
                this.confirmOpen = false;
                this.confirmKind = null;
                this.selectedAgent = null;
                this.targetAssetTypes = [];
                this.selectedAssetTypeId = null;
                this.loadingAssetTypes = false;
            }
        },
        async loadTargetAssetTypes() {
            if (! this.selectedAgent?.id) {
                return false;
            }

            this.loadingAssetTypes = true;
            this.selectedAssetTypeId = null;
            this.targetAssetTypes = [];

            try {
                const url = new URL(@js(route('property.agent-asset-types')), window.location.origin);
                url.searchParams.set('agent_id', this.selectedAgent.id);

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    throw new Error(data.message ?? 'โหลดประเภททรัพย์ไม่สำเร็จ');
                }

                this.targetAssetTypes = data.data ?? [];

                const matchedType = this.targetAssetTypes.find(
                    (type) => type.name === this.sourceAssetTypeName,
                );

                if (matchedType) {
                    this.selectedAssetTypeId = matchedType.id;
                } else if (this.targetAssetTypes.length === 1) {
                    this.selectedAssetTypeId = this.targetAssetTypes[0].id;
                }

                return true;
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'โหลดประเภททรัพย์ไม่สำเร็จ');

                return false;
            } finally {
                this.loadingAssetTypes = false;
            }
        },
        async prepareTransfer(agent) {
            this.loading = true;
            this.selectedAgent = agent;
            this.moveOpen = false;

            try {
                if (! await this.loadTargetAssetTypes()) {
                    this.selectedAgent = null;
                    this.moveOpen = true;

                    return;
                }

                this.openConfirm('transfer');
            } finally {
                this.loading = false;
            }
        },
        async prepareCopy(agent) {
            this.loading = true;
            this.selectedAgent = agent;
            this.copyOpen = false;

            try {
                if (! await this.loadTargetAssetTypes()) {
                    this.selectedAgent = null;
                    this.copyOpen = true;

                    return;
                }

                this.openConfirm('copy');
            } finally {
                this.loading = false;
            }
        },
        openRecommendConfirm() {
            this.openConfirm('recommend');
        },
        async runConfirm() {
            if (this.loading) {
                return;
            }

            if (this.confirmKind === 'transfer') {
                await this.doTransfer();
            } else if (this.confirmKind === 'copy') {
                await this.doCopy();
            } else if (this.confirmKind === 'recommend') {
                await this.doToggleRecommend();
            }
        },
        async confirmDelete() {
            if (this.loading) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(@js(route('property.destroy', $property)), {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    throw new Error(data.message ?? 'ลบทรัพย์สินไม่สำเร็จ');
                }

                Alpine.store('notify').success(data.message ?? 'ลบทรัพย์สินถาวรเรียบร้อยแล้ว');
                this.deleteOpen = false;
                this.$el.closest('tr')?.remove();
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'ลบทรัพย์สินไม่สำเร็จ');
            } finally {
                this.loading = false;
            }
        },
        async doTransfer() {
            const agentId = this.selectedAgent?.id;
            const assetTypeId = this.selectedAssetTypeId;

            if (! agentId || ! assetTypeId || String(agentId) === String(this.currentAgentId)) {
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
                    body: JSON.stringify({
                        agent_id: agentId,
                        asset_type_id: assetTypeId,
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    throw new Error(data.message ?? 'ย้ายเอเจนต์ไม่สำเร็จ');
                }

                Alpine.store('notify').success(data.message ?? 'ย้ายทรัพย์สินเรียบร้อยแล้ว');
                this.closeConfirm();
                this.$el.closest('tr')?.remove();
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'ย้ายเอเจนต์ไม่สำเร็จ');
            } finally {
                this.loading = false;
            }
        },
        async doCopy() {
            const agentId = this.selectedAgent?.id;
            const assetTypeId = this.selectedAssetTypeId;

            if (! agentId || ! assetTypeId || String(agentId) === String(this.currentAgentId)) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(@js(route('property.copy', $property)), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        agent_id: agentId,
                        asset_type_id: assetTypeId,
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    throw new Error(data.message ?? 'คัดลอกทรัพย์สินไม่สำเร็จ');
                }

                Alpine.store('notify').success(data.message ?? 'คัดลอกทรัพย์สินเรียบร้อยแล้ว');
                this.closeConfirm();
                this.copyOpen = false;
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'คัดลอกทรัพย์สินไม่สำเร็จ');
            } finally {
                this.loading = false;
            }
        },
        async doToggleRecommend() {
            const next = ! this.isRecommended;

            this.loading = true;

            try {
                const response = await fetch(@js(route('property.isrecommend.update', $property)), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                    body: JSON.stringify({ isrecommend: next }),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    throw new Error(data.message ?? 'อัปเดตทรัพย์แนะนำไม่สำเร็จ');
                }

                Alpine.store('notify').success(data.message ?? 'อัปเดตทรัพย์แนะนำเรียบร้อยแล้ว');
                this.closeConfirm();
                window.location.reload();
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'อัปเดตทรัพย์แนะนำไม่สำเร็จ');
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

            <button type="button" @click="openRecommendConfirm()" :disabled="loading" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-700 hover:bg-gray-100" role="menuitem">
                <span class="inline-flex text-base" :class="isRecommended ? 'text-warning-500' : 'text-gray-500'">
                    <i class="lni lni-star-fat" aria-hidden="true"></i>
                </span>
                <span x-text="isRecommended ? 'ยกเลิกทรัพย์แนะนำ' : 'ตั้งเป็นทรัพย์แนะนำ'"></span>
            </button>

            <button type="button" @click="openMove()" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-700 hover:bg-gray-100" role="menuitem">
                <i class="lni lni-arrow-right text-base text-gray-500"></i>
                ย้าย Agent
            </button>

            <button type="button" @click="openCopy()" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-700 hover:bg-gray-100" role="menuitem">
                <i class="lni lni-copy text-base text-gray-500"></i>
                คัดลอกไป Agent อื่น
            </button>

            <button type="button" @click="openDelete()" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-error-600 hover:bg-error-50" role="menuitem">
                <i class="lni lni-trash-can text-base"></i>
                ลบ
            </button>
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
                    <button type="button" @click="prepareTransfer(agent)" :disabled="loading" class="flex w-full items-center gap-3 rounded-xl border border-transparent px-3 py-3 text-left transition hover:border-gray-200 hover:bg-gray-50 disabled:opacity-50">
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

    <div x-show="copyOpen" x-cloak @keydown.escape.window="closeCopy()" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5">
        <div @click="closeCopy()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]"></div>

        <div @click.stop class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-theme-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">คัดลอกไป Agent อื่น</h3>
                    <p class="mt-0.5 text-theme-xs text-gray-500">{{ $property->code }} — {{ $property->name }}</p>
                    <p class="mt-1 text-theme-xs text-gray-400">คัดลอกข้อมูล รูปภาพ แท็ก และที่อยู่ทั้งหมด</p>
                </div>
                <button type="button" @click="closeCopy()" :disabled="loading" class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition hover:bg-gray-200 hover:text-gray-700 disabled:opacity-50">
                    <i class="lni lni-close"></i>
                </button>
            </div>

            <div class="max-h-72 overflow-y-auto p-3">
                <template x-if="targetAgents.length === 0">
                    <p class="px-2 py-6 text-center text-sm text-gray-500">ไม่มีเอเจนต์ปลายทางให้เลือก</p>
                </template>

                <template x-for="agent in targetAgents" :key="'copy-' + agent.id">
                    <button type="button" @click="prepareCopy(agent)" :disabled="loading" class="flex w-full items-center gap-3 rounded-xl border border-transparent px-3 py-3 text-left transition hover:border-gray-200 hover:bg-gray-50 disabled:opacity-50">
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

    <div x-show="confirmOpen" x-cloak @keydown.escape.window="closeConfirm()" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5">
        <div @click="closeConfirm()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]"></div>

        <div @click.stop class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-theme-xl">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-800" x-text="confirmTitle"></h3>
                <p class="mt-1 text-theme-xs text-gray-500">{{ $property->code }} — {{ $property->name }}</p>
            </div>

            <div class="px-5 py-4">
                <p class="text-sm text-gray-700" x-text="confirmMessage"></p>
                <p x-show="selectedAgent && (confirmKind === 'transfer' || confirmKind === 'copy')" x-cloak class="mt-3 rounded-lg bg-gray-50 px-3 py-2 text-theme-xs text-gray-600">
                    เอเจนต์ปลายทาง: <span class="font-semibold text-gray-800" x-text="selectedAgent?.name"></span>
                    <span x-show="selectedAgent?.code" x-text="' (' + selectedAgent.code + ')'"></span>
                </p>

                <div x-show="confirmKind === 'transfer' || confirmKind === 'copy'" x-cloak class="mt-4">
                    <label for="target-asset-type-{{ $property->id }}" class="mb-1.5 block text-theme-sm font-medium text-gray-700">
                        ประเภททรัพย์ในเอเจนต์ปลายทาง <span class="text-error-500">*</span>
                    </label>
                    <p class="mb-2 text-theme-xs text-gray-500">
                        ประเภทปัจจุบัน:
                        <span class="font-medium text-gray-700" x-text="sourceAssetTypeName || '-'"></span>
                    </p>

                    <p x-show="loadingAssetTypes" class="text-theme-xs text-gray-500">กำลังโหลดประเภททรัพย์...</p>

                    <select
                        x-show="! loadingAssetTypes && targetAssetTypes.length > 0"
                        id="target-asset-type-{{ $property->id }}"
                        x-model="selectedAssetTypeId"
                        :disabled="loading"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 disabled:opacity-50"
                    >
                        <option value="">-- เลือกประเภททรัพย์ --</option>
                        <template x-for="type in targetAssetTypes" :key="type.id">
                            <option :value="type.id" x-text="type.name"></option>
                        </template>
                    </select>

                    <p x-show="! loadingAssetTypes && targetAssetTypes.length === 0" class="text-theme-xs text-error-500">
                        เอเจนต์ปลายทางยังไม่มีประเภททรัพย์ กรุณาสร้างประเภททรัพย์ก่อน
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4">
                <button type="button" @click="closeConfirm()" :disabled="loading" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50">
                    ยกเลิก
                </button>
                <button
                    type="button"
                    @click="runConfirm()"
                    :disabled="loading || ! canSubmitAgentAction"
                    class="inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-medium text-white transition disabled:opacity-50"
                    :class="confirmButtonClass"
                >
                    <span x-show="! loading" x-text="confirmButtonLabel"></span>
                    <span x-show="loading">กำลังดำเนินการ...</span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="deleteOpen" x-cloak @keydown.escape.window="closeDelete()" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 sm:p-5">
        <div @click="closeDelete()" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]"></div>

        <div @click.stop class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-theme-xl">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-800">ลบทรัพย์สินถาวร</h3>
                <p class="mt-1 text-theme-xs text-gray-500">{{ $property->code }} — {{ $property->name }}</p>
            </div>

            <div class="px-5 py-4">
                <p class="text-sm text-gray-700">
                    การลบนี้จะลบข้อมูลทรัพย์สิน รูปภาพ และข้อมูลที่เกี่ยวข้องออกจากระบบถาวร
                    <span class="font-medium text-error-600">ไม่สามารถกู้คืนได้</span>
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4">
                <button type="button" @click="closeDelete()" :disabled="loading" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50">
                    ยกเลิก
                </button>
                <button type="button" @click="confirmDelete()" :disabled="loading" class="inline-flex h-10 items-center justify-center rounded-lg bg-error-500 px-4 text-sm font-medium text-white transition hover:bg-error-600 disabled:opacity-50">
                    <span x-show="! loading">ลบถาวร</span>
                    <span x-show="loading">กำลังลบ...</span>
                </button>
            </div>
        </div>
    </div>
</div>
