<template>
    <Transition name="loader-fade">
        <div v-if="visible" class="loader-overlay">
            <span class="corner tl" />
            <span class="corner br" />

            <div class="ring-wrap">
                <div class="arc" />
                <div class="ring-mid">
                    <div class="ring-inner">
                        <svg width="30" height="30" viewBox="0 0 32 32" fill="none">
                            <path d="M7 10h18l-2 12a2 2 0 01-2 1.5H11A2 2 0 019 22L7 10z" stroke="#C8A96A"
                                stroke-width="1.2" stroke-linejoin="round" />
                            <path d="M22 13h3a2 2 0 010 4h-3" stroke="#C8A96A" stroke-width="1.2"
                                stroke-linecap="round" />
                            <path d="M13 7c0-1.5 2-2 2-3.5M17 7c0-1.5 2-2 2-3.5" stroke="#C8A96A" stroke-width="1.2"
                                stroke-linecap="round" opacity="0.4" />
                        </svg>
                    </div>
                </div>
            </div>

            <h1 class="brand">Velora Café</h1>
            <p class="tagline">Taste the calm</p>

            <div class="progress-wrap">
                <div class="progress-bar" />
            </div>
            <p class="status">{{ status }}</p>
        </div>
    </Transition>
</template>

<script setup lang="ts">
const { visible } = useAppLoading()

const status = ref('Preparing your experience')
const messages = ['Preparing your experience', 'Brewing the details', 'Almost ready']

let interval: ReturnType<typeof setInterval>

watch(visible, (val) => {
    if (val) {
        let i = 0
        interval = setInterval(() => {
            i = (i + 1) % messages.length
            status.value = messages[i]
        }, 900)
    } else {
        clearInterval(interval)
    }
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Cormorant+Garamond:wght@300;400&display=swap');

.loader-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: #2C1A14;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.corner {
    position: absolute;
    width: 22px;
    height: 22px;
    opacity: 0.3;
}

.corner.tl {
    top: 20px;
    left: 20px;
    border-top: 1px solid #C8A96A;
    border-left: 1px solid #C8A96A;
}

.corner.br {
    bottom: 20px;
    right: 20px;
    border-bottom: 1px solid #C8A96A;
    border-right: 1px solid #C8A96A;
}

.ring-wrap {
    position: relative;
    width: 130px;
    height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 1px solid rgba(200, 169, 106, 0.18);
}

.arc {
    position: absolute;
    inset: -1px;
    border-radius: 50%;
    border: 1.5px solid transparent;
    border-top-color: #C8A96A;
    border-right-color: rgba(200, 169, 106, 0.25);
    animation: spin 2.2s linear infinite;
}

.ring-mid {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 1px solid rgba(200, 169, 106, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.ring-inner {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 1px solid rgba(200, 169, 106, 0.55);
    background: rgba(200, 169, 106, 0.06);
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    font-weight: 600;
    color: #F5EFEA;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    margin: 24px 0 0;
}

.tagline {
    font-family: 'Cormorant Garamond', serif;
    font-size: 12px;
    font-weight: 300;
    color: #C8A96A;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    margin: 5px 0 0;
}

.progress-wrap {
    margin-top: 32px;
    width: 150px;
    height: 1px;
    background: rgba(200, 169, 106, 0.12);
    overflow: hidden;
    border-radius: 2px;
}

.progress-bar {
    height: 100%;
    width: 35%;
    background: #C8A96A;
    opacity: 0.7;
    animation: slide 1.6s ease-in-out infinite;
}

.status {
    font-family: 'Cormorant Garamond', serif;
    font-size: 11px;
    color: rgba(245, 239, 234, 0.35);
    letter-spacing: 0.22em;
    text-transform: uppercase;
    margin-top: 12px;
    transition: opacity 0.3s;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes slide {
    0% {
        transform: translateX(-200%);
    }

    100% {
        transform: translateX(600%);
    }
}

.loader-fade-leave-active {
    transition: opacity 0.7s ease;
}

.loader-fade-leave-to {
    opacity: 0;
}
</style>