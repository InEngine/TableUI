/**
 * Vite build for the standalone Table UI stylesheet (Tailwind v4).
 * Outputs to public/css/tableui.css — use when not merging into the host Tailwind build.
 *
 * For Laravel apps that already use `@tailwindcss/vite`, prefer importing
 * `resources/css/tableui.css` once after `@import "tailwindcss"` instead of this bundle.
 */
import tailwindcss from '@tailwindcss/vite';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

const __dirname = dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    publicDir: false,
    plugins: [tailwindcss()],
    build: {
        outDir: resolve(__dirname, 'public/css'),
        emptyOutDir: true,
        rollupOptions: {
            input: resolve(__dirname, 'resources/css/tableui-standalone.css'),
            output: {
                assetFileNames: 'tableui.css',
            },
        },
    },
});
