export const useApi = () => {
    const config = useRuntimeConfig()
    const tokenCookie = useCookie('auth_token')

    return (url: string, options: any = {}) => {
        const token = tokenCookie.value
            ? decodeURIComponent(tokenCookie.value)
            : null

        return $fetch(`${config.public.apiBase}${url}`, {
            ...options,
            headers: {
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                Accept: 'application/json',
                ...options.headers,
            },
        })
    }
}