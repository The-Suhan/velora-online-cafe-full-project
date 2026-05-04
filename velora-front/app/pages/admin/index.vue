<template>
    <div class="dashboard">
        <!-- Page header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $t('admin.dashboard.title') }}</h1>
                <p class="page-sub">{{ $t('admin.dashboard.subtitle') }}</p>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="stat-grid">
            <div v-for="card in statCards" :key="card.label" class="stat-card">
                <div class="stat-icon" :style="{ background: card.bg }">
                    <span v-html="card.icon" />
                </div>
                <div class="stat-body">
                    <p class="stat-label">{{ card.label }}</p>
                    <p class="stat-value">{{ formatNumber(card.value) }}</p>
                    <p class="stat-growth" :class="card.growth >= 0 ? 'positive' : 'negative'">
                        {{ card.growth >= 0 ? '↑' : '↓' }} {{ Math.abs(card.growth) }}% {{ $t('admin.dashboard.thisWeek') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Middle row: chart + recent orders -->
        <div class="mid-row">
            <!-- Orders Overview chart -->
            <div class="chart-card">
                <div class="card-header">
                    <span class="card-title">{{ $t('admin.dashboard.ordersOverview') }}</span>
                    <div class="period-tabs">
                        <button v-for="p in periods" :key="p.value" class="period-btn"
                            :class="{ active: period === p.value }" @click="changePeriod(p.value)">
                            {{ p.label }}
                        </button>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas ref="chartCanvas" />
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="recent-card">
                <div class="card-header">
                    <span class="card-title">{{ $t('admin.dashboard.recentOrders') }}</span>
                    <NuxtLink to="/admin/orders" class="view-all">{{ $t('admin.common.viewAll') }}</NuxtLink>
                </div>
                <div class="orders-list">
                    <div v-if="loading" class="loading-placeholder">
                        <div v-for="i in 4" :key="i" class="skeleton-row" />
                    </div>
                    <div v-for="order in recentOrders" :key="order.id" class="order-row">
                        <div class="order-img-wrap">
                            <img v-if="order.product_img" :src="order.product_img" :alt="order.product_name"
                                class="order-img" />
                            <div v-else class="order-img-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                </svg>
                            </div>
                        </div>
                        <div class="order-info">
                            <p class="order-no">{{ order.order_no }}</p>
                            <p class="order-product">{{ order.product_name }}</p>
                        </div>
                        <div class="order-right">
                            <p class="order-price">${{ Number(order.total_price).toFixed(2) }}</p>
                            <p class="order-time">{{ timeAgo(order.created_at) }}</p>
                        </div>
                    </div>
                    <p v-if="!loading && recentOrders.length === 0" class="empty-msg">{{ $t('admin.dashboard.noRecentOrders') }}</p>
                </div>
            </div>
        </div>

        <!-- Bottom row: top products + categories -->
        <div class="bottom-row">
            <!-- Top Rated Products -->
            <div class="bottom-card">
                <div class="card-header">
                    <span class="card-title">{{ $t('admin.dashboard.topRatedProducts') }}</span>
                    <NuxtLink to="/admin/products" class="view-all">{{ $t('admin.common.viewAll') }}</NuxtLink>
                </div>
                <div class="products-list">
                    <div v-for="(product, idx) in topProducts" :key="product.id" class="product-row">
                        <span class="product-rank">{{ idx + 1 }}</span>
                        <div class="product-img-wrap">
                            <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                                class="product-img" />
                            <div v-else class="product-img-placeholder" />
                        </div>
                        <span class="product-name">{{ product.name }}</span>
                        <div class="stars">
                            <span v-for="s in 5" :key="s" class="star"
                                :class="s <= Math.round(product.avg_rating) ? 'filled' : 'empty'">★</span>
                        </div>
                        <span class="product-rating">{{ Number(product.avg_rating).toFixed(1) }}</span>
                    </div>
                    <p v-if="!loading && topProducts.length === 0" class="empty-msg">{{ $t('admin.dashboard.noRatedProducts') }}</p>
                </div>
            </div>

            <!-- Categories Overview -->
            <div class="bottom-card">
                <div class="card-header">
                    <span class="card-title">{{ $t('admin.dashboard.categoriesOverview') }}</span>
                    <NuxtLink to="/admin/categories" class="view-all">{{ $t('admin.common.viewAll') }}</NuxtLink>
                </div>
                <div class="cat-list">
                    <div v-for="cat in categories" :key="cat.id" class="cat-row">
                        <div class="cat-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="1.8">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                            </svg>
                        </div>
                        <span class="cat-name">{{ cat.name }}</span>
                        <span class="cat-count">{{ cat.products_count }}</span>
                    </div>
                    <p v-if="!loading && categories.length === 0" class="empty-msg">{{ $t('admin.dashboard.noCategories') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip,
} from 'chart.js'

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip)

definePageMeta({
    layout: 'admin' as any,
    middleware: 'admin',
})

const { t } = useI18n()
const { fetchDashboard, fetchOrdersChart } = useAdmin()
const pendingOrders = useState('admin:pendingOrders')
const unreadFeedbacks = useState('admin:unreadFeedbacks')

// ── State ──────────────────────────────────────────────────
type Period = 'daily' | 'weekly' | 'monthly' | 'yearly'
const loading = ref(true)
const stats = ref<any>({})
const recentOrders = ref<any[]>([])
const topProducts = ref<any[]>([])
const categories = ref<any[]>([])
const period = ref<Period>('weekly')
const chartCanvas = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null


const periods: { value: Period; label: string }[] = [
    { value: 'daily', label: t('admin.dashboard.periodDay') },
    { value: 'weekly', label: t('admin.dashboard.periodWeek') },
    { value: 'monthly', label: t('admin.dashboard.periodMonth') },
    { value: 'yearly', label: t('admin.dashboard.periodYear') },
]

// ── Stat cards ─────────────────────────────────────────────
const statCards = computed(() => [
    {
        label: t('admin.dashboard.totalUsers'),
        value: stats.value.total_users ?? 0,
        growth: stats.value.users_growth ?? 0,
        bg: '#e8f0e4',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="#4A6741" stroke-width="1.8">
      <circle cx="12" cy="8" r="4"/>
      <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
    </svg>`,
    },
    {
        label: t('admin.dashboard.totalOrders'),
        value: stats.value.total_orders ?? 0,
        growth: stats.value.orders_growth ?? 0,
        bg: '#fdf3e4',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="1.8">
      <path d="M6 2h12l3 7H3L6 2z"/>
      <path d="M3 9v11a2 2 0 002 2h14a2 2 0 002-2V9"/>
    </svg>`,
    },
    {
        label: t('admin.dashboard.totalProducts'),
        value: stats.value.total_products ?? 0,
        growth: 0,
        bg: '#2C1810',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="1.8">
      <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
      <line x1="3" y1="6" x2="21" y2="6"/>
      <path d="M16 10a4 4 0 01-8 0"/>
    </svg>`,
    },
    {
        label: t('admin.dashboard.totalCategories'),
        value: stats.value.total_categories ?? 0,
        growth: 0,
        bg: '#e8f0e4',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="#4A6741" stroke-width="1.8">
      <rect x="3" y="3" width="7" height="7" rx="1"/>
      <rect x="14" y="3" width="7" height="7" rx="1"/>
      <rect x="3" y="14" width="7" height="7" rx="1"/>
      <rect x="14" y="14" width="7" height="7" rx="1"/>
    </svg>`,
    },
])

// ── Load data ──────────────────────────────────────────────
onMounted(async () => {
    try {
        const [dash, chart] = await Promise.all([
            fetchDashboard(),
            fetchOrdersChart('weekly'),
        ])

        const d = dash as any
        stats.value = d.stats
        recentOrders.value = d.recent_orders
        topProducts.value = d.top_products
        categories.value = d.categories

        pendingOrders.value = d.stats.pending_orders
        unreadFeedbacks.value = d.stats.unread_feedbacks

        renderChart(chart as any)
    } catch (e) {
        console.error('Dashboard load error:', e)
    } finally {
        loading.value = false
    }
})

const changePeriod = async (p: Period) => {
    period.value = p
    const chart = await fetchOrdersChart(p)
    renderChart(chart as any)
}

// ── Chart ──────────────────────────────────────────────────
const renderChart = (data: { labels: string[]; data: number[] }) => {
    if (!chartCanvas.value) return
    if (chartInstance) chartInstance.destroy()

    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                borderColor: '#4A6741',
                backgroundColor: 'rgba(74, 103, 65, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#4A6741',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { mode: 'index' } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { family: 'Jost', size: 11 }, color: '#8a7060' },
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Jost', size: 11 }, color: '#8a7060' },
                },
            },
        },
    })
}

