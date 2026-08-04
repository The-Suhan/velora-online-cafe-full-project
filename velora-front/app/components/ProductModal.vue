<script setup>
import { ref, watch } from 'vue'
import { useCart } from '~/composables/useCart'
const { t } = useI18n()

const { resolveUrl } = useMediaUrl()
const { displayName, displayDesc } = useLocalized()

// ─── Props & emits ────────────────────────────────────────────
const props = defineProps({
    product: { type: Object, default: null },
    modelValue: { type: Boolean, default: false },
    initialUserRating: { type: Number, default: null },
})

const emit = defineEmits(['update:modelValue', 'rating-updated'])

function close() { emit('update:modelValue', false) }
function onOverlayClick(e) { if (e.target === e.currentTarget) close() }

// ─── API & state — watch'lardan ÖNCE tanımla ─────────────────
const api = useApi()
const userRatings = ref({})
const pendingRatings = ref({})
const activeProduct = ref(null)
const lightboxOpen = ref(false)
const relatedProducts = ref([])
const relatedLoading = ref(false)


// ─── Internal active product (switches when related card clicked) ──

watch(() => props.product, async (p) => {
    activeProduct.value = p
    if (!p || !props.modelValue) return
    try {
        const fresh = await api(`/products/${p.id}`)
        activeProduct.value = { ...p, ...fresh }
        userRatings.value[p.id] = fresh.user_rating?.score ?? null
    } catch (e) {
        console.error(e)
    }
}, { immediate: true })


async function flushPendingRatings() {
    const entries = Object.entries(pendingRatings.value)
    if (!entries.length) return

    pendingRatings.value = {}

    await Promise.allSettled(
        entries.map(async ([productId, { score }]) => {
            try {
                const res = await api(`/products/${productId}/rate`, {
                    method: 'POST',
                    body: { score: score ?? null },
                })
                if (res?.avg_rating != null && activeProduct.value?.id === Number(productId)) {
                    activeProduct.value = { ...activeProduct.value, avg_rating: res.avg_rating }
                }
                emit('rating-updated', { productId: Number(productId), avgRating: res?.avg_rating ?? null, userScore: score })
            } catch (e) {
                console.error(e)
            }
        })
    )
}

function selectRelated(rp) {
    activeProduct.value = rp
    lightboxOpen.value = false
}

// ─── Cart ─────────────────────────────────────────────────────
const { addItem, increaseQty, decreaseQty, getItem } = useCart()
function cartItem(id) { return getItem(id) }


watch(activeProduct, async (p, prevP) => {
    if (prevP) {
        await flushPendingRatings()
    }

    if (!p) return
    relatedLoading.value = true
    relatedProducts.value = []
    try {
        const catId = p.category?.id ?? p.category_id
        if (!catId) return
        const data = await api(`/categories/${catId}/products`, {
            params: { per_page: 10, sort: 'newest' },
        })
        relatedProducts.value = (data.data ?? []).filter(r => r.id !== p.id)
    } catch (e) {
        console.error(t('productModal.loadRelatedError'), e)
    } finally {
        relatedLoading.value = false
    }
}, { immediate: true })

// ─── Gradients fallback ───────────────────────────────────────
const gradients = [
    'linear-gradient(135deg,var(--color-accent-soft) 0%,var(--color-brown-43-2) 100%)',
    'linear-gradient(135deg,var(--color-brown-75) 0%,var(--color-brown-54-2) 100%)',
    'linear-gradient(135deg,var(--color-primary-espresso) 0%,var(--color-coffee-24) 100%)',
    'linear-gradient(135deg,var(--color-brown-52) 0%,var(--color-brown-58-2) 100%)',
]
function cardGradient(id) { return gradients[id % gradients.length] }

function rateProduct(product, score) {
    const current = userRatings.value[product.id] !== undefined
        ? userRatings.value[product.id]
        : product.avg_rating ?? 0

    const isSameStar = current !== null && Math.round(current) === score
    const newScore = isSameStar ? null : score

    userRatings.value[product.id] = newScore
    pendingRatings.value[product.id] = { score: newScore, product }
}
watch(() => props.modelValue, async (isOpen) => {
    if (isOpen && activeProduct.value) {
        try {
            const fresh = await api(`/products/${activeProduct.value.id}`)
            activeProduct.value = { ...activeProduct.value, ...fresh }
            userRatings.value[activeProduct.value.id] = fresh.user_rating?.score ?? null
        } catch (e) {
            console.error(e)
        }
    } else if (!isOpen) {
        await flushPendingRatings()
    }
})
</script>

