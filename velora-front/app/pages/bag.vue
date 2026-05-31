<template>
    <main class="bag-page">
        <div class="bag-container">

            <!-- ── Page Title ── -->
            <div class="bag-header">
                <h1 class="bag-title">My Bag</h1>
                <button v-if="items.length > 0" class="clear-btn" @click="clearCart">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                        <path d="M10 11v6M14 11v6" />
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                    </svg>
                    Clear bag
                </button>
            </div>

            <!-- ── Empty State ── -->
            <Transition name="fade" mode="out-in">
                <div v-if="items.length === 0" class="empty-bag" key="empty">
                    <div class="empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#C8A96A" stroke-width="1.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <path d="M16 10a4 4 0 0 1-8 0" />
                        </svg>
                    </div>
                    <p class="empty-title">Your bag is empty</p>
                    <p class="empty-sub">Add something delicious from the menu.</p>
                    <NuxtLink to="/" class="browse-btn">Browse Menu</NuxtLink>
                </div>

                <!-- ── Layout: Items + Summary ── -->
                <div v-else class="bag-layout" key="filled">

                    <!-- LEFT: Item list -->
                    <div class="bag-items">
                        <TransitionGroup name="item" tag="div" class="items-list">
                            <div v-for="item in items" :key="item.product_id" class="bag-item">

                                <!-- Thumbnail -->
                                <div class="item-thumb">
                                    <img v-if="item.image_url" :src="resolveUrl(item.image_url)"
                                        :alt="item.product_name" class="thumb-img" />
                                    <div v-else class="thumb-placeholder">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#C8A96A"
                                            stroke-width="1.4" stroke-linecap="round">
                                            <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                                            <line x1="6" y1="1" x2="6" y2="4" />
                                            <line x1="10" y1="1" x2="10" y2="4" />
                                            <line x1="14" y1="1" x2="14" y2="4" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="item-info">
                                    <p class="item-name">{{ item.product_name }}</p>
                                    <p class="item-unit-price">${{ Number(item.price).toFixed(2) }} / item</p>
                                </div>

                                <!-- Qty controls -->
                                <div class="item-qty">
                                    <button class="qty-btn" @click="decreaseQty(item.product_id)" aria-label="Decrease">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                    </button>
                                    <span class="qty-num">{{ item.quantity }}</span>
                                    <button class="qty-btn" @click="increaseQty(item.product_id)" aria-label="Increase">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                            <line x1="12" y1="5" x2="12" y2="19" />
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Subtotal + delete -->
                                <div class="item-right">
                                    <span class="item-subtotal">
                                        ${{ (Number(item.price) * item.quantity).toFixed(2) }}
                                    </span>
                                    <button class="delete-btn" @click="removeItem(item.product_id)" aria-label="Remove">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                            <line x1="18" y1="6" x2="6" y2="18" />
                                            <line x1="6" y1="6" x2="18" y2="18" />
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        </TransitionGroup>
                    </div>

                    <!-- RIGHT: Order summary -->
                    <aside class="bag-summary">
                        <div class="summary-card">
                            <h2 class="summary-title">Order Summary</h2>

                            <!-- Delivery toggle -->
                            <div class="delivery-toggle">
                                <button :class="['toggle-btn', { 'toggle-btn--active': deliveryType === 'pickup' }]"
                                    @click="deliveryType = 'pickup'">
                                    Pickup
                                </button>
                                <button :class="['toggle-btn', { 'toggle-btn--active': deliveryType === 'delivery' }]"
                                    @click="deliveryType = 'delivery'">
                                    Delivery
                                </button>
                            </div>

                            <!-- Delivery-only address field -->
                            <Transition name="expand">
                                <div v-if="deliveryType === 'delivery'" class="delivery-fields">
                                    <div class="field-group">
                                        <label class="field-label">Address</label>
                                        <input v-model="address" class="field-input" type="text"
                                            placeholder="Enter delivery address" />
                                    </div>
                                </div>
                            </Transition>

                            <!-- Phone (both pickup & delivery) -->
                            <div class="field-group">
                                <label class="field-label">Phone</label>
                                <div class="phone-input-wrap" :class="{ 'phone-input-wrap--error': phoneError }">
                                    <span class="phone-prefix">+993</span>
                                    <div class="phone-divider" />
                                    <input ref="phoneInputRef" class="phone-input-inner" type="text" inputmode="numeric"
                                        :placeholder="phonePlaceholder" :value="phoneDisplay" @input="handlePhoneInput"
                                        @keydown="handlePhoneKeydown" maxlength="9" autocomplete="tel" />
                                </div>
                                <Transition name="fade">
                                    <p v-if="phoneError" class="field-error">{{ phoneError }}</p>
                                </Transition>
                                <p class="field-hint">Operators: 71, 65, 64, 63, 62, 61</p>
                            </div>

                            <!-- Note -->
                            <div class="field-group">
                                <label class="field-label">Note <span class="optional">(optional)</span></label>
                                <textarea v-model="note" class="field-input field-textarea" rows="2"
                                    placeholder="Any special requests?" />
                            </div>

                            <!-- Price breakdown -->
                            <div class="price-rows">
                                <div class="price-row">
                                    <span>Subtotal</span>
                                    <span>${{ totalPrice.toFixed(2) }}</span>
                                </div>
                                <div v-if="deliveryType === 'delivery'" class="price-row">
                                    <span>Delivery fee</span>
                                    <span class="accent">${{ deliveryFee.toFixed(2) }}</span>
                                </div>
                                <div class="price-divider" />
                                <div class="price-row price-row--total">
                                    <span>Total</span>
                                    <span>${{ grandTotal.toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- CTA -->
                            <button class="place-btn" :disabled="ordering || !canOrder" @click="placeOrder">
                                <Transition name="fade" mode="out-in">
                                    <span v-if="ordering" key="loading" class="btn-inner">
                                        <svg class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                                        </svg>
                                        Placing order…
                                    </span>
                                    <span v-else key="idle" class="btn-inner">Place Order</span>
                                </Transition>
                            </button>

                            <!-- Error -->
                            <Transition name="fade">
                                <p v-if="orderError" class="order-error">{{ orderError }}</p>
                            </Transition>
                        </div>
                    </aside>

                </div>
            </Transition>

        </div>

        <!-- ── Success overlay ── -->
        <Transition name="fade">
            <div v-if="orderSuccess" class="success-overlay" @click="dismissSuccess">
                <div class="success-card" @click.stop>
                    <div class="success-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C8A96A" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <h3 class="success-title">Order Placed!</h3>
                    <p class="success-sub">
                        Order <strong>{{ lastOrderNo }}</strong> has been received. We'll have it ready for you soon.
                    </p>
                    <div class="success-actions">
                        <NuxtLink to="/profile" class="success-btn success-btn--ghost">View Orders</NuxtLink>
                        <NuxtLink to="/" class="success-btn success-btn--primary">Back to Menu</NuxtLink>
                    </div>
                </div>
            </div>
        </Transition>

    </main>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useCart } from '~/composables/useCart'

definePageMeta({
    layout: 'client',
    middleware: 'auth',
})

useHead({ title: 'My Bag — Velaro' })

// ─── Cart ─────────────────────────────────────────────────────
const { items, totalPrice, increaseQty, decreaseQty, removeItem, clearCart } = useCart()

// ─── Config / API ─────────────────────────────────────────────
const config = useRuntimeConfig()
const BACKEND_BASE = config.public.apiBase.replace(/\/api\/?$/, '')
const api = useApi()

function resolveUrl(url) {
    if (!url) return null
    if (url.startsWith('http')) return url
    return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
}

// ─── Phone validation ─────────────────────────────────────────
// Allowed operator codes
const VALID_OPERATORS = ['71', '65', '64', '63', '62', '61']

// Raw digits only (no prefix, no spaces) — max 8 digits: 2 operator + 6 number
const phoneRaw = ref('')
const phoneError = ref('')
const phoneInputRef = ref(null)

// Display string shown in the input: "XX XXXXXX" formatted as user types
const phoneDisplay = computed(() => {
    const d = phoneRaw.value
    if (d.length <= 2) return d
    return d.slice(0, 2) + ' ' + d.slice(2)
})

// Hint placeholder showing expected format based on allowed operators
const phonePlaceholder = computed(() => '65 XXXXXX')

// Full formatted phone for POST payload: "+993 XX XXXXXX"
const phoneFormatted = computed(() => {
    if (phoneRaw.value.length !== 8) return ''
    return `+993 ${phoneRaw.value.slice(0, 2)} ${phoneRaw.value.slice(2)}`
})

function validatePhone() {
    const d = phoneRaw.value
    if (d.length === 0) {
        phoneError.value = 'Phone number is required.'
        return false
    }
    if (d.length < 8) {
        phoneError.value = 'Enter a complete phone number.'
        return false
    }
    const op = d.slice(0, 2)
    if (!VALID_OPERATORS.includes(op)) {
        phoneError.value = `Operator must be one of: ${VALID_OPERATORS.join(', ')}.`
        return false
    }
    phoneError.value = ''
    return true
}

function handlePhoneInput(e) {
    // Strip everything except digits
    const onlyDigits = e.target.value.replace(/\D/g, '')

    // Enforce max 8 digits
    const trimmed = onlyDigits.slice(0, 8)

    // If we have 2+ digits, validate operator on the fly
    if (trimmed.length >= 2) {
        const op = trimmed.slice(0, 2)
        if (!VALID_OPERATORS.includes(op)) {
            // If operator is being typed (length === 2), block invalid combos
            // Allow first digit if it could still lead to valid operator
            const firstDigit = trimmed[0]
            const validFirstDigits = [...new Set(VALID_OPERATORS.map(o => o[0]))]
            if (!validFirstDigits.includes(firstDigit)) {
                phoneRaw.value = ''
                e.target.value = ''
                phoneError.value = `Operator must be one of: ${VALID_OPERATORS.join(', ')}.`
                return
            }
            // First digit OK but second locks in invalid operator — revert second digit
            if (trimmed.length >= 2) {
                phoneRaw.value = trimmed.slice(0, 1)
                e.target.value = trimmed.slice(0, 1)
                phoneError.value = `Operator must be one of: ${VALID_OPERATORS.join(', ')}.`
                return
            }
        } else {
            phoneError.value = ''
        }
    } else {
        phoneError.value = ''
    }

    phoneRaw.value = trimmed
    // Restore formatted display value (cursor position will naturally move to end)
    e.target.value = phoneDisplay.value
}

function handlePhoneKeydown(e) {
    // Allow: backspace, delete, tab, arrow keys, home, end
    const allowed = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End']
    if (allowed.includes(e.key)) return

    // Block non-digit keys
    if (!/^\d$/.test(e.key)) {
        e.preventDefault()
        return
    }

    // Prevent exceeding 8 digits
    if (phoneRaw.value.length >= 8) {
        e.preventDefault()
        return
    }

    // Validate operator digit by digit
    const currentRaw = phoneRaw.value
    const nextRaw = currentRaw + e.key

    if (nextRaw.length === 1) {
        // First digit: must be a valid leading digit (6 or 7)
        const validFirstDigits = [...new Set(VALID_OPERATORS.map(o => o[0]))]
        if (!validFirstDigits.includes(e.key)) {
            e.preventDefault()
            phoneError.value = `Operator must be one of: ${VALID_OPERATORS.join(', ')}.`
            return
        }
    }

    if (nextRaw.length === 2) {
        // Second digit: must form a valid operator
        const potentialOp = nextRaw.slice(0, 2)
        if (!VALID_OPERATORS.includes(potentialOp)) {
            e.preventDefault()
            phoneError.value = `Operator must be one of: ${VALID_OPERATORS.join(', ')}.`
            return
        }
    }

    phoneError.value = ''
}

// ─── Order form state ─────────────────────────────────────────
const deliveryType = ref('pickup')
const address = ref('')
const note = ref('')
const ordering = ref(false)
const orderError = ref('')
const orderSuccess = ref(false)
const lastOrderNo = ref('')

const DELIVERY_FEE = 3.99
const deliveryFee = computed(() => deliveryType.value === 'delivery' ? DELIVERY_FEE : 0)
const grandTotal = computed(() => totalPrice.value + deliveryFee.value)

const canOrder = computed(() => {
    if (items.value.length === 0) return false
    if (phoneRaw.value.length !== 8) return false
    const op = phoneRaw.value.slice(0, 2)
    if (!VALID_OPERATORS.includes(op)) return false
    if (deliveryType.value === 'delivery') {
        return address.value.trim().length > 0
    }
    return true
})

async function placeOrder() {
    if (!validatePhone()) return
    if (!canOrder.value || ordering.value) return
    ordering.value = true
    orderError.value = ''

    try {
        const payload = {
            items: items.value.map((i) => ({
                product_id: i.product_id,
                quantity: i.quantity,
            })),
            delivery_type: deliveryType.value,
            note: note.value.trim() || null,
            address: deliveryType.value === 'delivery' ? address.value.trim() : null,
            // Always send formatted phone: "+993 XX XXXXXX"
            phone: phoneFormatted.value,
        }

        const res = await api('/orders', { method: 'POST', body: payload })

        lastOrderNo.value = res.order?.order_no ?? '#ORD'
        clearCart()
        // reset form
        note.value = ''
        address.value = ''
        phoneRaw.value = ''
        phoneError.value = ''
        deliveryType.value = 'pickup'
        orderSuccess.value = true
    } catch (e) {
        orderError.value = e?.data?.message ?? 'Something went wrong. Please try again.'
    } finally {
        ordering.value = false
    }
}

function dismissSuccess() {
    orderSuccess.value = false
}
</script>

<style scoped>
/* ── Page ── */
.bag-page {
    min-height: 100vh;
    background: #F5EFEA;
    font-family: 'Georgia', serif;
    padding-bottom: 4rem;
}

.bag-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 2.5rem 2rem 0;
}

