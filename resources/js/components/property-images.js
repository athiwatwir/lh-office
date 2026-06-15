document.addEventListener('alpine:init', () => {
    Alpine.data('propertyImagesManager', (config) => ({
        images: config.initialImages ?? [],
        uploadUrl: config.uploadUrl,
        defaultUrl: config.defaultUrl,
        deleteUrl: config.deleteUrl,
        csrf: config.csrf,
        uploading: false,
        loadingId: null,
        dragOver: false,

        async uploadFiles(event) {
            const files = event.target?.files;

            if (!files?.length) {
                return;
            }

            await this.sendFiles(files);

            if (event.target) {
                event.target.value = '';
            }
        },

        async dropFiles(event) {
            this.dragOver = false;
            const files = event.dataTransfer?.files;

            if (!files?.length) {
                return;
            }

            await this.sendFiles(files);
        },

        async sendFiles(fileList) {
            if (this.uploading) {
                return;
            }

            this.uploading = true;

            const formData = new FormData();

            for (const file of fileList) {
                formData.append('images[]', file);
            }

            try {
                const response = await fetch(this.uploadUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    body: formData,
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message ?? 'อัปโหลดไม่สำเร็จ');
                }

                if (Array.isArray(data.images)) {
                    this.images.push(...data.images);
                    this.sortImages();
                }

                Alpine.store('notify').success(data.message ?? 'อัปโหลดรูปภาพเรียบร้อยแล้ว');
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'อัปโหลดไม่สำเร็จ');
            } finally {
                this.uploading = false;
            }
        },

        async setDefault(image) {
            if (this.loadingId || image.isDefault) {
                return;
            }

            this.loadingId = image.id;

            try {
                const response = await fetch(this.urlFor(image.id, this.defaultUrl), {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message ?? 'ตั้งค่ารูปหลักไม่สำเร็จ');
                }

                this.images = this.images.map((item) => ({
                    ...item,
                    isDefault: item.id === image.id,
                }));

                Alpine.store('notify').success(data.message ?? 'ตั้งค่ารูปหลักเรียบร้อยแล้ว');
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'ตั้งค่ารูปหลักไม่สำเร็จ');
            } finally {
                this.loadingId = null;
            }
        },

        async remove(image) {
            if (this.loadingId) {
                return;
            }

            if (!window.confirm('ต้องการลบรูปภาพนี้หรือไม่?')) {
                return;
            }

            this.loadingId = image.id;

            try {
                const response = await fetch(this.urlFor(image.id, this.deleteUrl), {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message ?? 'ลบรูปภาพไม่สำเร็จ');
                }

                this.images = this.images.filter((item) => item.id !== image.id);

                if (data.newDefaultId) {
                    this.images = this.images.map((item) => ({
                        ...item,
                        isDefault: item.id === data.newDefaultId,
                    }));
                }

                Alpine.store('notify').success(data.message ?? 'ลบรูปภาพเรียบร้อยแล้ว');
            } catch (error) {
                Alpine.store('notify').error(error.message ?? 'ลบรูปภาพไม่สำเร็จ');
            } finally {
                this.loadingId = null;
            }
        },

        sortImages() {
            this.images.sort((left, right) => left.seq - right.seq);
        },

        urlFor(id, template) {
            return template.replace('__ID__', id);
        },
    }));
});
