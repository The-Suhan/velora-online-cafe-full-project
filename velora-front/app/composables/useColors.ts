/**
 * Reads the tokens defined in ~/assets/css/color.css at runtime.
 *
 * Only needed where a color is handed to something that cannot resolve CSS
 * variables on its own — a canvas (Chart.js), or a document we open ourselves
 * (the print/PDF window). Everywhere else just use var(--color-*) directly.
 */
export function useColors() {
    const read = (prop: string): string => {
        if (!import.meta.client) return ''
        return getComputedStyle(document.documentElement).getPropertyValue(prop).trim()
    }

    /** Solid color, e.g. color('primary') -> "rgb(44 24 16)" */
    const color = (name: string): string => read(`--color-${name}`)

    /** Same color at an opacity, e.g. alpha('success', 0.08) */
    const alpha = (name: string, a: number): string => {
        const channels = read(`--rgb-${name}`)
        return channels ? `rgb(${channels} / ${a})` : ''
    }

    /**
     * A `:root { ... }` rule carrying the given tokens, for injecting into a
     * document that does not load our stylesheet (print window, iframe, email).
     */
    const rootBlock = (names: string[]): string => {
        const decls = names
            .map(n => `--color-${n}:${color(n)};`)
            .filter(d => !d.endsWith(':;'))
            .join('')
        return `:root{${decls}}`
    }

    return { color, alpha, rootBlock }
}
