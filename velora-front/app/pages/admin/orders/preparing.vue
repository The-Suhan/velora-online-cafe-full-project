<template>
    <OrdersStatusPage status="preparing" :meta="meta">
        <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24">
                <path d="M12 2a10 10 0 00-6.88 17.26L12 22l6.88-2.74A10 10 0 0012 2z" />
                <path d="M12 8v4l2 2" stroke-linecap="round" />
            </svg>
        </template>
        <template #empty-icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40">
                <path d="M12 2a10 10 0 00-6.88 17.26L12 22l6.88-2.74A10 10 0 0012 2z" />
                <path d="M12 8v4l2 2" stroke-linecap="round" />
            </svg>
        </template>
    </OrdersStatusPage>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' as any, middleware: 'admin' })

const meta = {
    label: 'Preparing',
    desc: 'Orders currently being prepared in the kitchen.',
    badge: 'In kitchen',
    emptyMsg: 'Kitchen is clear. No orders are being prepared.',
}
</script>