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
    /** sadece detay yanıtında (GET /api/orders/{id}) */
    status_history?: OrderStatusHistoryEntry[]
    updated_at_iso?: string
}

export type OrderStatus = Order['status']

export interface OrderStatusHistoryEntry {
    to_status: OrderStatus
    at: string
}

/** GET /api/orders/updates yanıtı */
export interface OrderUpdatesResponse {
    server_time: string
    active_count: number
    orders: {
        id: number
        order_no: string
        status: OrderStatus
        updated_at: string
    }[]
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

    // GET /api/orders/updates — canlı takip için hafif polling uç noktası.
    // `signal` sekme gizlendiğinde / bileşen unmount olduğunda isteği iptal eder.
    const fetchOrderUpdates = (since?: string | null, signal?: AbortSignal): Promise<OrderUpdatesResponse> =>
        $fetch<OrderUpdatesResponse>(`${apiBase}/orders/updates`, {
            headers: headers(),
            query: since ? { since } : {},
            signal,
        })

    const fetchMyFavorites = (): Promise<any[]> =>
        $fetch<any[]>(`${apiBase}/me/favorites`, { headers: headers() })


    const fetchMyFeedback = (): Promise<any[]> =>
        $fetch<any[]>(`${apiBase}/me/feedback`, { headers: headers() })

    const submitFeedback = (payload: { type: string; subject: string; message: string }) =>
        $fetch(`${apiBase}/me/feedback`, { method: 'POST', headers: headers(), body: payload })


    return { fetchMe, updateMe, fetchMyOrders, fetchOrder, cancelOrder, fetchOrderUpdates, fetchMyFavorites, fetchMyFeedback, submitFeedback }
}