export const useApi = () => {
    const config = useRuntimeConfig()
    const tokenCookie = useCookie('auth_token')
    const localeCookie = useCookie('i18n_redirected')

    return (url: string, options: any = {}) => {
        const token = tokenCookie.value
            ? decodeURIComponent(tokenCookie.value)
            : null

        return $fetch(`${config.public.apiBase}${url}`, {
            ...options,
            headers: {
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                Accept: 'application/json',
                'Accept-Language': localeCookie.value || 'en',
                ...options.headers,
            },
        })
    }
}