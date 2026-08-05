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
        <p class="tagline">{{ $t('auth.tagline') }}</p>

        <div v-if="errorMsg" class="error-box">{{ errorMsg }}</div>

        <div v-if="requiresVerify" class="info-box">
            {{ $t('auth.unverified') }}
            <NuxtLink :to="`/verify-otp?email=${form.email}&type=login`" class="otp-link">
                {{ $t('auth.goVerify') }}
            </NuxtLink>
        </div>

        <div class="form">
            <div class="input-wrap">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="4" width="20" height="16" rx="2" />
                        <path d="M2 7l10 7 10-7" />
                    </svg>
                </span>
                <input v-model="form.email" type="email" :placeholder="$t('auth.email')" autocomplete="email"
                    :class="{ 'input-error': errors.email }" @keyup.enter="handleLogin" />
            </div>
            <p v-if="errors.email" class="field-error">{{ errors.email }}</p>

            <div class="input-wrap">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="5" y="11" width="14" height="10" rx="2" />
                        <path d="M8 11V7a4 4 0 018 0v4" />
                    </svg>
                </span>
                <input v-model="form.password" :type="showPw ? 'text' : 'password'" :placeholder="$t('auth.password')"
                    autocomplete="current-password" :class="{ 'input-error': errors.password }"
                    @keyup.enter="handleLogin" />
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

            <button class="btn-submit" :disabled="loading" @click="handleLogin">
                <span v-if="loading" class="spinner" />
                <span v-else>{{ $t('auth.submit') }}</span>
            </button>
            <p class="forgot-link">
                <NuxtLink to="/forgot-password">{{ $t('auth.forgotPassword') }}</NuxtLink>
            </p>
        </div>

        <div class="divider">
            <span class="divider-line" />
            <svg class="bean-icon" viewBox="0 0 24 24" fill="currentColor">
                <ellipse cx="12" cy="12" rx="9" ry="6" transform="rotate(-30 12 12)" />
                <path d="M12 6 Q9 12 12 18" stroke="var(--color-surface-warm)" stroke-width="1.5" fill="none" />
            </svg>
            <span class="divider-line" />
        </div>

        <p class="no-account">{{ $t('auth.noAccount') }}</p>
        <NuxtLink to="/register" class="btn-register-outline">{{ $t('auth.register') }}</NuxtLink>
    </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'auth', middleware: 'guest' })
useHead({ title: 'Velora — Login' })

const { login } = useAuth()
const router = useRouter()
const { t } = useI18n()

const form = reactive({ email: '', password: '' })
const errors = reactive<Record<string, string>>({})
const errorMsg = ref('')
const loading = ref(false)
const showPw = ref(false)
const requiresVerify = ref(false)

const clearErrors = () => {
    Object.keys(errors).forEach(k => delete errors[k])
    errorMsg.value = ''
    requiresVerify.value = false
}

const handleLogin = async () => {
    clearErrors()
    if (!form.email.trim()) { errors.email = t('auth.emailRequired'); return }
    if (!form.password) { errors.password = t('auth.passwordRequired'); return }

    loading.value = true
    try {
        const res: any = await login(form)

        if (res.requires_verify) {
            requiresVerify.value = true
            return
        }

        router.push(res.user?.role === 'admin' ? '/admin' : '/')

    } catch (err: any) {
        const data = err?.data
        if (data?.errors) {
            Object.entries(data.errors).forEach(([field, msgs]: any) => {
                errors[field] = msgs[0]
            })
        } else {
            errorMsg.value = data?.message ?? t('auth.loginError')
        }
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.lang-row {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 16px;
}

.card {
    width: 100%;
    max-width: 420px;
    background: var(--color-surface-warm);
    border-radius: 24px;
    padding: 40px 36px 32px;
    box-shadow: 0 32px 80px rgb(var(--rgb-black) / .5);
    font-family: 'Jost', sans-serif;
}

.logo-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 12px;
}

.logo-img {
    width: 90px;
    height: auto;
    object-fit: contain;
}

.brand-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    font-weight: 600;
    color: var(--color-primary);
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
    margin: 5px 0 2px;
}

