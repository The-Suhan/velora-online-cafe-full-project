<script setup>
import { ref, watch, defineComponent, h } from 'vue'
import { useCart } from '~/composables/useCart'
const { locale, t } = useI18n()

const config = useRuntimeConfig()
const BACKEND_BASE = config.public.apiBase.replace(/\/api\/?$/, '')

const resolveUrl = (url) => {
    if (!url) return null
    if (url.startsWith('http')) return url
    return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
}

// ─── Props & emits ────────────────────────────────────────────
const props = defineProps({
    product: { type: Object, default: null },
    modelValue: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

function close() { emit('update:modelValue', false) }
function onOverlayClick(e) { if (e.target === e.currentTarget) close() }

// ─── Internal active product (switches when related card clicked) ──
const activeProduct = ref(null)

watch(() => props.product, (p) => {
    activeProduct.value = p
}, { immediate: true })

function selectRelated(rp) {
    activeProduct.value = rp
    lightboxOpen.value = false
}

// ─── Cart ─────────────────────────────────────────────────────
const { addItem, increaseQty, decreaseQty, getItem } = useCart()
function cartItem(id) { return getItem(id) }

// ─── Lightbox ─────────────────────────────────────────────────
const lightboxOpen = ref(false)

// ─── Related products ─────────────────────────────────────────
const api = useApi()
const relatedProducts = ref([])
const relatedLoading = ref(false)

watch(activeProduct, async (p) => {
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

// ─── StarRating component ─────────────────────────────────────
const StarRating = defineComponent({
    props: { score: { type: Number, default: 0 }, interactive: { type: Boolean, default: false } },
    emits: ['rate'],
    setup(props, { emit }) {
        const hovered = ref(null)
        function stars() {
            return Array.from({ length: 5 }, (_, i) => {
                const fill = Math.min(Math.max((props.score - i), 0), 1) * 100
                return h('button', {
                    class: 'star-wrap',
                    onClick: () => props.interactive && emit('rate', i + 1),
                    onMouseenter: () => { if (props.interactive) hovered.value = i + 1 },
                    onMouseleave: () => { if (props.interactive) hovered.value = null },
                }, [
                    h('svg', { class: 'star-svg', viewBox: '0 0 24 24' }, [
                        h('polygon', { points: '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26', fill: '#E8DDD0', stroke: 'none' }),
                    ]),
                    h('div', {
                        class: 'star-fill',
                        style: { width: `${hovered.value !== null && props.interactive ? (i < hovered.value ? 100 : 0) : fill}%` },
                    }, [
                        h('svg', { class: 'star-svg', viewBox: '0 0 24 24' }, [
                            h('polygon', { points: '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26', fill: '#C9A96E', stroke: 'none' }),
                        ]),
                    ]),
                ])
            })
        }
        return () => h('div', { class: 'pm-stars' }, stars())
    },
})

function getTranslation(item, loc, field) {
    if (!item?.translations) return ''
    const tr = item.translations
    const entry = Array.isArray(tr) ? tr.find(x => x.locale === loc) : tr[loc]
    return entry?.[field] ?? ''
}

function displayName(item) {
    return getTranslation(item, locale.value, 'name')
        || getTranslation(item, 'en', 'name')
        || item?.name || ''
}

function displayDesc(item) {
    return getTranslation(item, locale.value, 'description')
        || getTranslation(item, 'en', 'description')
        || item?.description || ''
}

// ─── Gradients fallback ───────────────────────────────────────
const gradients = [
    'linear-gradient(135deg,#C9A96E 0%,#9B7B3E 100%)',
    'linear-gradient(135deg,#D4C5A9 0%,#A8916B 100%)',
    'linear-gradient(135deg,#2C1A0E 0%,#5C3D1E 100%)',
    'linear-gradient(135deg,#B8924F 0%,#D4A853 100%)',
]
function cardGradient(id) { return gradients[id % gradients.length] }

const userRatings = ref({})

async function rateProduct(product, score) {
    try {
        const res = await api(`/products/${product.id}/rate`, { method: 'POST', body: { score } })
        userRatings.value[product.id] = res.score
        activeProduct.value = { ...activeProduct.value, avg_rating: res.avg_rating }
    } catch (e) {
        console.error(t('productModal.rateError'), e)
    }
}
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
                                <StarRating :score="userRatings[activeProduct.id] ?? activeProduct.avg_rating"
                                    :interactive="true" @rate="(s) => rateProduct(activeProduct, s)" />
                                <span class="pm-rating-val">
                                    {{ (userRatings[activeProduct.id] ?? activeProduct.avg_rating).toFixed(1) }}
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
    background: rgba(44, 26, 14, 0.6);
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
    background: #F5F0E8;
    width: 100%;
    max-width: 900px;
    border-radius: 2px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #C9A96E transparent;
}

/* ── Close button ── */
.pm-close {
    position: absolute;
    top: 10px;
    right: 14px;
    z-index: 10;
    background: rgba(245, 240, 232, 0.85);
    border: none;
    font-size: 22px;
    line-height: 1;
    color: #7A6652;
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
    background: #fff;
    color: #2C1A0E;
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
    background: rgba(245, 240, 232, 0.88);
    color: #2C1A0E;
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
    color: #9B7B3E;
    background: rgba(201, 169, 110, 0.15);
    padding: 4px 10px;
    font-family: 'Lato', sans-serif;
}

.pm-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.75rem;
    font-weight: 400;
    color: #2C1A0E;
    line-height: 1.2;
    margin: 0;
}

.pm-desc {
    font-size: 0.82rem;
    color: #7A6652;
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
    color: #8a6a50;
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
    background: #E8DDD0;
}

/* ── Meta ── */
.pm-meta-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.pm-meta-item {
    font-size: 0.7rem;
    color: #9a7a68;
    letter-spacing: 0.06em;
    font-family: 'Lato', sans-serif;
}

.pm-meta-item strong {
    color: #2C1A0E;
    font-weight: 500;
}

/* ── Price + cart ── */
.pm-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 0.5px solid #E8DDD0;
    margin-top: auto;
}

.pm-price {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2rem;
    color: #2C1A0E;
}

.pm-add-btn {
    background: #C9A96E;
    color: #fff;
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
    background: #b8924f;
}

.pm-qty-ctrl {
    display: flex;
    align-items: center;
    border: 1px solid #C9A96E;
}

.pm-qty-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #C9A96E;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}

.pm-qty-btn:hover {
    background: rgba(201, 169, 110, 0.12);
}

.pm-qty-num {
    width: 28px;
    text-align: center;
    font-size: 0.82rem;
    color: #2C1A0E;
    font-family: 'Lato', sans-serif;
}

/* ── Related section ── */
.pm-related {
    padding: 20px 24px 24px;
    border-top: 3px solid #C9A96E;
    background: #EDE8DE;
}

.pm-related-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.1rem;
    font-weight: 400;
    color: #2C1A0E;
    margin-bottom: 14px;
    letter-spacing: 0.01em;
}

