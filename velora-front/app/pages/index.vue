<template>
    <main class="velora-page">

        <!-- Category Filter -->
        <section class="category-bar">
            <div class="category-bar-inner">
                <button @click="selectCategory(null)"
                    :class="['cat-btn', { 'cat-btn--active': selectedCategory === null }]">
                    All Products
                </button>
                <button v-for="cat in categories" :key="cat.id" @click="selectCategory(cat.id)"
                    :class="['cat-btn', { 'cat-btn--active': selectedCategory === cat.id }]">
                    {{ cat.name }}
                </button>
            </div>
        </section>

        <!-- Search + Sort row -->
        <section class="toolbar">
            <div class="toolbar-inner">
                <div class="sort-group">
                    <button v-for="s in sortOptions" :key="s.value" @click="selectSort(s.value)"
                        :class="['sort-btn', { 'sort-btn--active': currentSort === s.value }]">
                        {{ s.label }}
                    </button>
                </div>
                <span class="product-count" v-if="!loading">{{ total }} items</span>
            </div>
        </section>

        <!-- Grid -->
        <section class="product-grid-section">
            <div class="product-grid">

                <!-- Skeleton -->
                <template v-if="loading">
                    <div v-for="n in 12" :key="n" class="product-card product-card--skeleton">
                        <div class="card-image skeleton-block" />
                        <div class="card-body">
                            <div class="skeleton-line skeleton-line--title" />
                            <div class="skeleton-line skeleton-line--desc" />
                            <div class="skeleton-line skeleton-line--desc short" />
                        </div>
                    </div>
                </template>

                <!-- Cards -->
                <div v-else v-for="product in products" :key="product.id" class="product-card">
                    <!-- Image -->
                    <div class="card-image" :style="{ background: cardGradient(product.id) }">
                        <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="card-img" />
                        <span v-if="product.category?.name" class="card-badge">
                            {{ product.category.name }}
                        </span>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <h3 class="card-title">{{ product.name }}</h3>
                        <p class="card-desc">{{ product.description }}</p>

                        <!-- Stars -->
                        <div class="card-rating">
                            <StarRating :score="userRatings[product.id] ?? product.avg_rating" :interactive="true"
                                @rate="(s) => rateProduct(product, s)" />
                            <span class="rating-value">
                                {{ (userRatings[product.id] ?? product.avg_rating).toFixed(1) }}
                            </span>
                        </div>

                        <!-- Price + CTA -->
                        <div class="card-footer">
                            <span class="card-price">${{ Number(product.price).toFixed(2) }}</span>

                            <!-- Add button -->
                            <button v-if="!cartItem(product.id)" @click="addToCart(product)" class="add-btn">
                                + Add
                            </button>

                            <!-- Qty controls -->
                            <div v-else class="qty-ctrl">
                                <button class="qty-btn" @click="decreaseQty(product.id)">−</button>
                                <span class="qty-num">{{ cartItem(product.id).quantity }}</span>
                                <button class="qty-btn" @click="increaseQty(product.id)">+</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Empty -->
            <div v-if="!loading && products.length === 0" class="empty-state">
                <p>No products found.</p>
            </div>

            <!-- Pagination -->
            <div v-if="lastPage > 1" class="pagination">
                <button v-for="p in lastPage" :key="p" @click="goToPage(p)"
                    :class="['page-btn', { 'page-btn--active': currentPage === p }]">{{ p }}</button>
            </div>
        </section>

    </main>
</template>

<script setup>
import { ref, computed, onMounted, h, defineComponent } from 'vue'

definePageMeta({
    layout: 'client',
    middleware: 'auth'
})

useHead({
    link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        {
            rel: 'stylesheet',
            href: 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300&family=Lato:wght@300;400&display=swap'
        }
    ]
})

// ... StarRating component aynı kalıyor ...

// ─── State ───────────────────────────────────────────────────
const api = useApi()   // ← BU EKLENDİ

const loading = ref(false)
const categories = ref([])
const products = ref([])
const selectedCategory = ref(null)
const currentSort = ref('newest')
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const userRatings = ref({})

const cart = useState('cart', () => [])

