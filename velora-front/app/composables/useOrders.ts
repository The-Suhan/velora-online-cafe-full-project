import { nextTick } from 'vue'

export type OrderStatus = 'pending' | 'preparing' | 'ready' | 'delivered' | 'cancelled'

export interface OrderItem {
    id: number
    quantity: number
    price: number
    subtotal: number
    product: { id: number; name: string; image_url: string | null } | null
}

export interface Order {
    id: number
    order_number: string
    status: OrderStatus
    allowed_next: OrderStatus[]
    total_price: number
    note: string | null
    items_count: number
    items?: OrderItem[]
    customer: { id: number; name: string; email: string } | null
    delivery_type: 'pickup' | 'delivery'
    address: string | null
    phone: string | null
    created_at: string
    created_at_time: string
    updated_at: string
}

export interface Stats {
    total: number
    growth: number
    pending: number
    preparing: number
    ready: number
    delivered: number
    cancelled: number
}

export interface Pagination {
    current_page: number
    last_page: number
    total: number
    from: number
    to: number
}

export const STATUS_META: Record<
    OrderStatus | 'all',
    { label: string; color: string; bg: string; dot: string; badge: string; badgeBg: string }
> = {
    all: {
        label: 'admin.orders.allOrders',
        color: '#C8A96E',
        bg: '#fdf3e4',
        dot: '#C8A96E',
        badge: 'admin.orders.allOrders',
        badgeBg: '#fdf3e4'
    },

    pending: {
        label: 'admin.statuses.pending',
        color: '#d97706',
        bg: '#fef9e7',
        dot: '#d97706',
        badge: 'admin.statuses.pending',
        badgeBg: '#fef3c7'
    },

    preparing: {
        label: 'admin.statuses.preparing',
        color: '#3b82f6',
        bg: '#eff6ff',
        dot: '#3b82f6',
        badge: 'admin.statuses.preparing',
        badgeBg: '#dbeafe'
    },

    ready: {
        label: 'admin.statuses.ready',
        color: '#16a34a',
        bg: '#f0fdf4',
        dot: '#16a34a',
        badge: 'admin.statuses.ready',
        badgeBg: '#dcfce7'
    },

    delivered: {
        label: 'admin.statuses.delivered',
        color: '#4A6741',
        bg: '#e8f0e4',
        dot: '#4A6741',
        badge: 'admin.statuses.delivered',
        badgeBg: '#e8f0e4'
    },

    cancelled: {
        label: 'admin.statuses.cancelled',
        color: '#dc2626',
        bg: '#fef2f2',
        dot: '#dc2626',
        badge: 'admin.statuses.cancelled',
        badgeBg: '#fee2e2'
    },
}

export const STATUS_ORDER: OrderStatus[] = ['pending', 'preparing', 'ready', 'delivered']