<template>
    <Teleport to="body">
        <Transition name="pm-fade">
            <div v-if="modelValue && activeProduct" class="pm-overlay" @click="onOverlayClick" role="dialog"
                aria-modal="true" :aria-label="displayName(activeProduct)">

                <div class="pm-modal">

                    <!-- Close -->
                    <button class="pm-close" @click="close" :aria-label="$t('productModal.closeModal')">&#215;</button>

                    <!-- ── Top: image + info ── -->
                    <div class="pm-top">

                        <!-- Image column -->
                        <div class="pm-img-col" :style="{ background: cardGradient(activeProduct.id) }"
                            @click="lightboxOpen = true">
                            <img v-if="activeProduct.image_url" :src="resolveUrl(activeProduct.image_url)"
                                :alt="displayName(activeProduct)" class="pm-img" draggable="false" />
                            <div class="pm-zoom-hint">{{ $t('productModal.clickToZoom') }}</div>
                        </div>

                        <!-- Info column -->
                        <div class="pm-info-col">

                            <span v-if="activeProduct.category?.name" class="pm-cat-badge">
                                {{ activeProduct.category.name }}
                            </span>

                            <h2 class="pm-name">{{ displayName(activeProduct) }}</h2>

                            <p class="pm-desc">{{ displayDesc(activeProduct) }}</p>

                            <!-- Rating -->
                            <div class="pm-rating-row">
                                <StarRating root-class="pm-stars"
                                    :score="(userRatings[activeProduct.id] !== undefined ? userRatings[activeProduct.id] : activeProduct.avg_rating) ?? 0"
                                    :interactive="true" @rate="(s) => rateProduct(activeProduct, s)" />
                                <span class="pm-rating-val">
                                    {{ ((userRatings[activeProduct.id] !== undefined ? userRatings[activeProduct.id] :
                                        activeProduct.avg_rating) ?? 0).toFixed(1) }}
                                </span>
                            </div>

                            <div class="pm-divider" />

                            <!-- Meta -->
                            <div class="pm-meta-row">
                                <span class="pm-meta-item">
                                    {{ $t('productModal.metaId') }}: <strong>#{{ activeProduct.id }}</strong>
                                </span>
                                <span v-if="activeProduct.category?.name" class="pm-meta-item">
                                    {{ $t('productModal.metaCategory') }}: <strong>{{ activeProduct.category.name
                                        }}</strong>
                                </span>
                            </div>

                            <!-- Price + cart -->
                            <div class="pm-footer">
                                <span class="pm-price">${{ Number(activeProduct.price).toFixed(2) }}</span>

                                <button v-if="!cartItem(activeProduct.id)" @click="addItem(activeProduct)"
                                    class="pm-add-btn">
                                    {{ $t('productModal.addToCart') }}
                                </button>
                                <div v-else class="pm-qty-ctrl">
                                    <button class="pm-qty-btn" @click="decreaseQty(activeProduct.id)"
                                        :aria-label="$t('home.decreaseQty')">−</button>
                                    <span class="pm-qty-num">{{ cartItem(activeProduct.id).quantity }}</span>
                                    <button class="pm-qty-btn" @click="increaseQty(activeProduct.id)"
                                        :aria-label="$t('home.increaseQty')">+</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Related products ── -->
                    <div class="pm-related">
                        <p class="pm-related-title">{{ $t('productModal.relatedTitle') }}</p>

                        <!-- Skeleton -->
                        <div v-if="relatedLoading" class="pm-rel-track">
                            <div v-for="n in 5" :key="n" class="pm-rel-card pm-rel-card--skeleton">
                                <div class="pm-rel-img pm-skeleton-block" />
                                <div class="pm-rel-body">
                                    <div class="pm-skeleton-line" style="width:70%;height:13px;" />
                                    <div class="pm-skeleton-line" style="width:40%;height:11px;margin-top:6px;" />
                                </div>
                            </div>
                        </div>

                        <!-- Cards -->
                        <div v-else-if="relatedProducts.length" class="pm-rel-track">
                            <div v-for="rp in relatedProducts" :key="rp.id" class="pm-rel-card"
                                @click="selectRelated(rp)">
                                <div class="pm-rel-img" :style="{ background: cardGradient(rp.id) }">
                                    <img v-if="rp.image_url" :src="resolveUrl(rp.image_url)" :alt="displayName(rp)"
                                        class="pm-rel-img-el" draggable="false" loading="lazy" />
                                </div>
                                <div class="pm-rel-body">
                                    <p class="pm-rel-name">{{ displayName(rp) }}</p>
                                    <p class="pm-rel-price">${{ Number(rp.price).toFixed(2) }}</p>
                                    <button v-if="!cartItem(rp.id)" @click.stop="addItem(rp)" class="pm-rel-add"
                                        :aria-label="$t('productModal.relatedAdd') + ' ' + displayName(rp)">
                                        {{ $t('productModal.relatedAdd') }}
                                    </button>
                                    <div v-else class="pm-rel-qty">
                                        <button @click.stop="decreaseQty(rp.id)" class="pm-rel-qty-btn"
                                            :aria-label="$t('home.decreaseQty')">−</button>
                                        <span>{{ cartItem(rp.id).quantity }}</span>
                                        <button @click.stop="increaseQty(rp.id)" class="pm-rel-qty-btn"
                                            :aria-label="$t('home.increaseQty')">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p v-else class="pm-related-empty">{{ $t('productModal.relatedEmpty') }}</p>
                    </div>
                </div>

                <!-- ── Lightbox ── -->
                <Transition name="pm-fade">
                    <div v-if="lightboxOpen" class="pm-lightbox" @click="lightboxOpen = false">
                        <button class="pm-lightbox-close" @click="lightboxOpen = false"
                            :aria-label="$t('productModal.closeLightbox')">&#215;</button>
                        <img :src="resolveUrl(activeProduct.image_url)" :alt="displayName(activeProduct)"
                            class="pm-lightbox-img" />
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* ── Overlay ── */
.pm-overlay {
    position: fixed;
    inset: 0;
    background: rgb(var(--rgb-primary-espresso) / 0.6);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    overflow-y: auto;
}