const sortOptions = [
    { label: 'Newest', value: 'newest' },
    { label: 'Price ↑', value: 'price_asc' },
    { label: 'Price ↓', value: 'price_desc' },
    { label: 'Top Rated', value: 'rating' },
]

const gradients = [
    'linear-gradient(135deg,#C9A96E 0%,#9B7B3E 100%)',
    'linear-gradient(135deg,#D4C5A9 0%,#A8916B 100%)',
    'linear-gradient(135deg,#2C1A0E 0%,#5C3D1E 100%)',
    'linear-gradient(135deg,#B8924F 0%,#D4A853 100%)',
]
function cardGradient(id) { return gradients[id % gradients.length] }
function cartItem(productId) { return cart.value.find(i => i.product_id === productId) ?? null }

// ─── API ─────────────────────────────────────────────────────
async function fetchCategories() {
    try {
        categories.value = await api('/categories')   // ← değişti
    } catch (e) { console.error(e) }
}

async function fetchProducts(page = 1) {
    loading.value = true
    try {
        const params = { page, per_page: 12, sort: currentSort.value }
        const url = selectedCategory.value
            ? `/categories/${selectedCategory.value}/products`   // ← değişti
            : '/products'                                         // ← değişti

        const data = await api(url, { params })   // ← değişti
        products.value = data.data
        lastPage.value = data.last_page
        currentPage.value = data.current_page
        total.value = data.total
    } catch (e) { console.error(e) }
    finally { loading.value = false }
}

function selectCategory(id) {
    selectedCategory.value = id
    currentPage.value = 1
    fetchProducts(1)
}

function selectSort(val) {
    currentSort.value = val
    fetchProducts(1)
}

function goToPage(p) {
    fetchProducts(p)
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

// ─── Cart ─────────────────────────────────────────────────────
function addToCart(product) {
    const existing = cart.value.find(i => i.product_id === product.id)
    if (existing) { existing.quantity++; return }
    cart.value.push({
        product_id: product.id,
        product_name: product.name,
        image_url: product.image_url,
        price: product.price,
        quantity: 1,
    })
}

function increaseQty(id) {
    const item = cart.value.find(i => i.product_id === id)
    if (item) item.quantity = Math.min(item.quantity + 1, 99)
}

function decreaseQty(id) {
    const idx = cart.value.findIndex(i => i.product_id === id)
    if (idx === -1) return
    cart.value[idx].quantity <= 1
        ? cart.value.splice(idx, 1)
        : cart.value[idx].quantity--
}

// ─── Rating ───────────────────────────────────────────────────
async function rateProduct(product, score) {
    try {
        const res = await api(`/products/${product.id}/rate`, {   // ← değişti
            method: 'POST',
            body: { score },
        })
        userRatings.value[product.id] = res.score
        const p = products.value.find(p => p.id === product.id)
        if (p) p.avg_rating = res.avg_rating
    } catch (e) { console.error(e) }
}

// ─── Init ─────────────────────────────────────────────────────
onMounted(async () => {
    await fetchCategories()
    await fetchProducts()
})
</script>

<style scoped>
/* ── Page wrapper ── */
.velora-page {
    min-height: 100vh;
    background: #F5F0E8;
    font-family: 'Lato', sans-serif;
}

/* ── Category bar ── */
.category-bar {
    border-bottom: 1px solid rgba(26, 10, 0, 0.08);
    background: #F5F0E8;
    position: sticky;
    top: 72px;
    /* header height */
    z-index: 30;
}

.category-bar-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    scrollbar-width: none;
    height: 52px;
    align-items: center;
}

.category-bar-inner::-webkit-scrollbar {
    display: none;
}

.cat-btn {
    flex-shrink: 0;
    padding: 0.35rem 1.1rem;
    border: 1px solid rgba(201, 169, 110, 0.5);
    background: transparent;
    color: #2C1A0E;
    font-family: 'Lato', sans-serif;
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.18s, border-color 0.18s;
    white-space: nowrap;
}

.cat-btn:hover {
    background: rgba(201, 169, 110, 0.12);
}

.cat-btn--active {
    background: #2C1A0E;
    color: #F5F0E8;
    border-color: #2C1A0E;
}

/* ── Toolbar ── */
.toolbar {
    border-bottom: 1px solid rgba(26, 10, 0, 0.06);
    background: #F5F0E8;
}

