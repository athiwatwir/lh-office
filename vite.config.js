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
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            // ป้องกัน reload loop จาก Blade ที่ compile แล้ว
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
