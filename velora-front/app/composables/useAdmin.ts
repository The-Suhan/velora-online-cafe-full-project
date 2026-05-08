export const useAdmin = () => {
    const config = useRuntimeConfig()
    const { token, fetchMe, user } = useAuth()

    const apiBase = config.public.apiBase

    const authHeaders = computed(() => ({
        Authorization: `Bearer ${token.value}`,
    }))

    const fetchDashboard = async () => {
        return await $fetch(`${apiBase}/admin/dashboard`, {
            headers: authHeaders.value,
        })
    }

    const fetchOrdersChart = async (
        period: 'daily' | 'weekly' | 'monthly' | 'yearly' | 'custom',
        options?: { startDate?: string; endDate?: string }
    ) => {
        const query: Record<string, string> = { period }
        if (period === 'custom' && options?.startDate && options?.endDate) {
            query.start_date = options.startDate
            query.end_date = options.endDate
        }
        return await $fetch(`${apiBase}/admin/orders/chart`, {
            headers: authHeaders.value,
            query,
        })
    }

    const initAuth = async () => {
        if (token.value && !user.value) {
            await fetchMe()
        }
    }

    // ── Categories ────────────────────────────────────────────
    const fetchCategories = async (params: {
        per_page?: number
        page?: number
        search?: string
    } = {}) => {
        return await $fetch(`${apiBase}/admin/categories`, {
            headers: authHeaders.value,
            query: params,
        })
    }

    const fetchCategoryStats = async () => {
        return await $fetch(`${apiBase}/admin/categories/stats`, {
            headers: authHeaders.value,
        })
    }

    const fetchParentCategories = async () => {
        return await $fetch(`${apiBase}/admin/categories/parents`, {
            headers: authHeaders.value,
        })
    }

    const fetchCategory = async (id: number) => {
        return await $fetch(`${apiBase}/admin/categories/${id}`, {
            headers: authHeaders.value,
        })
    }

    const createCategory = async (formData: FormData) => {
        return await $fetch(`${apiBase}/admin/categories`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token.value}` },
            body: formData,
        })
    }

    const updateCategory = async (id: number, formData: FormData) => {
        return await $fetch(`${apiBase}/admin/categories/${id}`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token.value}` },
            body: formData,
        })
    }

    const deleteCategory = async (id: number) => {
        return await $fetch(`${apiBase}/admin/categories/${id}`, {
            method: 'DELETE',
            headers: authHeaders.value,
        })
    }

    const toggleCategory = async (id: number) => {
        return await $fetch(`${apiBase}/admin/categories/${id}/toggle`, {
            method: 'PATCH',
            headers: authHeaders.value,
        })
    }

    // ── Users ─────────────────────────────────────────────────
    const fetchUsers = async (params: {
        search?: string
        role?: string
        page?: number
        per_page?: number
    } = {}) => {
        return await $fetch(`${apiBase}/admin/users`, {
            headers: authHeaders.value,
            query: params,
        })
    }

    const fetchUserStats = async () => {
        return await $fetch(`${apiBase}/admin/users/stats`, {
            headers: authHeaders.value,
        })
    }

    const fetchUser = async (id: number) => {
        return await $fetch(`${apiBase}/admin/users/${id}`, {
            headers: authHeaders.value,
        })
    }

    const deleteUser = async (id: number) => {
        return await $fetch(`${apiBase}/admin/users/${id}`, {
            method: 'DELETE',
            headers: authHeaders.value,
        })
    }

    return {
        fetchDashboard,
        fetchOrdersChart,
        initAuth,
        // categories
        fetchCategories,
        fetchCategoryStats,
        fetchParentCategories,
        fetchCategory,
        createCategory,
        updateCategory,
        deleteCategory,
        toggleCategory,
        // users
        fetchUsers,
        fetchUserStats,
        fetchUser,
        deleteUser,
    }
}