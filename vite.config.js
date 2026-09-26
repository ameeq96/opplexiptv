import { defineConfig } from 'vite';
import { purgeCSSPlugin as purgeCss } from '@fullhuman/postcss-purgecss';
import laravel from 'laravel-vite-plugin';

const purgeCriticalCss = (options) => {
    const plugin = purgeCss(options);

    return {
        postcssPlugin: 'opplex-critical-css-purge',
        OnceExit(root, helpers) {
            const source = root.source?.input.file?.replaceAll('\\', '/') ?? '';

            if (!source.endsWith('/resources/css/site-critical.css')) return;

            return plugin.OnceExit(root, helpers);
        },
    };
};

export default defineConfig(({ mode }) => ({
    css: {
        postcss: {
            plugins: mode === 'production' ? [
                purgeCriticalCss({
                    content: [
                        './app/**/*.php',
                        './config/**/*.php',
                        './database/**/*.php',
                        './resources/**/*.{blade.php,js,php,ts,vue}',
                        '!./resources/views/admin/**/*.blade.php',
                        './routes/**/*.php',
                        './public/js/{nav-tool.js,owl.js,script.js}',
                    ],
                    safelist: {
                        standard: [
                            'active',
                            'active-block',
                            'collapse',
                            'collapsed',
                            'collapsing',
                            'current',
                            'disabled',
                            'fa',
                            'fa-facebook-f',
                            'fa-instagram',
                            'fa-linkedin',
                            'fixed-header',
                            'flaticon-5g',
                            'flaticon-8k',
                            'flaticon-customer-service',
                            'flaticon-swimming-pool',
                            'is-active',
                            'is-copied',
                            'is-hidden',
                            'is-unavailable',
                            'is-visible',
                            'mobile-menu-visible',
                            'modal-open',
                            'native-dropdown-open',
                            'lnr-arrow-left',
                            'lnr-arrow-right',
                            'bi-router',
                            'open',
                            'show',
                        ],
                    },
                }),
            ] : [],
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/site-critical.css',
                'resources/css/site-deferred.css',
                'resources/css/checkout.css',
                'resources/css/blogs.css',
                'resources/css/admin.css',
                'resources/js/site.js',
                'resources/js/native-shell.js',
                'resources/js/voice-assistant.js',
                'resources/js/discount-wheel.js',
            ],
            refresh: true,
        }),
    ],
}));
