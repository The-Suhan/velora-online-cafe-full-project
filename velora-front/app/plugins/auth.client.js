export default defineNuxtPlugin(() => {
    addRouteMiddleware('auth', async (to) => {
        const token = useCookie('auth_token')
        const publicRoutes = ['/login', '/register']

        if (publicRoutes.includes(to.path)) return
        if (!token.value) return navigateTo('/login')

        const config = useRuntimeConfig()
        try {
            await $fetch(`${config.public.apiBase}/me`, {
                headers: { Authorization: `Bearer ${token.value}` }
            })
        } catch {
            token.value = null  
            return navigateTo('/login')
        }
    }, { global: true })
})