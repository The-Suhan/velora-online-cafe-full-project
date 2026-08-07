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
    '@nuxt/image',
    '@nuxtjs/sitemap',
  ],

  // Used by @nuxtjs/sitemap (and as the base for canonical/OG URLs) to build
  // absolute URLs. Override with NUXT_PUBLIC_SITE_URL in production — falls
  // back to a placeholder in dev so nothing crashes when it's unset.
  site: {
    url: process.env.NUXT_PUBLIC_SITE_URL || 'https://velora-cafe.example.com',
    name: 'Velora',
  },

  sitemap: {
    exclude: ['/admin/**', '/profile/**', '/bag', '/login', '/register', '/verify-otp', '/forgot-password/**'],
    // Category/product pages are dynamic routes the crawler can't discover
    // from file-based routing alone, so we pull their ids from the same API
    // the pages themselves use.
    urls: async () => {
      const apiBase = process.env.NUXT_PUBLIC_API_BASE
      if (!apiBase) return []
      try {
        const categories = await $fetch(`${apiBase}/categories`)
        const urls = []
        for (const cat of categories ?? []) {
          urls.push({ loc: `/categories/${cat.id}`, changefreq: 'weekly' })
          urls.push({ loc: `/categories/${cat.id}/products`, changefreq: 'weekly' })
          for (const child of cat.children ?? []) {
            urls.push({ loc: `/categories/${child.id}`, changefreq: 'weekly' })
            urls.push({ loc: `/categories/${child.id}/products`, changefreq: 'weekly' })
          }
        }
        return urls
      } catch {
        return []
      }
    },
  },

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
      // Every page already sets an explicit "Velora — X" title via useHead(),
      // so this is only the fallback for the rare page that doesn't.
      title: 'Velora — Online Café',
      meta: [
        { name: 'description', content: 'Order coffee, pastries and café favorites online from Velora — browse the menu, add to your bag and pick a pickup time.' },
        { property: 'og:site_name', content: 'Velora' },
        { property: 'og:type', content: 'website' },
        { property: 'og:image', content: '/icon-512.png' },
        { name: 'twitter:card', content: 'summary_large_image' },
        { name: 'theme-color', content: '#3f2a1d' },
      ],
      script: [
        {
          type: 'application/ld+json',
          innerHTML: JSON.stringify({
            '@context': 'https://schema.org',
            '@type': 'CafeOrCoffeeShop',
            name: 'Velora',
            servesCuisine: 'Café',
            image: '/icon-512.png',
            url: process.env.NUXT_PUBLIC_SITE_URL || 'https://velora-cafe.example.com',
          }),
        },
        {
          // Runs before first paint, so the page never renders in light mode
          // and then snaps to dark. Kept inline and dependency-free for the
          // same reason — anything async would be too late.
          //
          // Also mirrors the choice into the `velora-theme` cookie (not just
          // localStorage): localStorage never reaches the server, so on a
          // repeat visit SSR would always render 'light' and hydration would
          // mismatch against whatever this script applies client-side. The
          // cookie lets the *next* request's SSR render the right theme from
          // the start — see useTheme.ts.
          innerHTML: `(function(){try{`
            + `var m=document.cookie.match(/(?:^|; )velora-theme=([^;]+)/);`
            + `var t=m?decodeURIComponent(m[1]):localStorage.getItem('velora-theme');`
            + `if(!t)t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';`
            + `document.documentElement.dataset.theme=t;`
            + `document.cookie='velora-theme='+t+';path=/;max-age=31536000;samesite=lax';`
            + `}catch(e){}})();`,
          tagPosition: 'head',
        }
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: '/favicon.png' },
        { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' },
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