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
                    <p class="stat-value">
                        {{ card.format === 'currency' ? formatCurrency(card.value) : formatNumber(card.value) }} $
                    </p>
                    <p class="stat-growth" :class="card.growth >= 0 ? 'positive' : 'negative'">
                        {{ card.growth >= 0 ? '↑' : '↓' }} {{ Math.abs(card.growth) }}%
                        {{ $t('admin.dashboard.thisWeek') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Middle row: chart + recent orders -->
        <div class="mid-row">
            <div class="oc-card">
                <div class="oc-head">
                    <span class="oc-title">{{ $t('admin.dashboard.ordersOverview') }}</span>
                    <div class="oc-controls">
                        <div class="oc-tabs">
                            <button v-for="p in periods" :key="p.value" class="oc-tab"
                                :class="{ active: period === p.value }" @click="changePeriod(p.value)">
                                {{ p.label }}
                            </button>
                        </div>
                        <div class="oc-cal-wrap" ref="calendarRef">
                            <button class="oc-cal-btn" :class="{ active: period === 'custom' }"
                                @click.stop="isOpen = !isOpen">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6"
                                    class="oc-cal-icon">
                                    <rect x="3" y="4" width="14" height="13" rx="2" />
                                    <path d="M3 8h14M7 2v4M13 2v4" />
                                </svg>
                                <span class="oc-cal-label">{{ rangeLabel ?? $t('admin.dashboard.pickDate') }}</span>
                            </button>
                            <Transition name="oc-pop">
                                <div v-if="isOpen" class="oc-cal-popup" @click.stop>
                                    <DatePicker color="green" :model-value="(range as any)"
                                        @update:model-value="(val: any) => range = val" mode="date" is-range
                                        :popover="false" :locale="calendarLocale">
                                        <template #footer>
                                            <div class="oc-cal-footer">
                                                <button class="oc-cal-clear" @click="clearRange">
                                                    {{ $t('admin.dashboard.clearDate') }}
                                                </button>
                                                <button class="oc-cal-apply" @click="applyRange">
                                                    {{ $t('admin.dashboard.applyDate') }}
                                                </button>
                                            </div>
                                        </template>
                                    </DatePicker>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
                <div class="oc-chart-wrap">
                    <div v-if="chartLoading" class="oc-loading">
                        <div class="oc-skeleton" />
                    </div>
                    <canvas v-else ref="chartCanvas" />
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
                    <template v-else>
                        <div v-for="order in recentOrders" :key="order.id" class="order-row">
                            <div class="order-info">
                                <p class="order-no">{{ order.order_no }}</p>
                                <p class="order-product">{{ order.product_name }}</p>
                            </div>
                            <div class="order-right">
                                <p class="order-price">${{ Number(order.total_price).toFixed(2) }}</p>
                                <p class="order-time">{{ timeAgo(order.created_at) }}</p>
                            </div>
                        </div>
                        <p v-if="recentOrders.length === 0" class="empty-msg">
                            {{ $t('admin.dashboard.noRecentOrders') }}
                        </p>
                    </template>
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
                            <img v-if="product.image_url" :src="product.image_url" :alt="getProductName(product)"
                                class="product-img" />
                            <div v-else class="product-img-placeholder" />
                        </div>
                        <span class="product-name">{{ getProductName(product) }}</span>
                        <div class="stars">
                            <span v-for="s in 5" :key="s" class="star"
                                :class="s <= Math.round(product.avg_rating) ? 'filled' : 'empty'">★</span>
                        </div>
                        <span class="product-rating">{{ Number(product.avg_rating).toFixed(1) }}</span>
                    </div>
                    <p v-if="!loading && topProducts.length === 0" class="empty-msg">
                        {{ $t('admin.dashboard.noRatedProducts') }}
                    </p>
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
                        <span class="cat-name">{{ getCategoryName(cat) }}</span>
                        <span class="cat-count">{{ cat.products_count }}</span>
                    </div>
                    <p v-if="!loading && categories.length === 0" class="empty-msg">
                        {{ $t('admin.dashboard.noCategories') }}
                    </p>
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
import { DatePicker } from 'v-calendar'
import 'v-calendar/style.css'
import { nextTick } from 'vue'

definePageMeta({
    layout: 'admin' as any,
    middleware: 'admin',
})

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip)

const { t, locale } = useI18n()
const { fetchDashboard, fetchOrdersChart } = useAdmin()

// ── State ──────────────────────────────────────────────────
const loading = ref(true)
const chartLoading = ref(true)
const stats = ref<any>({})
const recentOrders = ref<any[]>([])
const topProducts = ref<any[]>([])
const categories = ref<any[]>([])

// ── Translation helpers ────────────────────────────────────
// Returns translated name from translations object/array, falls back to .name
function getTranslatedField(item: any, field: 'name' | 'description'): string {
    if (!item) return ''
    const tr = item.translations
    if (tr) {
        const loc = locale.value
        const entry = Array.isArray(tr)
            ? tr.find((x: any) => x.locale === loc)
            : tr[loc]
        if (entry?.[field]) return entry[field]
        // fallback to EN
        const enEntry = Array.isArray(tr)
            ? tr.find((x: any) => x.locale === 'en')
            : tr['en']
        if (enEntry?.[field]) return enEntry[field]
    }
    return item[field] ?? item.name ?? ''
}

function getCategoryName(cat: any): string {
    return getTranslatedField(cat, 'name')
}

function getProductName(product: any): string {
    return getTranslatedField(product, 'name')
}

// ── Stat cards ─────────────────────────────────────────────
const statCards = computed(() => [
    {
        label: t('admin.dashboard.totalUsers'),
        value: stats.value.total_users ?? 0,
        growth: stats.value.users_growth ?? 0,
        bg: '#e8f0e4',
        format: 'number',
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
        format: 'number',
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
        format: 'number',
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
        format: 'number',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="#4A6741" stroke-width="1.8">
      <rect x="3" y="3" width="7" height="7" rx="1"/>
      <rect x="14" y="3" width="7" height="7" rx="1"/>
      <rect x="3" y="14" width="7" height="7" rx="1"/>
      <rect x="14" y="14" width="7" height="7" rx="1"/>
    </svg>`,
    },
    {
        label: t('admin.dashboard.weeklyRevenue'),
        value: stats.value.weekly_revenue ?? 0,
        growth: stats.value.revenue_growth ?? 0,
        bg: '#fdf3e4',
        format: 'number',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="1.8">
      <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
    </svg>`,
    },
    {
        label: t('admin.dashboard.yearlyRevenue'),
        value: stats.value.yearly_revenue ?? 0,
        growth: 0,
        bg: '#e8f0e4',
        format: 'number',
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="#4A6741" stroke-width="1.8">
      <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
    </svg>`,
    },
])

// ── Calendar locale ────────────────────────────────────────
const calendarLocale = computed(() => locale.value === 'tm' ? 'en' : locale.value)

// ── Chart ──────────────────────────────────────────────────
type Period = 'daily' | 'weekly' | 'monthly' | 'yearly' | 'custom'

const chartCanvas = ref<HTMLCanvasElement | null>(null)
const calendarRef = ref<HTMLElement | null>(null)
let chartInstance: Chart | null = null

const period = ref<Period>('weekly')
const isOpen = ref(false)
const range = ref<{ start: Date; end: Date } | null>(null)
const pendingData = ref<{ labels: string[]; data: number[] } | null>(null)

const periods = computed(() => [
    { value: 'daily' as const, label: t('admin.dashboard.periodDay') },
    { value: 'weekly' as const, label: t('admin.dashboard.periodWeek') },
    { value: 'monthly' as const, label: t('admin.dashboard.periodMonth') },
    { value: 'yearly' as const, label: t('admin.dashboard.periodYear') },
])

const rangeLabel = computed(() => {
    if (!range.value?.start || !range.value?.end) return null
    const fmt = (d: Date) =>
        d.toLocaleDateString(locale.value === 'tm' ? 'ru-RU' : locale.value, {
            day: '2-digit', month: 'short', year: 'numeric',
        })
    return `${fmt(range.value.start)} — ${fmt(range.value.end)}`
})

const toISODate = (d: Date) => d.toISOString().split('T')[0]

watch(chartCanvas, (canvas) => {
    if (canvas && pendingData.value) {
        renderChart(pendingData.value)
        pendingData.value = null
    }
})

const renderChart = (data: { labels: string[]; data: number[] }) => {
    if (!chartCanvas.value) { pendingData.value = data; return }
    if (chartInstance) { chartInstance.destroy(); chartInstance = null }

    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: data.labels ?? [],
            datasets: [{
                data: data.data ?? [],
                borderColor: '#4A6741',
                backgroundColor: 'rgba(74,103,65,0.08)',
                borderWidth: 2.5,
                pointBackgroundColor: '#4A6741',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#2C1810',
                    titleColor: '#C8A96E',
                    bodyColor: '#F0EDE6',
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    min: 0,
                    suggestedMax: 1,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { family: 'Jost', size: 11 }, color: '#8a7060', stepSize: 1, precision: 0 },
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Jost', size: 11 }, color: '#8a7060', maxRotation: 45, autoSkip: true, maxTicksLimit: 12 },
                },
            },
        },
    })
}

const loadPeriod = async (p: Exclude<Period, 'custom'>) => {
    chartLoading.value = true
    try {
        const data = await fetchOrdersChart(p)
        chartLoading.value = false
        await nextTick()
        renderChart(data as any)
    } catch (e) {
        console.error('Chart load error:', e)
        chartLoading.value = false
    }
}

const loadCustomRange = async () => {
    if (!range.value?.start || !range.value?.end) return
    chartLoading.value = true
    try {
        const data = await fetchOrdersChart('custom', {
            startDate: toISODate(range.value.start),
            endDate: toISODate(range.value.end),
        })
        chartLoading.value = false
        await nextTick()
        renderChart(data as any)
    } catch (e) {
        console.error('Custom chart error:', e)
        chartLoading.value = false
    }
}

const applyRange = () => { isOpen.value = false; period.value = 'custom'; loadCustomRange() }
const clearRange = () => { range.value = null; period.value = 'weekly'; isOpen.value = false; loadPeriod('weekly') }
const changePeriod = (p: Exclude<Period, 'custom'>) => { period.value = p; range.value = null; isOpen.value = false; loadPeriod(p) }

const onClickOutside = (e: MouseEvent) => {
    if (calendarRef.value && !calendarRef.value.contains(e.target as HTMLElement)) isOpen.value = false
}

// ── Dashboard data ─────────────────────────────────────────
const loadDashboard = async () => {
    loading.value = true
    try {
        const data = await fetchDashboard() as any
        stats.value = data.stats ?? data
        recentOrders.value = data.recent_orders ?? []
        topProducts.value = data.top_products ?? []
        categories.value = data.categories ?? []
    } catch (e) {
        console.error('Dashboard load error:', e)
    } finally {
        loading.value = false
    }
}

// ── Lifecycle ──────────────────────────────────────────────
onMounted(() => {
    loadDashboard()
    loadPeriod('weekly')
    document.addEventListener('click', onClickOutside)
})

onUnmounted(() => {
    chartInstance?.destroy()
    document.removeEventListener('click', onClickOutside)
})

// ── Helpers ────────────────────────────────────────────────
const formatNumber = (n: number) => new Intl.NumberFormat().format(n ?? 0)

const timeAgo = (dateStr: string) => {
    const diff = Date.now() - new Date(dateStr).getTime()
    const mins = Math.floor(diff / 60000)
    if (mins < 1) return t('admin.dashboard.justNow')
    if (mins < 60) return t('admin.dashboard.minAgo', { n: mins })
    const hrs = Math.floor(mins / 60)
    if (hrs < 24) return t('admin.dashboard.hrsAgo', { n: hrs })
    return t('admin.dashboard.daysAgo', { n: Math.floor(hrs / 24) })
}
const formatCurrency = (n: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n ?? 0)
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

.mid-row {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 16px;
    margin-bottom: 16px;
}

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

.order-img-placeholder svg {
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

.loading-placeholder {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.skeleton-row {
    height: 44px;
    background: linear-gradient(90deg, #F0EDE6 25%, #e8e2d9 50%, #F0EDE6 75%);
    background-size: 400px 100%;
    border-radius: 10px;
    animation: shimmer 1.2s infinite linear;
}

@keyframes shimmer {
    0% {
        background-position: -400px 0;
    }

    100% {
        background-position: 400px 0;
    }
}

.bottom-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

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

.cat-icon-wrap svg {
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

/* ── Chart card ──────────────────────────────────────────── */
.oc-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 1px 4px rgba(44, 24, 16, 0.06);
}

.oc-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.oc-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.05rem;
    font-weight: 600;
    color: #2C1810;
}

.oc-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.oc-tabs {
    display: flex;
    gap: 3px;
    background: #F0EDE6;
    border-radius: 8px;
    padding: 3px;
}

.oc-tab {
    background: none;
    border: none;
    border-radius: 6px;
    padding: 4px 11px;
    font-family: 'Jost', sans-serif;
    font-size: 0.75rem;
    color: #8a7060;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}

.oc-tab.active {
    background: #fff;
    color: #2C1810;
    font-weight: 500;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.oc-cal-wrap {
    position: relative;
}

.oc-cal-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #F0EDE6;
    border: 1.5px solid transparent;
    border-radius: 8px;
    padding: 5px 11px;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    font-family: 'Jost', sans-serif;
    font-size: 0.75rem;
    color: #8a7060;
}

.oc-cal-btn.active,
.oc-cal-btn:hover {
    border-color: #4A6741;
    color: #2C1810;
    background: #edf2eb;
}

.oc-cal-icon {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    color: #4A6741;
}

.oc-cal-label {
    white-space: nowrap;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.oc-cal-popup {
    position: absolute;
    z-index: 200;
    right: 0;
    top: calc(100% + 8px);
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(44, 24, 16, 0.14);
    overflow: hidden;
}

.oc-pop-enter-active,
.oc-pop-leave-active {
    transition: opacity 0.18s, transform 0.18s;
}

.oc-pop-enter-from,
.oc-pop-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

.oc-cal-footer {
    display: flex;
    gap: 6px;
    padding: 8px 10px;
    border-top: 1px solid #F0EDE6;
}

.oc-cal-clear,
.oc-cal-apply {
    flex: 1;
    padding: 7px 0;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    font-size: 0.82rem;
    font-weight: 500;
    transition: background 0.15s;
}

.oc-cal-clear {
    background: #F0EDE6;
    color: #8a7060;
}

.oc-cal-clear:hover {
    background: #e4ddd3;
}

.oc-cal-apply {
    background: #4A6741;
    color: #fff;
}

.oc-cal-apply:hover {
    background: #3a5232;
}

.oc-chart-wrap {
    position: relative;
    height: 240px;
}

.oc-chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
}

.oc-loading {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.oc-skeleton {
    width: 100%;
    height: 100%;
    border-radius: 10px;
    background: linear-gradient(90deg, #F0EDE6 25%, #e8e2d9 50%, #F0EDE6 75%);
    background-size: 400px 100%;
    animation: shimmer 1.2s infinite linear;
}

:deep(.vc-container) {
    border: none !important;
    font-family: 'Jost', sans-serif !important;
}

:deep(.vc-header) {
    padding: 10px 16px 6px !important;
}

:deep(.vc-title) {
    font-family: 'Cormorant Garamond', serif !important;
    font-size: 1rem !important;
    color: #2C1810 !important;
}

:deep(.vc-weekday) {
    color: #8a7060 !important;
    font-size: 0.72rem !important;
}

:deep(.vc-day-content) {
    font-size: 0.82rem !important;
    border-radius: 8px !important;
}

:deep(.vc-highlight) {
    background-color: #4A6741 !important;
}

:deep(.vc-highlight-content-solid) {
    color: #fff !important;
}
</style>