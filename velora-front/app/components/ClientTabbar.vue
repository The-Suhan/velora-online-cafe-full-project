<template>
    <nav class="velora-tabbar">
        <NuxtLink to="/" class="tab-item" exact-active-class="tab-item--active">
            <span class="tab-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
            </span>
            <span class="tab-label">{{ $t('nav.home') }}</span>
        </NuxtLink>

        <NuxtLink to="/menu" class="tab-item" active-class="tab-item--active">
            <span class="tab-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6" />
                    <line x1="8" y1="12" x2="21" y2="12" />
                    <line x1="8" y1="18" x2="21" y2="18" />
                    <line x1="3" y1="6" x2="3.01" y2="6" />
                    <line x1="3" y1="12" x2="3.01" y2="12" />
                    <line x1="3" y1="18" x2="3.01" y2="18" />
                </svg>
            </span>
            <span class="tab-label">{{ $t('nav.menu') }}</span>
        </NuxtLink>

        <NuxtLink to="/categories" class="tab-item" active-class="tab-item--active">
            <span class="tab-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" />
                    <rect x="14" y="3" width="7" height="7" />
                    <rect x="3" y="14" width="7" height="7" />
                    <rect x="14" y="14" width="7" height="7" />
                </svg>
            </span>
            <span class="tab-label">{{ $t('nav.categories') }}</span>
        </NuxtLink>

        <NuxtLink to="/profile" class="tab-item" active-class="tab-item--active">
            <span class="tab-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </span>
            <span class="tab-label">{{ $t('nav.profile') }}</span>
        </NuxtLink>
    </nav>
</template>

<script setup>

defineProps({
    bagCount: { type: Number, default: 0 }
})
</script>

<style scoped>
.velora-tabbar {
    display: none;
}

@media (max-width: 860px) {
    .velora-tabbar {
        display: flex;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 100;
        background: #f5f0e8;
        border-top: 1px solid rgba(26, 10, 0, 0.1);
        align-items: center;
        justify-content: space-around;
        height: 64px;
        padding: 0 0.5rem;
        padding-bottom: env(safe-area-inset-bottom, 0px);
        box-shadow: 0 -4px 20px rgba(26, 10, 0, 0.06);
    }
}

.tab-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    text-decoration: none;
    color: #9a7a60;
    padding: 6px 2px;
    border-radius: 10px;
    transition: color 0.2s;
    -webkit-tap-highlight-color: transparent;
    min-width: 0;
}

.tab-item--active {
    color: #1a0a00;
}

.tab-item--active .tab-icon svg {
    stroke-width: 2.2;
}

.tab-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
}

.tab-label {
    font-family: 'Georgia', serif;
    font-size: 0.6rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    white-space: nowrap;
}
</style>