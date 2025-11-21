import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import basicSsl from '@vitejs/plugin-basic-ssl';

export default defineConfig({
    plugins: [
        basicSsl(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/scanner.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    optimizeDeps: {
        include: ['@ericblade/quagga2']
    },
    server: {
        https: true,
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost'
        }
    }
});
