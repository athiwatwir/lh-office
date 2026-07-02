import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/custom-app.css',
                'resources/js/app.js',
                'resources/js/components/wysiwyg-editor.js',
                'resources/js/components/property-location.js',
            ],
            refresh: [
                'resources/views/**',
                'routes/**',
                'app/View/Components/**',
            ],
        }),
        tailwindcss(),
    ],
    // npm run dev = โหลด asset จาก Vite (port 5173) + HMR — ต้องรันคู่กับ php artisan serve
    // ถ้ารันแค่ php artisan serve ให้ใช้ npm run build แทน (และ npm run dev:clean ถ้ามี public/hot ค้าง)
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            ignored: [
                '**/storage/**',
                '**/vendor/**',
                '**/public/build/**',
                '**/node_modules/**',
            ],
        },
    },
});
