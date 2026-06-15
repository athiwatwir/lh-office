<div
    id="page-preloader"
    class="fixed inset-0 z-999999 flex items-center justify-center bg-white transition-opacity duration-150"
>
    <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
</div>

<script>
    (function () {
        const hide = () => {
            const el = document.getElementById('page-preloader');

            if (!el || el.dataset.hidden === '1') {
                return;
            }

            el.dataset.hidden = '1';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 150);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', hide, { once: true });
        } else {
            hide();
        }

        // กัน UI ค้างถ้า asset จาก Vite dev โหลดไม่สำเร็จ
        setTimeout(hide, 2000);
    })();
</script>
