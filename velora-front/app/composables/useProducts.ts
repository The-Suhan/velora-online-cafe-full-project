export const useProducts = () => {
    const config = useRuntimeConfig()
    const { token } = useAuth()
    const apiBase = config.public.apiBase

    const authHeaders = computed(() => ({
        Authorization: `Bearer ${token.value}`,
    }))

    const fetchProducts = async (params: {
        search?: string
        category_id?: number | string
        is_active?: string
        page?: number
        per_page?: number
    } = {}) => {
        return await $fetch(`${apiBase}/admin/products`, {
            headers: authHeaders.value,
            query: params,
        })
    }

    const fetchProductStats = async () => {
        return await $fetch(`${apiBase}/admin/products/stats`, {
            headers: authHeaders.value,
        })
    }

    const fetchProduct = async (id: number) => {
        return await $fetch(`${apiBase}/admin/products/${id}`, {
            headers: authHeaders.value,
        })
    }

    const createProduct = async (formData: FormData) => {
        return await $fetch(`${apiBase}/admin/products`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token.value}` },
            body: formData,
        })
    }

    const updateProduct = async (id: number, formData: FormData) => {
        return await $fetch(`${apiBase}/admin/products/${id}`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token.value}` },
            body: formData,
        })
    }

    const deleteProduct = async (id: number) => {
        return await $fetch(`${apiBase}/admin/products/${id}`, {
            method: 'DELETE',
            headers: authHeaders.value,
        })
    }

    const toggleProduct = async (id: number) => {
        return await $fetch(`${apiBase}/admin/products/${id}/toggle`, {
            method: 'PATCH',
            headers: authHeaders.value,
        })
    }

    return {
        fetchProducts,
        fetchProductStats,
        fetchProduct,
        createProduct,
        updateProduct,
        deleteProduct,
        toggleProduct,
    }
}