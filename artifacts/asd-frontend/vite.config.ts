import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'node:path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, './src'),
    },
  },
  base: process.env.BASE_PATH ?? '/',
  server: {
    port: parseInt(process.env.PORT ?? '3000'),
    allowedHosts: true,
  },
  build: {
    target: 'es2022',
    sourcemap: false,
  },
})
