<script setup>
import { ref, watch, onMounted, defineComponent, h } from 'vue'
import { useCart } from '~/composables/useCart'
import { useSearch } from '~/composables/useSearch'

const config = useRuntimeConfig()
const BACKEND_BASE = config.public.apiBase.replace(/\/api\/?$/, '')

const resolveUrl = (url) => {
    if (!url) return null
    if (url.startsWith('http')) return url
    return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
}

definePageMeta({ layout: 'client', middleware: 'auth' })

useHead({
    link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        {
            rel: 'stylesheet',
            href: 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300&family=Lato:wght@300;400&display=swap',
        },
    ],
})

const selectedProduct = ref(null)
const modalOpen = ref(false)

function openModal(product) {
    selectedProduct.value = product
    modalOpen.value = true
}

// ─── Cart ─────────────────────────────────────────────────────
const { addItem, increaseQty, decreaseQty, getItem } = useCart()

// ─── Search ───────────────────────────────────────────────────
const { searchQuery } = useSearch()

// ─── State ────────────────────────────────────────────────────
const api = useApi()
const loading = ref(true)

const allCategoryRows = ref([])
const categoryRows = ref([])

watch(searchQuery, (q) => {
    const term = q.trim().toLowerCase()
    if (!term) {
        categoryRows.value = allCategoryRows.value
        return
    }
    categoryRows.value = allCategoryRows.value
        .map(cat => ({
            ...cat,
            products: cat.products.filter(p =>
                p.name?.toLowerCase().includes(term) ||
                p.description?.toLowerCase().includes(term)
            )
        }))
        .filter(cat => cat.products.length > 0)
})

// ─── StarRating ───────────────────────────────────────────────
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
                    h('div', { class: 'star-fill', style: { width: `${hovered.value !== null && props.interactive ? (i < hovered.value ? 100 : 0) : fill}%` } }, [
                        h('svg', { class: 'star-svg', viewBox: '0 0 24 24' }, [
                            h('polygon', { points: '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26', fill: '#C9A96E', stroke: 'none' }),
                        ]),
                    ]),
                ])
            })
        }
        return () => h('div', { class: 'stars' }, stars())
    },
})

// ─── Carousel refs ────────────────────────────────────────────
const trackRefs = ref({})

function setTrackRef(el, catId) {
    if (el) trackRefs.value[catId] = el
}

const dragState = {}

function initDrag(catId) {
    dragState[catId] = { down: false, startX: 0, scrollLeft: 0 }
}

function onMouseDown(e, catId) {
    if (!dragState[catId]) initDrag(catId)
    const track = trackRefs.value[catId]
    if (!track) return
    dragState[catId].down = true
    dragState[catId].startX = e.pageX - track.offsetLeft
    dragState[catId].scrollLeft = track.scrollLeft
    track.style.cursor = 'grabbing'
    track.style.userSelect = 'none'
}

function onMouseMove(e, catId) {
    if (!dragState[catId]?.down) return
    const track = trackRefs.value[catId]
    if (!track) return
    e.preventDefault()
    const x = e.pageX - track.offsetLeft
    const walk = (x - dragState[catId].startX) * 1.2
    track.scrollLeft = dragState[catId].scrollLeft - walk
}

function onMouseUp(catId) {
    if (!dragState[catId]) return
    dragState[catId].down = false
    const track = trackRefs.value[catId]
    if (track) { track.style.cursor = 'grab'; track.style.userSelect = '' }
}

function scrollCarousel(catId, dir) {
    const track = trackRefs.value[catId]
    if (!track) return
    const cardW = track.querySelector('.product-card')?.offsetWidth ?? 300
    track.scrollBy({ left: dir * (cardW * 3 + 24), behavior: 'smooth' })
}

// ─── API ──────────────────────────────────────────────────────
const userRatings = ref({})

async function loadAll() {
    loading.value = true
    try {
        const mainCats = await api('/categories')
        const subCats = mainCats.flatMap(c => Array.isArray(c.children) ? c.children : [])
        const targetCats = subCats.length > 0 ? subCats : mainCats

        const rows = await Promise.all(
            targetCats.map(async (cat) => {
                try {
                    const data = await api(`/categories/${cat.id}/products`, {
                        params: { per_page: 20, sort: 'newest' },
                    })
                    return { ...cat, products: data.data ?? [] }
                } catch {
                    return { ...cat, products: [] }
                }
            })
        )

        const filtered = rows.filter(r => r.products.length > 0)
        allCategoryRows.value = filtered
        categoryRows.value = filtered
    } catch (e) {
        console.error('[loadAll] error:', e)
    } finally {
        loading.value = false
    }
}

