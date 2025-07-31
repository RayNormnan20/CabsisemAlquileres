import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    base: process.env.ASSET_URL ? `${process.env.ASSET_URL}/build/` : '/build/',
    plugins: [
        laravel({
            input: [
                'resources/css/filament.scss',
                'resources/js/filament.js',
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