/* ── Header ── */
.bag-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 2rem;
}

.bag-title {
    font-family: 'Georgia', serif;
    font-size: 2rem;
    font-weight: 400;
    color: #2C1A14;
    letter-spacing: 0.01em;
    margin: 0;
}

.clear-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: transparent;
    border: 1px solid rgba(44, 26, 20, 0.2);
    color: #7a5c4a;
    font-family: 'Georgia', serif;
    font-size: 0.74rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.35rem 0.85rem;
    cursor: pointer;
    border-radius: 4px;
    transition: border-color 0.18s, color 0.18s, background 0.18s;
}

.clear-btn:hover {
    border-color: #c8574a;
    color: #c8574a;
    background: rgba(200, 87, 74, 0.05);
}

/* ── Empty ── */
.empty-bag {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 5rem 2rem;
    gap: 0.75rem;
}

.empty-icon {
    margin-bottom: 0.5rem;
    opacity: 0.65;
}

.empty-title {
    font-size: 1.4rem;
    font-weight: 400;
    color: #2C1A14;
    margin: 0;
}

.empty-sub {
    font-size: 0.85rem;
    color: #9a7a68;
    margin: 0;
    font-family: 'Georgia', serif;
}

.browse-btn {
    margin-top: 1rem;
    background: #2C1A14;
    color: #F5EFEA;
    text-decoration: none;
    font-family: 'Georgia', serif;
    font-size: 0.78rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    padding: 0.7rem 2rem;
    border-radius: 4px;
    transition: background 0.2s;
}

