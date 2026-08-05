// composables/useAdminOrderPulse.ts
//
// Admin tarafı canlı sipariş nabzı: yeni sipariş gelince ses + toast, ve
// kenar çubuğu/tabbar'daki bekleyen sipariş rozetini besler.
//
// Rozet altyapısı zaten hazırdı (layouts/admin.vue → AdminSidebar/AdminTabbar
// `admin:pendingOrders` state'ini render ediyor) ama hiçbir yer değer yazmıyordu.
//
// Döngü kuralları useOrderTracking ile aynı: zincirlenmiş setTimeout, sekme
// gizliyken sıfır istek, hatada backoff, unmount'ta abort.

import type { OrderStatus } from './Useprofile'

export interface AdminOrderUpdatesResponse {
    server_time: string
    new_orders: number
    changed: { id: number; status: OrderStatus; is_new: boolean }[]
    counts: { pending: number; preparing: number; ready: number }
}

const TICK_MS = 15_000
const MAX_BACKOFF_MS = 120_000

let timer: ReturnType<typeof setTimeout> | null = null
let controller: AbortController | null = null
let subscribers = 0
let failures = 0
let stopped = false

export const useAdminOrderPulse = () => {
    const config = useRuntimeConfig()
    const API = config.public.apiBase as string

    const { token } = useAuth()
    const { push } = useToast()
    const { play, armUnlock } = useNotifySound()
    const { notify, hydrate } = useBrowserNotify()

    const cursor = useState<string | null>('pulse:cursor', () => null)
    const running = useState('pulse:running', () => false)
    // Sipariş listeleri bunu izleyip kendini tazeleyebilir.
    const changedIds = useState<number[]>('pulse:changedIds', () => [])
    const counts = useState('pulse:counts', () => ({ pending: 0, preparing: 0, ready: 0 }))

    // Rozetler layouts/admin.vue içinde bu state'ten okunuyor.
    const pendingOrders = useState('admin:pendingOrders', () => 0)

    const tr = (key: string, params?: Record<string, unknown>) =>
        (useNuxtApp().$i18n as any).t(key, params ?? {}) as string

    function nextDelay(): number {
        return failures > 0 ? Math.min(TICK_MS * 2 ** failures, MAX_BACKOFF_MS) : TICK_MS
    }

    function schedule() {
        if (stopped) return
        if (timer) clearTimeout(timer)
        timer = setTimeout(poll, nextDelay())
    }

    async function poll() {
        if (stopped) return

        if (!token.value || (import.meta.client && document.visibilityState === 'hidden')) {
            schedule()
            return
        }

        controller = new AbortController()

        try {
            const res = await $fetch<AdminOrderUpdatesResponse>(`${API}/admin/orders/updates`, {
                headers: { Authorization: `Bearer ${token.value}`, Accept: 'application/json' },
                query: cursor.value ? { since: cursor.value } : {},
                signal: controller.signal,
            })

            failures = 0
            counts.value = res.counts
            pendingOrders.value = res.counts.pending
            changedIds.value = res.changed.map(c => c.id)

            if (res.new_orders > 0) {
                const message = res.new_orders === 1
                    ? tr('admin.orders.live.newOrderOne')
                    : tr('admin.orders.live.newOrders', { n: res.new_orders })

                push(message, 'info')
                play('new-order')
                notify(tr('admin.orders.live.title'), message)
            }

            cursor.value = res.server_time
        } catch (err: any) {
            if (err?.name === 'AbortError') return

            if (err?.status === 401 || err?.response?.status === 401) {
                stop()
                return
            }

            // cursor korunur — kaçırılan siparişler bir sonraki başarılı istekte gelir.
            failures++
        }

        schedule()
    }

    function onVisibility() {
        if (document.visibilityState === 'visible') {
            if (timer) clearTimeout(timer)
            failures = 0
            poll()
        } else {
            if (timer) clearTimeout(timer)
            timer = null
            controller?.abort()
        }
    }

    function start() {
        if (!import.meta.client || running.value || !token.value) return
        stopped = false
        running.value = true
        poll()
    }

    function stop() {
        if (timer) clearTimeout(timer)
        timer = null
        controller?.abort()
        controller = null
        stopped = true
        running.value = false
    }

    function useTracking() {
        onMounted(() => {
            subscribers++
            hydrate()
            armUnlock()
            start()
            document.addEventListener('visibilitychange', onVisibility)
        })

        onUnmounted(() => {
            subscribers--
            document.removeEventListener('visibilitychange', onVisibility)
            if (subscribers <= 0) {
                subscribers = 0
                stop()
            }
        })
    }

    return { counts, changedIds, running, start, stop, useTracking }
}
