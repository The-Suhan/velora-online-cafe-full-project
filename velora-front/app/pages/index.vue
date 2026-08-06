<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useCart } from '~/composables/useCart'
import { useProductSearch } from '~/composables/useProductSearch'
import { useSearch } from '~/composables/useSearch'
import { onBeforeRouteLeave } from 'vue-router'

// ─── Ratings flush — route değişince API'ye gönder ────────────
onBeforeRouteLeave(async () => {
    await flushPendingRatings()
})

const imageLoaded = ref({})
function onImageLoad(productId) {
    imageLoaded.value[productId] = true
}

const config = useRuntimeConfig()
const { resolveUrl } = useMediaUrl()
const { displayName, displayDesc } = useLocalized()
const { t } = useI18n()

definePageMeta({ layout: 'client', middleware: 'auth' })

// Font families are declared once in nuxt.config so the browser fetches a
// single stylesheet instead of a second render-blocking one per page.
useHead({ title: 'Velora — Menu' })

const selectedProduct = ref(null)
const modalOpen = ref(false)

async function openModal(product) {
    await flushPendingRatings()
    selectedProduct.value = product
    modalOpen.value = true
}

// ─── Cart ─────────────────────────────────────────────────────
const { addItem, increaseQty, decreaseQty, getItem } = useCart()

// ─── State ────────────────────────────────────────────────────
const api = useApi()
const { show: showLoading, hide: hideLoading } = useAppLoading()

const allCategoryRows = useState('home-category-rows', () => [])
const categoryRows = useState('home-category-rows-filtered', () => [])

const loading = ref(allCategoryRows.value.length === 0)

// ─── Search & filters (backend-powered) ────────────────────────
// The header's search icon lives on this page only; typing there updates
// this same shared ref, and the filter bar below adds price/category/
// discount/sort on top of it. When nothing is active we show the normal
// carousel rows from /home; otherwise we switch to a flat, paginated
// results grid from /products.
const { searchQuery } = useSearch()
const { fetchProducts } = useProductSearch()

const minPrice = ref('')
const maxPrice = ref('')
const filterCategoryId = ref('')
const onDiscount = ref(false)
const sort = ref('newest')
const showFilters = ref(false)

const isFiltering = computed(() =>
    searchQuery.value.trim() !== '' ||
    minPrice.value !== '' ||
    maxPrice.value !== '' ||
    filterCategoryId.value !== '' ||
    onDiscount.value ||
    sort.value !== 'newest'
)

const filteredProducts = ref([])
const filteredLoading = ref(false)
const filteredPagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
let filterTimeout

const allCategories = useState('categories-list', () => [])
const flatCategories = computed(() => {
    const flat = []
    for (const c of allCategories.value) {
        flat.push(c)
        for (const child of c.children ?? []) flat.push(child)
    }
    return flat
})

async function loadCategoriesIfNeeded() {
    if (allCategories.value.length > 0) return
    try {
        allCategories.value = await api('/categories') ?? []
    } catch (e) {
        console.error('[home] categories load error:', e)
    }
}

const visibleFilterPages = computed(() => {
    const cur = filteredPagination.value.current_page
    const last = filteredPagination.value.last_page
    if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
    const pages = [1]
    if (cur > 3) pages.push('...')
    for (let i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) pages.push(i)
    if (cur < last - 2) pages.push('...')
    pages.push(last)
    return pages
})

async function loadFiltered(page = 1) {
    filteredLoading.value = true
    try {
        const params = { search: searchQuery.value.trim(), sort: sort.value, per_page: 12, page }
        if (filterCategoryId.value) params.category_id = filterCategoryId.value
        if (minPrice.value !== '') params.min_price = minPrice.value
        if (maxPrice.value !== '') params.max_price = maxPrice.value
        if (onDiscount.value) params.on_discount = 1

        const data = await fetchProducts(params)
        filteredProducts.value = data.data ?? []
        filteredPagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            from: data.from ?? 0,
            to: data.to ?? 0,
        }
    } catch (e) {
        console.error('[home] filtered load error:', e)
        filteredProducts.value = []
    } finally {
        filteredLoading.value = false
    }
}

function goToFilteredPage(page) { loadFiltered(page) }

function resetFilters() {
    minPrice.value = ''
    maxPrice.value = ''
    filterCategoryId.value = ''
    onDiscount.value = false
    sort.value = 'newest'
    showFilters.value = false
}