/* ── Transition ── */
.pm-fade-enter-active,
.pm-fade-leave-active {
    transition: opacity 0.22s ease;
}

.pm-fade-enter-from,
.pm-fade-leave-to {
    opacity: 0;
}

/* ── Modal shell ── */
.pm-modal {
    position: relative;
    background: var(--color-surface-warm);
    width: 100%;
    max-width: 900px;
    border-radius: 2px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--color-accent-soft) transparent;
}

/* ── Close button ── */
.pm-close {
    position: absolute;
    top: 10px;
    right: 14px;
    z-index: 10;
    background: rgb(var(--rgb-surface-warm) / 0.85);
    border: none;
    font-size: 22px;
    line-height: 1;
    color: var(--color-brown-40);
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.15s, color 0.15s;
}

.pm-close:hover {
    background: var(--color-white);
    color: var(--color-primary-espresso);
}

/* ── Top section ── */
.pm-top {
    display: flex;
    min-height: 400px;
}

/* ── Image column ── */
.pm-img-col {
    flex: 0 0 50%;
    position: relative;
    overflow: hidden;
    cursor: zoom-in;
}

.pm-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.45s ease;
}

.pm-img-col:hover .pm-img {
    transform: scale(1.04);
}

.pm-zoom-hint {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgb(var(--rgb-surface-warm) / 0.88);
    color: var(--color-primary-espresso);
    font-size: 10px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    padding: 4px 10px;
    pointer-events: none;
    font-family: 'Lato', sans-serif;
}

/* ── Info column ── */
.pm-info-col {
    flex: 0 0 50%;
    padding: 28px 28px 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
}

.pm-cat-badge {
    display: inline-block;
    align-self: flex-start;
    font-size: 10px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-brown-43-2);
    background: rgb(var(--rgb-accent-soft) / 0.15);
    padding: 4px 10px;
    font-family: 'Lato', sans-serif;
}

.pm-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.75rem;
    font-weight: 400;
    color: var(--color-primary-espresso);
    line-height: 1.2;
    margin: 0;
}

.pm-desc {
    font-size: 0.82rem;
    color: var(--color-brown-40);
    line-height: 1.65;
    flex: 1;
    font-family: 'Lato', sans-serif;
}

/* ── Stars ── */
.pm-rating-row {
    display: flex;
    align-items: center;
    gap: 6px;
}

.pm-rating-val {
    font-size: 0.72rem;
    color: var(--color-brown-43);
    font-family: 'Lato', sans-serif;
}

:deep(.pm-stars) {
    display: flex;
    gap: 2px;
}

:deep(.star-wrap) {
    position: relative;
    width: 16px;
    height: 16px;
    border: none;
    background: transparent;
    padding: 0;
    cursor: default;
    flex-shrink: 0;
}

:deep(.star-wrap):focus {
    outline: none;
}

:deep(.star-svg) {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

:deep(.star-fill) {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    overflow: hidden;
}

/* ── Divider ── */
.pm-divider {
    height: 0.5px;
    background: var(--color-brown-86);
}

/* ── Meta ── */
.pm-meta-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.pm-meta-item {
    font-size: 0.7rem;
    color: var(--color-brown-51);
    letter-spacing: 0.06em;
    font-family: 'Lato', sans-serif;
}

.pm-meta-item strong {
    color: var(--color-primary-espresso);
    font-weight: 500;
}

/* ── Price + cart ── */
.pm-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 0.5px solid var(--color-brown-86);
    margin-top: auto;
}

.pm-price {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2rem;
    color: var(--color-primary-espresso);
}