async function rateProduct(product, score) {
    try {
        const res = await api(`/products/${product.id}/rate`, { method: 'POST', body: { score } })
        userRatings.value[product.id] = res.score
        for (const row of categoryRows.value) {
            const p = row.products.find(p => p.id === product.id)
            if (p) p.avg_rating = res.avg_rating
        }
    } catch (e) { console.error(e) }
}

function cartItem(productId) { return getItem(productId) }

const gradients = [
    'linear-gradient(135deg,#C9A96E 0%,#9B7B3E 100%)',
    'linear-gradient(135deg,#D4C5A9 0%,#A8916B 100%)',
    'linear-gradient(135deg,#2C1A0E 0%,#5C3D1E 100%)',
    'linear-gradient(135deg,#B8924F 0%,#D4A853 100%)',
]
function cardGradient(id) { return gradients[id % gradients.length] }

onMounted(loadAll)
</script>

<template>
    <main class="velora-page">

        <!-- ── Loading skeleton ── -->
        <template v-if="loading">
            <div v-for="n in 3" :key="n" class="category-section">
                <div class="section-head">
                    <div class="skeleton-line sk-title" />
                    <div class="skeleton-line sk-btn" />
                </div>
                <div class="carousel-wrap">
                    <div class="carousel-track no-scroll">
                        <div v-for="k in 5" :key="k" class="product-card product-card--skeleton">
                            <div class="card-image skeleton-block" />
                            <div class="card-body">
                                <div class="skeleton-line sk-name" />
                                <div class="skeleton-line sk-desc" />
                                <div class="skeleton-line sk-short" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ── Category rows ── -->
        <template v-else>
            <section v-for="(cat, rowIdx) in categoryRows" :key="cat.id" class="category-section"
                :style="{ '--row-delay': `${rowIdx * 60}ms` }">
                <!-- Section header -->
                <div class="section-head">
                    <div class="section-head-left">
                        <h2 class="section-title">{{ cat.name }}</h2>
                        <span class="section-count">{{ cat.products.length }} items</span>
                    </div>
                    <NuxtLink :to="`/categories/${cat.id}`" class="see-all-btn">
                        See all
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </NuxtLink>
                </div>

                <!-- Carousel -->
                <div class="carousel-wrap">
                    <!-- Left arrow -->
                    <button class="carousel-arrow carousel-arrow--left" @click="scrollCarousel(cat.id, -1)"
                        aria-label="Scroll left">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                    </button>

                    <!-- Track -->
                    <div class="carousel-track" :ref="el => setTrackRef(el, cat.id)"
                        @mousedown="onMouseDown($event, cat.id)" @mousemove="onMouseMove($event, cat.id)"
                        @mouseup="onMouseUp(cat.id)" @mouseleave="onMouseUp(cat.id)">
                        <div v-for="(product, cardIdx) in cat.products" :key="product.id" class="product-card"
                            :style="{ '--card-delay': `${rowIdx * 60 + cardIdx * 30}ms` }">
                            <!-- Image -->
                            <div class="card-image" :style="{ background: cardGradient(product.id) }">
                                <img v-if="product.image_url" :src="resolveUrl(product.image_url)" :alt="product.name"
                                    class="card-img" draggable="false" />
                                <span v-if="product.category?.name" class="card-badge">
                                    {{ product.category.name }}
                                </span>
                            </div>

                            <!-- Body -->
                            <div class="card-body">
                                <h3 class="card-title">{{ product.name }}</h3>
                                <p class="card-desc">{{ product.description }}</p>

                                <div class="card-rating">
                                    <StarRating :score="userRatings[product.id] ?? product.avg_rating"
                                        :interactive="true" @rate="(s) => rateProduct(product, s)" />
                                    <span class="rating-value">
                                        {{ (userRatings[product.id] ?? product.avg_rating).toFixed(1) }}
                                    </span>
                                </div>

                                <div class="card-footer">
                                    <span class="card-price">${{ Number(product.price).toFixed(2) }}</span>

                                    <div class="card-actions">
                                        <button class="detail-btn" @click.stop="openModal(product)">Detail</button>

                                        <button v-if="!cartItem(product.id)" @click.stop="addItem(product)"
                                            class="add-btn">
                                            + Add
                                        </button>
                                        <div v-else class="qty-ctrl">
                                            <button class="qty-btn" @click.stop="decreaseQty(product.id)">−</button>
                                            <span class="qty-num">{{ cartItem(product.id).quantity }}</span>
                                            <button class="qty-btn" @click.stop="increaseQty(product.id)">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right arrow -->
                    <button class="carousel-arrow carousel-arrow--right" @click="scrollCarousel(cat.id, 1)"
                        aria-label="Scroll right">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </button>
                </div>
            </section>

            <!-- Empty state -->
            <div v-if="categoryRows.length === 0" class="empty-state">
                <p>No products available.</p>
            </div>
        </template>
    </main>
    <ProductModal v-model="modalOpen" :product="selectedProduct" />
