import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/custom.css',
                'resources/css/mini-app.css',
                'resources/js/app.js',
                'resources/js/mini-app.js',
            ],
            refresh: true,
        }),
    ],
});
