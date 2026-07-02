document.addEventListener('alpine:init', () => {
    Alpine.data('articleSortable', (config) => ({
        reorderUrl: config.reorderUrl,
        csrf: config.csrf,
        saving: false,
        dragId: null,
        dragRow: null,

        rows() {
            return [...this.$refs.tbody.querySelectorAll('tr[data-article-id]')];
        },

        onDragStart(event) {
            const handle = event.target.closest('[data-drag-handle]');
            const row = event.target.closest('tr[data-article-id]');

            if (!handle || !row) {
                event.preventDefault();
                return;
            }

            this.dragId = row.dataset.articleId;
            this.dragRow = row;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', this.dragId);
            row.classList.add('opacity-50', 'bg-brand-50/40');
        },

        onDragOver(event) {
            event.preventDefault();

            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'move';
            }

            if (!this.dragId || !this.dragRow) {
                return;
            }

            const targetRow = event.target.closest('tr[data-article-id]');

            if (!targetRow || targetRow.dataset.articleId === this.dragId) {
                return;
            }

            const rect = targetRow.getBoundingClientRect();
            const after = event.clientY > rect.top + rect.height / 2;

            if (after) {
                const next = targetRow.nextElementSibling;
                if (next) {
                    this.$refs.tbody.insertBefore(this.dragRow, next);
                } else {
                    this.$refs.tbody.appendChild(this.dragRow);
                }
            } else {
                this.$refs.tbody.insertBefore(this.dragRow, targetRow);
            }
        },

        onDrop(event) {
            event.preventDefault();
            this.finishDrag();
        },

        onDragEnd() {
            this.dragRow?.classList.remove('opacity-50', 'bg-brand-50/40');
            this.finishDrag();
        },

        async finishDrag() {
            if (!this.dragId) {
                return;
            }

            this.dragId = null;
            this.dragRow = null;
            await this.saveOrder();
        },

        async saveOrder() {
            if (this.saving) {
                return;
            }

            const order = this.rows().map((row) => row.dataset.articleId);

            this.saving = true;

            try {
                const response = await fetch(this.reorderUrl, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ order }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message ?? 'บันทึกลำดับไม่สำเร็จ');
                }

                this.rows().forEach((row, index) => {
                    const seqCell = row.querySelector('[data-seq-value]');
                    if (seqCell) {
                        seqCell.textContent = String((index + 1) * 10);
                    }
                });

                Alpine.store('notify').success(data.message ?? 'บันทึกลำดับเรียบร้อยแล้ว');
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'บันทึกลำดับไม่สำเร็จ');
            } finally {
                this.saving = false;
            }
        },
    }));
});
