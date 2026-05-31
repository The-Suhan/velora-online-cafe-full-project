<template>
    <div class="error-root">
        <span class="corner tl" />
        <span class="corner tr" />
        <span class="corner bl" />
        <span class="corner br" />

        <div class="four04-bg">404</div>

        <div class="cup-wrap">
            <svg width="80" height="90" viewBox="0 0 80 90" fill="none">
                <g class="steam s1">
                    <path d="M32 18 C32 14 36 12 36 8" stroke="#C8A96A" stroke-width="1.2" stroke-linecap="round"
                        opacity="0.6" />
                </g>
                <g class="steam s2">
                    <path d="M40 16 C40 12 44 10 44 6" stroke="#C8A96A" stroke-width="1.2" stroke-linecap="round"
                        opacity="0.5" />
                </g>
                <g class="steam s3">
                    <path d="M48 18 C48 14 52 12 52 8" stroke="#C8A96A" stroke-width="1.2" stroke-linecap="round"
                        opacity="0.4" />
                </g>
                <path d="M14 28h52l-6 34a4 4 0 01-4 3H24a4 4 0 01-4-3L14 28z" stroke="#C8A96A" stroke-width="1.4"
                    stroke-linejoin="round" fill="rgba(200,169,106,0.06)" />
                <path d="M58 36h8a6 6 0 010 12h-8" stroke="#C8A96A" stroke-width="1.4" stroke-linecap="round" />
                <path d="M20 72h40" stroke="#C8A96A" stroke-width="1" stroke-linecap="round" opacity="0.4" />
                <ellipse cx="40" cy="38" rx="8" ry="4" stroke="#C8A96A" stroke-width="1" opacity="0.3" />
            </svg>
        </div>

        <p class="label">Page not found</p>
        <h1 class="title">Looks like this cup<br>is empty</h1>
        <p class="subtitle">The page you're looking for has gone cold.</p>

        <div class="divider" />

        <button class="btn-home" @click="handleError">Back to home</button>
    </div>
</template>

<script setup lang="ts">
const props = defineProps<{ error: { statusCode: number; message: string } }>()

const handleError = () => clearError({ redirect: '/' })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=Cormorant+Garamond:wght@300;400;500&display=swap');

.error-root {
    min-height: 100dvh;
    background: #2C1A14;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    padding: 48px 24px;
    font-family: 'Cormorant Garamond', serif;
}

.corner {
    position: absolute;
    width: 28px;
    height: 28px;
    opacity: 0.25;
}

.corner.tl {
    top: 20px;
    left: 20px;
    border-top: 1px solid #C8A96A;
    border-left: 1px solid #C8A96A;
}

.corner.tr {
    top: 20px;
    right: 20px;
    border-top: 1px solid #C8A96A;
    border-right: 1px solid #C8A96A;
}

.corner.bl {
    bottom: 20px;
    left: 20px;
    border-bottom: 1px solid #C8A96A;
    border-left: 1px solid #C8A96A;
}

.corner.br {
    bottom: 20px;
    right: 20px;
    border-bottom: 1px solid #C8A96A;
    border-right: 1px solid #C8A96A;
}

.four04-bg {
    font-family: 'Playfair Display', serif;
    font-size: clamp(80px, 20vw, 140px);
    font-weight: 600;
    color: rgba(200, 169, 106, 0.07);
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    user-select: none;
    letter-spacing: 0.06em;
    pointer-events: none;
}

.cup-wrap {
    position: relative;
    z-index: 2;
    margin-bottom: 20px;
}

.label {
    font-size: 13px;
    font-weight: 400;
    font-style: italic;
    color: #C8A96A;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    margin: 0 0 10px;
    position: relative;
    z-index: 2;
}

.title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(22px, 5vw, 30px);
    font-weight: 600;
    color: #F5EFEA;
    text-align: center;
    line-height: 1.3;
    letter-spacing: 0.02em;
    margin: 0 0 10px;
    position: relative;
    z-index: 2;
}

.subtitle {
    font-size: 16px;
    font-weight: 300;
    color: rgba(245, 239, 234, 0.45);
    letter-spacing: 0.06em;
    text-align: center;
    margin: 0 0 36px;
    position: relative;
    z-index: 2;
}

.divider {
    width: 48px;
    height: 1px;
    background: rgba(200, 169, 106, 0.3);
    margin: 0 auto 32px;
    position: relative;
    z-index: 2;
}

.btn-home {
    font-family: 'Cormorant Garamond', serif;
    font-size: 13px;
    font-weight: 500;
    color: #2C1A14;
    background: #C8A96A;
    border: none;
    padding: 13px 36px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    cursor: pointer;
    position: relative;
    z-index: 2;
    transition: opacity 0.2s;
}

.btn-home:hover {
    opacity: 0.85;
}

.link-menu {
    font-size: 13px;
    color: rgba(200, 169, 106, 0.55);
    letter-spacing: 0.12em;
    text-decoration: none;
    margin-top: 16px;
    display: block;
    text-align: center;
    position: relative;
    z-index: 2;
    transition: color 0.2s;
}

.link-menu:hover {
    color: #C8A96A;
}

.steam {
    animation: steam-rise 2.8s ease-in-out infinite;
}

.s2 {
    animation-delay: 0.9s;
}

.s3 {
    animation-delay: 1.8s;
}

@keyframes steam-rise {
    0% {
        transform: translateY(0) scaleX(1);
        opacity: 0;
    }

    20% {
        opacity: 1;
    }

    100% {
        transform: translateY(-60px) scaleX(1.3);
        opacity: 0;
    }
}
</style>