</template>

<style scoped>
/* ── Page ── */
.velora-page {
    min-height: 100vh;
    background: #F5F0E8;
    font-family: 'Lato', sans-serif;
    padding-bottom: 4rem;
}

/* ── Category section ── */
.category-section {
    padding: 2.5rem 0 0;
    animation: fadeUp 0.45s ease both;
    animation-delay: var(--row-delay, 0ms);
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(18px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ── Section head ── */
.section-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    padding: 0 3.5rem 1rem;
    gap: 1rem;
}

.section-head-left {
    display: flex;
    align-items: baseline;
    gap: 0.85rem;
}

.section-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.65rem;
    font-weight: 400;
    color: #2C1A0E;
    margin: 0;
    letter-spacing: 0.01em;
}

.section-count {
    font-size: 0.7rem;
    color: #9a7a68;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.see-all-btn {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.7rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #C9A96E;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: color 0.15s, border-color 0.15s;
    white-space: nowrap;
    flex-shrink: 0;
}

.see-all-btn:hover {
    color: #b8924f;
    border-bottom-color: #b8924f;
}

/* ── Carousel wrap ── */
.carousel-wrap {
    position: relative;
}

/* Arrow buttons */
.carousel-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid rgba(44, 26, 14, 0.15);
    background: #F5F0E8;
    color: #2C1A0E;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 12px rgba(44, 26, 14, 0.12);
    transition: background 0.18s, box-shadow 0.18s, opacity 0.18s;
}

.carousel-arrow:hover {
    background: #fff;
    box-shadow: 0 4px 20px rgba(44, 26, 14, 0.18);
}

.carousel-arrow--left {
    left: 16px;
}

.carousel-arrow--right {
    right: 16px;
}

/* ── Track ── */
.carousel-track {
    display: flex;
    gap: 1.25rem;
    overflow-x: auto;
    padding: 0.5rem 3.5rem 1.5rem;
    scroll-snap-type: x mandatory;
    scroll-padding-left: 3.5rem;
    cursor: grab;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    box-sizing: border-box;
}

.carousel-track::after {
    content: '';
    flex: 0 0 2.25rem;
    display: block;
}

.carousel-track::-webkit-scrollbar {
    display: none;
}

.carousel-track.no-scroll {
    overflow: hidden;
}

/* ── Card ── */
.product-card {
    flex: 0 0 260px;
    width: 260px;
    background: #fff;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    scroll-snap-align: start;
    box-shadow: 0 1px 4px rgba(44, 26, 14, 0.07);
    transition: box-shadow 0.25s, transform 0.25s;
    animation: cardIn 0.4s ease both;
    animation-delay: var(--card-delay, 0ms);
}

@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(12px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.product-card:hover {
    box-shadow: 0 6px 24px rgba(44, 26, 14, 0.13);
    transform: translateY(-2px);
}

/* Image */
.card-image {
    position: relative;
    height: 190px;
    overflow: hidden;
    flex-shrink: 0;
}

.card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
    pointer-events: none;
}

.product-card:hover .card-img {
    transform: scale(1.05);
}

.card-badge {
    position: absolute;
    bottom: 10px;
    left: 12px;
    background: rgba(255, 255, 255, 0.92);
    color: #2C1A0E;
    font-size: 0.6rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    padding: 3px 10px;
    font-family: 'Lato', sans-serif;
}

/* Body */
.card-body {
    padding: 1rem 1.15rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex: 1;
}

