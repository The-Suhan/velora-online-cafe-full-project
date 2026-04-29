export default defineNuxtRouteMiddleware(async () => {
    const token = useCookie('auth_token')

    if (!token.value) return navigateTo('/login')

    const user = useState('user')

    if (!user.value) {
        const config = useRuntimeConfig()
        try {
            const data = await $fetch(`${config.public.apiBase}/me`, {
                headers: { Authorization: `Bearer ${token.value}` },
            })
            user.value = data
        } catch {
            return navigateTo('/login')
        }
    }

    if (user.value?.role !== 'admin') {
        return navigateTo('/')
    }
})