.pm-related-empty {
    font-size: 0.78rem;
    color: #9a7a68;
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
    background: #fff;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 0.5px solid rgba(44, 26, 14, 0.08);
    transition: box-shadow 0.2s, transform 0.2s;
    cursor: pointer;
}

.pm-rel-card:hover {
    box-shadow: 0 4px 16px rgba(44, 26, 14, 0.12);
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
    color: #2C1A0E;
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pm-rel-price {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 0.85rem;
    color: #C9A96E;
}

.pm-rel-add {
    margin-top: auto;
    background: #C9A96E;
    color: #fff;
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
    background: #b8924f;
}

.pm-rel-qty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin-top: auto;
    border: 1px solid #C9A96E;
    padding: 3px 0;
    font-size: 0.75rem;
    color: #2C1A0E;
    font-family: 'Lato', sans-serif;
}

.pm-rel-qty-btn {
    background: transparent;
    border: none;
    color: #C9A96E;
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
    background: linear-gradient(90deg, #EDE5D8 25%, #E4DAC8 50%, #EDE5D8 75%);
    background-size: 200% 100%;
    animation: pm-shimmer 1.4s infinite;
}

.pm-skeleton-line {
    border-radius: 2px;
    background: linear-gradient(90deg, #EDE5D8 25%, #E4DAC8 50%, #EDE5D8 75%);
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
    background: rgba(0, 0, 0, 0.9);
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
    background: rgba(255, 255, 255, 0.12);
    border: none;
    color: #fff;
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
    background: rgba(255, 255, 255, 0.22);
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