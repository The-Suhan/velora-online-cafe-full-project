<script setup>
defineProps({
    width: { type: String, default: '100%' },
    height: { type: String, default: '16px' },
    radius: { type: String, default: '4px' },
    variant: { type: String, default: 'light' },
})
</script>

<template>
    <span class="app-shimmer" :class="`app-shimmer--${variant}`" :style="{ width, height, borderRadius: radius }"
        aria-hidden="true" />
</template>

<style scoped>
.app-shimmer {
    display: block;
    background-size: 200% 100%;
    animation: shimmer 1.5s ease-in-out infinite;
}

.app-shimmer--light {
    background: linear-gradient(90deg,
            var(--color-brown-89-2) 0%,
            var(--color-brown-84) 40%,
            var(--color-brown-89-2) 80%);
}

.app-shimmer--dark {
    background: linear-gradient(90deg,
            var(--color-primary-deep) 0%,
            var(--color-coffee-17) 25%,
            var(--color-coffee-24-2) 50%,
            var(--color-coffee-17) 75%,
            var(--color-primary-deep) 100%);
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}
</style>