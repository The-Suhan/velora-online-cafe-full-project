// composables/useToast.ts
//
// Uygulama genelinde paylaşılan toast kuyruğu. AppToast.vue app.vue'de bir kez
// mount edilir, bu yüzden hem client hem admin layout'undan push() çağrılabilir.

export type ToastType = 'success' | 'error' | 'info'

export interface Toast {
    id: number
    message: string
    type: ToastType
}

export const useToast = () => {
    const toasts = useState<Toast[]>('toasts', () => [])
    const seq = useState('toasts:seq', () => 0)

    function dismiss(id: number) {
        toasts.value = toasts.value.filter(t => t.id !== id)
    }

    function push(message: string, type: ToastType = 'info', ms = 4500) {
        const id = ++seq.value
        toasts.value = [...toasts.value, { id, message, type }]

        if (import.meta.client && ms > 0) {
            setTimeout(() => dismiss(id), ms)
        }

        return id
    }

    return { toasts, push, dismiss }
}
