// composables/useAuth.ts

interface AuthUser {
    id: number
    name: string
    email: string
    role: string
    is_verified?: boolean
}

export const useAuth = () => {
    const config = useRuntimeConfig()
    const router = useRouter()

    const token = useCookie('auth_token', {
        maxAge: 60 * 60 * 24 * 7,
        sameSite: 'lax',
    })

    // tip verildi → artık AuthUser | null, never değil
    const user = useState<AuthUser | null>('user', () => null)

    const apiBase = config.public.apiBase

    // ── Register ───────────────────────────────────────────────
    const register = async (form: {
        name: string
        email: string
        password: string
        password_confirmation: string
    }) => {
        const data = await $fetch(`${apiBase}/register`, {
            method: 'POST',
            body: form,
        })
        return data
    }

    // ── Verify OTP ─────────────────────────────────────────────
    const verifyOtp = async (email: string, code: string, type: string = 'register') => {
        const data: any = await $fetch(`${apiBase}/verify-otp`, {
            method: 'POST',
            body: { email, code, type },
        })
        token.value = data.token
        user.value = data.user
        return data
    }

    // ── Login ──────────────────────────────────────────────────
    const login = async (form: { email: string; password: string }) => {
        const data: any = await $fetch(`${apiBase}/login`, {
            method: 'POST',
            body: form,
        })
        if (data.token) {
            token.value = data.token
            user.value = data.user
        }
        return data
    }

    // ── Logout ─────────────────────────────────────────────────
    const logout = async () => {
        await $fetch(`${apiBase}/logout`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token.value}` },
        }).catch(() => { })
        token.value = null
        user.value = null
        router.push('/login')
    }

    // ── Me ─────────────────────────────────────────────────────
    const fetchMe = async () => {
        if (!token.value) return
        const data: any = await $fetch(`${apiBase}/me`, {
            headers: { Authorization: `Bearer ${token.value}` },
        })
        user.value = data
    }

    const deleteAccount = async () => {
        await $fetch(`${apiBase}/me`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${token.value}` },
        })
        token.value = null
        user.value = null
        router.push('/login')
    }

    const isLoggedIn = computed(() => !!token.value)
    const isAdmin = computed(() => user.value?.role === 'admin')

    return {
        token,
        user,
        isLoggedIn,
        isAdmin,
        register,
        verifyOtp,
        login,
        logout,
        fetchMe,
        deleteAccount
    }
}