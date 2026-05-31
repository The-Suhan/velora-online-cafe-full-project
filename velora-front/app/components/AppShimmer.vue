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
            #EDE5D8 0%,
            #E4DAC8 40%,
            #EDE5D8 80%);
}

.app-shimmer--dark {
    background: linear-gradient(90deg,
            #2C1A14 0%,
            #3d2518 25%,
            #5c3820 50%,
            #3d2518 75%,
            #2C1A14 100%);
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