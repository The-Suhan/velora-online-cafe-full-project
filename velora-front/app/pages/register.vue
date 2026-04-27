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


    <div v-if="errorMsg" class="error-box">
      {{ errorMsg }}
    </div>

    <!-- Form -->
    <div class="form">
      <!-- name -->
      <div class="input-wrap">
        <span class="input-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="8" r="4" />
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
          </svg>
        </span>
        <input v-model="form.name" type="text" :placeholder="$t('auth.name')" autocomplete="name"
          :class="{ 'input-error': errors.name }" />
      </div>
      <p v-if="errors.name" class="field-error">{{ errors.name }}</p>

      <!-- Email -->
      <div class="input-wrap">
        <span class="input-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="2" y="4" width="20" height="16" rx="2" />
            <path d="M2 7l10 7 10-7" />
          </svg>
        </span>
        <input v-model="form.email" type="email" :placeholder="$t('auth.email')" autocomplete="email"
          :class="{ 'input-error': errors.email }" />
      </div>
      <p v-if="errors.email" class="field-error">{{ errors.email }}</p>

      <!-- password -->
      <div class="input-wrap">
        <span class="input-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="5" y="11" width="14" height="10" rx="2" />
            <path d="M8 11V7a4 4 0 018 0v4" />
          </svg>
        </span>
        <input v-model="form.password" :type="showPw ? 'text' : 'password'" :placeholder="$t('auth.password')"
          autocomplete="new-password" :class="{ 'input-error': errors.password }" />
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

      <!-- return password -->
      <div class="input-wrap">
        <span class="input-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="5" y="11" width="14" height="10" rx="2" />
            <path d="M8 11V7a4 4 0 018 0v4" />
          </svg>
        </span>
        <input v-model="form.password_confirmation" :type="showPwConfirm ? 'text' : 'password'"
          :placeholder="$t('auth.confirmPassword')" autocomplete="new-password"
          :class="{ 'input-error': errors.password_confirmation }" />
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

      <!-- submit button -->
      <button class="btn-register" :disabled="loading" @click="handleRegister">
        <span v-if="loading" class="spinner" />
        <span v-else>{{ $t('auth.register') }}</span>
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

    <p class="login-link">
      {{ $t('auth.haveAccount') }}
      <NuxtLink to="/login">{{ $t('auth.login') }}</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'guest',
})

const { register } = useAuth()
const router = useRouter()
const { t } = useI18n()         

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = reactive<Record<string, string>>({})
const errorMsg = ref('')
const loading = ref(false)
const showPw = ref(false)
const showPwConfirm = ref(false)

const clearErrors = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  errorMsg.value = ''
}

const handleRegister = async () => {
  clearErrors()
  if (!form.name.trim()) { errors.name = t('auth.nameRequired'); return }
  if (!form.email.trim()) { errors.email = t('auth.emailRequired'); return }
  if (form.password.length < 8) { errors.password = t('auth.passwordMin'); return }
  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = t('auth.passwordMismatch')
    return
  }

  loading.value = true
  try {
    await register(form)
    router.push({ path: '/verify-otp', query: { email: form.email } })
  } catch (err: any) {
    const data = err?.data
    if (data?.errors) {
      Object.entries(data.errors).forEach(([field, msgs]: any) => {
        errors[field] = msgs[0]
      })
    } else {
      errorMsg.value = data?.message ?? t('auth.genericError') 
    }
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
  box-shadow: 0 32px 80px rgba(0, 0, 0, 0.45);
  font-family: 'Jost', sans-serif;
}

/* ── Logo ──────────────────────────────── */
.logo-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: 12px;
}

/* ── Brand ─────────────────────────────── */
.brand-name {
  font-family: 'Cormorant Garamond', serif;
  font-size: 2rem;
  font-weight: 600;
  color: #2C1810;
  letter-spacing: 0.14em;
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

.lang-row {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 16px;
}

.cafe-text {
  font-family: 'Jost', sans-serif;
  font-size: 0.72rem;
  letter-spacing: 0.28em;
  color: #C8A96E;
  font-weight: 500;
}

.line {
  flex: 1;
  max-width: 44px;
  height: 0.5px;
  background: #C8A96E;
  opacity: 0.6;
}

.tagline {
  font-family: 'Cormorant Garamond', serif;
  font-style: italic;
  font-size: 1rem;
  color: #8a7060;
  text-align: center;
  margin: 0 0 22px;
}

/* ── Error box ─────────────────────────── */
.error-box {
  background: #fde8e8;
  border: 1px solid #f5a5a5;
  color: #8b1a1a;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 0.83rem;
  margin-bottom: 14px;
  text-align: center;
}

/* ── Form ──────────────────────────────── */
.form {
  display: flex;
  flex-direction: column;
  gap: 0;
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
  border: 1.5px solid rgba(200, 169, 110, 0.35);
  border-radius: 12px;
  padding: 13px 44px 13px 44px;
  font-family: 'Jost', sans-serif;
  font-size: 0.9rem;
  color: #2C1810;
  outline: none;
  transition: border-color 0.2s;
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
  font-size: 0.78rem;
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

/* ── Button ────────────────────────────── */
.btn-register {
  width: 100%;
  background: #4A6741;
  color: #d4b882;
  border: none;
  border-radius: 12px;
  padding: 14px;
  font-family: 'Jost', sans-serif;
  font-size: 0.78rem;
  font-weight: 500;
  letter-spacing: 0.24em;
  cursor: pointer;
  margin-top: 4px;
  transition: background 0.2s, transform 0.1s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 50px;
}

.btn-register:hover:not(:disabled) {
  background: #5a7a50;
}

.btn-register:active:not(:disabled) {
  transform: scale(0.98);
}

.btn-register:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(212, 184, 130, 0.3);
  border-top-color: #d4b882;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ── Divider ───────────────────────────── */
.divider {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 18px 0 14px;
}

.divider-line {
  flex: 1;
  height: 0.5px;
  background: #C8A96E;
  opacity: 0.35;
}

.bean-icon {
  width: 16px;
  height: 16px;
  color: #C8A96E;
  opacity: 0.7;
}

/* ── Login link ────────────────────────── */
.login-link {
  text-align: center;
  font-size: 0.82rem;
  color: #8a7060;
  margin: 0;
}

.login-link a {
  color: #C8A96E;
  text-decoration: none;
  font-weight: 500;
  margin-left: 4px;
}

.login-link a:hover {
  text-decoration: underline;
}

/* ── Mobile ────────────────────────────── */
@media (max-width: 480px) {
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