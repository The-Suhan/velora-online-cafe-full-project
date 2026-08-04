// Colors live in app/assets/css/color.css — this only maps them onto
// Tailwind's cafe-* utilities. <alpha-value> keeps bg-cafe-accent/50 working.
export default {
  theme: {
    extend: {
      colors: {
        cafe: {
          primary: 'rgb(var(--rgb-primary-deep) / <alpha-value>)',
          secondary: 'rgb(var(--rgb-surface) / <alpha-value>)',
          accent: 'rgb(var(--rgb-accent-warm) / <alpha-value>)',
        }
      },
      fontFamily: {
        display: ['"Playfair Display"', 'serif'],
        body: ['"Cormorant Garamond"', 'serif'],
      }
    }
  }
}