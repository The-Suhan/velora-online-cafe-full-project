/** Customer-facing product search/filter — wraps GET /products (see useProducts.ts for the admin equivalent). */
export const useProductSearch = () => {
    const api = useApi()

    const fetchProducts = async (params: {
        search?: string
        category_id?: number | string
        min_price?: number | string
        max_price?: number | string
        on_discount?: string
        sort?: string
        page?: number
        per_page?: number
    } = {}) => {
        return await api('/products', { params })
    }

    return { fetchProducts }
}
