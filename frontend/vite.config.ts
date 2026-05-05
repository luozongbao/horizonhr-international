import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'
import Components from 'unplugin-vue-components/vite'
import AutoImport from 'unplugin-auto-import/vite'
import { visualizer } from 'rollup-plugin-visualizer'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    // Auto-import custom components
    Components({
      dts: 'src/components.d.ts',
    }),
    // Auto-import Vue, Vue Router, Pinia composables
    AutoImport({
      imports: ['vue', 'vue-router', 'pinia'],
      dts: 'src/auto-imports.d.ts',
    }),
    // Bundle size visualizer — generates bundle-stats.html after `npm run build`
    visualizer({ filename: 'bundle-stats.html', gzipSize: true, brotliSize: true }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
    },
  },
  optimizeDeps: {
    include: [
      'vue',
      'vue-router',
      'pinia',
      'axios',
      'vue-i18n',
      '@unhead/vue/client',
      '@element-plus/icons-vue',
    ],
  },
  build: {
    outDir: '../public/build',
    emptyOutDir: true,
    manifest: true,
  },
})