watch([searchQuery, minPrice, maxPrice, filterCategoryId, onDiscount, sort], () => {
    clearTimeout(filterTimeout)
    filterTimeout = setTimeout(() => {
        if (isFiltering.value) loadFiltered(1)
    }, 400)
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

const userRatings = ref({})
const pendingRatings = ref({})

async function loadAll() {
    loading.value = true
    try {
        // One request for every carousel row. This previously fetched
        // /categories and then /categories/{id}/products once per category.
        const rows = await api('/home')
        allCategoryRows.value = rows
        categoryRows.value = rows
    } catch (e) {
        console.error(t('home.loadError'), e)
    } finally {
        loading.value = false
        hideLoading()
    }
}

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
                    for (const row of allCategoryRows.value) {
                        const p = row.products.find(p => p.id === Number(productId))
                        if (p) p.avg_rating = res.avg_rating
                    }
                    const fp = filteredProducts.value.find(p => p.id === Number(productId))
                    if (fp) fp.avg_rating = res.avg_rating
                }
            } catch (e) {
                console.error(e)
            }
        })
    )
}

const gradients = [
    'linear-gradient(135deg,var(--color-accent-soft) 0%,var(--color-brown-43-2) 100%)',
    'linear-gradient(135deg,var(--color-brown-75) 0%,var(--color-brown-54-2) 100%)',
    'linear-gradient(135deg,var(--color-primary-espresso) 0%,var(--color-coffee-24) 100%)',
    'linear-gradient(135deg,var(--color-brown-52) 0%,var(--color-brown-58-2) 100%)',
]
function cardGradient(id) { return gradients[id % gradients.length] }

// Best-effort flush of un-sent ratings when the tab closes.
function beaconPendingRatings() {
    for (const [productId, { score }] of Object.entries(pendingRatings.value)) {
        navigator.sendBeacon(
            `${config.public.apiBase}/products/${productId}/rate`,
            new Blob([JSON.stringify({ score: score ?? null })], { type: 'application/json' })
        )
    }
}

onMounted(() => {
    if (allCategoryRows.value.length === 0) {
        showLoading()
        loadAll()
    } else {
        hideLoading()
    }
    loadCategoriesIfNeeded()

    window.addEventListener('beforeunload', beaconPendingRatings)
})

// The listener used to be added on every mount and never removed, so revisiting
// the page stacked up duplicates that each fired their own beacon.
onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', beaconPendingRatings)
})
function onRatingUpdated({ productId, avgRating, userScore }) {
    if (avgRating != null) {
        for (const row of allCategoryRows.value) {
            const p = row.products.find(p => p.id === productId)
            if (p) p.avg_rating = avgRating
        }
        const fp = filteredProducts.value.find(p => p.id === productId)
        if (fp) fp.avg_rating = avgRating
    }
    userRatings.value[productId] = userScore ?? null
}
</script>

