import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    vue({
      template: {
        compilerOptions: {
          // This ensures runtime compilation works
        }
      }
    })
  ],
  resolve: {
    alias: {
      // Use the full Vue build that includes the compiler
      'vue': 'vue/dist/vue.esm-bundler.js'
    }
  }
})