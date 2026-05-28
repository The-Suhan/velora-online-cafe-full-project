<script setup>
import { ref, computed, watch, onMounted, defineComponent, h } from 'vue'
import { useCart } from '~/composables/useCart'

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

// ─── Route ────────────────────────────────────────────────────
const route = useRoute()
const categoryId = computed(() => route.params.id)

// ─── Cart ─────────────────────────────────────────────────────
const { addItem, increaseQty, decreaseQty, getItem } = useCart()
function cartItem(productId) { return getItem(productId) }

// ─── State ────────────────────────────────────────────────────
const api = useApi()
const loading = ref(true)
const category = ref(null)
const products = ref([])

// ─── Modal ────────────────────────────────────────────────────
const selectedProduct = ref(null)
const modalOpen = ref(false)

function openModal(product) {
    selectedProduct.value = product
    modalOpen.value = true
}

// ─── StarRating ───────────────────────────────────────────────
const StarRating = defineComponent({
    props: {
        score: { type: Number, default: 0 },
        interactive: { type: Boolean, default: false },
    },
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
                        h('polygon', {
                            points: '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26',
                            fill: '#E8DDD0', stroke: 'none',
                        }),
                    ]),
                    h('div', {
                        class: 'star-fill',
                        style: { width: `${hovered.value !== null && props.interactive ? (i < hovered.value ? 100 : 0) : fill}%` },
                    }, [
                        h('svg', { class: 'star-svg', viewBox: '0 0 24 24' }, [
                            h('polygon', {
                                points: '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26',
                                fill: '#C9A96E', stroke: 'none',
                            }),
                        ]),
                    ]),
                ])
            })
        }
        return () => h('div', { class: 'stars' }, stars())
    },
})

// ─── Gradients fallback ───────────────────────────────────────
const gradients = [
    'linear-gradient(135deg,#C9A96E 0%,#9B7B3E 100%)',
    'linear-gradient(135deg,#D4C5A9 0%,#A8916B 100%)',
    'linear-gradient(135deg,#2C1A0E 0%,#5C3D1E 100%)',
    'linear-gradient(135deg,#B8924F 0%,#D4A853 100%)',
]
function cardGradient(id) { return gradients[id % gradients.length] }

// ─── User ratings ─────────────────────────────────────────────
const userRatings = ref({})

async function rateProduct(product, score) {
    try {
        const res = await api(`/products/${product.id}/rate`, { method: 'POST', body: { score } })
        userRatings.value[product.id] = res.score
        const p = products.value.find(p => p.id === product.id)
        if (p) p.avg_rating = res.avg_rating
    } catch (e) { console.error(e) }
}

// ─── Load ─────────────────────────────────────────────────────
async function loadData() {
    loading.value = true
    try {
        const [catData, productsData] = await Promise.all([
            api(`/categories/${categoryId.value}`).catch(() => null),
            api(`/categories/${categoryId.value}/products`, {
                params: { per_page: 100, sort: 'newest' },
            }),
        ])
        category.value = catData
        products.value = productsData.data ?? []
    } catch (e) {
        console.error('[products page] load error:', e)
    } finally {
        loading.value = false
    }
}

onMounted(loadData)
watch(categoryId, loadData)
</script>

