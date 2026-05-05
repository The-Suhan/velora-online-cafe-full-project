<template>
    <OrdersStatusPage status="pending" :meta="meta">
        <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 6v6l4 2" stroke-linecap="round" />
            </svg>
        </template>
        <template #empty-icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 6v6l4 2" stroke-linecap="round" />
            </svg>
        </template>
    </OrdersStatusPage>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' as any, middleware: 'admin' })

const { t } = useI18n()

const meta = computed(() => ({
    label: t('admin.orders.pending.label'),
    desc: t('admin.orders.pending.desc'),
    badge: t('admin.orders.pending.badge'),
    emptyMsg: t('admin.orders.pending.emptyMsg'),
}))
</script>