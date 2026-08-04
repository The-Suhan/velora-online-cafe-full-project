/**
 * Translation lookup for API models that carry a `translations` payload.
 *
 * The backend returns translations either keyed by locale (`{ en: {...} }`) or
 * as a flat array (`[{ locale: 'en', ... }]`) depending on the endpoint, so both
 * shapes are handled here rather than re-implemented in every page.
 *
 * Results are memoised per item object + locale because `displayName()` is
 * called from templates, i.e. once per item on every re-render.
 */

export type TranslationField = 'name' | 'description'

export interface TranslationEntry {
    locale?: string
    name?: string | null
    description?: string | null
}

export interface Translatable {
    name?: string | null
    description?: string | null
    translations?: Record<string, TranslationEntry> | TranslationEntry[] | null
}

const FALLBACK_LOCALE = 'en'

type CacheEntry = { locale: string; name?: string; description?: string }

/** Keyed by the item object itself, so entries die with the data they describe. */
const cache = new WeakMap<object, CacheEntry>()

function readTranslation(
    item: Translatable,
    locale: string,
    field: TranslationField
): string {
    const translations = item?.translations
    if (!translations) return ''

    const entry = Array.isArray(translations)
        ? translations.find((x) => x?.locale === locale)
        : translations[locale]

    return entry?.[field] ?? ''
}

function resolveField(
    item: Translatable | null | undefined,
    locale: string,
    field: TranslationField
): string {
    if (!item) return ''

    return (
        readTranslation(item, locale, field) ||
        readTranslation(item, FALLBACK_LOCALE, field) ||
        item[field] ||
        ''
    )
}

/**
 * Reads a translated field, caching per (item, locale). The cache is dropped
 * whenever the locale changes so a language switch re-resolves everything.
 */
function resolveCached(
    item: Translatable | null | undefined,
    locale: string,
    field: TranslationField
): string {
    if (!item || typeof item !== 'object') return ''

    let entry = cache.get(item)
    if (!entry || entry.locale !== locale) {
        entry = { locale }
        cache.set(item, entry)
    }

    const cached = entry[field]
    if (cached !== undefined) return cached

    const value = resolveField(item, locale, field)
    entry[field] = value

    return value
}

export const useLocalized = () => {
    const { locale } = useI18n()

    /** Translated name, falling back to EN then the untranslated column. */
    const displayName = (item: Translatable | null | undefined): string =>
        resolveCached(item, locale.value, 'name')

    /** Translated description, falling back to EN then the untranslated column. */
    const displayDesc = (item: Translatable | null | undefined): string =>
        resolveCached(item, locale.value, 'description')

    /**
     * Raw lookup for an explicit locale with no fallback — used by the admin
     * preview panels that list each locale's value side by side.
     */
    const getTranslation = (
        item: Translatable | null | undefined,
        loc: string,
        field: TranslationField
    ): string => (item ? readTranslation(item, loc, field) : '')

    return { displayName, displayDesc, getTranslation, locale }
}
