import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/gaya-umum.css',
                'resources/css/monitoring_dashboard.css',
                'resources/css/inventaris_stok.css',
                'resources/css/monitoring_penjualan.css',
                'resources/css/monitoring_suhu.css',
                'resources/css/detail_mesin.css',
                'resources/css/riwayat_alert.css',
                'resources/css/laporan.css',
                'resources/css/login.css',
                'resources/css/lupa_password.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
