export interface FeedbackUser {
    id: number
    name: string
    email: string
    avatar: string | null
}

export interface FeedbackItem {
    id: number
    type: 'complaint' | 'request' | 'question'
    subject: string
    message: string
    is_done: boolean
    status: 'pending' | 'resolved'
    user: FeedbackUser | null
    created_at: string
    resolved_by?: { id: number; name: string } | null
    resolved_at?: string | null
}

export interface FeedbackStats {
    total: number
    pending: number
    resolved: number
    resolution_rate: number
    growth: number
}

export interface PaginationMeta {
    current_page: number
    last_page: number
    total: number
    from: number
    to: number
}

export interface FeedbackListResponse {
    data: FeedbackItem[]
    current_page: number
    last_page: number
    total: number
    from: number
    to: number
}

export const useFeedback = () => {
    const config = useRuntimeConfig()
    const API = config.public.apiBase
    const { token } = useAuth()

    const authHeaders = computed(() => ({
        Authorization: `Bearer ${token.value}`,
    }))

    // ── Stats ──────────────────────────────────────────────────
    const fetchStats = async (): Promise<FeedbackStats> => {
        return await $fetch<FeedbackStats>(`${API}/admin/feedback/stats`, {
            headers: authHeaders.value,
        })
    }

    // ── List (index) ───────────────────────────────────────────
    const fetchFeedback = async (params: {
        page?: number
        per_page?: number
        search?: string
        type?: string
        status?: string
    }): Promise<FeedbackListResponse> => {
        const query = new URLSearchParams()
        if (params.page) query.set('page', String(params.page))
        if (params.per_page) query.set('per_page', String(params.per_page))
        if (params.search) query.set('search', params.search)
        if (params.type) query.set('type', params.type)
        if (params.status) query.set('status', params.status)

        return await $fetch<FeedbackListResponse>(`${API}/admin/feedback?${query}`, {
            headers: authHeaders.value,
        })
    }

    // ── Pending list ───────────────────────────────────────────
    const fetchPending = async (params: {
        page?: number
        per_page?: number
        search?: string
        type?: string
    }): Promise<FeedbackListResponse> => {
        const query = new URLSearchParams()
        if (params.page) query.set('page', String(params.page))
        if (params.per_page) query.set('per_page', String(params.per_page))
        if (params.search) query.set('search', params.search)
        if (params.type) query.set('type', params.type)

        return await $fetch<FeedbackListResponse>(`${API}/admin/feedback/pending?${query}`, {
            headers: authHeaders.value,
        })
    }

    // ── Resolved list ──────────────────────────────────────────
    const fetchResolved = async (params: {
        page?: number
        per_page?: number
        search?: string
        type?: string
    }): Promise<FeedbackListResponse> => {
        const query = new URLSearchParams()
        if (params.page) query.set('page', String(params.page))
        if (params.per_page) query.set('per_page', String(params.per_page))
        if (params.search) query.set('search', params.search)
        if (params.type) query.set('type', params.type)

        return await $fetch<FeedbackListResponse>(`${API}/admin/feedback/resolved?${query}`, {
            headers: authHeaders.value,
        })
    }

    // ── Show single ────────────────────────────────────────────
    const fetchOne = async (id: number): Promise<FeedbackItem> => {
        return await $fetch<FeedbackItem>(`${API}/admin/feedback/${id}`, {
            headers: authHeaders.value,
        })
    }

    // ── Resolve ────────────────────────────────────────────────
    const resolve = async (id: number) => {
        return await $fetch(`${API}/admin/feedback/${id}/resolve`, {
            method: 'PATCH',
            headers: authHeaders.value,
        })
    }

    // ── Unresolve ──────────────────────────────────────────────
    const unresolve = async (id: number) => {
        return await $fetch(`${API}/admin/feedback/${id}/unresolve`, {
            method: 'PATCH',
            headers: authHeaders.value,
        })
    }

    // ── Delete ─────────────────────────────────────────────────
    const destroy = async (id: number) => {
        return await $fetch(`${API}/admin/feedback/${id}`, {
            method: 'DELETE',
            headers: authHeaders.value,
        })
    }

    // ── Helpers ────────────────────────────────────────────────
    const typeLabel = (type: string) => {
        const map: Record<string, string> = {
            complaint: 'Complaint',
            request: 'Request',
            question: 'Question',
        }
        return map[type] ?? type
    }

    const typeColor = (type: string): string => {
        const map: Record<string, string> = {
            complaint: 'type-complaint',
            request: 'type-request',
            question: 'type-question',
        }
        return map[type] ?? ''
    }

    return {
        fetchStats,
        fetchFeedback,
        fetchPending,
        fetchResolved,
        fetchOne,
        resolve,
        unresolve,
        destroy,
        typeLabel,
        typeColor,
    }
}