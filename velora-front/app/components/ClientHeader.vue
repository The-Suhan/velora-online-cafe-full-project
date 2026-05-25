<template>
    <header class="velora-header">
        <div class="header-inner">

            <!-- Logo -->
            <NuxtLink to="/" class="header-logo">
                <img src="/logo-black.png" alt="Velaro Café" class="logo-img" />
                <div class="logo-text">
                    <span class="logo-name">Velaro</span>
                    <span class="logo-sub">Taste the calm</span>
                </div>
            </NuxtLink>

            <!-- Desktop Nav (hidden on mobile/tablet) -->
            <nav class="header-nav desktop-only">
                <NuxtLink to="/" class="nav-link" exact-active-class="nav-link--active">{{ $t('nav.home') }}</NuxtLink>
                <NuxtLink to="/menu" class="nav-link" active-class="nav-link--active">{{ $t('nav.menu') }}</NuxtLink>
                <NuxtLink to="/categories" class="nav-link" active-class="nav-link--active">{{ $t('nav.categories') }}</NuxtLink>
                <NuxtLink to="/profile" class="nav-link" active-class="nav-link--active">{{ $t('nav.profile') }}</NuxtLink>
            </nav>

            <!-- Right Actions -->
            <div class="header-actions">

                <!-- Search (always visible) -->
                <button class="icon-btn" aria-label="Search" @click="searchOpen = !searchOpen">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                </button>

                <!-- Bag button — desktop: standalone dark pill | mobile: icon only -->
                <NuxtLink to="/bag" class="bag-btn" aria-label="Bag">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <path d="M16 10a4 4 0 0 1-8 0" />
                    </svg>
                    <span v-if="bagCount > 0" class="bag-badge">{{ bagCount }}</span>
                </NuxtLink>

                <!-- Burger — only on mobile/tablet, rightmost, no badge -->
                <button class="burger-btn mobile-only" aria-label="Open menu" @click="$emit('toggle-menu')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>

            </div>
        </div>

        <!-- Search Bar Dropdown -->
        <Transition name="search-drop">
            <div v-if="searchOpen" class="search-bar-wrap">
                <div class="search-bar-inner">
                    <svg class="search-bar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input ref="searchInput" v-model="searchQuery" class="search-bar-input" type="text"
                        placeholder="Search coffee, menu items…" @keydown.escape="searchOpen = false"
                        @keydown.enter="handleSearch" />
                    <button class="search-close-btn" @click="searchOpen = false" aria-label="Close search">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>

    </header>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'

defineProps({
    bagCount: { type: Number, default: 0 }
})
defineEmits(['toggle-menu'])

const searchOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref(null)

watch(searchOpen, async (val) => {
    if (val) {
        await nextTick()
        searchInput.value?.focus()
    } else {
        searchQuery.value = ''
    }
})

function handleSearch() {
    if (!searchQuery.value.trim()) return
    // TODO: navigateTo(`/search?q=${searchQuery.value}`)
    console.log('search:', searchQuery.value)
}
</script>

<style scoped>
.velora-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #f5f0e8;
    border-bottom: 1px solid rgba(26, 10, 0, 0.08);
}

.header-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    height: 72px;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

/* ── Logo ── */
.header-logo {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    text-decoration: none;
    flex-shrink: 0;
}

.logo-img {
    width: 52px;
    height: 52px;
    border-radius: 50%;
}

.logo-text {
    display: flex;
    flex-direction: column;
    line-height: 1;
}

.logo-name {
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a0a00;
    letter-spacing: 0.01em;
}

.logo-sub {
    font-family: 'Georgia', serif;
    font-size: 0.52rem;
    color: #8a6a50;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-top: 3px;
}

/* ── Desktop Nav ── */
.desktop-only {
    display: flex;
}

.header-nav {
    align-items: center;
    gap: 0.1rem;
    margin-left: auto;
}

.nav-link {
    font-family: 'Georgia', serif;
    font-size: 0.78rem;
    font-weight: 500;
    color: #5a3e2b;
    text-decoration: none;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.45rem 0.95rem;
    border-radius: 4px;
    transition: color 0.2s, background 0.2s;
}

.nav-link:hover {
    color: #1a0a00;
    background: rgba(26, 10, 0, 0.05);
}

.nav-link--active {
    color: #1a0a00;
    font-weight: 700;
}

/* ── Actions ── */
.header-actions {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-left: auto;
}

/* when nav is present push actions flush */
.header-nav.desktop-only+.header-actions {
    margin-left: 0.75rem;
}

/* Search icon btn */
.icon-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    color: #1a0a00;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s;
}

.icon-btn:hover {
    background: rgba(26, 10, 0, 0.07);
}

/* ── Bag button ── */
.bag-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #f5f0e8;
    background: #1a0a00;
    border-radius: 8px;
    transition: background 0.2s;

    /* desktop size */
    width: 46px;
    height: 38px;
}

.bag-btn:hover {
    background: #2d1200;
}

.bag-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 18px;
    height: 18px;
    background: #c8a97e;
    color: #1a0a00;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: sans-serif;
}

/* ── Burger ── */
.burger-btn {
    display: none;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border: 1.5px solid rgba(26, 10, 0, 0.2);
    background: transparent;
    color: #1a0a00;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
}

.burger-btn:hover {
    background: rgba(26, 10, 0, 0.05);
    border-color: rgba(26, 10, 0, 0.35);
}

/* ── Search Bar ── */
.search-bar-wrap {
    border-top: 1px solid rgba(26, 10, 0, 0.08);
    background: #f5f0e8;
}

.search-bar-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0.75rem 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.search-bar-icon {
    color: #8a6a50;
    flex-shrink: 0;
}

.search-bar-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    font-family: 'Georgia', serif;
    font-size: 1rem;
    color: #1a0a00;
    letter-spacing: 0.02em;
}

.search-bar-input::placeholder {
    color: #b0957a;
}

.search-close-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #8a6a50;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.search-close-btn:hover {
    background: rgba(26, 10, 0, 0.07);
    color: #1a0a00;
}

/* ── Transitions ── */
.search-drop-enter-active,
.search-drop-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.search-drop-enter-from,
.search-drop-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* ── Responsive ── */
@media (max-width: 860px) {
    .desktop-only {
        display: none !important;
    }

    .mobile-only {
        display: flex !important;
    }

    .burger-btn {
        display: flex;
    }

    .header-inner {
        padding: 0 1.25rem;
    }

    /* On mobile bag becomes icon-only, same style as icon-btn */
    .bag-btn {
        width: 40px;
        height: 40px;
        background: transparent;
        color: #1a0a00;
        border-radius: 50%;
        border: none;
    }

    .bag-btn:hover {
        background: rgba(26, 10, 0, 0.07);
    }

    .search-bar-inner {
        padding: 0.75rem 1.25rem;
    }
}

@media (max-width: 480px) {
    .logo-sub {
        display: none;
    }
}
</style>