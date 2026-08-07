<template>
    <div class="auth-root">
        <div class="side side-left">
            <img src="/left_background_login.webp" alt="" loading="lazy" />
        </div>

        <div class="center-slot">
            <slot />
        </div>

        <div class="side side-right">
            <img src="/right_background_login.webp" alt="" loading="lazy" />
        </div>
    </div>
</template>

<script setup>
// Login/register/password-reset flows have no indexable content and
// shouldn't compete with the real pages in search results.
useSeoMeta({ robots: 'noindex, nofollow' })
</script>

<style scoped>
.auth-root {
    min-height: 100vh;
    display: flex;
    background: var(--color-brand-surface);
    position: relative;
    overflow: hidden;
}

.side {
    flex: 1;
    position: relative;
    overflow: hidden;
}

.side img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.center-slot {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    padding: 24px 16px;
}

@media (max-width: 1023px) {
    .side {
        display: none;
    }
}
</style>