.toolbar-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0.75rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.sort-group {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.sort-btn {
    padding: 0.3rem 0.9rem;
    border: 1px solid rgba(201, 169, 110, 0.45);
    background: transparent;
    color: #5a3e2b;
    font-family: 'Lato', sans-serif;
    font-size: 0.7rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s;
    border-radius: 2px;
}

.sort-btn:hover {
    background: rgba(201, 169, 110, 0.1);
}

.sort-btn--active {
    background: rgba(201, 169, 110, 0.2);
    border-color: #C9A96E;
    color: #2C1A0E;
}

.product-count {
    font-size: 0.72rem;
    color: #8a6a50;
    letter-spacing: 0.06em;
    white-space: nowrap;
}

/* ── Grid section ── */
.product-grid-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

/* ── Card ── */
.product-card {
    background: #fff;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(44, 26, 14, 0.07);
    transition: box-shadow 0.25s, transform 0.25s;
}

.product-card:hover {
    box-shadow: 0 6px 24px rgba(44, 26, 14, 0.12);
    transform: translateY(-2px);
}

/* Image */
.card-image {
    position: relative;
    height: 220px;
    overflow: hidden;
    flex-shrink: 0;
}

.card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
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
    font-size: 0.62rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    padding: 3px 10px;
    font-family: 'Lato', sans-serif;
}

/* Body */
.card-body {
    padding: 1.1rem 1.25rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    flex: 1;
}

.card-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.25rem;
    font-weight: 400;
    color: #2C1A0E;
    margin: 0;
    line-height: 1.2;
}

.card-desc {
    font-size: 0.78rem;
    color: #7A6652;
    line-height: 1.55;
    margin: 0;
    flex: 1;
}

/* Rating row */
.card-rating {
    display: flex;
    align-items: center;
    gap: 6px;
}

.rating-value {
    font-size: 0.72rem;
    color: #8a6a50;
}

/* Stars */
:deep(.stars) {
    display: flex;
    gap: 2px;
}

:deep(.star-wrap) {
    position: relative;
    width: 18px;
    height: 18px;
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
    padding-top: 0.6rem;
    border-top: 1px solid #F0E8D8;
    margin-top: auto;
}

.card-price {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.3rem;
    color: #2C1A0E;
}

/* Add button */
.add-btn {
    background: #C9A96E;
    color: #fff;
    border: none;
    padding: 0.45rem 1.1rem;
    font-family: 'Lato', sans-serif;
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.18s;
}

.add-btn:hover {
    background: #b8924f;
}

/* Qty controls */
.qty-ctrl {
    display: flex;
    align-items: center;
    border: 1px solid #C9A96E;
}

.qty-btn {
    width: 30px;
    height: 30px;
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
    width: 28px;
    text-align: center;
    font-size: 0.82rem;
    color: #2C1A0E;
    font-family: 'Lato', sans-serif;
}

/* ── Skeleton ── */
.product-card--skeleton {
    pointer-events: none;
}

.skeleton-block {
    height: 220px;
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
    margin-bottom: 8px;
}

.skeleton-line--title {
    height: 18px;
    width: 70%;
}

.skeleton-line--desc {
    width: 90%;
}

.skeleton-line.short {
    width: 55%;
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
    padding: 4rem 0;
    color: #8a6a50;
    font-size: 0.9rem;
    font-family: 'Lato', sans-serif;
}

/* ── Pagination ── */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 3rem;
}

.page-btn {
    width: 36px;
    height: 36px;
    border: 1px solid rgba(201, 169, 110, 0.5);
    background: transparent;
    color: #2C1A0E;
    font-family: 'Lato', sans-serif;
    font-size: 0.82rem;
    cursor: pointer;
    transition: background 0.15s;
}

.page-btn:hover {
    background: rgba(201, 169, 110, 0.12);
}

.page-btn--active {
    background: #2C1A0E;
    color: #F5F0E8;
    border-color: #2C1A0E;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
    .product-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .product-grid-section {
        padding: 1.25rem;
    }

    .category-bar-inner,
    .toolbar-inner {
        padding: 0 1.25rem;
    }
}

@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
}
</style>