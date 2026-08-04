/**
 * Validates the stored token once per app load and primes the shared `user`
 * state from it.
 *
 * This was previously registered as a *global* route middleware named 'auth',
 * which had two costs: every in-app navigation blocked on a round trip to /me,
 * and registering it under the name 'auth' shadowed `middleware/auth.js` for
 * every page that asked for `middleware: 'auth'`.
 *
 * The token only needs verifying when the app boots — after that the API's own
 * 401s are the source of truth, and `middleware/auth.js` still gates routes on
 * the token's presence. Priming `user` here also means `middleware/admin.js`
 * finds it already populated instead of refetching.
 */
export default defineNuxtPlugin(async () => {
    const token = useCookie('auth_token')
    if (!token.value) return

    const user = useState('user')
    if (user.value) return

    const config = useRuntimeConfig()

    try {
        user.value = await $fetch(`${config.public.apiBase}/me`, {
            headers: { Authorization: `Bearer ${token.value}` },
        })
    } catch {
        token.value = null
        user.value = null
        await navigateTo('/login')
    }
})
