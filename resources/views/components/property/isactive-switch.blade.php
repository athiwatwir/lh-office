@props([
'property',
'active' => false,
])

<div x-data="{
        active: @js($active),
        loading: false,
        async toggle() {
            if (this.loading) {
                return;
            }

            const next = !this.active;
            this.loading = true;

            try {
                const response = await fetch(@js(route('property.isactive.update', $property)), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                    body: JSON.stringify({ isactive: next }),
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const data = await response.json();
                this.active = data.isactive === 'Y';

                Alpine.store('notify').success(
                    data.message ?? (this.active ? 'เปิดใช้งานทรัพย์สินแล้ว' : 'ปิดใช้งานทรัพย์สินแล้ว')
                );
            } catch (error) {
                Alpine.store('notify').error('อัปเดตสถานะไม่สำเร็จ');
            } finally {
                this.loading = false;
            }
        },
    }" class="flex items-center gap-2">
    <button type="button" role="switch" :aria-checked="active" @click="toggle()" :disabled="loading" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors duration-200 disabled:cursor-not-allowed disabled:opacity-60" :class="active ? 'bg-brand-500' : 'bg-gray-200'">
        <span class="pointer-events-none inline-block size-5 translate-y-0.5 rounded-full bg-white shadow-theme-sm transition duration-200" :class="active ? 'translate-x-5' : 'translate-x-0.5'"></span>
    </button>

</div>
