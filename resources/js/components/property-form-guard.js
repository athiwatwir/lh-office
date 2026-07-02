document.addEventListener('alpine:init', () => {
    Alpine.data('propertyFormGuard', () => ({
        tick: 0,
        codeStatus: 'idle',

        init() {
            const bump = () => {
                this.tick++;
            };

            this.$el.addEventListener('input', bump, true);
            this.$el.addEventListener('change', bump, true);
            this.$el.addEventListener('property-code-status', (event) => {
                this.codeStatus = event.detail?.status ?? 'idle';
                bump();
            });

            this.$nextTick(bump);
        },

        fieldValue(name) {
            void this.tick;

            const field = this.$el.querySelector(`[name="${name}"]`);

            return (field?.value ?? '').trim();
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

            return [
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

        handleSubmit(event) {
            if (this.canSubmit) {
                return;
            }

            event.preventDefault();

            const pending = this.checks
                .filter((check) => !check.done)
                .map((check) => check.label)
                .join(', ');

            Alpine.store('notify').error(`กรุณากรอกข้อมูลให้ครบ: ${pending}`);
        },
    }));
});
