<template>
    <OrdersStatusPage status="ready" :meta="meta">
        <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24">
                <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </template>
        <template #empty-icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40">
                <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </template>
    </OrdersStatusPage>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' as any, middleware: 'admin' })

const meta = {
    label: 'Ready',
    desc: 'Orders ready and waiting for pickup or delivery.',
    badge: 'To pick up',
    emptyMsg: 'Nothing ready yet. Prepared orders will appear here.',
}
</script>