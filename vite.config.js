import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/animations.js',
            ],
            refresh: true,
            https: true,
            publicDirectory: 'public',
        }),
    ],
    server: {
        https: true,
        host: '0.0.0.0',
    },
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            output: {
                assetFileNames: '[name]-[hash][extname]',
                chunkFileNames: '[name]-[hash].js',
                entryFileNames: '[name]-[hash].js',
            },
        },
        assetsDir: '',
    },
});
