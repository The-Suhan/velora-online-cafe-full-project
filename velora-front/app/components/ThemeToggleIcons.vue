<template>
    <!--
      Both icons are always in the DOM, stacked, and cross-faded. Swapping one
      for the other with v-if would make the button jump as the box collapses
      for a frame, and there would be nothing to animate between.
    -->
    <span class="icon-stack" aria-hidden="true">
        <svg class="icon icon-sun" :class="{ hidden: dark }" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="4.2" />
            <path d="M12 2.6v2.2M12 19.2v2.2M4.3 4.3l1.6 1.6M18.1 18.1l1.6 1.6M2.6 12h2.2M19.2 12h2.2M4.3 19.7l1.6-1.6M18.1 5.9l1.6-1.6" />
        </svg>

        <svg class="icon icon-moon" :class="{ hidden: !dark }" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.5 14.3A8.6 8.6 0 1 1 9.7 3.5a6.9 6.9 0 0 0 10.8 10.8z" />
        </svg>
    </span>
</template>

<script setup lang="ts">
defineProps<{ dark: boolean }>()
</script>

<style scoped>
.icon-stack {
    position: relative;
    display: block;
    width: 18px;
    height: 18px;
}

.icon {
    position: absolute;
    inset: 0;
    width: 18px;
    height: 18px;
    transition: opacity 0.24s ease, transform 0.32s cubic-bezier(.4, 0, .2, 1);
}

.icon-sun.hidden {
    opacity: 0;
    transform: rotate(70deg) scale(.4);
}

.icon-moon.hidden {
    opacity: 0;
    transform: rotate(-70deg) scale(.4);
}

@media (prefers-reduced-motion: reduce) {
    .icon {
        transition: opacity 0.15s ease;
    }

    .icon.hidden {
        transform: none;
    }
}
</style>
