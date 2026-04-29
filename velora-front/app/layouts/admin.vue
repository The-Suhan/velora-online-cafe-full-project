<template>
    <div class="admin-root">
        <!-- Sidebar (desktop) -->
        <AdminSidebar :pending-orders="pendingOrders" :unread-feedbacks="unreadFeedbacks" class="admin-sidebar" />

        <!-- Main area -->
        <div class="admin-main">
            <AdminHeader :user-name="userName" />
            <div class="admin-content">
                <slot />
            </div>
        </div>

        <!-- Tabbar (mobile) -->
        <AdminTabbar :pending-orders="pendingOrders" :unread-feedbacks="unreadFeedbacks" class="admin-tabbar" />
    </div>
</template>

<script setup lang="ts">
const { user } = useAuth()

const pendingOrders = useState('admin:pendingOrders', () => 0)
const unreadFeedbacks = useState('admin:unreadFeedbacks', () => 0)
const userName = computed(() => (user.value as any)?.name ?? 'Admin')
</script>

<style scoped>
.admin-root {
    display: flex;
    min-height: 100vh;
    background: #F0EDE6;
    font-family: 'Jost', sans-serif;
}

.admin-sidebar {
    width: 200px;
    flex-shrink: 0;
    position: sticky;
    top: 0;
    height: 100vh;
}

.admin-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.admin-content {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
}

.admin-tabbar {
    display: none;
}

@media (max-width: 768px) {
    .admin-sidebar {
        display: none;
    }

    .admin-tabbar {
        display: flex;
    }

    .admin-content {
        padding: 16px 12px 80px;
    }
}
</style>