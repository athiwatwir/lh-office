document.addEventListener('alpine:init', () => {
    Alpine.store('propertyFormGuard', {
        codeStatus: 'idle',

        setCodeStatus(status) {
            this.codeStatus = status ?? 'idle';
        },

        reset() {
            this.codeStatus = 'idle';
        },
    });

    Alpine.data('propertyFormGuard', (features = {}) => ({
        features: {
            zone: features.zone ?? true,
            special_price: features.special_price ?? false,
        },
        tick: 0,

        init() {
            Alpine.store('propertyFormGuard').reset();

            this.formEl = this.$el.matches('form')
                ? this.$el
                : (this.$el.querySelector('form') ?? this.$el.closest('form'));

            const bump = () => {
                this.tick++;
            };

            if (this.formEl) {
                this.formEl.addEventListener('input', bump, true);
                this.formEl.addEventListener('change', bump, true);
            }

            this.$watch(() => Alpine.store('propertyFormGuard').codeStatus, bump);

            this.$nextTick(bump);
        },

        onCodeStatus(event) {
            Alpine.store('propertyFormGuard').setCodeStatus(event.detail?.status);
            this.tick++;
        },

        fieldValue(name) {
            void this.tick;

            const field = this.formEl?.querySelector(`[name="${name}"]`);

            return (field?.value ?? '').trim();
        },

        get codeStatus() {
            void this.tick;

            return Alpine.store('propertyFormGuard').codeStatus;
        },

        isCodeValid() {
            void this.tick;

            if (this.codeStatus === 'checking') {
                return false;
            }

            if (this.codeStatus === 'duplicate' || this.codeStatus === 'error') {
                return false;
            }

            if (this.codeStatus === 'available') {
                return this.fieldValue('code') !== '';
            }

            return false;
        },

        get checks() {
            void this.tick;

            const checks = [
                {
                    key: 'code',
                    label: 'รหัสทรัพย์',
                    hint: this.codeHint(),
                    done: this.isCodeValid(),
                },
                {
                    key: 'name',
                    label: 'ชื่อทรัพย์',
                    hint: null,
                    done: this.fieldValue('name') !== '',
                },
                {
                    key: 'asset_type_id',
                    label: 'ประเภททรัพย์',
                    hint: null,
                    done: this.fieldValue('asset_type_id') !== '',
                },
                {
                    key: 'zone_id',
                    label: 'โซน',
                    hint: null,
                    done: this.fieldValue('zone_id') !== '',
                },
                {
                    key: 'user_id',
                    label: 'ตัวแทน',
                    hint: null,
                    done: this.fieldValue('user_id') !== '',
                },
            ];

            if (! this.features.zone) {
                return checks.filter((check) => check.key !== 'zone_id');
            }

            return checks;
        },

        codeHint() {
            if (this.codeStatus === 'checking') {
                return 'กำลังตรวจสอบรหัส...';
            }

            if (this.codeStatus === 'duplicate') {
                return 'รหัสทรัพย์ซ้ำ';
            }

            if (this.codeStatus === 'error') {
                return 'ตรวจสอบรหัสไม่สำเร็จ';
            }

            if (this.fieldValue('code') === '') {
                return 'กรุณากรอกรหัสทรัพย์';
            }

            if (this.codeStatus === 'available') {
                return 'รหัสทรัพย์ใช้ได้';
            }

            return 'รอตรวจสอบรหัสทรัพย์';
        },

        get completedCount() {
            return this.checks.filter((check) => check.done).length;
        },

        get totalCount() {
            return this.checks.length;
        },

        get progressPercent() {
            if (this.totalCount === 0) {
                return 0;
            }

            return Math.round((this.completedCount / this.totalCount) * 100);
        },

        get canSubmit() {
            return this.completedCount === this.totalCount;
        },

        notifyPending() {
            const pending = this.checks
                .filter((check) => !check.done)
                .map((check) => check.label)
                .join(', ');

            Alpine.store('notify').error(`กรุณากรอกข้อมูลให้ครบ: ${pending}`);
        },

        handleSubmit(event) {
            if (this.canSubmit) {
                return;
            }

            event.preventDefault();
            this.notifyPending();
        },

        submitForm() {
            if (!this.canSubmit) {
                this.notifyPending();

                return;
            }

            if (!this.formEl) {
                return;
            }

            if (typeof this.formEl.requestSubmit === 'function') {
                this.formEl.requestSubmit();
            } else {
                this.formEl.submit();
            }
        },
    }));
});
