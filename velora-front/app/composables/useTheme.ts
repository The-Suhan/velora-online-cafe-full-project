/**
 * Light / dark theme.
 *
 * The actual colors live in ~/assets/css/color.css — this only decides which
 * of the two token sets is active by putting data-theme on <html>.
 *
 * The first value is picked in the inline script in nuxt.config.ts, which runs
 * before the page paints so there is no flash of the wrong theme.
 *
 * Backed by a cookie (not useState + localStorage): a plain useState always
 * starts at its default on the server, since SSR has no access to
 * localStorage. On any repeat visit that made the server render 'light' while
 * the inline script immediately flipped the DOM to 'dark' client-side —
 * client and server disagreeing at the exact moment Vue hydrates, which Vue
 * reports as a hydration mismatch. useCookie's value travels with the
 * request, so SSR and the client agree from the first render.
 */
export type Theme = 'light' | 'dark'

export const THEME_STORAGE_KEY = 'velora-theme'

export function useTheme() {
    const theme = useCookie<Theme>(THEME_STORAGE_KEY, {
        default: () => 'light',
        maxAge: 60 * 60 * 24 * 365,
        sameSite: 'lax',
    })

    /** What the pre-paint script already put on <html>, if anything. */
    const readFromDocument = (): Theme =>
        document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light'

    const apply = (value: Theme) => {
        const root = document.documentElement
        root.dataset.theme = value

        // Only animate a deliberate switch, never the initial paint.
        root.classList.add('theme-switching')
        window.setTimeout(() => root.classList.remove('theme-switching'), 300)
    }

    const setTheme = (value: Theme) => {
        // useCookie writes document.cookie itself — no manual localStorage
        // bookkeeping needed, the cookie is now the single source of truth.
        theme.value = value
        if (!import.meta.client) return
        apply(value)
    }

    const toggle = () => setTheme(theme.value === 'dark' ? 'light' : 'dark')

    const isDark = computed(() => theme.value === 'dark')

    return { theme, isDark, setTheme, toggle, readFromDocument }
}
