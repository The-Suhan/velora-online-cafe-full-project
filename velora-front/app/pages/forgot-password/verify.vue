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

        <p class="otp-title">{{ $t('auth.verificationCode') }}</p>
        <p class="otp-desc">
            <strong>{{ email }}</strong><br />
            {{ $t('auth.verificationDesc') }}
        </p>

        <div v-if="errorMsg" class="error-box">{{ errorMsg }}</div>

        <div class="otp-inputs">
            <input v-for="(_, i) in 6" :key="i" :ref="el => { if (el) inputs[i] = el as HTMLInputElement }"
                v-model="digits[i]" type="text" inputmode="numeric" maxlength="1" class="otp-box"
                :class="{ 'otp-error': errorMsg }" @input="onInput(i)" @keydown.backspace="onBackspace(i)"
                @paste="onPaste" />
        </div>

        <button class="btn-verify" :disabled="loading || code.length < 6" @click="handleVerify">
            <span v-if="loading" class="spinner" />
            <span v-else>{{ $t('auth.verify') }}</span>
        </button>

        <div class="resend-wrap">
            <span v-if="countdown > 0" class="resend-timer">{{ $t('auth.resendTimer', { n: countdown }) }}</span>
            <button v-else class="resend-btn" :disabled="resending" @click="handleResend">
                {{ resending ? $t('auth.resending') : $t('auth.resend') }}
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
            <NuxtLink to="/forgot-password">← {{ $t('auth.backToForgot') }}</NuxtLink>
        </p>
    </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'auth', middleware: 'guest' })

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const { t } = useI18n()

const email = ref((route.query.email as string) || '')
const digits = ref<string[]>(Array(6).fill(''))
const inputs = ref<HTMLInputElement[]>([])
const loading = ref(false)
const resending = ref(false)
const errorMsg = ref('')
const countdown = ref(60)

const code = computed(() => digits.value.join(''))

let timer: ReturnType<typeof setInterval>
onMounted(() => {
    timer = setInterval(() => {
        if (countdown.value > 0) countdown.value--
        else clearInterval(timer)
    }, 1000)
    nextTick(() => inputs.value[0]?.focus())
})
onUnmounted(() => clearInterval(timer))

const onInput = (i: number) => {
    if (digits.value[i] && i < 5) nextTick(() => inputs.value[i + 1]?.focus())
    errorMsg.value = ''
}
const onBackspace = (i: number) => {
    if (!digits.value[i] && i > 0) {
        digits.value[i - 1] = ''
        nextTick(() => inputs.value[i - 1]?.focus())
    }
}
const onPaste = (e: ClipboardEvent) => {
    e.preventDefault()
    const pasted = e.clipboardData?.getData('text').replace(/\D/g, '').slice(0, 6) ?? ''
    pasted.split('').forEach((ch, i) => { digits.value[i] = ch })
    nextTick(() => inputs.value[Math.min(pasted.length, 5)]?.focus())
}

const handleVerify = async () => {
    loading.value = true
    errorMsg.value = ''
    try {
        const res: any = await $fetch(`${config.public.apiBase}/forgot-password/verify`, {
            method: 'POST',
            body: { email: email.value, code: code.value },
        })
        // reset_token ile şifre sıfırlama sayfasına git
        router.push({
            path: '/forgot-password/reset',
            query: { token: res.reset_token },
        })
    } catch (err: any) {
        errorMsg.value = err?.data?.message ?? t('auth.invalidCode')
        digits.value = Array(6).fill('')
        nextTick(() => inputs.value[0]?.focus())
    } finally {
        loading.value = false
    }
}

const handleResend = async () => {
    resending.value = true
    try {
        await $fetch(`${config.public.apiBase}/forgot-password`, {
            method: 'POST',
            body: { email: email.value },
        })
        countdown.value = 60
        clearInterval(timer)
        timer = setInterval(() => {
            if (countdown.value > 0) countdown.value--
            else clearInterval(timer)
        }, 1000)
        digits.value = Array(6).fill('')
        errorMsg.value = ''
        nextTick(() => inputs.value[0]?.focus())
    } catch (err: any) {
        errorMsg.value = t('auth.sendFailed')
    } finally {
        resending.value = false
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
    font-size: 1.9rem;
    font-weight: 600;
    color: #2C1810;
    letter-spacing: .14em;
    text-align: center;
    margin: 0;
}

.brand-cafe {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 4px 0 16px;
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

.otp-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    font-weight: 600;
    color: #2C1810;
    text-align: center;
    margin: 0 0 6px;
}

.otp-desc {
    font-size: .84rem;
    color: #8a7060;
    text-align: center;
    margin: 0 0 20px;
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

.otp-inputs {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-bottom: 20px;
}

.otp-box {
    width: 48px;
    height: 56px;
    background: #EDE5D8;
    border: 1.5px solid rgba(200, 169, 110, .35);
    border-radius: 12px;
    text-align: center;
    font-family: 'Jost', sans-serif;
    font-size: 1.4rem;
    font-weight: 500;
    color: #2C1810;
    outline: none;
    transition: border-color .2s;
}

.otp-box:focus {
    border-color: #C8A96E;
}

.otp-box.otp-error {
    border-color: #e05252;
}

.btn-verify {
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
    transition: background .2s, transform .1s;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 50px;
}

.btn-verify:hover:not(:disabled) {
    background: #5a7a50;
}

.btn-verify:active:not(:disabled) {
    transform: scale(.98);
}

.btn-verify:disabled {
    opacity: .6;
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

.resend-wrap {
    text-align: center;
    margin-top: 14px;
}

.resend-timer {
    font-size: .82rem;
    color: #b0967a;
}

.resend-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    font-size: .82rem;
    color: #C8A96E;
    font-weight: 500;
    text-decoration: underline;
}

.resend-btn:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 18px 0 14px;
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
        padding: 32px 16px 28px;
    }

    .otp-box {
        width: 42px;
        height: 50px;
        font-size: 1.2rem;
    }

    .otp-inputs {
        gap: 7px;
    }
}
</style>