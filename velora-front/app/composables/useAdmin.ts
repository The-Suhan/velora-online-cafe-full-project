export const useAdmin = () => {
    const config = useRuntimeConfig()
    const { token, fetchMe, user } = useAuth()

    const apiBase = config.public.apiBase

    const authHeaders = computed(() => ({
        Authorization: `Bearer ${token.value}`,
    }))

    const fetchDashboard = async () => {
        console.log('Token:', token.value)
        return await $fetch(`${apiBase}/admin/dashboard`, {
            headers: authHeaders.value,
        })
    }

    const fetchOrdersChart = async (period: 'daily' | 'weekly' | 'monthly' | 'yearly') => {
        return await $fetch(`${apiBase}/admin/orders/chart`, {
            headers: authHeaders.value,
            query: { period },
        })
    }

    const initAuth = async () => {
        if (token.value && !user.value) {
            await fetchMe()
        }
    }
    const fetchCategories = async (params: { per_page?: number; page?: number; search?: string } = {}) => {
        return await $fetch(`${apiBase}/admin/categories`, {
            headers: authHeaders.value,
            query: params,
        })
    }

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
        fetchUsers,
        fetchUserStats,
        fetchUser,
        deleteUser,
        fetchCategories,
    }
}