// ── Helpers ────────────────────────────────────────────────
const formatNumber = (n: number) =>
    new Intl.NumberFormat().format(n ?? 0)

const timeAgo = (dateStr: string) => {
    const diff = Date.now() - new Date(dateStr).getTime()
    const mins = Math.floor(diff / 60000)
    if (mins < 1) return 'just now'
    if (mins < 60) return `${mins} min ago`
    const hrs = Math.floor(mins / 60)
    if (hrs < 24) return `${hrs}h ago`
    return `${Math.floor(hrs / 24)}d ago`
}

onUnmounted(() => { chartInstance?.destroy() })
</script>

<style scoped>
.dashboard {
    max-width: 1200px;
}

.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 24px;
}

.page-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.8rem;
    font-weight: 600;
    color: #2C1810;
    margin: 0 0 4px;
}

.page-sub {
    font-size: 0.84rem;
    color: #8a7060;
    margin: 0;
}

/* ── Stat cards ────────────────────────────────────────── */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 4px rgba(44, 24, 16, 0.06);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon :deep(svg) {
    width: 22px;
    height: 22px;
}

.stat-label {
    font-size: 0.78rem;
    color: #8a7060;
    margin: 0 0 2px;
}

.stat-value {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.6rem;
    font-weight: 600;
    color: #2C1810;
    margin: 0 0 2px;
    line-height: 1;
}

