import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        symfonyPlugin()
    ],
    root: ".",
    base: "/build/",
    publicDir: true,
    build: {
        manifest: true,
        emptyOutDir: true,
        assetsDir: "./assets",
        outDir: "./public/build",
        rollupOptions: {
            input: {
                app: "./assets/app.js",
            },
        },
        base: "/sax/",
        server: {
            port: 13714
        }
    }
});
