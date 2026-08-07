<script setup>
const { visible, close } = useAuthGate()
const router = useRouter()

function goLogin() {
    close()
    router.push('/login')
}
</script>

<template>
    <Transition name="auth-gate-fade">
        <div v-if="visible" class="auth-gate-overlay" @click.self="close">
            <div class="auth-gate-box" role="dialog" aria-modal="true">
                <p class="auth-gate-title">{{ $t('authGate.title') }}</p>
                <p class="auth-gate-msg">{{ $t('authGate.message') }}</p>
                <div class="auth-gate-actions">
                    <button class="auth-gate-back" @click="close">{{ $t('authGate.back') }}</button>
                    <button class="auth-gate-signin" @click="goLogin">{{ $t('authGate.signIn') }}</button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.auth-gate-overlay {
    position: fixed;
    inset: 0;
    z-index: 10000;
    background: rgb(0 0 0 / 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.auth-gate-box {
    background: var(--color-card);
    border-radius: 10px;
    padding: 1.75rem 1.5rem;
    max-width: 340px;
    width: 100%;
    text-align: center;
    box-shadow: 0 12px 40px rgb(0 0 0 / 0.25);
}

.auth-gate-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.3rem;
    color: var(--color-primary-espresso);
    margin: 0 0 0.5rem;
}

.auth-gate-msg {
    font-family: 'Lato', sans-serif;
    font-size: 0.85rem;
    color: var(--color-brown-43);
    margin: 0 0 1.35rem;
    line-height: 1.5;
}

.auth-gate-actions {
    display: flex;
    gap: 0.6rem;
}

.auth-gate-back,
.auth-gate-signin {
    flex: 1;
    padding: 0.6rem 0.8rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.75rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.18s, color 0.18s;
}

.auth-gate-back {
    background: transparent;
    border: 1px solid var(--color-border);
    color: var(--color-brown-43);
}

.auth-gate-back:hover {
    background: rgb(var(--rgb-primary-black) / 0.05);
}

.auth-gate-signin {
    background: var(--color-accent-soft);
    border: none;
    color: var(--color-white);
}

.auth-gate-signin:hover {
    background: var(--color-brown-52);
}

.auth-gate-fade-enter-active,
.auth-gate-fade-leave-active {
    transition: opacity 0.18s ease;
}

.auth-gate-fade-enter-from,
.auth-gate-fade-leave-to {
    opacity: 0;
}
</style>
