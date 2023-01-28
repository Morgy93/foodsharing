import { defineConfig } from 'vite'
import symfonyPlugin from 'vite-plugin-symfony'

export default defineConfig({
  plugins: [
    symfonyPlugin(),
  ],

  build: {
    rollupOptions: {
      input: {
        /* relative to the root option */
        app: './assets/app.ts',

        /* you can also provide css files to prevent FOUC */
        theme: './assets/theme.css',
      },
    },
  },
})
