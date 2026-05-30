<template>
    <div class="client-layout">
        <ClientHeader :bag-count="totalCount" @open-search="searchOpen = true" @toggle-menu="menuOpen = !menuOpen" />
        <!-- Page content -->
        <main class="client-main">
            <slot />
        </main>

        <!-- Bottom Tabbar (tablet + mobile) -->
        <ClientFooter />
        <ClientTabbar :bag-count="totalCount" />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useCart } from '~/composables/useCart'

const menuOpen = ref(false)
const searchOpen = ref(false)

const { totalCount } = useCart()
</script>

<style scoped>
.client-layout {
    min-height: 100vh;
    background: #f5f0e8;
    display: flex;
    flex-direction: column;
}

.client-main {
    flex: 1;
    padding-bottom: 0;
}

@media (max-width: 860px) {
    .client-main {
        padding-bottom: calc(64px + env(safe-area-inset-bottom, 0px));
    }
}

.mobile-menu {
    position: fixed;
    inset: 0;
    top: 72px;
    z-index: 99;
    background: rgba(26, 10, 0, 0.25);
    backdrop-filter: blur(2px);
}

.mobile-menu-inner {
    background: #f5f0e8;
    padding: 1rem 1.5rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    border-bottom: 1px solid rgba(26, 10, 0, 0.08);
    box-shadow: 0 8px 24px rgba(26, 10, 0, 0.12);
}

.mobile-nav-link {
    font-family: 'Georgia', serif;
    font-size: 1rem;
    color: #5a3e2b;
    text-decoration: none;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 0.75rem 0.5rem;
    border-radius: 6px;
    transition: color 0.15s, background 0.15s;
}

.mobile-nav-link:hover {
    color: #1a0a00;
    background: rgba(26, 10, 0, 0.04);
}

.mobile-nav-link--active {
    color: #1a0a00;
    font-weight: 700;
}

.mobile-menu-divider {
    height: 1px;
    background: rgba(26, 10, 0, 0.1);
    margin: 0.5rem 0;
}

.mobile-bag-link {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-family: 'Georgia', serif;
    font-size: 0.9rem;
    color: #1a0a00;
    text-decoration: none;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 0.65rem 0.9rem;
    background: #1a0a00;
    color: #f5f0e8;
    border-radius: 8px;
    margin-top: 0.25rem;
    transition: background 0.15s;
}

.mobile-bag-link:hover {
    background: #2d1200;
}

.mobile-badge {
    margin-left: auto;
    width: 20px;
    height: 20px;
    background: #c8a97e;
    color: #1a0a00;
    font-size: 0.7rem;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: sans-serif;
}

/* ── Transition ── */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: opacity 0.2s ease, transform 0.22s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>