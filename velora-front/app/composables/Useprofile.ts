// composables/useProfile.ts

export interface Order {
    id: number
    order_no: string
    status: 'pending' | 'preparing' | 'ready' | 'delivered' | 'cancelled'
    total_price: number
    delivery_type: 'pickup' | 'delivery'
    note: string | null
    created_at: string
    address?: string
    phone?: string
    items: OrderItem[]
}

export interface OrderItem {
    product_id: number
    product_name: string
    image_url: string | null
    quantity: number
    price: number
    subtotal: number
}

export interface UserProfile {
    id: number
    name: string
    email: string
    role: string
    is_verified: boolean
}

export interface PaginatedResponse<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

export const useProfile = () => {
    const config = useRuntimeConfig()
    const { token } = useAuth()

    const apiBase = config.public.apiBase as string

    function headers(): Record<string, string> {
        return {
            Authorization: `Bearer ${token.value ?? ''}`,
            Accept: 'application/json',
        }
    }

    // GET /api/me
    const fetchMe = (): Promise<UserProfile> =>
        $fetch<UserProfile>(`${apiBase}/me`, { headers: headers() })

    // PATCH /api/me
    const updateMe = (payload: {
        name?: string
        current_password?: string
        password?: string
        password_confirmation?: string
    }): Promise<{ message: string; user: UserProfile }> =>
        $fetch(`${apiBase}/me`, {
            method: 'PATCH',
            headers: headers(),
            body: payload,
        })

    // GET /api/orders
    const fetchMyOrders = (page = 1, perPage = 8): Promise<PaginatedResponse<Order>> =>
        $fetch<PaginatedResponse<Order>>(`${apiBase}/orders`, {
            headers: headers(),
            query: { page, per_page: perPage },
        })

    // GET /api/orders/{id}
    const fetchOrder = (id: number): Promise<Order> =>
        $fetch<Order>(`${apiBase}/orders/${id}`, { headers: headers() })

    // PATCH /api/orders/{id}/cancel
    const cancelOrder = (id: number): Promise<{ message: string; status: string }> =>
        $fetch(`${apiBase}/orders/${id}/cancel`, {
            method: 'PATCH',
            headers: headers(),
        })

    const fetchMyFavorites = (): Promise<any[]> =>
        $fetch<any[]>(`${apiBase}/me/favorites`, { headers: headers() })


    const fetchMyFeedback = (): Promise<any[]> =>
        $fetch<any[]>(`${apiBase}/me/feedback`, { headers: headers() })

    const submitFeedback = (payload: { type: string; subject: string; message: string }) =>
        $fetch(`${apiBase}/me/feedback`, { method: 'POST', headers: headers(), body: payload })


    return { fetchMe, updateMe, fetchMyOrders, fetchOrder, cancelOrder, fetchMyFavorites, fetchMyFeedback, submitFeedback }
}