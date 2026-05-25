import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/site-critical.css',
                'resources/css/site-deferred.css',
                'resources/css/checkout.css',
                'resources/css/blogs.css',
                'resources/css/admin.css',
                'resources/js/site.js',
                'resources/js/voice-assistant.js',
                'resources/js/discount-wheel.js',
            ],
            refresh: true,
        }),
    ],
});
