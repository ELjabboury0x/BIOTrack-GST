import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: '../public/hot',
            publicDirectory: '../public',
            buildDirectory: 'build',
            input: [
                '../public/css/dashboard.css',
                '../public/css/modern-ui.css',
                '../public/js/modern-ui.js',
                '../public/js/dashboard.js',
                '../public/js/table.js',
                '../public/js/charts.js',
                '../public/js/pwa-push.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        cssCodeSplit: true,
        sourcemap: false,
    },
});