<template>
    <main class="velora-page">

        <!-- ── Filters bar ── -->
        <div class="filters-bar">
            <div class="filters-inner">
                <button class="filter-toggle-btn" @click.stop="showFilters = !showFilters">
                    {{ $t('search.filters') }}
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" :class="{ 'is-open': showFilters }">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <select v-model="sort" class="sort-select">
                    <option value="newest">{{ $t('search.sortNewest') }}</option>
                    <option value="price_asc">{{ $t('search.sortPriceAsc') }}</option>
                    <option value="price_desc">{{ $t('search.sortPriceDesc') }}</option>
                    <option value="rating">{{ $t('search.sortRating') }}</option>
                </select>

                <span v-if="isFiltering" class="active-filters-note">
                    {{ $t('search.resultsFor', { q: searchQuery.trim() || $t('search.filters') }) }}
                </span>
            </div>

            <Transition name="filters-drop">
                <div v-if="showFilters" class="filters-panel" @click.stop>
                    <div class="filter-field">
                        <label>{{ $t('search.priceRange') }}</label>
                        <div class="price-range-inputs">
                            <input v-model="minPrice" type="number" min="0" step="0.01" :placeholder="$t('search.minPrice')" />
                            <span>—</span>
                            <input v-model="maxPrice" type="number" min="0" step="0.01" :placeholder="$t('search.maxPrice')" />
                        </div>
                    </div>

                    <div class="filter-field">
                        <label>{{ $t('search.category') }}</label>
                        <select v-model="filterCategoryId">
                            <option value="">{{ $t('search.allCategories') }}</option>
                            <option v-for="c in flatCategories" :key="c.id" :value="c.id">{{ displayName(c) }}</option>
                        </select>
                    </div>

                    <label class="filter-checkbox">
                        <input v-model="onDiscount" type="checkbox" />
                        {{ $t('search.onDiscount') }}
                    </label>

                    <div class="filter-actions">
                        <button class="filter-reset-btn" @click="resetFilters">{{ $t('search.reset') }}</button>
                        <button class="filter-apply-btn" @click="showFilters = false">{{ $t('search.apply') }}</button>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ── Filtered results (search / price / category / discount active) ── -->
        <template v-if="isFiltering">
            <div v-if="filteredLoading" class="results-grid">
                <div v-for="n in 12" :key="n" class="product-card product-card--skeleton">
                    <div class="card-image skeleton-block" />
                    <div class="card-body">
                        <div class="skeleton-line sk-name" />
                        <div class="skeleton-line sk-desc" />
                        <div class="skeleton-line sk-short" />
                    </div>
                </div>
            </div>

            <div v-else-if="filteredProducts.length > 0" class="results-grid">
                <div v-for="(product, idx) in filteredProducts" :key="product.id" class="product-card"
                    :style="{ '--card-delay': `${idx * 25}ms` }">
                    <div class="card-image" :style="{ background: cardGradient(product.id) }">
                        <img v-if="product.image_url" :src="resolveUrl(product.image_url)" :alt="displayName(product)"
                            class="card-img card-img--loaded" draggable="false" loading="lazy" />
                        <span v-if="product.category?.name" class="card-badge">{{ product.category.name }}</span>
                        <span v-if="product.has_discount" class="discount-badge">-{{ product.discount_percent }}%</span>
                    </div>

                    <div class="card-body">
                        <h3 class="card-title">{{ displayName(product) }}</h3>
                        <p class="card-desc">{{ displayDesc(product) }}</p>

                        <div class="card-rating">
                            <StarRating
                                :score="(userRatings[product.id] !== undefined ? userRatings[product.id] : product.avg_rating) ?? 0"
                                :interactive="true" @rate="(s) => rateProduct(product, s)" />
                            <span class="rating-value">
                                {{ ((userRatings[product.id] !== undefined ? userRatings[product.id] : product.avg_rating) ?? 0).toFixed(1) }}
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
                                <button v-if="!getItem(product.id)" @click.stop="addItem(product)" class="add-btn">
                                    {{ $t('home.addToCart') }}
                                </button>
                                <div v-else class="qty-ctrl">
                                    <button class="qty-btn" @click.stop="decreaseQty(product.id)">−</button>
                                    <span class="qty-num">{{ getItem(product.id).quantity }}</span>
                                    <button class="qty-btn" @click.stop="increaseQty(product.id)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="empty-state">
                <p>{{ $t('search.noResults', { q: searchQuery.trim() }) }}</p>
            </div>

            <div v-if="!filteredLoading && filteredPagination.last_page > 1" class="pagination">
                <button class="pg-btn" :disabled="filteredPagination.current_page === 1"
                    @click="goToFilteredPage(filteredPagination.current_page - 1)">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                        <path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" />
                    </svg>
                </button>
                <template v-for="page in visibleFilterPages" :key="page">
                    <span v-if="page === '...'" class="pg-ellipsis">…</span>
                    <button v-else class="pg-btn" :class="{ active: page === filteredPagination.current_page }"
                        @click="goToFilteredPage(page)">{{ page }}</button>
                </template>
                <button class="pg-btn" :disabled="filteredPagination.current_page === filteredPagination.last_page"
                    @click="goToFilteredPage(filteredPagination.current_page + 1)">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                        <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" />
                    </svg>
                </button>
            </div>
        </template>

        <!-- ── Loading skeleton ── -->
        <template v-else-if="loading">
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
                        <h2 class="section-title">{{ displayName(cat) }}</h2>
                        <span class="section-count">{{ $t('home.itemsCount', { n: cat.products.length }) }}</span>
                    </div>
                    <NuxtLink :to="`/categories/${cat.id}`" class="see-all-btn">
                        {{ $t('home.seeAll') }}
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
                        :aria-label="$t('home.scrollLeft')">
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
                                <div class="img-shimmer" :class="{ 'img-shimmer--hidden': imageLoaded[product.id] }" />
                                <NuxtImg v-if="product.image_url" :src="resolveUrl(product.image_url)"
                                    :alt="product.name" class="card-img"
                                    :class="{ 'card-img--loaded': imageLoaded[product.id] }"
                                    :loading="cardIdx < 3 && rowIdx === 0 ? 'eager' : 'lazy'"
                                    sizes="260px md:230px sm:200px" draggable="false" @load="onImageLoad(product.id)" />
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
                                        {{ ((userRatings[product.id] !== undefined ? userRatings[product.id] :
                                            product.avg_rating) ?? 0).toFixed(1) }}
                                    </span>
                                </div>

                                <div class="card-footer">
                                    <span v-if="product.has_discount" class="card-price card-price--discount">
                                        <span class="card-price-old">${{ Number(product.price).toFixed(2) }}</span>
                                        <span class="card-price-new">${{ Number(product.final_price).toFixed(2)
                                            }}</span>
                                    </span>
                                    <span v-else class="card-price">${{ Number(product.price).toFixed(2) }}</span>

                                    <div class="card-actions">
                                        <button class="detail-btn" @click.stop="openModal(product)">
                                            {{ $t('home.detail') }}
                                        </button>

                                        <button v-if="!getItem(product.id)" @click.stop="addItem(product)"
                                            class="add-btn">
                                            {{ $t('home.addToCart') }}
                                        </button>
                                        <div v-else class="qty-ctrl">
                                            <button class="qty-btn" @click.stop="decreaseQty(product.id)"
                                                :aria-label="$t('home.decreaseQty')">−</button>
                                            <span class="qty-num">{{ getItem(product.id).quantity }}</span>
                                            <button class="qty-btn" @click.stop="increaseQty(product.id)"
                                                :aria-label="$t('home.increaseQty')">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right arrow -->
                    <button class="carousel-arrow carousel-arrow--right" @click="scrollCarousel(cat.id, 1)"
                        :aria-label="$t('home.scrollRight')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </button>
                </div>
            </section>

            <!-- Empty state -->
            <div v-if="categoryRows.length === 0" class="empty-state">
                <p>{{ $t('home.noProducts') }}</p>
            </div>
        </template>
    </main>
    <ProductModal v-model="modalOpen" :product="selectedProduct"
        :initial-user-rating="selectedProduct ? (userRatings[selectedProduct.id] !== undefined ? userRatings[selectedProduct.id] : null) : null"
        @rating-updated="onRatingUpdated" />
