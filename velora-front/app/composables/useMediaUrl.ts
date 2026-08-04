/**
 * Turns the relative `image_url` values the API returns (`/storage/...`) into
 * absolute URLs against the backend origin.
 *
 * The API base ends in `/api`, which is stripped once here instead of being
 * re-derived with the same regex in every page that renders an image.
 */
export const useMediaUrl = () => {
    const config = useRuntimeConfig()
    const backendBase = (config.public.apiBase as string).replace(/\/api\/?$/, '')

    const resolveUrl = (url: string | null | undefined): string | null => {
        if (!url) return null
        if (url.startsWith('http')) return url

        return `${backendBase}${url.startsWith('/') ? '' : '/'}${url}`
    }

    return { resolveUrl, backendBase }
}
