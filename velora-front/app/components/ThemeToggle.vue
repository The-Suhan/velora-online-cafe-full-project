<template>
    <!-- Admin sidebar: a full-width row that reads like the nav items above it -->
    <button v-if="variant === 'sidebar'" class="theme-row" type="button" role="switch" :aria-checked="isDark"
        :aria-label="$t('theme.toggle')" v-bind="$attrs" @click="toggle">
        <span class="theme-row-icon">
            <ThemeToggleIcons :dark="isDark" />
        </span>
        <span class="theme-row-label">{{ isDark ? $t('theme.dark') : $t('theme.light') }}</span>
        <span class="theme-track" :class="{ on: isDark }">
            <span class="theme-knob" />
        </span>
    </button>

    <!-- Client header: a compact pill matching the language switcher beside it -->
    <button v-else class="theme-pill" type="button" role="switch" :aria-checked="isDark"
        :aria-label="$t('theme.toggle')" :title="$t('theme.toggle')" v-bind="$attrs" @click="toggle">
        <ThemeToggleIcons :dark="isDark" />
    </button>
</template>

<script setup lang="ts">
// İki farklı kök eleman (v-if/v-else) arasında Vue'nun otomatik $attrs
// geçişi SSR ile CSR'da tutarsız davranıyordu (sunucu "theme-pill", istemci
// "theme-pill header-theme-toggle" render ediyordu) — hydration uyarısına
// yol açıyordu. inheritAttrs kapatılıp her iki kökte de elle bağlanıyor.
defineOptions({ inheritAttrs: false })

withDefaults(defineProps<{ variant?: 'header' | 'sidebar' }>(), {
    variant: 'header',
})

const { isDark, toggle } = useTheme()
</script>

<style scoped>
/* ── Shared icon animation ───────────────────────────────────────────────── */
.theme-row-icon,
.theme-pill {
    /* the two icons are stacked, so the box must not collapse */
    position: relative;
}

/* ── Client header pill ──────────────────────────────────────────────────── */
.theme-pill {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    padding: 0;
    background: rgb(var(--rgb-accent) / 0.12);
    border: 1.5px solid rgb(var(--rgb-accent) / 0.4);
    border-radius: 10px;
    color: var(--color-primary);
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s, color 0.2s;
}

.theme-pill:hover {
    background: rgb(var(--rgb-accent) / 0.22);
    border-color: var(--color-accent);
}

.theme-pill:focus-visible {
    outline: 2px solid var(--color-accent);
    outline-offset: 2px;
}

/* ── Admin sidebar row ───────────────────────────────────────────────────── */
.theme-row {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 11px 20px;
    background: none;
    border: none;
    border-top: 1px solid rgb(var(--rgb-accent) / 0.15);
    color: rgb(var(--rgb-on-brand) / 0.65);
    font-family: 'Jost', sans-serif;
    font-size: 0.88rem;
    text-align: left;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.theme-row:hover {
    background: rgb(var(--rgb-accent) / 0.1);
    color: var(--color-on-brand);
}

.theme-row:focus-visible {
    outline: 2px solid var(--color-accent);
    outline-offset: -2px;
}

.theme-row-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.theme-row-label {
    flex: 1;
}

/* Miniature track/knob switch on the right of the row */
.theme-track {
    width: 32px;
    height: 18px;
    border-radius: 99px;
    background: rgb(var(--rgb-on-brand) / 0.18);
    flex-shrink: 0;
    padding: 2px;
    display: flex;
    transition: background 0.22s ease;
}

.theme-track.on {
    background: rgb(var(--rgb-accent) / 0.55);
}

.theme-knob {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--color-on-brand);
    transition: transform 0.22s cubic-bezier(.4, 0, .2, 1);
}

.theme-track.on .theme-knob {
    transform: translateX(14px);
}

/* Below the header's breakpoint the pill drops its chrome and becomes a plain
   round icon button, the same treatment the language switcher gets. */
@media (max-width: 860px) {
    .theme-pill {
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 50%;
        background: transparent;
        color: var(--color-primary-black);
    }

    .theme-pill:hover {
        background: rgb(var(--rgb-primary-black) / 0.07);
    }
}

@media (prefers-reduced-motion: reduce) {

    .theme-knob,
    .theme-track {
        transition: none;
    }
}
</style>