export function useOrders(fixedStatus?: OrderStatus) {
    const config = useRuntimeConfig()
    const API = config.public.apiBase as string
    const BACKEND_BASE = API.replace(/\/api\/?$/, '')

    const { token } = useAuth()
    const authHeaders = computed(() => ({ Authorization: `Bearer ${token.value}` }))

    // ── State ──────────────────────────────────────────────────
    const orders = ref<Order[]>([])
    const stats = ref<Stats>({ total: 0, growth: 0, pending: 0, preparing: 0, ready: 0, delivered: 0, cancelled: 0 })
    const loading = ref(true)
    const detailLoading = ref(false)
    const search = ref('')
    const filterStatus = ref<OrderStatus | ''>(fixedStatus ?? '')
    const filterFrom = ref('')
    const filterTo = ref('')
    const showFilter = ref(false)
    const currentPage = ref(1)
    const pagination = ref<Pagination>({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

    // Modal state
    const activeModal = ref<'preview' | 'edit' | 'cancel' | 'delete' | 'receipt' | null>(null)
    const selectedOrder = ref<Order | null>(null)
    const openDrop = ref<number | null>(null)
    const dropPos = ref({ top: 0, right: 0 })
    const submitting = ref(false)
    const pdfLoading = ref(false)

    const editForm = reactive({ status: '' as OrderStatus | '', note: '' })
    const editErrors = reactive({ status: '' })
    const toast = reactive({ visible: false, message: '', type: 'success' as 'success' | 'error' })

    let searchTimeout: ReturnType<typeof setTimeout>

    // ── Computed ───────────────────────────────────────────────
    const visiblePages = computed(() => {
        const cur = pagination.value.current_page
        const last = pagination.value.last_page
        if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
        const pages: (number | string)[] = [1]
        if (cur > 3) pages.push('...')
        for (let i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) pages.push(i)
        if (cur < last - 2) pages.push('...')
        pages.push(last)
        return pages
    })

    // ── API ────────────────────────────────────────────────────
    async function loadOrders(page = 1) {
        loading.value = true
        try {
            const params = new URLSearchParams({ page: String(page), per_page: '10' })
            if (search.value) params.append('search', search.value)
            // fixed status overrides filter
            const statusToSend = fixedStatus ?? filterStatus.value
            if (statusToSend) params.append('status', statusToSend)
            if (filterFrom.value) params.append('from', filterFrom.value)
            if (filterTo.value) params.append('to', filterTo.value)

            const data = await $fetch<any>(`${API}/admin/orders?${params}`, { headers: authHeaders.value })
            orders.value = data.data
            pagination.value = {
                current_page: data.current_page,
                last_page: data.last_page,
                total: data.total,
                from: data.from ?? 0,
                to: data.to ?? 0,
            }
        } catch {
            showToast('Failed to load orders', 'error')
        } finally {
            loading.value = false
        }
    }

    async function loadStats() {
        try {
            stats.value = await $fetch<Stats>(`${API}/admin/orders/stats`, { headers: authHeaders.value })
        } catch { }
    }

    async function loadOrderDetail(id: number) {
        detailLoading.value = true
        try {
            const data = await $fetch<Order>(`${API}/admin/orders/${id}`, { headers: authHeaders.value })
            selectedOrder.value = { ...selectedOrder.value, ...data } as Order
        } catch { }
        finally { detailLoading.value = false }
    }

    // ── Search / Filter ────────────────────────────────────────
    function onSearch() {
        clearTimeout(searchTimeout)
        searchTimeout = setTimeout(() => { currentPage.value = 1; loadOrders(1) }, 400)
    }

    function setStatusFilter(status: OrderStatus | '') {
        if (fixedStatus) return // fixed pages don't allow status changes
        filterStatus.value = status
        currentPage.value = 1
        loadOrders(1)
    }

    function applyFilter() {
        showFilter.value = false
        currentPage.value = 1
        loadOrders(1)
    }

    function resetFilter() {
        if (!fixedStatus) filterStatus.value = ''
        filterFrom.value = ''
        filterTo.value = ''
        showFilter.value = false
        loadOrders(1)
    }

    function goToPage(page: number) {
        currentPage.value = page
        loadOrders(page)
    }

    // ── Dropdown ───────────────────────────────────────────────
    function toggleDropdown(id: number, event: MouseEvent) {
        if (openDrop.value === id) { openDrop.value = null; return }
        const rect = (event.currentTarget as HTMLElement).getBoundingClientRect()
        dropPos.value = { top: rect.bottom + 6, right: window.innerWidth - rect.right }
        openDrop.value = id
        nextTick(() => {
            const h = () => { openDrop.value = null; document.removeEventListener('click', h) }
            document.addEventListener('click', h)
        })
    }

    // ── Modal openers ──────────────────────────────────────────
    async function openPreviewModal(order: Order) {
        selectedOrder.value = order
        activeModal.value = 'preview'
        openDrop.value = null
        await loadOrderDetail(order.id)
    }

    function openEditModal(order: Order) {
        selectedOrder.value = order
        editForm.status = ''
        editForm.note = order.note ?? ''
        editErrors.status = ''
        activeModal.value = 'edit'
        openDrop.value = null
    }

    async function openReceiptModal(order: Order) {
        selectedOrder.value = order
        activeModal.value = 'receipt'
        openDrop.value = null
        if (!order.items) await loadOrderDetail(order.id)
    }

    function openCancelModal(order: Order) {
        selectedOrder.value = order
        activeModal.value = 'cancel'
        openDrop.value = null
    }

    function openDeleteModal(order: Order) {
        selectedOrder.value = order
        activeModal.value = 'delete'
        openDrop.value = null
    }

    function closeModal() {
        activeModal.value = null
        selectedOrder.value = null
    }

    // ── Submits ────────────────────────────────────────────────
    async function submitEdit() {
        editErrors.status = ''
        if (!editForm.status) { editErrors.status = 'Please select a new status'; return }
        submitting.value = true
        try {
            await $fetch(`${API}/admin/orders/${selectedOrder.value!.id}/status`, {
                method: 'PATCH',
                headers: { ...authHeaders.value, 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: editForm.status, note: editForm.note || undefined }),
            })
            showToast('Order status updated', 'success')
            closeModal()
            await Promise.all([loadOrders(currentPage.value), loadStats()])
        } catch (err: any) {
            showToast(err?.data?.message || 'Failed to update', 'error')
        } finally { submitting.value = false }
    }

    async function submitCancel() {
        submitting.value = true
        try {
            await $fetch(`${API}/admin/orders/${selectedOrder.value!.id}/cancel`, {
                method: 'PATCH',
                headers: authHeaders.value,
            })
            showToast('Order cancelled', 'success')
            closeModal()
            await Promise.all([loadOrders(currentPage.value), loadStats()])
        } catch (err: any) {
            showToast(err?.data?.message || 'Failed to cancel', 'error')
        } finally { submitting.value = false }
    }

    async function submitDelete() {
        submitting.value = true
        try {
            await $fetch(`${API}/admin/orders/${selectedOrder.value!.id}`, {
                method: 'DELETE',
                headers: authHeaders.value,
            })
            showToast('Order deleted', 'success')
            closeModal()
            if (orders.value.length === 1 && currentPage.value > 1) currentPage.value--
            await Promise.all([loadOrders(currentPage.value), loadStats()])
        } catch (err: any) {
            showToast(err?.data?.message || 'Failed to delete', 'error')
        } finally { submitting.value = false }
    }

    // ── PDF Export ─────────────────────────────────────────────
    async function downloadPdf() {
        if (!selectedOrder.value) return
        pdfLoading.value = true
        try {
            const order = selectedOrder.value
            const items = order.items ?? []

            // Build receipt HTML string
            const itemsHtml = items.map(item => `
        <tr>
          <td style="padding:6px 0;font-size:12px;color:#2C1810;">${item.product?.name ?? 'Item'}</td>
          <td style="padding:6px 0;font-size:12px;color:#8a7060;text-align:center;">×${item.quantity}</td>
          <td style="padding:6px 0;font-size:12px;font-weight:700;color:#2C1810;text-align:right;">$${item.subtotal.toFixed(2)}</td>
        </tr>
      `).join('')

            const receiptHtml = `
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8"/>
          <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family:'Courier New',monospace; background:#fff; }
            .receipt { max-width:320px; margin:0 auto; padding:32px 24px; }
            .header { text-align:center; margin-bottom:16px; }
            .logo { font-size:28px; margin-bottom:8px; }
            .brand { font-size:16px; font-weight:800; letter-spacing:5px; color:#2C1810; }
            .tagline { font-size:10px; color:#8a7060; font-style:italic; margin-top:4px; }
            .divider { text-align:center; color:#d0c4b0; font-size:9px; margin:12px 0; letter-spacing:3px; }
            .info-table { width:100%; border-collapse:collapse; margin-bottom:4px; }
            .info-table td { padding:3px 0; font-size:11px; color:#2C1810; }
            .info-table td:first-child { color:#8a7060; width:70px; }
            .info-table td:last-child { text-align:right; font-weight:500; }
            .items-table { width:100%; border-collapse:collapse; }
            .items-header { font-size:9px; color:#8a7060; text-transform:uppercase; letter-spacing:1px; border-bottom:1px dashed #e5e7eb; padding-bottom:4px; margin-bottom:4px; }
            .items-header td { padding:0 0 6px; }
            .items-header td:last-child { text-align:right; }
            .items-header td:nth-child(2) { text-align:center; }
            .total-row { display:flex; justify-content:space-between; font-weight:800; font-size:14px; color:#2C1810; padding:8px 0; border-top:2px solid #2C1810; margin-top:8px; letter-spacing:1px; }
            .note-box { background:#fafaf8; border-left:3px solid #C8A96E; padding:8px 10px; margin-top:8px; border-radius:2px; }
            .note-label { font-size:8px; color:#8a7060; text-transform:uppercase; letter-spacing:1px; margin-bottom:3px; }
            .note-text { font-size:10px; color:#6b7280; font-style:italic; line-height:1.4; }
            .footer { text-align:center; margin-top:4px; }
            .quote { font-style:italic; font-size:10px; color:#8a7060; line-height:1.5; margin-bottom:8px; }
            .thanks { font-size:11px; font-weight:700; color:#2C1810; margin-bottom:4px; }
            .website { font-size:8px; color:#b0967a; text-transform:uppercase; letter-spacing:3px; }
            .status-text { font-weight:700; text-transform:uppercase; letter-spacing:1px; }
          </style>
        </head>
        <body>
          <div class="receipt">
            <div class="header">
              <div class="logo">☕</div>
              <div class="brand">VELORA CAFÉ</div>
              <div class="tagline">Every sip tells a story.</div>
            </div>
            <div class="divider">· · · · · · · · · · · · · · · · · · · ·</div>
         <table class="info-table">
            <tr><td>Order</td><td>${order.order_number}</td></tr>
            <tr><td>Date</td><td>${order.created_at} ${order.created_at_time}</td></tr>
            <tr><td>Customer</td><td>${order.customer?.name ?? '—'}</td></tr>
            <tr><td>Status</td><td><span class="status-text">${order.status}</span></td></tr>
            <tr><td>Type</td><td>${order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup'}</td></tr>
            ${order.delivery_type === 'delivery' && order.address ? `<tr><td>Address</td><td style="text-align:right;">${order.address}</td></tr>` : ''}
            ${order.delivery_type === 'delivery' && order.phone ? `<tr><td>Phone</td><td>${order.phone}</td></tr>` : ''}
        </table>
            <div class="divider">· · · · · · · · · · · · · · · · · · · ·</div>
            <table class="items-table">
              <tr class="items-header">
                <td>Item</td>
                <td style="text-align:center;">Qty</td>
                <td style="text-align:right;">Price</td>    
              </tr>
              ${itemsHtml}
            </table>
            <div class="total-row">
              <span>TOTAL</span>
              <span>$${order.total_price.toFixed(2)} $</span>
            </div>
            ${order.note ? `
            <div class="note-box">
              <div class="note-label">Note</div>
              <div class="note-text">${order.note}</div>
            </div>` : ''}
            <div class="divider">· · · · · · · · · · · · · · · · · · · ·</div>
            <div class="footer">
              <div class="quote">"Good coffee is a pleasure. Good friends are a treasure."</div>
              <div class="thanks">Thank you for choosing Velora Café!</div>
            </div>
          </div>
        </body>
        </html>
      `

            // Open print window — works without any npm package, produces perfect PDF via browser print
            const win = window.open('', '_blank', 'width=400,height=700')
            if (!win) { showToast('Please allow popups for PDF export', 'error'); return }
            win.document.write(receiptHtml)
            win.document.close()
            // Give fonts a moment to load, then trigger print
            setTimeout(() => {
                win.focus()
                win.print()
                // Close after print dialog closes (user saves as PDF)
                win.onafterprint = () => win.close()
            }, 400)

        } catch (e) {
            showToast('Failed to generate PDF', 'error')
        } finally {
            pdfLoading.value = false
        }
    }

    // ── Helpers ────────────────────────────────────────────────
    const capitalize = (s: string) => s ? s.charAt(0).toUpperCase() + s.slice(1) : ''

    const initials = (name?: string | null) => {
        if (!name) return '?'
        return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
    }

    const resolveUrl = (url: string | null | undefined): string | null => {
        if (!url) return null
        if (url.startsWith('http')) return url
        return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
    }

    const canCancel = (status: OrderStatus) => !['delivered', 'cancelled'].includes(status)
    const canDelete = (status: OrderStatus) => ['delivered', 'cancelled'].includes(status)

    const isStepDone = (stepKey: OrderStatus, currentStatus: OrderStatus) => {
        if (currentStatus === 'cancelled') return false
        return STATUS_ORDER.indexOf(stepKey) <= STATUS_ORDER.indexOf(currentStatus)
    }

    function showToast(message: string, type: 'success' | 'error' = 'success') {
        toast.message = message
        toast.type = type
        toast.visible = true
        setTimeout(() => { toast.visible = false }, 3500)
    }

    function handleOutsideClick() {
        openDrop.value = null
        showFilter.value = false
    }

    return {
        // state
        orders, stats, loading, detailLoading, search, filterStatus, filterFrom, filterTo,
        showFilter, currentPage, pagination, activeModal, selectedOrder, openDrop, dropPos,
        submitting, pdfLoading, editForm, editErrors, toast,
        // computed
        visiblePages,
        // methods
        loadOrders, loadStats, loadOrderDetail,
        onSearch, setStatusFilter, applyFilter, resetFilter, goToPage,
        toggleDropdown,
        openPreviewModal, openEditModal, openReceiptModal, openCancelModal, openDeleteModal, closeModal,
        submitEdit, submitCancel, submitDelete, downloadPdf,
        capitalize, initials, resolveUrl, canCancel, canDelete, isStepDone, showToast,
        handleOutsideClick,
    }
}