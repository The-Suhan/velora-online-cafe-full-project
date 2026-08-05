/**
 * Syncs the reactive theme state with whatever the pre-paint script in
 * nuxt.config.ts already applied to <html>.
 *
 * Only matters on a first-ever visit: the theme cookie doesn't exist yet, so
 * SSR renders with the 'light' default while the inline script picks the
 * OS preference and paints the page dark. This brings the reactive state
 * (icon/pill classes) in line with what's already on screen.
 *
 * Deliberately runs on 'app:mounted', not synchronously at plugin setup —
 * doing it before mount would make the very first client render disagree
 * with what the server sent, which Vue reports as a hydration mismatch.
 * Running after mount is a normal reactive update instead.
 */
export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.hook('app:mounted', () => {
        const { theme, readFromDocument } = useTheme()
        theme.value = readFromDocument()
    })
})