.browse-btn:hover {
    background: #4a2e22;
}

/* ── Layout ── */
.bag-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 2rem;
    align-items: start;
}

/* ── Items list ── */
.bag-items {
    background: #fff;
    border: 1px solid rgba(44, 26, 20, 0.08);
    border-radius: 4px;
    overflow: hidden;
}

.items-list {
    display: flex;
    flex-direction: column;
}

.bag-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.1rem 1.25rem;
    border-bottom: 1px solid rgba(44, 26, 20, 0.07);
    transition: background 0.15s;
}

.bag-item:last-child {
    border-bottom: none;
}

.bag-item:hover {
    background: rgba(245, 239, 234, 0.5);
}

/* Thumb */
.item-thumb {
    width: 72px;
    height: 72px;
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
    background: #F5EFEA;
}

.thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumb-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #ede5d8, #d4c5a9);
}

/* Info */
.item-info {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-family: 'Georgia', serif;
    font-size: 0.95rem;
    color: #2C1A14;
    margin: 0 0 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-unit-price {
    font-size: 0.72rem;
    color: #9a7a68;
    margin: 0;
}

/* Qty */
.item-qty {
    display: flex;
    align-items: center;
    gap: 0;
    border: 1px solid rgba(200, 169, 106, 0.55);
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
}

.qty-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #C8A96A;
    cursor: pointer;
    transition: background 0.15s;
}

