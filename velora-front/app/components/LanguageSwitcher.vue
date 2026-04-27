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
    background: rgba(200, 169, 110, 0.12);
    border: 1.5px solid rgba(200, 169, 110, 0.4);
    border-radius: 10px;
    padding: 7px 10px;
    cursor: pointer;
    color: #2C1810;
    font-size: 0.8rem;
    font-weight: 500;
    letter-spacing: 0.03em;
    transition: background 0.2s, border-color 0.2s;
}

.lang-selected:hover,
.open .lang-selected {
    background: rgba(200, 169, 110, 0.22);
    border-color: #C8A96E;
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
    color: #C8A96E;
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
    background: #F5F0E8;
    border: 1.5px solid rgba(200, 169, 110, 0.4);
    border-radius: 12px;
    padding: 6px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
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
    color: #2C1810;
    transition: background 0.15s;
    text-align: left;
    width: 100%;
}

.lang-option:hover {
    background: rgba(200, 169, 110, 0.15);
}

.lang-option.active {
    background: rgba(200, 169, 110, 0.25);
    font-weight: 600;
    color: #7a5c30;
}
</style>