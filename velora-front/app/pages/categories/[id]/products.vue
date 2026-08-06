<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useCart } from '~/composables/useCart'
import { onBeforeRouteLeave } from 'vue-router'

const config = useRuntimeConfig()
const { resolveUrl } = useMediaUrl()

definePageMeta({ layout: 'client', middleware: 'auth' })


// ─── Route ────────────────────────────────────────────────────
const route = useRoute()
const categoryId = computed(() => route.params.id)
const headerStyle = computed(() => {
    if (category.value?.image_url) {
        return { '--header-img': `url(${resolveUrl(category.value.image_url)})` }
    }
    return {}
})

// ─── Cart ─────────────────────────────────────────────────────
const { addItem, increaseQty, decreaseQty, getItem } = useCart()
function cartItem(productId) { return getItem(productId) }

// ─── State ────────────────────────────────────────────────────
const api = useApi()
const loading = ref(false)
const allCategories = useState('categories-list', () => [])

function findInCache(id) {
    for (const c of allCategories.value) {
        if (String(c.id) === String(id)) return c
        const child = c.children?.find(x => String(x.id) === String(id))
        if (child) return child
    }
    return null
}

const category = ref(findInCache(categoryId.value))
const productsCache = useState('category-products', () => ({}))
const products = computed(() => productsCache.value[categoryId.value] ?? [])

// ─── Modal ────────────────────────────────────────────────────
const selectedProduct = ref(null)
const modalOpen = ref(false)

// ─── Gradients fallback ───────────────────────────────────────
const gradients = [
    'linear-gradient(135deg,var(--color-accent-soft) 0%,var(--color-brown-43-2) 100%)',
    'linear-gradient(135deg,var(--color-brown-75) 0%,var(--color-brown-54-2) 100%)',
    'linear-gradient(135deg,var(--color-primary-espresso) 0%,var(--color-coffee-24) 100%)',
    'linear-gradient(135deg,var(--color-brown-52) 0%,var(--color-brown-58-2) 100%)',
]
function cardGradient(id) { return gradients[id % gradients.length] }

// ─── User ratings ─────────────────────────────────────────────
const userRatings = ref({})
const pendingRatings = ref({})

onBeforeRouteLeave(async () => {
    await flushPendingRatings()
})

function rateProduct(product, score) {
    const current = userRatings.value[product.id] !== undefined
        ? userRatings.value[product.id]
        : product.avg_rating ?? 0

    const isSameStar = current !== null && Math.round(current) === score
    const newScore = isSameStar ? null : score

    userRatings.value[product.id] = newScore
    pendingRatings.value[product.id] = { score: newScore, product }
}

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
                if (res?.avg_rating != null) {
                    const p = products.value.find(p => p.id === Number(productId))
                    if (p) p.avg_rating = res.avg_rating
                }
            } catch (e) {
                console.error(e)
            }
        })
    )
}

async function openModal(product) {
    await flushPendingRatings()
    selectedProduct.value = product
    modalOpen.value = true
}

function onRatingUpdated({ productId, avgRating, userScore }) {
    if (avgRating != null) {
        const p = products.value.find(p => p.id === productId)
        if (p) p.avg_rating = avgRating
    }
    userRatings.value[productId] = userScore ?? null
}

// ─── Load ─────────────────────────────────────────────────────
async function loadData() {
    const id = categoryId.value
    const cachedProducts = productsCache.value[id]
    const cachedCategory = findInCache(id)

    if (cachedProducts && cachedCategory) {
        category.value = cachedCategory
        loading.value = false
        return
    }

    loading.value = true
    try {
        const [catData, productsData] = await Promise.all([
            cachedCategory ? Promise.resolve(cachedCategory) : api(`/categories/${id}`).catch(() => null),
            cachedProducts ? Promise.resolve({ data: cachedProducts }) : api(`/categories/${id}/products`, {
                params: { per_page: 100, sort: 'newest' },
            }),
        ])
        category.value = catData
        productsCache.value = {
            ...productsCache.value,
            [id]: productsData.data ?? [],
        }
    } catch (e) {
        console.error('[products page] load error:', e)
    } finally {
        loading.value = false
    }
}
const { displayName, displayDesc } = useLocalized()

// Fonts are loaded once from nuxt.config, not per page.
useHead({
    title: computed(() => `Velora — ${displayName(category.value) || 'Products'}`),
})

onMounted(() => {
    loadData()
    window.addEventListener('beforeunload', () => {
        const entries = Object.entries(pendingRatings.value)
        entries.forEach(([productId, { score }]) => {
            navigator.sendBeacon(
                `${config.public.apiBase}/products/${productId}/rate`,
                new Blob([JSON.stringify({ score: score ?? null })], { type: 'application/json' })
            )
        })
    })
})
watch(categoryId, loadData)
</script>