.cafe-text {
    font-size: .72rem;
    letter-spacing: .28em;
    color: var(--color-accent);
    font-weight: 500;
}

.line {
    flex: 1;
    max-width: 44px;
    height: .5px;
    background: var(--color-accent);
    opacity: .6;
}

.tagline {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 1rem;
    color: var(--color-muted);
    text-align: center;
    margin: 0 0 24px;
}

.error-box {
    background: var(--color-red-95);
    border: 1px solid var(--color-red-80);
    color: var(--color-red-32);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .83rem;
    margin-bottom: 14px;
    text-align: center;
}

.forgot-link {
    text-align: right;
    margin: 8px 0 0;
}

.forgot-link a {
    font-size: 0.78rem;
    color: var(--color-muted);
    text-decoration: none;
    font-family: 'Jost', sans-serif;
    font-weight: 400;
    letter-spacing: 0.02em;
    border-bottom: 0.5px solid rgb(var(--rgb-accent) / 0.4);
    padding-bottom: 1px;
    transition: color 0.2s, border-color 0.2s;
}

.forgot-link a:hover {
    color: var(--color-accent);
    border-bottom-color: var(--color-accent);
}

.info-box {
    background: var(--color-green-95);
    border: 1px solid var(--color-green-72);
    color: var(--color-green-26-2);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .83rem;
    margin-bottom: 14px;
    text-align: center;
    line-height: 1.5;
}

.otp-link {
    display: block;
    color: var(--color-success);
    font-weight: 600;
    margin-top: 4px;
    text-decoration: none;
}

.otp-link:hover {
    text-decoration: underline;
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
    color: var(--color-accent);
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
    background: var(--color-brown-89-2);
    border: 1.5px solid rgb(var(--rgb-accent) / .35);
    border-radius: 12px;
    padding: 13px 44px;
    font-family: 'Jost', sans-serif;
    font-size: .9rem;
    color: var(--color-primary);
    outline: none;
    transition: border-color .2s;
    box-sizing: border-box;
}

.input-wrap input::placeholder {
    color: var(--color-muted-light);
}

.input-wrap input:focus {
    border-color: var(--color-accent);
}

.input-wrap input.input-error {
    border-color: var(--color-red-60);
}

.field-error {
    font-size: .78rem;
    color: var(--color-red-46);
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
    color: var(--color-muted);
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
    background: var(--color-accent);
    color: var(--color-on-accent);
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
    background: var(--color-brown-67);
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
    border: 2px solid rgb(var(--rgb-primary) / .2);
    border-top-color: var(--color-primary);
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
    margin: 20px 0 16px;
}

.divider-line {
    flex: 1;
    height: .5px;
    background: var(--color-accent);
    opacity: .35;
}

.bean-icon {
    width: 16px;
    height: 16px;
    color: var(--color-accent);
    opacity: .7;
}

.no-account {
    text-align: center;
    font-size: .84rem;
    color: var(--color-muted);
    margin: 0 0 12px;
}

.btn-register-outline {
    display: block;
    width: 100%;
    background: transparent;
    border: 1.5px solid var(--color-accent);
    border-radius: 12px;
    padding: 13px;
    font-family: 'Jost', sans-serif;
    font-size: .78rem;
    font-weight: 500;
    letter-spacing: .24em;
    color: var(--color-accent);
    text-align: center;
    text-decoration: none;
    transition: background .2s, color .2s;
    box-sizing: border-box;
}

.btn-register-outline:hover {
    background: var(--color-accent);
    color: var(--color-on-accent);
}

@media (max-width:480px) {
    .card {
        padding: 32px 20px 28px;
        border-radius: 20px;
    }

    .logo-img {
        width: 76px;
    }

    .brand-name {
        font-size: 1.75rem;
    }
}
</style>