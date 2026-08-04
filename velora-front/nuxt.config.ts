export default defineNuxtConfig({
  devtools: { enabled: false },

  devServer: {
    host: '0.0.0.0',
    port: 2010,
  },


  router: {},
  modules: [
    '@nuxtjs/tailwindcss',
    '@nuxtjs/i18n',
    '@nuxt/image'
  ],

  image: {
    quality: 80,
    format: ['webp', 'jpg'],
  },

  hooks: {
    'pages:extend'(pages) {
      pages.push({
        name: 'categories-id-products',
        path: '/categories/:id/products',
        file: '~/pages/categories/[id]/products.vue',
      })
    }
  },

  experimental: {
    typedPages: true,
    scanPageMeta: 'after-resolve',
  },

  css: ['~/assets/css/color.css', '~/assets/css/main.css'],

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
        // Preconnect so the font CSS and the font files themselves don't each
        // pay for a fresh DNS + TLS handshake.
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          // Single request covering every family/weight the app uses. These
          // were previously spread across this file, two per-page useHead()
          // calls and two render-blocking CSS @imports, which meant up to four
          // serialized stylesheet fetches before first paint.
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2'
            + '?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300'
            + '&family=Jost:wght@300;400;500'
            + '&family=Lato:wght@300;400'
            + '&family=Playfair+Display:ital,wght@0,600;1,400;1,600'
            + '&display=swap'
        }
      ]
    }
  },
})