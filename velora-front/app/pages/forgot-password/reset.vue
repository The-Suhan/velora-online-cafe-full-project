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
                <svg viewBox="0 0 24 24" fill="none" stroke="#4A6741" stroke-width="1.6">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
            </div>
            <p class="head-title">{{ $t('auth.newPasswordTitle') }}</p>
            <p class="head-desc">{{ $t('auth.newPasswordDesc') }}</p>
        </div>

        <div v-if="errorMsg" class="error-box">{{ errorMsg }}</div>

        <div class="form">
            <div class="input-wrap">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="5" y="11" width="14" height="10" rx="2" />
                        <path d="M8 11V7a4 4 0 018 0v4" />
                    </svg>
                </span>
                <input v-model="form.password" :type="showPw ? 'text' : 'password'"
                    :placeholder="$t('auth.newPassword')" :class="{ 'input-error': errors.password }" />
                <button class="eye-btn" type="button" @click="showPw = !showPw">
                    <svg v-if="!showPw" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <ellipse cx="12" cy="12" rx="10" ry="6" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94" />
                        <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                </button>
            </div>
            <p v-if="errors.password" class="field-error">{{ errors.password }}</p>

            <div class="input-wrap">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="5" y="11" width="14" height="10" rx="2" />
                        <path d="M8 11V7a4 4 0 018 0v4" />
                    </svg>
                </span>
                <input v-model="form.password_confirmation" :type="showPwConfirm ? 'text' : 'password'"
                    :placeholder="$t('auth.confirmPassword')" :class="{ 'input-error': errors.password_confirmation }"
                    @keyup.enter="handleReset" />
                <button class="eye-btn" type="button" @click="showPwConfirm = !showPwConfirm">
                    <svg v-if="!showPwConfirm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <ellipse cx="12" cy="12" rx="10" ry="6" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94" />
                        <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                </button>
            </div>
            <p v-if="errors.password_confirmation" class="field-error">{{ errors.password_confirmation }}</p>

            <button class="btn-submit" :disabled="loading" @click="handleReset">
                <span v-if="loading" class="spinner" />
                <span v-else>{{ $t('auth.resetPassword') }}</span>
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

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const { t } = useI18n()

const resetToken = ref((route.query.token as string) || '')

if (!resetToken.value) {
    navigateTo('/forgot-password')
}

const form = reactive({ password: '', password_confirmation: '' })
const errors = reactive<Record<string, string>>({})
const errorMsg = ref('')
const loading = ref(false)
const showPw = ref(false)
const showPwConfirm = ref(false)

const handleReset = async () => {
    Object.keys(errors).forEach(k => delete errors[k])
    errorMsg.value = ''

    if (form.password.length < 8) { errors.password = t('auth.passwordMin'); return }
    if (form.password !== form.password_confirmation) {
        errors.password_confirmation = t('auth.passwordMismatch'); return
    }

    loading.value = true
    try {
        await $fetch(`${config.public.apiBase}/forgot-password/reset`, {
            method: 'POST',
            body: {
                reset_token: resetToken.value,
                password: form.password,
                password_confirmation: form.password_confirmation,
            },
        })
        router.push({ path: '/login', query: { reset: 'success' } })
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
    background: #e8f0e8;
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
    padding: 13px 44px;
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

.field-error {
    font-size: .78rem;
    color: #c0392b;
    margin: -6px 0 8px 4px;
}

.eye-btn {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #8a7060;
    padding: 0;
    display: flex;
    align-items: center;
    width: 20px;
    height: 20px;
}

.eye-btn svg {
    width: 18px;
    height: 18px;
}

.btn-submit {
    width: 100%;
    background: #4A6741;
    color: #d4b882;
    border: none;
    border-radius: 12px;
    padding: 14px;
    font-family: 'Jost', sans-serif;
    font-size: .78rem;
    font-weight: 500;
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
    background: #5a7a50;
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
    border: 2px solid rgba(212, 184, 130, .3);
    border-top-color: #d4b882;
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