</template>

<style scoped>
/* ── Page ── */
.velora-page {
    min-height: 100vh;
    background: var(--color-surface-warm);
    font-family: 'Lato', sans-serif;
    padding-bottom: 4rem;
}

/* ── Filters bar ── */
.filters-bar {
    position: relative;
    background: var(--color-surface-warm);
    border-bottom: 1px solid rgb(var(--rgb-primary-black) / 0.08);
}

.filters-inner {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1rem 3.5rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-wrap: wrap;
}

.filter-toggle-btn,
.sort-select {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--color-card);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 0.55rem 0.9rem;
    font-size: 0.78rem;
    letter-spacing: 0.04em;
    color: var(--color-primary-black);
    cursor: pointer;
    font-family: 'Lato', sans-serif;
}

.filter-toggle-btn svg {
    transition: transform 0.18s;
}

.filter-toggle-btn svg.is-open {
    transform: rotate(180deg);
}

.sort-select {
    cursor: pointer;
}

.active-filters-note {
    font-size: 0.75rem;
    color: var(--color-brown-43);
}

.filters-panel {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1rem 3.5rem 1.25rem;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 1.25rem;
}

.filter-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.filter-field label {
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--color-brown-43);
}

.filter-field select {
    background: var(--color-card);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 0.5rem 0.7rem;
    font-size: 0.82rem;
    color: var(--color-primary-black);
    min-width: 180px;
}

.price-range-inputs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color-brown-43);
}

.price-range-inputs input {
    width: 90px;
    background: var(--color-card);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 0.5rem 0.6rem;
    font-size: 0.82rem;
    color: var(--color-primary-black);
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    color: var(--color-primary-black);
    cursor: pointer;
}

.filter-actions {
    display: flex;
    gap: 0.6rem;
    margin-left: auto;
}

.filter-reset-btn,
.filter-apply-btn {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    border-radius: 8px;
    cursor: pointer;
    font-family: 'Lato', sans-serif;
}

.filter-reset-btn {
    background: transparent;
    border: 1px solid var(--color-border);
    color: var(--color-brown-43);
}

.filter-apply-btn {
    background: var(--color-accent-soft);
    border: none;
    color: var(--color-white);
}

.filters-drop-enter-active,
.filters-drop-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.filters-drop-enter-from,
.filters-drop-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* ── Filtered results grid ── */
.results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1.5rem;
    padding: 2rem 3.5rem 0;
    max-width: 1400px;
    margin: 0 auto;
}

