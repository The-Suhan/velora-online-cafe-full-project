// composables/useCart.js
// Cart state is stored in a cookie so it persists across page refreshes.
// Cookie key: velaro_cart  |  value: JSON array of cart items

const COOKIE_KEY = 'velaro_cart'

export function useCart() {
    const cookie = useCookie(COOKIE_KEY, {
        default: () => [],
        maxAge: 60 * 60 * 24 * 7, // 7 days
        watch: true,
    })

    // Always return a clean array (guard against corrupt cookie)
    const items = computed({
        get: () => (Array.isArray(cookie.value) ? cookie.value : []),
        set: (val) => { cookie.value = val },
    })

    const totalCount = computed(() =>
        items.value.reduce((sum, i) => sum + (i.quantity ?? 0), 0)
    )

    const totalPrice = computed(() =>
        items.value.reduce((sum, i) => sum + (i.price ?? 0) * (i.quantity ?? 0), 0)
    )

    function addItem(product) {
        const list = [...items.value]
        const idx = list.findIndex((i) => i.product_id === product.id)
        if (idx !== -1) {
            list[idx] = { ...list[idx], quantity: list[idx].quantity + 1 }
        } else {
            list.push({
                product_id: product.id,
                product_name: product.name,
                image_url: product.image_url ?? null,
                price: Number(product.price),
                quantity: 1,
            })
        }
        items.value = list
    }

    function increaseQty(productId) {
        const list = [...items.value]
        const idx = list.findIndex((i) => i.product_id === productId)
        if (idx === -1) return
        list[idx] = { ...list[idx], quantity: Math.min(list[idx].quantity + 1, 99) }
        items.value = list
    }

    function decreaseQty(productId) {
        const list = [...items.value]
        const idx = list.findIndex((i) => i.product_id === productId)
        if (idx === -1) return
        if (list[idx].quantity <= 1) {
            list.splice(idx, 1)
        } else {
            list[idx] = { ...list[idx], quantity: list[idx].quantity - 1 }
        }
        items.value = list
    }

    function removeItem(productId) {
        items.value = items.value.filter((i) => i.product_id !== productId)
    }

    function clearCart() {
        items.value = []
    }

    function getItem(productId) {
        return items.value.find((i) => i.product_id === productId) ?? null
    }
    

    return {
        items,
        totalCount,
        totalPrice,
        addItem,
        increaseQty,
        decreaseQty,
        removeItem,
        clearCart,
        getItem,
    }
}