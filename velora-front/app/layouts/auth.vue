<template>
    <div class="auth-root">
        <div class="side side-left">
            <img src="/left_background_login.png" alt="" />
        </div>

        <div class="center-slot">
            <slot />
        </div>

        <div class="side side-right">
            <img src="/right_background_login.png" alt="" />
        </div>
    </div>
</template>

<style scoped>
.auth-root {
    min-height: 100vh;
    display: flex;
    background: #2C1810;
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