// composables/useOrderTracking.ts
//
// Müşterinin siparişlerinin canlı takibi.
//
// Websocket yerine adaptif polling: bu backend'de queue worker / broadcast
// altyapısı yok ve statefulApi() + database session yüzünden uzun ömürlü SSE
// stream'leri PHP-FPM worker'larını kilitlerdi. Polling'in bedeli gecikme,
// ve o bedel şu korumalarla sınırlanıyor:
//
//   • zincirlenmiş setTimeout (setInterval değil) → yavaş yanıtta istek yığılmaz
//   • bileşenler arası refcount'lu TEK döngü
//   • sekme gizliyken sıfır istek
//   • aktif sipariş yokken 10 sn yerine 60 sn
//   • hatada 120 sn'ye kadar exponential backoff
//   • 401'de kalıcı duruş, unmount'ta AbortController
//
// Gerçek zamanlıya geçilirse yalnızca poll() değişir; dışa açılan arayüz aynı kalır.

import type { OrderStatus } from './Useprofile'

export interface TrackedOrder {
    id: number
    order_no: string
    status: OrderStatus
}

const FAST_MS = 10_000
const SLOW_MS = 60_000
const MAX_BACKOFF_MS = 120_000

// Modül düzeyi: döngünün kendisi tekil. useState reaktif durumu tutar,
// bunlar ise reaktif olmayan zamanlayıcı/abonelik defterini tutar.
let timer: ReturnType<typeof setTimeout> | null = null
let controller: AbortController | null = null
let subscribers = 0
let failures = 0
let stopped = false

export const useOrderTracking = () => {
    const { token } = useAuth()
    const { fetchOrderUpdates } = useProfile()
    const { push } = useToast()
    const { play, armUnlock } = useNotifySound()
    const { notify, hydrate } = useBrowserNotify()

    const statuses = useState<Record<number, OrderStatus>>('track:statuses', () => ({}))
    const cursor = useState<string | null>('track:cursor', () => null)
    const activeCount = useState('track:active', () => 0)
    const lastChange = useState<TrackedOrder | null>('track:lastChange', () => null)
    const running = useState('track:running', () => false)

    // useI18n() yalnızca setup içinde çağrılabilir; poll() ise bileşen ömrünü
    // aşabilir, o yüzden global $i18n üzerinden çeviri yapılır.
    const tr = (key: string, params?: Record<string, unknown>) =>
        (useNuxtApp().$i18n as any).t(key, params ?? {}) as string

    function nextDelay(): number {
        if (failures > 0) return Math.min(FAST_MS * 2 ** failures, MAX_BACKOFF_MS)
        return activeCount.value > 0 ? FAST_MS : SLOW_MS
    }

    function schedule() {
        if (stopped) return
        if (timer) clearTimeout(timer)
        timer = setTimeout(poll, nextDelay())
    }

    function announce(order: TrackedOrder) {
        const message = tr(`profile.tracking.notify.${order.status}`, { order: order.order_no })

        push(message, order.status === 'cancelled' ? 'error' : 'success')
        play('status-change')
        notify(tr('profile.tracking.notify.title'), message)

        lastChange.value = order
    }

    async function poll() {
        if (stopped) return

        // Token yoksa veya sekme arkadaysa istek atma — sadece yeniden zamanla.
        if (!token.value || (import.meta.client && document.visibilityState === 'hidden')) {
            schedule()
            return
        }

        controller = new AbortController()

        try {
            const isBaseline = cursor.value === null
            const res = await fetchOrderUpdates(cursor.value, controller.signal)

            failures = 0
            activeCount.value = res.active_count

            const next = { ...statuses.value }

            for (const order of res.orders) {
                const prev = next[order.id]
                next[order.id] = order.status

                // İlk çağrı sadece tohumlar; geçmiş durumlar için bildirim atılmaz.
                if (isBaseline) continue

                // prev === undefined → daha önce görmediğimiz sipariş, sessizce tohumla.
                // prev === order.status → durum değişmemiş. `PATCH /orders/{id}/note`
                // updated_at'i durumu değiştirmeden güncellediği için bu satır gelir;
                // bildirim atmamak için karşılaştırma şart.
                if (prev !== undefined && prev !== order.status) {
                    announce({ id: order.id, order_no: order.order_no, status: order.status })
                }
            }

            statuses.value = next
            cursor.value = res.server_time
        } catch (err: any) {
            // Abort yalnızca sekme gizlenince veya stop() ile olur; ikisi de
            // bilinçli olarak yeniden zamanlamaz.
            if (err?.name === 'AbortError') return

            // Token geçersiz — yeniden denemek anlamsız.
            if (err?.status === 401 || err?.response?.status === 401) {
                stop()
                return
            }

            // cursor'a DOKUNMA: bir sonraki başarılı istek kaçırılanları getirir.
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

    /**
     * Sunucudan zaten çekilmiş bir sipariş listesiyle önbelleği tohumla.
     * Böylece tracker'ın başlangıç durumu ekranda görünenle aynı olur ve
     * ilk poll yanıtı yanlışlıkla "değişti" saymaz.
     */
    function seed(orders: { id: number; status: OrderStatus }[]) {
        const next = { ...statuses.value }
        for (const o of orders) next[o.id] = o.status
        statuses.value = next
    }

    /** Bileşen yaşam döngüsüne bağlanan sarmalayıcı. Setup içinde çağrılmalı. */
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

    return { statuses, cursor, activeCount, lastChange, running, seed, start, stop, useTracking }
}
