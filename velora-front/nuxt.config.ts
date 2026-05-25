export default defineNuxtConfig({
  devtools: { enabled: true },



  devServer: {
    host: '0.0.0.0',
    port: 3000,
  },
  modules: [
    '@nuxtjs/tailwindcss',
    '@nuxtjs/i18n',
  ],

  experimental: {
    typedPages: true,
  },

  css: ['~/assets/css/main.css'],

  i18n: {
    locales: [
      { code: 'tm', file: 'tm.json' },
      { code: 'ru', file: 'ru.json' },
      { code: 'en', file: 'en.json' },
    ],
    defaultLocale: 'en',
    langDir: 'locales/',
    strategy: 'no_prefix',
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE,
    },
  },

  app: {
    head: {
      link: [
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Jost:wght@300;400;500&display=swap'
        }
      ]
    }
  },
})