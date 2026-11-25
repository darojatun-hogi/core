import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Port Vite per app (VITE_PORT di .env), supaya beberapa app bisa `composer dev` bersamaan. Bawaan 5173.
const port = Number(loadEnv('', process.cwd(), '').VITE_PORT) || 5173;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port,
        strictPort: true,
        origin: `http://localhost:${port}`,
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
});