.qty-btn:hover {
    background: rgba(200, 169, 106, 0.12);
}

.qty-num {
    min-width: 28px;
    text-align: center;
    font-family: 'Georgia', serif;
    font-size: 0.88rem;
    color: #2C1A14;
    border-left: 1px solid rgba(200, 169, 106, 0.35);
    border-right: 1px solid rgba(200, 169, 106, 0.35);
    line-height: 32px;
    height: 32px;
}

/* Right column */
.item-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.4rem;
    flex-shrink: 0;
}

.item-subtotal {
    font-family: 'Georgia', serif;
    font-size: 1rem;
    font-weight: 600;
    color: #2C1A14;
}

.delete-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    color: #b0957a;
    cursor: pointer;
    border-radius: 50%;
    transition: background 0.15s, color 0.15s;
}

.delete-btn:hover {
    background: rgba(200, 87, 74, 0.1);
    color: #c8574a;
}

/* ── Summary card ── */
.summary-card {
    background: #fff;
    border: 1px solid rgba(44, 26, 20, 0.08);
    border-radius: 4px;
    padding: 1.75rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
    position: sticky;
    top: 88px;
}

.summary-title {
    font-family: 'Georgia', serif;
    font-size: 1.15rem;
    font-weight: 400;
    color: #2C1A14;
    margin: 0;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(44, 26, 20, 0.08);
}

