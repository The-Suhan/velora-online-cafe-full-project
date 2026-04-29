<template>
    <div class="card">
        <div class="lang-row">
            <LanguageSwitcher />
        </div>

        <h1 class="brand-name">VELORA</h1>
        <div class="brand-cafe">
            <span class="line" />
            <span class="cafe-text">CAFÉ</span>
            <span class="line" />
        </div>

        <div class="head-wrap">
            <div class="icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="1.6">
                    <rect x="5" y="11" width="14" height="10" rx="2" />
                    <path d="M8 11V7a4 4 0 018 0v4" />
                    <circle cx="12" cy="16" r="1.2" fill="#C8A96E" stroke="none" />
                </svg>
            </div>
            <p class="head-title">{{ $t('auth.forgotTitle') }}</p>
            <p class="head-desc">{{ $t('auth.forgotDesc') }}</p>
        </div>

        <div v-if="errorMsg" class="error-box">{{ errorMsg }}</div>
        <div v-if="successMsg" class="success-box">{{ successMsg }}</div>

        <div class="form">
            <div class="input-wrap">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="4" width="20" height="16" rx="2" />
                        <path d="M2 7l10 7 10-7" />
                    </svg>
                </span>
                <input v-model="email" type="email" :placeholder="$t('auth.email')" autocomplete="email"
                    :class="{ 'input-error': errorMsg }" @keyup.enter="handleSend" />
            </div>

            <button class="btn-submit" :disabled="loading" @click="handleSend">
                <span v-if="loading" class="spinner" />
                <span v-else>{{ $t('auth.sendCode') }}</span>
            </button>
        </div>

        <div class="divider">
            <span class="divider-line" />
            <svg class="bean-icon" viewBox="0 0 24 24" fill="currentColor">
                <ellipse cx="12" cy="12" rx="9" ry="6" transform="rotate(-30 12 12)" />
                <path d="M12 6 Q9 12 12 18" stroke="#F5F0E8" stroke-width="1.5" fill="none" />
            </svg>
            <span class="divider-line" />
        </div>

        <p class="back-link">
            <NuxtLink to="/login">{{ $t('auth.backToLogin') }}</NuxtLink>
        </p>
    </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'auth', middleware: 'guest' })

const router = useRouter()
const config = useRuntimeConfig()
const { t } = useI18n()

const email = ref('')
const loading = ref(false)
const errorMsg = ref('')
const successMsg = ref('')

const handleSend = async () => {
    errorMsg.value = ''
    successMsg.value = ''

    if (!email.value.trim()) {
        errorMsg.value = t('auth.emailRequired')
        return
    }

    loading.value = true
    try {
        await $fetch(`${config.public.apiBase}/forgot-password`, {
            method: 'POST',
            body: { email: email.value },
        })
        router.push({
            path: '/forgot-password/verify',
            query: { email: email.value },
        })
    } catch (err: any) {
        errorMsg.value = err?.data?.message ?? t('auth.genericError')
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.card {
    width: 100%;
    max-width: 420px;
    background: #F5F0E8;
    border-radius: 24px;
    padding: 40px 36px 32px;
    box-shadow: 0 32px 80px rgba(0, 0, 0, .45);
    font-family: 'Jost', sans-serif;
}

.lang-row {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 16px;
}

.brand-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    font-weight: 600;
    color: #2C1810;
    letter-spacing: .14em;
    text-align: center;
    line-height: 1;
    margin: 0;
}

.brand-cafe {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 5px 0 20px;
}

.cafe-text {
    font-size: .72rem;
    letter-spacing: .28em;
    color: #C8A96E;
    font-weight: 500;
}

.line {
    flex: 1;
    max-width: 44px;
    height: .5px;
    background: #C8A96E;
    opacity: .6;
}

.head-wrap {
    text-align: center;
    margin-bottom: 24px;
}

.icon-wrap {
    width: 56px;
    height: 56px;
    background: #EDE5D8;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
}

.icon-wrap svg {
    width: 28px;
    height: 28px;
}

.head-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    font-weight: 600;
    color: #2C1810;
    margin: 0 0 6px;
}

.head-desc {
    font-size: .84rem;
    color: #8a7060;
    margin: 0;
    line-height: 1.5;
}

.error-box {
    background: #fde8e8;
    border: 1px solid #f5a5a5;
    color: #8b1a1a;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .83rem;
    margin-bottom: 14px;
    text-align: center;
}

.success-box {
    background: #eef6ee;
    border: 1px solid #a5c8a5;
    color: #2d5a2d;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .83rem;
    margin-bottom: 14px;
    text-align: center;
}

.form {
    display: flex;
    flex-direction: column;
}

.input-wrap {
    position: relative;
    margin-bottom: 10px;
}

.input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    color: #C8A96E;
    pointer-events: none;
    display: flex;
    align-items: center;
}

.input-icon svg {
    width: 18px;
    height: 18px;
}

.input-wrap input {
    width: 100%;
    background: #EDE5D8;
    border: 1.5px solid rgba(200, 169, 110, .35);
    border-radius: 12px;
    padding: 13px 14px 13px 44px;
    font-family: 'Jost', sans-serif;
    font-size: .9rem;
    color: #2C1810;
    outline: none;
    transition: border-color .2s;
    box-sizing: border-box;
}

.input-wrap input::placeholder {
    color: #b0967a;
}

.input-wrap input:focus {
    border-color: #C8A96E;
}

.input-wrap input.input-error {
    border-color: #e05252;
}

.btn-submit {
    width: 100%;
    background: #C8A96E;
    color: #2C1810;
    border: none;
    border-radius: 12px;
    padding: 14px;
    font-family: 'Jost', sans-serif;
    font-size: .78rem;
    font-weight: 600;
    letter-spacing: .24em;
    cursor: pointer;
    margin-top: 4px;
    transition: background .2s, transform .1s;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 50px;
}

.btn-submit:hover:not(:disabled) {
    background: #d4b882;
}

.btn-submit:active:not(:disabled) {
    transform: scale(.98);
}

.btn-submit:disabled {
    opacity: .7;
    cursor: not-allowed;
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(44, 24, 16, .2);
    border-top-color: #2C1810;
    border-radius: 50%;
    animation: spin .7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 20px 0 14px;
}

.divider-line {
    flex: 1;
    height: .5px;
    background: #C8A96E;
    opacity: .35;
}

.bean-icon {
    width: 16px;
    height: 16px;
    color: #C8A96E;
    opacity: .7;
}

.back-link {
    text-align: center;
    margin: 0;
}

.back-link a {
    font-size: .82rem;
    color: #C8A96E;
    text-decoration: none;
    font-weight: 500;
}

.back-link a:hover {
    text-decoration: underline;
}

@media (max-width: 480px) {
    .card {
        padding: 32px 20px 28px;
        border-radius: 20px;
    }

    .brand-name {
        font-size: 1.75rem;
    }
}
</style>