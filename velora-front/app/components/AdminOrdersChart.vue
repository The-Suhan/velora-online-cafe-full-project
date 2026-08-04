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

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip)

const { fetchOrdersChart } = useAdmin()
const { locale, t } = useI18n()

type Period = 'daily' | 'weekly' | 'monthly' | 'yearly' | 'custom'

const chartCanvas = ref<HTMLCanvasElement | null>(null)
const calendarRef = ref<HTMLElement | null>(null)
let chartInstance: Chart | null = null

const period = ref<Period>('weekly')
const isOpen = ref(false)
const loading = ref(false)
const range = ref<{ start: Date; end: Date } | null>(null)

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

// ── Chart render ──────────────────────────────────────────────────
// Chart.js paints on a canvas, which cannot resolve var(), so the tokens are
// read out of color.css at render time instead.
const { color, alpha } = useColors()

const renderChart = (data: { labels: string[]; data: number[] }) => {
    if (!chartCanvas.value) return
    if (chartInstance) chartInstance.destroy()

    const labels = data.labels?.length ? data.labels : []
    const values = data.data?.length ? data.data : []

    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: color('success'),
                backgroundColor: alpha('success', 0.08),
                borderWidth: 2.5,
                pointBackgroundColor: color('success'),
                pointBorderColor: color('white'),
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
                    backgroundColor: color('primary'),
                    titleColor: color('accent'),
                    bodyColor: color('surface-alt'),
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    min: 0,
                    suggestedMax: 1,          
                    grid: { color: alpha('black', 0.04) },
                    ticks: {
                        font: { family: 'Jost', size: 11 },
                        color: color('muted'),
                        stepSize: 1,
                        precision: 0,
                    },
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Jost', size: 11 },
                        color: color('muted'),
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 12,
                    },
                },
            },
        },
    })
}

// ── Data load ─────────────────────────────────────────────────────
const loadPeriod = async (p: Exclude<Period, 'custom'>) => {
    loading.value = true
    try {
        const data = await fetchOrdersChart(p)
        renderChart(data as any)
    } catch (e) {
        console.error('Chart load error:', e)
    } finally {
        loading.value = false
    }
}

const loadCustomRange = async () => {
    if (!range.value?.start || !range.value?.end) return
    loading.value = true
    try {
        const data = await fetchOrdersChart('custom', {
            startDate: toISODate(range.value.start),
            endDate: toISODate(range.value.end),
        })
        renderChart(data as any)
    } catch (e) {
        console.error('Custom chart error:', e)
    } finally {
        loading.value = false
    }
}

const applyRange = () => {
    isOpen.value = false
    period.value = 'custom'
    loadCustomRange()
}

const clearRange = () => {
    range.value = null
    period.value = 'weekly'
    isOpen.value = false
    loadPeriod('weekly')
}

const changePeriod = (p: Exclude<Period, 'custom'>) => {
    period.value = p
    range.value = null
    isOpen.value = false
    loadPeriod(p)
}

const onClickOutside = (e: MouseEvent) => {
    const target = e.target as HTMLElement
    if (calendarRef.value && !calendarRef.value.contains(target)) {
        isOpen.value = false
    }
}

onMounted(async () => {
    await loadPeriod('weekly')
    document.addEventListener('click', onClickOutside)
})

onUnmounted(() => {
    chartInstance?.destroy()
    document.removeEventListener('click', onClickOutside)
})
</script>

<template>
    <div class="oc-card">
        <div class="oc-head">
            <span class="oc-title">{{ $t('admin.dashboard.ordersOverview') }}</span>

            <div class="oc-controls">
                <div class="oc-tabs">
                    <button v-for="p in periods" :key="p.value" class="oc-tab" :class="{ active: period === p.value }"
                        @click="changePeriod(p.value)">
                        {{ p.label }}
                    </button>
                </div>

                <div class="oc-cal-wrap" ref="calendarRef">
                    <button class="oc-cal-btn" :class="{ active: period === 'custom' }" @click.stop="isOpen = !isOpen">
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
                                @update:model-value="(val: any) => range = val" mode="date" is-range :popover="false"
                                :locale="locale === 'tm' ? 'ru' : locale">
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
            <Transition name="fade">
                <div v-if="loading" class="oc-loading">
                    <div class="oc-skeleton" />
                </div>
                <canvas v-else ref="chartCanvas" />
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.oc-card {
    background: var(--color-white);
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 1px 4px rgb(var(--rgb-primary) / 0.06);
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
    color: var(--color-primary);
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
    background: var(--color-surface-alt);
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
    color: var(--color-muted);
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}

.oc-tab.active {
    background: var(--color-white);
    color: var(--color-primary);
    font-weight: 500;
    box-shadow: 0 1px 3px rgb(var(--rgb-black) / 0.08);
}

.oc-cal-wrap {
    position: relative;
}

.oc-cal-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--color-surface-alt);
    border: 1.5px solid transparent;
    border-radius: 8px;
    padding: 5px 11px;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    font-family: 'Jost', sans-serif;
    font-size: 0.75rem;
    color: var(--color-muted);
}

.oc-cal-btn.active,
.oc-cal-btn:hover {
    border-color: var(--color-success);
    color: var(--color-primary);
    background: var(--color-green-94-2);
}

.oc-cal-icon {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    color: var(--color-success);
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
    background: var(--color-white);
    border-radius: 14px;
    box-shadow: 0 8px 32px rgb(var(--rgb-primary) / 0.14);
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
    border-top: 1px solid var(--color-surface-alt);
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
    background: var(--color-surface-alt);
    color: var(--color-muted);
}

.oc-cal-clear:hover {
    background: var(--color-brown-86-2);
}

.oc-cal-apply {
    background: var(--color-success);
    color: var(--color-white);
}

.oc-cal-apply:hover {
    background: var(--color-green-26);
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
    background: linear-gradient(90deg, var(--color-surface-alt) 25%, var(--color-brown-88) 50%, var(--color-surface-alt) 75%);
    background-size: 400px 100%;
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
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
    color: var(--color-primary) !important;
}

:deep(.vc-weekday) {
    color: var(--color-muted) !important;
    font-size: 0.72rem !important;
}

:deep(.vc-day-content) {
    font-size: 0.82rem !important;
    border-radius: 8px !important;
}

:deep(.vc-highlight) {
    background-color: var(--color-success) !important;
}

:deep(.vc-highlight-content-solid) {
    color: var(--color-white) !important;
}
</style>