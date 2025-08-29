import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'index.html'),
                widget: resolve(__dirname, 'public/styleai-widget.html'),
                demo: resolve(__dirname, 'public/color-style-demo.html'),
                auth: resolve(__dirname, 'public/auth-confirm.html')
            }
        },
        outDir: 'dist',
        copyPublicDir: true
    },
    publicDir: 'public'
});