.stat-growth {
    font-size: 0.72rem;
    margin: 0;
}

.positive {
    color: #4A6741;
}

.negative {
    color: #c0392b;
}

/* ── Mid row ───────────────────────────────────────────── */
.mid-row {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 16px;
    margin-bottom: 16px;
}

/* ── Cards ─────────────────────────────────────────────── */
.chart-card,
.recent-card,
.bottom-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 1px 4px rgba(44, 24, 16, 0.06);
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.05rem;
    font-weight: 600;
    color: #2C1810;
}

.view-all {
    font-size: 0.78rem;
    color: #4A6741;
    text-decoration: none;
    font-weight: 500;
}

.view-all:hover {
    text-decoration: underline;
}

/* Period tabs */
.period-tabs {
    display: flex;
    gap: 4px;
    background: #F0EDE6;
    border-radius: 8px;
    padding: 3px;
}

.period-btn {
    background: none;
    border: none;
    border-radius: 6px;
    padding: 4px 10px;
    font-family: 'Jost', sans-serif;
    font-size: 0.75rem;
    color: #8a7060;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.period-btn.active {
    background: #fff;
    color: #2C1810;
    font-weight: 500;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

/* Chart */
.chart-wrap {
    height: 240px;
    position: relative;
}

/* Recent orders */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.order-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.order-img-wrap {
    flex-shrink: 0;
}

.order-img,
.order-img-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    object-fit: cover;
}

.order-img-placeholder {
    background: #F0EDE6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.order-img-placeholder :deep(svg) {
    width: 20px;
    height: 20px;
    color: #C8A96E;
}

.order-info {
    flex: 1;
    min-width: 0;
}

.order-no {
    font-size: 0.82rem;
    font-weight: 500;
    color: #2C1810;
    margin: 0 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.order-product {
    font-size: 0.75rem;
    color: #8a7060;
    margin: 0;
}

.order-right {
    text-align: right;
    flex-shrink: 0;
}

.order-price {
    font-size: 0.85rem;
    font-weight: 600;
    color: #4A6741;
    margin: 0 0 2px;
}

.order-time {
    font-size: 0.72rem;
    color: #b0967a;
    margin: 0;
}

/* Skeleton */
.skeleton-row {
    height: 44px;
    background: linear-gradient(90deg, #F0EDE6 25%, #e8e2d9 50%, #F0EDE6 75%);
    border-radius: 10px;
    animation: shimmer 1.2s infinite;
}

@keyframes shimmer {
    0% {
        background-position: -400px 0;
    }

    100% {
        background-position: 400px 0;
    }
}

/* ── Bottom row ────────────────────────────────────────── */
.bottom-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Top products */
.products-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.product-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.product-rank {
    font-size: 0.78rem;
    font-weight: 500;
    color: #8a7060;
    width: 16px;
    text-align: center;
}

.product-img,
.product-img-placeholder {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    object-fit: cover;
}

.product-img-placeholder {
    background: #F0EDE6;
}

.product-name {
    flex: 1;
    font-size: 0.85rem;
    color: #2C1810;
}

.stars {
    display: flex;
    gap: 1px;
}

.star {
    font-size: 13px;
}

.star.filled {
    color: #C8A96E;
}

.star.empty {
    color: #E8E2D9;
}

.product-rating {
    font-size: 0.78rem;
    font-weight: 500;
    color: #8a7060;
    min-width: 28px;
    text-align: right;
}

/* Categories */
.cat-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.cat-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cat-icon-wrap {
    width: 36px;
    height: 36px;
    background: #fdf3e4;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.cat-icon-wrap :deep(svg) {
    width: 18px;
    height: 18px;
}

.cat-name {
    flex: 1;
    font-size: 0.85rem;
    color: #2C1810;
}

.cat-count {
    font-size: 0.82rem;
    font-weight: 500;
    color: #8a7060;
    background: #F0EDE6;
    padding: 2px 8px;
    border-radius: 99px;
}

.empty-msg {
    font-size: 0.82rem;
    color: #b0967a;
    text-align: center;
    padding: 12px 0;
}

/* ── Responsive ────────────────────────────────────────── */
@media (max-width: 1024px) {
    .stat-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .mid-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .stat-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .bottom-row {
        grid-template-columns: 1fr;
    }

    .page-title {
        font-size: 1.4rem;
    }

    .stat-value {
        font-size: 1.3rem;
    }
}
</style>