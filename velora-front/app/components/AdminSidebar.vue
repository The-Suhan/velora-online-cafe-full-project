<template>
    <aside class="sidebar">
        <!-- Logo -->
        <div class="sidebar-logo">
            <img src="/logo-black.png" alt="Velora Café" class="logo-img" />
        </div>

        <!-- Nav -->
        <nav class="sidebar-nav">
            <NuxtLink v-for="item in navItems" :key="item.to" :to="item.to" class="nav-item" active-class="nav-active">
                <span class="nav-icon" v-html="item.icon" />
                <span class="nav-label">{{ item.label }}</span>
                <!-- Badge -->
                <span v-if="item.badge && item.badge > 0" class="nav-badge">
                    {{ item.badge > 99 ? '99+' : item.badge }}
                </span>
            </NuxtLink>
        </nav>

        <!-- Logout -->
        <button class="sidebar-logout" @click="handleLogout">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
            </span>
            <span>Logout</span>
        </button>
    </aside>
</template>

<script setup lang="ts">
const props = defineProps<{
    pendingOrders: number
    unreadFeedbacks: number
}>()

const { logout } = useAuth()
const { t } = useI18n()

const navItems = computed(() => [
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
      <path d="M16 10a4 4 0 01-8 0"/>
    </svg>`,
        badge: 0,
    },
    {
        to: '/admin/categories',
        label: 'Categories',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <rect x="3" y="3" width="7" height="4" rx="1"/>
      <rect x="14" y="3" width="7" height="4" rx="1"/>
      <rect x="3" y="11" width="18" height="4" rx="1"/>
      <rect x="3" y="19" width="18" height="2" rx="1"/>
    </svg>`,
        badge: 0,
    },
    {
        to: '/admin/orders',
        label: 'Orders',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <path d="M6 2h12l3 7H3L6 2z"/>
      <path d="M3 9v11a2 2 0 002 2h14a2 2 0 002-2V9"/>
      <line x1="12" y1="12" x2="12" y2="18"/>
      <line x1="9" y1="15" x2="15" y2="15"/>
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

const handleLogout = async () => {
    await logout()
}
</script>

<style scoped>
.sidebar {
    background: #1B0E08;
    display: flex;
    flex-direction: column;
    padding: 0;
    height: 100vh;
    position: sticky;
    top: 0;
}

.sidebar-logo {
    width: 100%;
}

.logo-img {
    width: 100%;
    height: 100%;
}

.sidebar-nav {
    flex: 1;
    padding: 12px 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow-y: auto;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 20px;
    color: rgba(245, 240, 232, 0.65);
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 400;
    transition: background 0.15s, color 0.15s;
    border-radius: 0;
    position: relative;
    cursor: pointer;
}

.nav-item:hover {
    background: rgba(200, 169, 110, 0.1);
    color: #F5F0E8;
}

.nav-active {
    background: #4A6741 !important;
    color: #F5F0E8 !important;
}

.nav-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-icon :deep(svg) {
    width: 18px;
    height: 18px;
}

.nav-label {
    flex: 1;
}

.nav-badge {
    background: #e05252;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 600;
    border-radius: 99px;
    padding: 1px 6px;
    min-width: 18px;
    text-align: center;
    line-height: 1.4;
}

.sidebar-logout {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: none;
    border: none;
    border-top: 1px solid rgba(200, 169, 110, 0.15);
    color: rgba(245, 240, 232, 0.5);
    font-family: 'Jost', sans-serif;
    font-size: 0.88rem;
    cursor: pointer;
    width: 100%;
    transition: color 0.15s;
}

.sidebar-logout:hover {
    color: #e05252;
}

.sidebar-logout .nav-icon {
    width: 20px;
    height: 20px;
}

.sidebar-logout :deep(svg) {
    width: 18px;
    height: 18px;
}
</style>