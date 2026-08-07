// composables/useAuthGate.js
// Gate for actions that need a signed-in customer (add to cart, rate a
// product, place an order) on pages that are otherwise public. Instead of
// hard-redirecting away from the page (which is what the `auth` route
// middleware used to do), this shows a small "you need to sign in" popup
// and lets the visitor keep browsing if they dismiss it.

export function useAuthGate() {
    const visible = useState('auth-gate-visible', () => false)
    const token = useCookie('auth_token')

    // Returns true and lets the caller proceed when already signed in;
    // otherwise opens the popup and returns false so the caller can bail
    // out of whatever action (add to cart, rate, etc.) triggered it.
    function requireAuth() {
        if (token.value) return true
        visible.value = true
        return false
    }

    function close() {
        visible.value = false
    }

    return { visible, requireAuth, close }
}
