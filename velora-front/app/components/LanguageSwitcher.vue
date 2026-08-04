<template>
    <div class="lang-switcher" :class="{ open: isOpen }" ref="switcher">
        <button class="lang-selected" @click="isOpen = !isOpen" type="button">
            <img :src="`/flag/${locale}.png`" :alt="locale" class="lang-flag" />
            <span class="lang-label">{{ current?.label }}</span>
            <svg class="lang-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9l6 6 6-6" />
            </svg>
        </button>

        <div v-if="isOpen" class="lang-dropdown">
            <button v-for="lang in langs" :key="lang.code" class="lang-option" :class="{ active: locale === lang.code }"
                @click="select(lang.code)" type="button">
                <img :src="`/flag/${lang.code}.png`" :alt="lang.code" class="lang-flag" />
                <span>{{ lang.label }}</span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
const { locale, setLocale } = useI18n()

const langs = [
    { code: 'tm' as const, label: 'Türkmençe' },
    { code: 'ru' as const, label: 'Русский' },
    { code: 'en' as const, label: 'English' },
]

const current = computed(() => langs.find(l => l.code === locale.value) ?? langs[0]!)
const isOpen = ref(false)
const switcher = ref<HTMLElement | null>(null)

const select = (code: 'tm' | 'ru' | 'en') => {
    setLocale(code)
    isOpen.value = false
}

onMounted(() => {
    document.addEventListener('click', (e) => {
        if (switcher.value && !switcher.value.contains(e.target as Node)) {
            isOpen.value = false
        }
    })
})
</script>

<style scoped>
.lang-switcher {
    position: relative;
    display: inline-block;
    font-family: 'Jost', sans-serif;
}

.lang-selected {
    display: flex;
    align-items: center;
    gap: 7px;
    background: rgb(var(--rgb-accent) / 0.12);
    border: 1.5px solid rgb(var(--rgb-accent) / 0.4);
    border-radius: 10px;
    padding: 7px 10px;
    cursor: pointer;
    color: var(--color-primary);
    font-size: 0.8rem;
    font-weight: 500;
    letter-spacing: 0.03em;
    transition: background 0.2s, border-color 0.2s;
}

.lang-selected:hover,
.open .lang-selected {
    background: rgb(var(--rgb-accent) / 0.22);
    border-color: var(--color-accent);
}

.lang-flag {
    width: 22px;
    height: 16px;
    object-fit: cover;
    border-radius: 3px;
    flex-shrink: 0;
}

.lang-label {
    white-space: nowrap;
}

.lang-chevron {
    width: 14px;
    height: 14px;
    color: var(--color-accent);
    transition: transform 0.2s;
    flex-shrink: 0;
}

.open .lang-chevron {
    transform: rotate(180deg);
}

.lang-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    background: var(--color-surface-warm);
    border: 1.5px solid rgb(var(--rgb-accent) / 0.4);
    border-radius: 12px;
    padding: 6px;
    box-shadow: 0 8px 24px rgb(var(--rgb-black) / 0.15);
    z-index: 50;
    min-width: 140px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.lang-option {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 8px 10px;
    border: none;
    background: none;
    border-radius: 8px;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    font-size: 0.83rem;
    color: var(--color-primary);
    transition: background 0.15s;
    text-align: left;
    width: 100%;
}

.lang-option:hover {
    background: rgb(var(--rgb-accent) / 0.15);
}

.lang-option.active {
    background: rgb(var(--rgb-accent) / 0.25);
    font-weight: 600;
    color: var(--color-coffee-33);
}

@media (max-width: 860px) {
    .lang-label {
        display: none;
    }

    .lang-chevron {
        display: none;
    }

    .lang-selected {
        width: 40px;
        height: 40px;
        padding: 0;
        border-radius: 50%;
        justify-content: center;
        border: none;
        background: transparent;
    }

    .lang-selected:hover {
        background: rgb(var(--rgb-primary-black) / 0.07);
    }

    .lang-flag {
        width: 22px;
        height: 16px;
    }

    .lang-dropdown {
        left: auto;
        right: 0;
    }
}
</style>