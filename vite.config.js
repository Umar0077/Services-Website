import { rmSync } from 'node:fs';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const hotFile = resolve(import.meta.dirname, 'public/hot');

export default defineConfig({
    plugins: [
        {
            name: 'clean-stale-hot-file',
            apply: 'build',
            buildStart() {
                rmSync(hotFile, { force: true });
            },
            closeBundle() {
                rmSync(hotFile, { force: true });
            },
        },
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**', '**/vendor/**'],
        },
    },
});
