<template>
    <nav class="tabbar">
        <NuxtLink v-for="item in items" :key="item.to" :to="item.to" class="tab-item" active-class="tab-active">
            <div class="tab-icon-wrap">
                <span class="tab-icon" v-html="item.icon" />
                <span v-if="item.badge && item.badge > 0" class="tab-badge">
                    {{ item.badge > 9 ? '9+' : item.badge }}
                </span>
            </div>
            <span class="tab-label">{{ item.label }}</span>
        </NuxtLink>
    </nav>
</template>

<script setup lang="ts">
const props = defineProps<{
    pendingOrders: number
    unreadFeedbacks: number
}>()

const items = computed(() => [
    {
        to: '/admin',
        label: 'Dashboard',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <rect x="3" y="3" width="7" height="7" rx="1"/>
      <rect x="14" y="3" width="7" height="7" rx="1"/>
      <rect x="3" y="14" width="7" height="7" rx="1"/>
      <rect x="14" y="14" width="7" height="7" rx="1"/>
    </svg>`,
        badge: 0,
    },
    {
        to: '/admin/users',
        label: 'Users',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <circle cx="12" cy="8" r="4"/>
      <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
    </svg>`,
        badge: 0,
    },
    {
        to: '/admin/products',
        label: 'Products',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
      <line x1="3" y1="6" x2="21" y2="6"/>
    </svg>`,
        badge: 0,
    },
    {
        to: '/admin/orders',
        label: 'Orders',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <path d="M6 2h12l3 7H3L6 2z"/>
      <path d="M3 9v11a2 2 0 002 2h14a2 2 0 002-2V9"/>
    </svg>`,
        badge: props.pendingOrders,
    },
    {
        to: '/admin/feedback',
        label: 'Feedback',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
    </svg>`,
        badge: props.unreadFeedbacks,
    },
])
</script>

<style scoped>
.tabbar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #2C1810;
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 8px 0 max(8px, env(safe-area-inset-bottom));
    z-index: 100;
    border-top: 1px solid rgba(200, 169, 110, 0.2);
}

.tab-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    color: rgba(245, 240, 232, 0.5);
    text-decoration: none;
    flex: 1;
    transition: color 0.15s;
    -webkit-tap-highlight-color: transparent;
}

.tab-active {
    color: #C8A96E !important;
}

.tab-icon-wrap {
    position: relative;
    width: 24px;
    height: 24px;
}

.tab-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.tab-icon :deep(svg) {
    width: 22px;
    height: 22px;
}

.tab-badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background: #e05252;
    color: #fff;
    font-size: 0.58rem;
    font-weight: 700;
    border-radius: 99px;
    padding: 0 4px;
    min-width: 14px;
    height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tab-label {
    font-size: 0.62rem;
    font-family: 'Jost', sans-serif;
    letter-spacing: 0.02em;
}
</style>