/* Delivery toggle */
.delivery-toggle {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border: 1px solid rgba(200, 169, 106, 0.5);
    border-radius: 4px;
    overflow: hidden;
}

.toggle-btn {
    padding: 0.5rem;
    border: none;
    background: transparent;
    color: #7a5c4a;
    font-family: 'Georgia', serif;
    font-size: 0.78rem;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.toggle-btn--active {
    background: #2C1A14;
    color: #F5EFEA;
}

/* Fields */
.delivery-fields {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.field-label {
    font-family: 'Georgia', serif;
    font-size: 0.7rem;
    color: #9a7a68;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.optional {
    color: #b0957a;
    font-size: 0.65rem;
}

.field-input {
    background: #F5EFEA;
    border: 1px solid rgba(44, 26, 20, 0.12);
    border-radius: 3px;
    padding: 0.55rem 0.75rem;
    font-family: 'Georgia', serif;
    font-size: 0.85rem;
    color: #2C1A14;
    outline: none;
    transition: border-color 0.15s;
    resize: none;
    width: 100%;
    box-sizing: border-box;
}

.field-input:focus {
    border-color: #C8A96A;
}

.field-input::placeholder {
    color: #b0957a;
}

.field-textarea {
    min-height: 60px;
}

/* ── Phone input ── */
.phone-input-wrap {
    display: flex;
    align-items: center;
    background: #F5EFEA;
    border: 1px solid rgba(44, 26, 20, 0.12);
    border-radius: 3px;
    overflow: hidden;
    transition: border-color 0.15s;
}

.phone-input-wrap:focus-within {
    border-color: #C8A96A;
}

.phone-input-wrap--error {
    border-color: #c8574a;
}

.phone-prefix {
    font-family: 'Georgia', serif;
    font-size: 0.85rem;
    color: #2C1A14;
    font-weight: 600;
    padding: 0.55rem 0.6rem 0.55rem 0.75rem;
    white-space: nowrap;
    user-select: none;
    flex-shrink: 0;
    letter-spacing: 0.02em;
}

.phone-divider {
    width: 1px;
    height: 18px;
    background: rgba(44, 26, 20, 0.15);
    flex-shrink: 0;
}

.phone-input-inner {
    flex: 1;
    min-width: 0;
    border: none;
    background: transparent;
    padding: 0.55rem 0.75rem 0.55rem 0.6rem;
    font-family: 'Georgia', serif;
    font-size: 0.85rem;
    color: #2C1A14;
    outline: none;
    letter-spacing: 0.05em;
}

.phone-input-inner::placeholder {
    color: #b0957a;
    letter-spacing: 0.02em;
}

.field-error {
    font-family: 'Georgia', serif;
    font-size: 0.72rem;
    color: #c8574a;
    margin: 0;
    line-height: 1.4;
}

.field-hint {
    font-family: 'Georgia', serif;
    font-size: 0.68rem;
    color: #b0957a;
    margin: 0;
    letter-spacing: 0.02em;
}

/* Price rows */
.price-rows {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.price-row {
    display: flex;
    justify-content: space-between;
    font-family: 'Georgia', serif;
    font-size: 0.85rem;
    color: #5a3e2b;
}

.accent {
    color: #C8A96A;
}

.price-divider {
    height: 1px;
    background: rgba(44, 26, 20, 0.08);
    margin: 0.25rem 0;
}

.price-row--total {
    font-size: 1.05rem;
    font-weight: 600;
    color: #2C1A14;
}

/* Place order btn */
.place-btn {
    width: 100%;
    padding: 0.85rem;
    background: #C8A96A;
    color: #2C1A14;
    border: none;
    border-radius: 4px;
    font-family: 'Georgia', serif;
    font-size: 0.88rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s, opacity 0.2s;
    font-weight: 600;
}

.place-btn:hover:not(:disabled) {
    background: #b8924f;
}

.place-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.btn-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.order-error {
    font-size: 0.75rem;
    color: #c8574a;
    text-align: center;
    margin: 0;
    font-family: 'Georgia', serif;
}

/* ── Success overlay ── */
.success-overlay {
    position: fixed;
    inset: 0;
    background: rgba(44, 26, 20, 0.55);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 200;
    padding: 1.5rem;
}

.success-card {
    background: #fff;
    border-radius: 6px;
    padding: 2.5rem 2rem;
    max-width: 420px;
    width: 100%;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 24px 64px rgba(44, 26, 20, 0.22);
}

.success-icon {
    opacity: 0.9;
    margin-bottom: 0.25rem;
}

.success-title {
    font-family: 'Georgia', serif;
    font-size: 1.6rem;
    font-weight: 400;
    color: #2C1A14;
    margin: 0;
}

.success-sub {
    font-family: 'Georgia', serif;
    font-size: 0.88rem;
    color: #7a5c4a;
    margin: 0;
    line-height: 1.6;
}

.success-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.75rem;
    width: 100%;
}

.success-btn {
    flex: 1;
    text-align: center;
    padding: 0.7rem 1rem;
    text-decoration: none;
    font-family: 'Georgia', serif;
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border-radius: 4px;
    transition: background 0.18s;
}

.success-btn--ghost {
    border: 1px solid rgba(44, 26, 20, 0.2);
    color: #5a3e2b;
    background: transparent;
}

.success-btn--ghost:hover {
    background: rgba(44, 26, 20, 0.05);
}

.success-btn--primary {
    background: #2C1A14;
    color: #F5EFEA;
}

.success-btn--primary:hover {
    background: #4a2e22;
}

/* ── Spinner ── */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.spin {
    animation: spin 0.7s linear infinite;
}

/* ── Transitions ── */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.item-enter-active {
    transition: all 0.22s ease;
}

.item-leave-active {
    transition: all 0.18s ease;
}

.item-enter-from {
    opacity: 0;
    transform: translateX(14px);
}

.item-leave-to {
    opacity: 0;
    transform: translateX(-14px);
}

.item-leave-active {
    position: absolute;
    width: 100%;
}

.expand-enter-active,
.expand-leave-active {
    transition: opacity 0.2s ease, max-height 0.25s ease;
    overflow: hidden;
    max-height: 200px;
}

.expand-enter-from,
.expand-leave-to {
    opacity: 0;
    max-height: 0;
}

/* ── Responsive ── */
@media (max-width: 860px) {
    .bag-layout {
        grid-template-columns: 1fr;
    }

    .summary-card {
        position: static;
    }

    .bag-container {
        padding: 1.5rem 1.25rem 0;
    }
}

@media (max-width: 480px) {
    .bag-title {
        font-size: 1.5rem;
    }

    .item-thumb {
        width: 56px;
        height: 56px;
    }

    .bag-item {
        padding: 0.85rem 1rem;
        gap: 0.75rem;
    }
}
</style>