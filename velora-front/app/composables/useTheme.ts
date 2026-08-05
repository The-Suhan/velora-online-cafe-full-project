/**
 * Light / dark theme.
 *
 * The actual colors live in ~/assets/css/color.css — this only decides which
 * of the two token sets is active by putting data-theme on <html>.
 *
 * The first value is picked in the inline script in nuxt.config.ts, which runs
 * before the page paints so there is no flash of the wrong theme. This
 * composable reads back what that script decided rather than deciding again.
 */
export type Theme = 'light' | 'dark'

export const THEME_STORAGE_KEY = 'velora-theme'

export function useTheme() {
    const theme = useState<Theme>('theme', () => 'light')

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
        theme.value = value
        if (!import.meta.client) return
        apply(value)
        try {
            localStorage.setItem(THEME_STORAGE_KEY, value)
        } catch {
            // Private mode / storage disabled — the theme still applies for
            // this page, it just will not be remembered.
        }
    }

    const toggle = () => setTheme(theme.value === 'dark' ? 'light' : 'dark')

    const isDark = computed(() => theme.value === 'dark')

    return { theme, isDark, setTheme, toggle, readFromDocument }
}