<template>
    <main class="velora-cat-page">

        <!-- ── Page Header ── -->
        <div v-if="loading" class="page-header">
            <div class="header-inner">
                <div class="skeleton-breadcrumb" />
                <div class="skeleton-line sk-title" />
            </div>
        </div>

        <div v-else-if="category" class="page-header" :style="headerStyle">
            <div class="header-bg-overlay" />
            <div class="header-inner">
                <nav class="breadcrumb">
                    <button @click="$router.back()" class="breadcrumb-link">
                        <svg viewBox="0 0 20 20" fill="none" class="back-icon">
                            <path d="M13 16l-5-6 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        {{ $t('categoriesPage.back') }}
                    </button>
                </nav>
                <h1 class="page-title">{{ displayName(category) ?? 'Products' }}</h1>
                <p class="page-subtitle">
                    {{ $t('home.ProductitemsCount', products.length, { named: { n: products.length } }) }}
                </p>
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
                    <img v-if="product.image_url" :src="resolveUrl(product.image_url)" :alt="displayName(product)"
                        class="card-img" draggable="false" loading="lazy" />
                    <span v-if="product.category?.name" class="card-badge">
                        {{ product.category.name }}
                    </span>
                    <span v-if="product.has_discount" class="discount-badge">
                        -{{ product.discount_percent }}%
                    </span>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <h3 class="card-title">{{ displayName(product) }}</h3>
                    <p class="card-desc">{{ displayDesc(product) }}</p>

                    <div class="card-rating">
                        <StarRating
                            :score="(userRatings[product.id] !== undefined ? userRatings[product.id] : product.avg_rating) ?? 0"
                            :interactive="true" @rate="(s) => rateProduct(product, s)" />
                        <span class="rating-value">
                            {{ ((userRatings[product.id] !== undefined ? userRatings[product.id] : product.avg_rating)
                                ?? 0).toFixed(1) }}
                        </span>
                    </div>

                    <div class="card-footer">
                        <span v-if="product.has_discount" class="card-price card-price--discount">
                            <span class="card-price-old">${{ Number(product.price).toFixed(2) }}</span>
                            <span class="card-price-new">${{ Number(product.final_price).toFixed(2) }}</span>
                        </span>
                        <span v-else class="card-price">${{ Number(product.price).toFixed(2) }}</span>

                        <div class="card-actions">
                            <button class="detail-btn" @click.stop="openModal(product)">{{ $t('home.detail') }}</button>

                            <button v-if="!cartItem(product.id)" @click.stop="addItem(product)" class="add-btn">
                                {{ $t('home.addToCart') }}
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
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent-soft)" stroke-width="1.2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
            </svg>
            <p>{{ $t('home.noProducts') }}</p>
        </div>

    </main>

    <ProductModal v-model="modalOpen" :product="selectedProduct" @rating-updated="onRatingUpdated" />
</template>

<style scoped>
/* ── Page ── */
.velora-cat-page {
    min-height: 100vh;
    background: var(--color-surface-warm);
    font-family: 'Lato', sans-serif;
    padding-bottom: 5rem;
}

/* ── Header ── */
.page-header {
    background: var(--color-brand-surface);
    padding: 1.75rem 3.5rem 2rem;
    position: relative;
    overflow: hidden;
    min-height: 140px;
}

.page-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: var(--header-img, none);
    background-size: cover;
    background-position: center;
    opacity: 0.18;
    pointer-events: none;
}

.header-bg-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgb(var(--rgb-scrim) / 0.85) 0%, rgb(var(--rgb-scrim) / 0.5) 100%);
    pointer-events: none;
}

.header-inner {
    max-width: 1400px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.breadcrumb {
    margin-bottom: 0.75rem;
}

.breadcrumb-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.68rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--color-accent-soft);
    text-decoration: none;
    transition: color 0.15s;
}

.breadcrumb-link:hover {
    color: var(--color-on-brand);
}

.back-icon {
    width: 16px;
    height: 16px;
}

.page-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2.4rem;
    font-weight: 400;
    color: var(--color-on-brand);
    margin: 0 0 0.25rem;
    letter-spacing: 0.01em;
    min-height: 2.9rem;
}

.page-subtitle {
    font-size: 0.7rem;
    color: rgb(var(--rgb-on-brand) / 0.65);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin: 0;
}

/* skeleton içindeki header için */
.skeleton-breadcrumb {
    height: 12px;
    width: 100px;
    background: rgb(var(--rgb-accent) / 0.25);
    border-radius: 3px;
    margin-bottom: 0.75rem;
    animation: shimmer 1.4s infinite;
}

/* ── Responsive header padding ── */
@media (max-width: 1024px) {
    .page-header {
        padding: 1.75rem 2.5rem 2rem;
    }
}

