@props([
    'value' => '',
    'propertyId' => null,
    'inputClass' => '',
    'agentName' => null,
])

@php
    $hasServerError = $errors->has('code');
@endphp

<div
    x-data="{
        code: @js(old('code', $value)),
        initialCode: @js(old('code', $value)),
        status: 'idle',
        message: '',
        agentName: @js($agentName),
        timer: null,
        requestId: 0,
        scheduleCheck() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.check(), 400);
        },
        updateValidity() {
            const input = this.$refs.codeInput;
            if (! input) {
                this.notifyStatus();
                return;
            }
            if (this.status === 'duplicate') {
                input.setCustomValidity(this.message || 'รหัสทรัพย์นี้ถูกใช้แล้วในเอเจนต์นี้');
            } else {
                input.setCustomValidity('');
            }
            this.notifyStatus();
        },
        notifyStatus() {
            this.$dispatch('property-code-status', { status: this.status });
        },
        async check() {
            const trimmed = (this.code ?? '').trim();
            if (trimmed === '') {
                this.status = 'empty';
                this.message = '';
                this.updateValidity();
                return;
            }

            if (trimmed === (this.initialCode ?? '').trim() && @js($propertyId)) {
                this.status = 'available';
                this.message = 'รหัสทรัพย์ปัจจุบัน';
                this.updateValidity();
                return;
            }

            const currentRequest = ++this.requestId;
            this.status = 'checking';
            this.message = 'กำลังตรวจสอบ...';
            this.updateValidity();

            const params = new URLSearchParams({ code: trimmed });
            @if ($propertyId)
            params.set('exclude', @js($propertyId));
            @endif

            try {
                const response = await fetch(@js(route('property.check-code', [], false)) + '?' + params.toString(), {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });

                if (currentRequest !== this.requestId) {
                    return;
                }

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    this.status = 'error';
                    this.message = data.message ?? 'ตรวจสอบรหัสไม่สำเร็จ';
                    this.updateValidity();
                    return;
                }

                if (data.agent_name) {
                    this.agentName = data.agent_name;
                }

                this.status = data.available ? 'available' : 'duplicate';
                this.message = data.message ?? (data.available ? 'รหัสทรัพย์นี้ใช้ได้' : 'รหัสทรัพย์นี้ถูกใช้แล้วในเอเจนต์นี้');
            } catch (error) {
                if (currentRequest !== this.requestId) {
                    return;
                }
                this.status = 'error';
                this.message = 'ตรวจสอบรหัสไม่สำเร็จ';
            }

            this.updateValidity();
        },
        init() {
            if ((this.code ?? '').trim() !== '') {
                this.scheduleCheck();
            } else {
                this.notifyStatus();
            }
        },
    }"
    class="space-y-1"
>
    <label for="code" class="mb-1.5 block text-theme-sm font-medium text-gray-700">
        รหัสทรัพย์ <span class="text-error-500">*</span>
    </label>

    <div class="relative">
        <input
            x-ref="codeInput"
            id="code"
            type="text"
            name="code"
            x-model="code"
            @input="scheduleCheck()"
            @blur="check()"
            required
            class="{{ $inputClass }} pr-10 @if ($hasServerError) border-error-500 @endif"
            :class="{
                'border-error-500 focus:border-error-500 focus:ring-error-500/10': status === 'duplicate',
                'border-success-500 focus:border-success-500 focus:ring-success-500/10': status === 'available' && code.trim() !== '',
            }"
        />

        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <svg
                x-show="status === 'checking'"
                x-cloak
                class="h-5 w-5 animate-spin text-gray-400"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>

            <svg
                x-show="status === 'available' && code.trim() !== ''"
                x-cloak
                class="h-5 w-5 text-success-500"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>

            <svg
                x-show="status === 'duplicate'"
                x-cloak
                class="h-5 w-5 text-error-500"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    <p x-show="agentName" x-cloak class="text-theme-xs text-gray-500">
        ตรวจสอบภายใต้เอเจนต์: <span x-text="agentName"></span>
    </p>

    <p
        x-show="message && status !== 'idle' && status !== 'empty'"
        x-cloak
        x-text="message"
        class="text-theme-xs"
        :class="{
            'text-gray-500': status === 'checking',
            'text-success-600': status === 'available',
            'text-error-500': status === 'duplicate' || status === 'error',
        }"
    ></p>

    @error('code')
        <p class="text-theme-xs text-error-500">{{ $message }}</p>
    @enderror
</div>
