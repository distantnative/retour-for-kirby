import { defineConfig } from 'vite'
import { createVuePlugin } from 'vite-plugin-vue2'

const path = require('path')

export default defineConfig({
  plugins: [createVuePlugin()],
  build: {
    lib: {
      entry: path.resolve(__dirname, 'src/panel/index.js'),
      formats: ['es']
    },
    outDir: '.',
    rollupOptions: {
      external: ['vue'],
      output: {
        entryFileNames: `[name].js`,
        assetFileNames: `index.[ext]`,
        globals: {
          vue: 'Vue'
        }
      }
    }
  }
})
