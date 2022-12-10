// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: false,
  modules: [
    '@nuxtjs/i18n'
  ],
  i18n: {
    locales: [
      {
        code: 'de',
        iso: 'de',
        file: 'de.yaml'
      },
      {
        code: 'en',
        iso: 'en',
        file: 'en.yaml'
      }
    ],
    // lazy: true,
    langDir: 'locales/',
    defaultLocale: 'de',
    vueI18n: {
      fallbackLocale: 'de',
    },
  },
  // vite: {
  //   server: {
  //     hmr: process.env.GITPOD_WORKSPACE_URL ? {
  //         // removes the protocol and replaces it with the port we're connecting to
  //         host: process.env.GITPOD_WORKSPACE_URL.replace('https://', '18080-'),
  //         protocol: 'wss',
  //         clientPort: 443
  //       }
  //     : true
  //   },
  // }
})