<template>
    <main class="velora-cat-page">

        <!-- ── Page Header ── -->
        <div class="page-header">
            <div class="page-header-inner">
                <NuxtLink to="/" class="breadcrumb-link">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 5l-7 7 7 7" />
                    </svg>
                    Home
                </NuxtLink>

                <div class="header-title-row">
                    <div>
                        <h1 class="page-title">
                            <template v-if="loading">
                                <div class="skeleton-line sk-title" />
                            </template>
                            <template v-else>
                                {{ category?.name ?? 'Products' }}
                            </template>
                        </h1>
                        <p class="page-subtitle" v-if="!loading">
                            {{ products.length }} item{{ products.length !== 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Decorative divider ── -->
        <div class="header-divider">
            <span class="divider-line" />
            <span class="divider-ornament">✦</span>
            <span class="divider-line" />
        </div>

        <!-- ── Skeleton grid ── -->
        <div v-if="loading" class="products-grid">
            <div v-for="n in 12" :key="n" class="product-card product-card--skeleton">
                <div class="card-image skeleton-block" />
                <div class="card-body">
                    <div class="skeleton-line sk-name" />
                    <div class="skeleton-line sk-desc" />
                    <div class="skeleton-line sk-short" />
                </div>
            </div>
        </div>

        <!-- ── Products grid ── -->
        <div v-else-if="products.length > 0" class="products-grid">
            <div v-for="(product, idx) in products" :key="product.id" class="product-card"
                :style="{ '--card-delay': `${idx * 25}ms` }">

                <!-- Image -->
                <div class="card-image" :style="{ background: cardGradient(product.id) }">
                    <img v-if="product.image_url" :src="resolveUrl(product.image_url)" :alt="product.name"
                        class="card-img" draggable="false" loading="lazy" />
                    <span v-if="product.category?.name" class="card-badge">
                        {{ product.category.name }}
                    </span>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <h3 class="card-title">{{ product.name }}</h3>
                    <p class="card-desc">{{ product.description }}</p>

                    <div class="card-rating">
                        <StarRating :score="userRatings[product.id] ?? product.avg_rating ?? 0" :interactive="true"
                            @rate="(s) => rateProduct(product, s)" />
                        <span class="rating-value">
                            {{ (userRatings[product.id] ?? product.avg_rating ?? 0).toFixed(1) }}
                        </span>
                    </div>

                    <div class="card-footer">
                        <span class="card-price">${{ Number(product.price).toFixed(2) }}</span>

                        <div class="card-actions">
                            <button class="detail-btn" @click.stop="openModal(product)">Detail</button>

                            <button v-if="!cartItem(product.id)" @click.stop="addItem(product)" class="add-btn">
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

        <!-- ── Empty state ── -->
        <div v-else class="empty-state">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C9A96E" stroke-width="1.2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
            </svg>
            <p>No products found.</p>
        </div>

    </main>

    <ProductModal v-model="modalOpen" :product="selectedProduct" />
</template>

<style scoped>
/* ── Page ── */
.velora-cat-page {
    min-height: 100vh;
    background: #F5F0E8;
    font-family: 'Lato', sans-serif;
    padding-bottom: 5rem;
}

/* ── Header ── */
.page-header {
    padding: 2.5rem 3.5rem 0;
}

.page-header-inner {
    max-width: 1400px;
    margin: 0 auto;
}

.breadcrumb-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.68rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #9a7a68;
    text-decoration: none;
    transition: color 0.15s;
    margin-bottom: 1.25rem;
}

.breadcrumb-link:hover {
    color: #C9A96E;
}

.header-title-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
    padding-bottom: 1.5rem;
}

.page-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2.4rem;
    font-weight: 400;
    color: #2C1A0E;
    margin: 0 0 0.25rem;
    letter-spacing: 0.01em;
    min-height: 2.9rem;
}

.page-subtitle {
    font-size: 0.7rem;
    color: #9a7a68;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin: 0;
}

/* ── Decorative divider ── */
.header-divider {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0 3.5rem;
    margin-bottom: 2.5rem;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
}

.divider-line {
    flex: 1;
    height: 0.5px;
    background: linear-gradient(to right, transparent, #C9A96E55, transparent);
}

.divider-ornament {
    font-size: 0.55rem;
    color: #C9A96E;
    letter-spacing: 0.2em;
}

/* ── Grid ── */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1.5rem;
    padding: 0 3.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* ── Card ── */
.product-card {
    background: #fff;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(44, 26, 14, 0.07);
    transition: box-shadow 0.25s, transform 0.25s;
    animation: cardIn 0.4s ease both;
    animation-delay: var(--card-delay, 0ms);
}

@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(14px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.product-card:hover {
    box-shadow: 0 6px 24px rgba(44, 26, 14, 0.13);
    transform: translateY(-3px);
}

/* Image */
.card-image {
    position: relative;
    height: 210px;
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
    height: 210px;
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
    height: 36px;
    width: 45%;
    border-radius: 3px;
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

@keyframes shimmer {
    from {
        background-position: 200% 0;
    }

    to {
        background-position: -200% 0;
    }
}

/* ── Empty state ── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 6rem 0;
    color: #8a6a50;
    font-size: 0.9rem;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
    .page-header {
        padding: 2rem 2.5rem 0;
    }

    .header-divider {
        padding: 0 2.5rem;
    }

    .products-grid {
        padding: 0 2.5rem;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    }
}

@media (max-width: 768px) {
    .page-header {
        padding: 1.5rem 1.5rem 0;
    }

    .header-divider {
        padding: 0 1.5rem;
        margin-bottom: 1.75rem;
    }

    .products-grid {
        padding: 0 1.5rem;
        gap: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    }

    .page-title {
        font-size: 1.85rem;
    }

    .header-title-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .card-image,
    .skeleton-block {
        height: 160px;
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
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        padding: 0 1rem;
        gap: 0.75rem;
    }

    .header-divider {
        padding: 0 1rem;
    }

    .page-header {
        padding: 1.25rem 1rem 0;
    }

    .card-image,
    .skeleton-block {
        height: 140px;
    }

    .card-title {
        font-size: 1rem;
    }

    .card-price {
        font-size: 1.05rem;
    }
}
</style>