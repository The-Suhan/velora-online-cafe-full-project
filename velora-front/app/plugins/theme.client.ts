/**
 * Syncs the reactive theme state with whatever the pre-paint script in
 * nuxt.config.ts already applied to <html>. Without this the toggle would
 * start out believing the app is in light mode even when it rendered dark.
 */
export default defineNuxtPlugin(() => {
    const { theme, readFromDocument } = useTheme()
    theme.value = readFromDocument()
})
