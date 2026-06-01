<template>
    <div class="min-h-screen bg-[#F5EFEA]">

        <!-- Header -->
        <div class="bg-[#2C1A14] px-6 py-5 flex items-center gap-4">
            <NuxtLink to="/profile" class="text-[#C8A96A] hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
            </NuxtLink>
            <h1 class="text-[#F5EFEA] text-lg font-medium">{{ $t('profile.edit.title') }}</h1>
        </div>

        <div class="max-w-lg mx-auto px-4 py-6 flex flex-col gap-4">

            <!-- Avatar -->
            <div class="bg-white rounded-2xl border border-[#E0D5CC] p-6 flex flex-col items-center gap-2">
                <div
                    class="w-20 h-20 rounded-full bg-[#2C1A14] flex items-center justify-center text-[#C8A96A] text-2xl font-medium">
                    {{ initials }}
                </div>
                <p class="text-[#2C1A14] font-medium text-sm">{{ form.name }}</p>
                <p class="text-[#7A6558] text-xs">{{ form.email }}</p>
            </div>

            <!-- Personal Info -->
            <div class="bg-white rounded-2xl border border-[#E0D5CC] p-5">
                <p class="text-[#2C1A14] font-medium text-sm mb-4">{{ $t('profile.edit.personalInfo') }}</p>
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="text-[#7A6558] text-xs uppercase tracking-wide block mb-1.5">{{
                            $t('profile.edit.fullName') }}</label>
                        <input v-model="form.name" type="text" :placeholder="$t('profile.edit.fullNamePlaceholder')"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-[#E0D5CC] text-[#2C1A14] text-sm bg-white focus:outline-none focus:border-[#C8A96A] focus:ring-1 focus:ring-[#C8A96A] transition-colors placeholder:text-[#BFB5AD]" />
                    </div>
                    <div>
                        <label class="text-[#7A6558] text-xs uppercase tracking-wide block mb-1.5">{{
                            $t('profile.edit.email') }}</label>
                        <input :value="form.email" type="email" disabled
                            class="w-full px-3.5 py-2.5 rounded-lg border border-[#E0D5CC] text-[#BFB5AD] text-sm bg-[#F5EFEA] cursor-not-allowed" />
                        <p class="text-[10px] text-[#7A6558] mt-1">{{ $t('profile.edit.emailHint') }}</p>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-2xl border border-[#E0D5CC] p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[#2C1A14] font-medium text-sm">{{ $t('profile.edit.password') }}</p>
                    <button @click="showPassword = !showPassword" class="text-[#C8A96A] text-xs hover:underline">
                        {{ showPassword ? $t('profile.edit.cancel') : $t('profile.edit.change') }}
                    </button>
                </div>

                <template v-if="showPassword">
                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="text-[#7A6558] text-xs uppercase tracking-wide block mb-1.5">{{
                                $t('profile.edit.currentPassword') }}</label>
                            <input v-model="passwordForm.current" type="password" placeholder="••••••••"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E0D5CC] text-[#2C1A14] text-sm focus:outline-none focus:border-[#C8A96A] focus:ring-1 focus:ring-[#C8A96A] transition-colors placeholder:text-[#BFB5AD]" />
                        </div>
                        <div>
                            <label class="text-[#7A6558] text-xs uppercase tracking-wide block mb-1.5">{{
                                $t('profile.edit.newPassword') }}</label>
                            <input v-model="passwordForm.new" type="password" placeholder="••••••••"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E0D5CC] text-[#2C1A14] text-sm focus:outline-none focus:border-[#C8A96A] focus:ring-1 focus:ring-[#C8A96A] transition-colors placeholder:text-[#BFB5AD]" />
                        </div>
                        <div>
                            <label class="text-[#7A6558] text-xs uppercase tracking-wide block mb-1.5">{{
                                $t('profile.edit.confirmNewPassword') }}</label>
                            <input v-model="passwordForm.confirm" type="password" placeholder="••••••••"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E0D5CC] text-[#2C1A14] text-sm focus:outline-none focus:border-[#C8A96A] focus:ring-1 focus:ring-[#C8A96A] transition-colors placeholder:text-[#BFB5AD]" />
                        </div>
                        <p v-if="passwordError" class="text-red-500 text-xs">{{ passwordError }}</p>
                    </div>
                </template>
                <template v-else>
                    <p class="text-[#BFB5AD] text-sm tracking-widest">••••••••••••</p>
                </template>
            </div>

            <!-- Feedback messages -->
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-600 text-sm">
                {{ error }}
            </div>
            <div v-if="success" class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-green-700 text-sm">
                {{ success }}
            </div>

            <!-- Save button -->
            <button @click="handleSave" :disabled="saving"
                class="w-full py-3.5 bg-[#2C1A14] text-[#F5EFEA] rounded-xl text-sm font-medium hover:bg-[#3d261d] active:scale-[0.98] transition-all disabled:opacity-50">
                {{ saving ? $t('profile.edit.saving') : $t('profile.edit.save') }}
            </button>

            <!-- Danger zone -->
            <div class="bg-white rounded-2xl border border-red-100 p-5">
                <p class="text-[#2C1A14] font-medium text-sm mb-1">{{ $t('profile.edit.dangerZone') }}</p>
                <p class="text-[#7A6558] text-xs mb-3">{{ $t('profile.edit.dangerDesc') }}</p>
                <button @click="handleDeleteAccount"
                    class="text-red-500 text-sm border border-red-200 px-4 py-2 rounded-lg hover:bg-red-50 transition-colors">
                    {{ $t('profile.edit.deleteAccount') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { t } = useI18n()

const { user: authUser, logout, deleteAccount } = useAuth()
const { fetchMe, updateMe } = useProfile()

const form = reactive({ name: '', email: '', locale: 'en' })
const passwordForm = reactive({ current: '', new: '', confirm: '' })
const showPassword = ref(false)
const saving = ref(false)
const error = ref('')
const success = ref('')
const passwordError = ref('')

const initials = computed(() =>
    form.name.split(' ').map((w: string) => w[0]).join('').slice(0, 2).toUpperCase()
)

onMounted(async () => {
    const cached = authUser.value as any
    if (cached?.name) {
        form.name = cached.name
        form.email = cached.email
    } else {
        const me = await fetchMe()
        form.name = me.name
        form.email = me.email
    }
})

async function handleDeleteAccount() {
    if (!confirm(t('profile.edit.deleteConfirm'))) return
    try {
        await deleteAccount()
    } catch (e: any) {
        error.value = e?.data?.message ?? 'Could not delete account.'
    }
}

async function handleSave() {
    error.value = ''
    success.value = ''
    passwordError.value = ''

    if (showPassword.value) {
        if (!passwordForm.current) {
            passwordError.value = t('profile.edit.errorCurrentRequired')
            return
        }
        if (passwordForm.new.length < 8) {
            passwordError.value = t('profile.edit.errorPasswordMin')
            return
        }
        if (passwordForm.new !== passwordForm.confirm) {
            passwordError.value = t('profile.edit.errorPasswordMismatch')
            return
        }
    }

    saving.value = true
    try {
        const res = await updateMe({
            name: form.name,
            ...(showPassword.value && {
                current_password: passwordForm.current,
                password: passwordForm.new,
                password_confirmation: passwordForm.confirm,
            }),
        })

        if (authUser.value) {
            authUser.value.name = res.user.name
        }

        success.value = res.message ?? t('profile.edit.successMsg')
        showPassword.value = false
        passwordForm.current = passwordForm.new = passwordForm.confirm = ''
    } catch (e: any) {
        const msg = e?.data?.message
        const errs = e?.data?.errors
        if (errs) {
            error.value = Object.values(errs).flat().join(' ')
        } else {
            error.value = msg ?? t('profile.edit.errorGeneric')
        }
    } finally {
        saving.value = false
    }
}
</script>