.card-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.15rem;
    font-weight: 400;
    color: #2C1A0E;
    margin: 0;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-desc {
    font-size: 0.75rem;
    color: #7A6652;
    line-height: 1.5;
    margin: 0;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-rating {
    display: flex;
    align-items: center;
    gap: 6px;
}

.rating-value {
    font-size: 0.7rem;
    color: #8a6a50;
}

:deep(.stars) {
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
    cursor: pointer;
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

/* Footer */
.card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.55rem;
    border-top: 1px solid #F0E8D8;
    margin-top: auto;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.card-price {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.2rem;
    color: #2C1A0E;
}

.add-btn {
    background: #C9A96E;
    color: #fff;
    border: none;
    padding: 0.4rem 1rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.68rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.18s;
}

.add-btn:hover {
    background: #b8924f;
}

.qty-ctrl {
    display: flex;
    align-items: center;
    border: 1px solid #C9A96E;
}

.qty-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    color: #C9A96E;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}

.qty-btn:hover {
    background: rgba(201, 169, 110, 0.12);
}

.qty-num {
    width: 26px;
    text-align: center;
    font-size: 0.8rem;
    color: #2C1A0E;
    font-family: 'Lato', sans-serif;
}

/* ── Skeleton ── */
.product-card--skeleton {
    pointer-events: none;
}

.skeleton-block {
    height: 190px;
    background: linear-gradient(90deg, #EDE5D8 25%, #E4DAC8 50%, #EDE5D8 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
}

.skeleton-line {
    height: 12px;
    border-radius: 2px;
    background: linear-gradient(90deg, #EDE5D8 25%, #E4DAC8 50%, #EDE5D8 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
}

.sk-title {
    height: 22px;
    width: 55%;
    margin-bottom: 0;
}

.sk-btn {
    height: 16px;
    width: 60px;
}

.sk-name {
    height: 16px;
    width: 70%;
    margin-bottom: 6px;
}

.sk-desc {
    width: 90%;
    margin-bottom: 6px;
}

.sk-short {
    width: 50%;
}

.card-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.detail-btn {
    background: transparent;
    color: #C9A96E;
    border: 1px solid #C9A96E;
    padding: 0.4rem 0.75rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.68rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.18s, color 0.18s;
}

.detail-btn:hover {
    background: #C9A96E;
    color: #fff;
}

@keyframes shimmer {
    from {
        background-position: 200% 0;
    }

    to {
        background-position: -200% 0;
    }
}

/* ── Empty ── */
.empty-state {
    text-align: center;
    padding: 5rem 0;
    color: #8a6a50;
    font-size: 0.9rem;
}

/* ── Responsive ── */

/* Tablet */
@media (max-width: 1024px) {
    .product-card {
        flex: 0 0 230px;
        width: 230px;
    }

    .section-head {
        padding: 0 2.5rem 0.85rem;
    }

    .carousel-track {
        padding: 0.5rem 2.5rem 1.5rem;
    }

    .carousel-arrow--left {
        left: 10px;
    }

    .carousel-arrow--right {
        right: 10px;
    }
}

/* Mobile: arrows gizle */
@media (max-width: 768px) {
    .carousel-arrow {
        display: none;
    }

    .product-card {
        flex: 0 0 200px;
        width: 200px;
    }

    .card-image {
        height: 160px;
    }

    .section-head {
        padding: 0 1.5rem 0.75rem;
    }

    .carousel-track {
        padding: 0.5rem 1.5rem 1.25rem;
        gap: 1rem;
    }

    .section-title {
        font-size: 1.35rem;
    }

    .card-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .card-actions {
        width: 100%;
        justify-content: space-between;
    }

    .detail-btn,
    .add-btn {
        flex: 1;
        text-align: center;
        padding: 0.45rem 0.5rem;
        font-size: 0.62rem;
    }

    .qty-ctrl {
        flex: 1;
        justify-content: space-between;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
    }
}

@media (max-width: 480px) {
    .product-card {
        flex: 0 0 175px;
        width: 175px;
    }

    .card-image {
        height: 140px;
    }

    .card-title {
        font-size: 1rem;
    }

    .card-price {
        font-size: 1.05rem;
    }

    .add-btn {
        padding: 0.35rem 0.75rem;
        font-size: 0.64rem;
    }

    .detail-btn,
    .add-btn {
        padding: 0.4rem 0.4rem;
        font-size: 0.6rem;
    }

    .qty-btn {
        width: 30px;
        height: 30px;
        font-size: 1.1rem;
    }

    .qty-num {
        font-size: 0.75rem;
    }
}
</style>