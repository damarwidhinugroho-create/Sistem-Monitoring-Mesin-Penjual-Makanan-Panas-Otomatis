import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/gaya-umum.css',
                'resources/css/monitoring-dashboard.css',
                'resources/css/inventaris-stok.css',
                'resources/css/monitoring-penjualan.css',
                'resources/css/monitoring-suhu.css',
                'resources/css/detail-mesin.css',
                'resources/css/riwayat-alert.css',
                'resources/css/laporan.css',
                'resources/css/masuk-operator.css',
                'resources/css/lupa-password.css',
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