@media (max-width: 768px) {
    .page-header {
        padding: 1.5rem 1.5rem 1.75rem;
    }

    .page-title {
        font-size: 1.85rem;
    }
}

@media (max-width: 480px) {
    .page-header {
        padding: 1.25rem 1rem 1.5rem;
    }
}

/* ── Decorative divider ── */
.header-divider {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 1.25rem 3.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.divider-line {
    flex: 1;
    height: 0.5px;
    background: linear-gradient(to right, transparent, var(--color-accent-soft-a33), transparent);
}

.divider-ornament {
    font-size: 0.55rem;
    color: var(--color-accent-soft);
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
    background: var(--color-card);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 1px 4px rgb(var(--rgb-primary-espresso) / 0.07);
    transition: box-shadow 0.35s var(--ease-smooth), transform 0.35s var(--ease-smooth);
    animation: cardIn 0.55s var(--ease-smooth) both;
    animation-delay: var(--card-delay, 0ms);
}

@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(22px) scale(0.96);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.product-card:hover {
    box-shadow: 0 6px 24px rgb(var(--rgb-primary-espresso) / 0.13);
    transform: translateY(-4px);
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
    background: rgb(var(--rgb-white) / 0.92);
    color: var(--color-primary-espresso);
    font-size: 0.6rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    padding: 3px 10px;
    font-family: 'Lato', sans-serif;
}

.discount-badge {
    position: absolute;
    top: 10px;
    left: 12px;
    background: var(--color-danger, #c0392b);
    color: var(--color-white);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    padding: 4px 9px;
    border-radius: 3px;
    font-family: 'Lato', sans-serif;
    z-index: 2;
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
    color: var(--color-primary-espresso);
    margin: 0;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.breadcrumb-link {
    background: transparent;
    border: none;
    cursor: pointer;
    font-family: 'Lato', sans-serif;
    padding: 0;
}

.card-desc {
    font-size: 0.75rem;
    color: var(--color-brown-40);
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
    color: var(--color-brown-43);
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
/* Price on its own line, actions underneath — see the note in index.vue. */
.card-footer {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding-top: 0.55rem;
    border-top: 1px solid var(--color-gold-89-2);
    margin-top: auto;
    gap: 0.4rem;
}

.card-price {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.2rem;
    color: var(--color-primary-espresso);
}

.card-price--discount {
    display: inline-flex;
    align-items: baseline;
    gap: 6px;
}

.card-price-old {
    font-size: 0.85rem;
    color: var(--color-brown-51);
    text-decoration: line-through;
    font-family: 'Lato', sans-serif;
}

.card-price-new {
    font-family: 'Cormorant Garamond', Georgia, serif;
    color: var(--color-danger, #c0392b);
}

.card-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: nowrap;
    justify-content: flex-end;
}

.detail-btn {
    background: transparent;
    color: var(--color-accent-soft);
    border: 1px solid var(--color-accent-soft);
    padding: 0.4rem 0.7rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.68rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    white-space: nowrap;
    cursor: pointer;
    transition: background 0.18s, color 0.18s;
}

.detail-btn:hover {
    background: var(--color-accent-soft);
    color: var(--color-white);
}

.add-btn {
    background: var(--color-accent-soft);
    color: var(--color-white);
    border: none;
    padding: 0.4rem 0.7rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.68rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    white-space: nowrap;
    cursor: pointer;
    transition: background 0.18s;
}

.add-btn:hover {
    background: var(--color-brown-52);
}

.qty-ctrl {
    display: flex;
    align-items: center;
    border: 1px solid var(--color-accent-soft);
}

.qty-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    color: var(--color-accent-soft);
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}

.qty-btn:hover {
    background: rgb(var(--rgb-accent-soft) / 0.12);
}

.qty-num {
    width: 26px;
    text-align: center;
    font-size: 0.8rem;
    color: var(--color-primary-espresso);
    font-family: 'Lato', sans-serif;
}

/* ── Skeleton ── */
.product-card--skeleton {
    pointer-events: none;
}

.skeleton-block {
    height: 210px;
    background: linear-gradient(90deg, var(--color-brown-89-2) 25%, var(--color-brown-84) 50%, var(--color-brown-89-2) 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
}

.skeleton-line {
    height: 12px;
    border-radius: 2px;
    background: linear-gradient(90deg, var(--color-brown-89-2) 25%, var(--color-brown-84) 50%, var(--color-brown-89-2) 75%);
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
    color: var(--color-brown-43);
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
        margin-top: 1rem;
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
        align-items: stretch;
        gap: 4px;
    }

    .detail-btn,
    .add-btn {
        width: 100%;
        text-align: center;
        padding: 0.4rem 0.5rem;
        font-size: 0.62rem;
        box-sizing: border-box;
    }

    .qty-ctrl {
        width: 100%;
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
        margin-top: 1.25rem;
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