.pm-add-btn {
    background: var(--color-accent-soft);
    color: var(--color-white);
    border: none;
    padding: 0.5rem 1.4rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.7rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.18s;
}

.pm-add-btn:hover {
    background: var(--color-brown-52);
}

.pm-qty-ctrl {
    display: flex;
    align-items: center;
    border: 1px solid var(--color-accent-soft);
}

.pm-qty-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: var(--color-accent-soft);
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}

.pm-qty-btn:hover {
    background: rgb(var(--rgb-accent-soft) / 0.12);
}

.pm-qty-num {
    width: 28px;
    text-align: center;
    font-size: 0.82rem;
    color: var(--color-primary-espresso);
    font-family: 'Lato', sans-serif;
}

/* ── Related section ── */
.pm-related {
    padding: 20px 24px 24px;
    border-top: 3px solid var(--color-accent-soft);
    background: var(--color-brown-90);
}

.pm-related-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.1rem;
    font-weight: 400;
    color: var(--color-primary-espresso);
    margin-bottom: 14px;
    letter-spacing: 0.01em;
}

.pm-related-empty {
    font-size: 0.78rem;
    color: var(--color-brown-51);
    font-family: 'Lato', sans-serif;
}

.pm-rel-track {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-bottom: 4px;
}

.pm-rel-track::-webkit-scrollbar {
    display: none;
}

/* ── Related card ── */
.pm-rel-card {
    flex: 0 0 160px;
    background: var(--color-white);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 0.5px solid rgb(var(--rgb-primary-espresso) / 0.08);
    transition: box-shadow 0.2s, transform 0.2s;
    cursor: pointer;
}

.pm-rel-card:hover {
    box-shadow: 0 4px 16px rgb(var(--rgb-primary-espresso) / 0.12);
    transform: translateY(-2px);
}

.pm-rel-img {
    height: 100px;
    overflow: hidden;
    flex-shrink: 0;
}

.pm-rel-img-el {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
    display: block;
}

.pm-rel-card:hover .pm-rel-img-el {
    transform: scale(1.06);
}

.pm-rel-body {
    padding: 8px 10px 10px;
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
}

.pm-rel-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 0.9rem;
    color: var(--color-primary-espresso);
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pm-rel-price {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 0.85rem;
    color: var(--color-accent-soft);
}

.pm-rel-add {
    margin-top: auto;
    background: var(--color-accent-soft);
    color: var(--color-white);
    border: none;
    width: 100%;
    padding: 5px 0;
    font-family: 'Lato', sans-serif;
    font-size: 0.62rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s;
}

.pm-rel-add:hover {
    background: var(--color-brown-52);
}

.pm-rel-qty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin-top: auto;
    border: 1px solid var(--color-accent-soft);
    padding: 3px 0;
    font-size: 0.75rem;
    color: var(--color-primary-espresso);
    font-family: 'Lato', sans-serif;
}

.pm-rel-qty-btn {
    background: transparent;
    border: none;
    color: var(--color-accent-soft);
    font-size: 1rem;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
}

/* ── Related skeleton ── */
.pm-rel-card--skeleton {
    pointer-events: none;
}

.pm-skeleton-block {
    background: linear-gradient(90deg, var(--color-brown-89-2) 25%, var(--color-brown-84) 50%, var(--color-brown-89-2) 75%);
    background-size: 200% 100%;
    animation: pm-shimmer 1.4s infinite;
}

.pm-skeleton-line {
    border-radius: 2px;
    background: linear-gradient(90deg, var(--color-brown-89-2) 25%, var(--color-brown-84) 50%, var(--color-brown-89-2) 75%);
    background-size: 200% 100%;
    animation: pm-shimmer 1.4s infinite;
}

@keyframes pm-shimmer {
    from {
        background-position: 200% 0;
    }

    to {
        background-position: -200% 0;
    }
}

/* ── Lightbox ── */
.pm-lightbox {
    position: fixed;
    inset: 0;
    background: rgb(var(--rgb-black) / 0.9);
    z-index: 1100;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: zoom-out;
}

.pm-lightbox-img {
    max-width: 90vw;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 2px;
}

.pm-lightbox-close {
    position: absolute;
    top: 20px;
    right: 24px;
    background: rgb(var(--rgb-white) / 0.12);
    border: none;
    color: var(--color-white);
    font-size: 28px;
    line-height: 1;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.15s;
}

.pm-lightbox-close:hover {
    background: rgb(var(--rgb-white) / 0.22);
}

/* ── Responsive ── */
@media (max-width: 700px) {
    .pm-top {
        flex-direction: column;
        min-height: unset;
    }

    .pm-img-col {
        flex: none;
        height: 260px;
    }

    .pm-info-col {
        padding: 20px 18px 18px;
    }

    .pm-name {
        font-size: 1.4rem;
    }
}
</style>