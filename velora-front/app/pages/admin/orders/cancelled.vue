<template>
    <OrdersStatusPage status="cancelled" :meta="meta">
        <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24">
                <circle cx="12" cy="12" r="10" />
                <path d="M15 9l-6 6M9 9l6 6" stroke-linecap="round" />
            </svg>
        </template>
        <template #empty-icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40">
                <circle cx="12" cy="12" r="10" />
                <path d="M15 9l-6 6M9 9l6 6" stroke-linecap="round" />
            </svg>
        </template>
    </OrdersStatusPage>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' as any, middleware: 'admin' })

const meta = {
    label: 'Cancelled',
    desc: 'Orders that were cancelled and refunded.',
    badge: 'Refunded',
    emptyMsg: 'No cancelled orders. That\'s great news!',
}
</script>