.results-grid .product-card {
    width: auto;
    flex: none;
}

.results-grid .card-image {
    height: 210px;
}

/* ── Pagination ── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 2.5rem 2rem 0;
}

.pg-btn {
    width: 34px;
    height: 34px;
    border: 1.5px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-card);
    color: var(--color-primary-black);
    font-size: 0.83rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s;
}

.pg-btn:hover:not(:disabled) {
    border-color: var(--color-accent-soft);
    color: var(--color-accent-soft);
}

.pg-btn.active {
    background: var(--color-accent-soft);
    border-color: var(--color-accent-soft);
    color: var(--color-white);
}

.pg-btn:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.pg-ellipsis {
    padding: 0 4px;
    color: var(--color-brown-51);
    font-size: 0.9rem;
}

@media (max-width: 1024px) {
    .filters-inner, .filters-panel { padding-left: 2.5rem; padding-right: 2.5rem; }
    .results-grid { padding: 2rem 2.5rem 0; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); }
}

@media (max-width: 768px) {
    .filters-inner, .filters-panel { padding-left: 1.5rem; padding-right: 1.5rem; }
    .results-grid { padding: 1.5rem 1.5rem 0; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); }
    .filter-actions { margin-left: 0; width: 100%; }
}

@media (max-width: 480px) {
    .results-grid { grid-template-columns: repeat(2, 1fr); padding: 1.25rem 1rem 0; gap: 0.75rem; }
}

/* ── Category section ── */
.category-section {
    padding: 2.5rem 0 0;
    animation: fadeUp 0.6s var(--ease-smooth) both;
    animation-delay: var(--row-delay, 0ms);
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(24px);
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
    color: var(--color-primary-espresso);
    margin: 0;
    letter-spacing: 0.01em;
}

.section-count {
    font-size: 0.7rem;
    color: var(--color-brown-51);
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
    color: var(--color-accent-soft);
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: color 0.15s, border-color 0.15s;
    white-space: nowrap;
    flex-shrink: 0;
}

.see-all-btn:hover {
    color: var(--color-brown-52);
    border-bottom-color: var(--color-brown-52);
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
    border: 1px solid rgb(var(--rgb-primary-espresso) / 0.15);
    background: var(--color-surface-warm);
    color: var(--color-primary-espresso);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 12px rgb(var(--rgb-primary-espresso) / 0.12);
    transition: background 0.18s, box-shadow 0.18s, opacity 0.18s;
}

.carousel-arrow:hover {
    background: var(--color-card);
    box-shadow: 0 4px 20px rgb(var(--rgb-primary-espresso) / 0.18);
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
    align-items: stretch;
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
    background: var(--color-card);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    scroll-snap-align: start;
    box-shadow: 0 1px 4px rgb(var(--rgb-primary-espresso) / 0.07);
    transition: box-shadow 0.35s var(--ease-smooth), transform 0.35s var(--ease-smooth);
    animation: cardIn 0.55s var(--ease-smooth) both;
    animation-delay: var(--card-delay, 0ms);
    align-self: stretch;
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
    height: 190px;
    overflow: hidden;
    flex-shrink: 0;
}

.img-shimmer {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg,
            var(--color-primary-deep) 0%,
            var(--color-coffee-17) 30%,
            var(--color-coffee-24-2) 50%,
            var(--color-coffee-17) 70%,
            var(--color-primary-deep) 100%);
    background-size: 200% 100%;
    animation: imgShimmer 1.6s ease-in-out infinite;
    transition: opacity 0.35s ease;
    z-index: 1;
}

.img-shimmer--hidden {
    opacity: 0;
    pointer-events: none;
}

.card-img {
    opacity: 0;
    transition: opacity 0.4s ease;
}

.card-img--loaded {
    opacity: 1;
}

@keyframes imgShimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
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
/* Price on its own line, actions underneath. Explicitly a column rather than
   a wrapping row: with wrapping, whether the buttons sat beside the price or
   below it depended on how long the translated label happened to be, so the
   card changed shape between languages. */
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
    height: 190px;
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
    color: var(--color-brown-43);
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
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 0.4rem;
    }

    .card-actions {
        display: flex;
        flex-direction: column;
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

    /* ↓ Bu blokları kaldır veya şununla değiştir: */
    .card-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .card-actions {
        width: 100%;
        justify-content: space-between;
    }

    .qty-ctrl {
        flex: 1;
        justify-content: space-between;
        width: 100%;
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