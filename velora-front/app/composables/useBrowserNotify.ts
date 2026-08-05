// composables/useBrowserNotify.ts
//
// Tarayıcı/işletim sistemi bildirimleri.
//
// Notification.requestPermission() sayfa yüklenirken ASLA çağrılmaz — Chrome
// bunu cezalandırır ve kullanıcı refleksle reddeder. Yalnızca açık bir tıklama
// (zil butonu) üzerinden istenir. Reddedildiyse tekrar sorulmaz.

const STORAGE_KEY = 'velora:notify'

export const useBrowserNotify = () => {
    const enabled = useState('notify:enabled', () => false)
    const hydrated = useState('notify:hydrated', () => false)

    const supported = computed(() => import.meta.client && 'Notification' in window)

    const permission = computed<NotificationPermission | 'unsupported'>(() =>
        supported.value ? Notification.permission : 'unsupported'
    )

    /** Kullanıcının önceki tercihini localStorage'dan yükle. Idempotent. */
    function hydrate() {
        if (!import.meta.client || hydrated.value) return
        hydrated.value = true

        try {
            // İzin sonradan tarayıcı ayarlarından geri alınmış olabilir.
            enabled.value =
                localStorage.getItem(STORAGE_KEY) === '1' &&
                'Notification' in window &&
                Notification.permission === 'granted'
        } catch {
            enabled.value = false
        }
    }

    function persist(value: boolean) {
        try {
            localStorage.setItem(STORAGE_KEY, value ? '1' : '0')
        } catch {
            // private mode — bellekte tut, yeter.
        }
    }

    /** Sadece bir click handler içinden çağrılmalı. */
    async function request(): Promise<boolean> {
        if (!supported.value) return false

        if (Notification.permission === 'denied') {
            // Tekrar sormak anlamsız; UI 'blocked' ipucu gösterir.
            enabled.value = false
            persist(false)
            return false
        }

        if (Notification.permission !== 'granted') {
            const result = await Notification.requestPermission()
            if (result !== 'granted') {
                enabled.value = false
                persist(false)
                return false
            }
        }

        enabled.value = true
        persist(true)
        return true
    }

    function disable() {
        enabled.value = false
        persist(false)
    }

    function toggle(): Promise<boolean> | boolean {
        if (enabled.value) {
            disable()
            return false
        }
        return request()
    }

    function notify(title: string, body: string) {
        if (!enabled.value || !supported.value) return
        if (Notification.permission !== 'granted') return
        // Sekme zaten öndeyse toast yeterli, OS bildirimiyle üstüne binme.
        if (document.visibilityState === 'visible') return

        try {
            new Notification(title, { body, icon: '/logo.png', tag: 'velora-order' })
        } catch {
            // Bazı platformlarda constructor kullanımı desteklenmez (mobil Chrome).
        }
    }

    return { enabled, supported, permission, hydrate, request, disable, toggle, notify }
}
