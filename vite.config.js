import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        // puedes incluso borrar todo el bloque 'server'
        host: "127.0.0.1",
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: { host: "127.0.0.1" },
        proxy: {
            '/storage': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
            }
        }
    },
});
