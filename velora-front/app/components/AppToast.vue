<template>
    <!-- Tek, her zaman render edilen kök eleman.
         app.vue çok köklü bir fragment: buradaki düğüm sayısı sunucu ve istemcide
         birebir aynı olmazsa sonraki kardeşler kayıyor ve NuxtLayout'un header'ı
         hydration hatası veriyor. Bu yüzden ne <ClientOnly> ne de tag'siz
         <TransitionGroup> (fragment üretir) kullanılabilir — `tag="div"` şart. -->
    <TransitionGroup tag="div" name="toast" class="toast-stack" role="status" aria-live="polite">
        <div v-for="toast in toasts" :key="toast.id" class="toast" :class="toast.type" @click="dismiss(toast.id)">
            <span class="toast-dot" />
            <span class="toast-message">{{ toast.message }}</span>
        </div>
    </TransitionGroup>
</template>

<script setup lang="ts">
const { toasts, dismiss } = useToast()
</script>

<style scoped>
.toast-stack {
    position: fixed;
    top: 1.25rem;
    right: 1.25rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    /* Yığın tıklamaları yakalamasın; sadece toast'ların kendisi tıklanabilir. */
    pointer-events: none;
}

.toast {
    pointer-events: auto;
    display: flex;
    align-items: center;
    gap: 0.625rem;
    min-width: 15rem;
    max-width: min(22rem, calc(100vw - 2.5rem));
    padding: 0.75rem 1rem;
    border-radius: 0.625rem;
    border: 1px solid var(--color-border);
    border-left: 3px solid var(--color-info);
    background: var(--color-card);
    color: var(--color-ink);
    font-family: 'Jost', sans-serif;
    font-size: 0.875rem;
    line-height: 1.35;
    box-shadow: 0 8px 24px rgb(var(--rgb-black) / 0.14);
    cursor: pointer;
}

.toast.success {
    border-left-color: var(--color-success);
}

.toast.error {
    border-left-color: var(--color-danger);
}

.toast-dot {
    flex-shrink: 0;
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background: var(--color-info);
}

.toast.success .toast-dot {
    background: var(--color-success);
}

.toast.error .toast-dot {
    background: var(--color-danger);
}

.toast-message {
    flex: 1;
}

.toast-enter-active,
.toast-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(1.5rem);
}

.toast-move {
    transition: transform 0.25s ease;
}

@media (max-width: 640px) {
    .toast-stack {
        top: auto;
        bottom: 5rem;
        /* mobil tabbar'ın üstünde */
        left: 1rem;
        right: 1rem;
    }

    .toast {
        min-width: 0;
        max-width: none;
    }
}
</style>
