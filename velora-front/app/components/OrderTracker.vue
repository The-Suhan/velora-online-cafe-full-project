<template>
    <!-- İptal edilen siparişte adım çizelgesi anlamsız — tek durum gösterilir. -->
    <div v-if="status === 'cancelled'" class="tracker cancelled" :class="{ compact }">
        <span class="cancelled-dot" />
        <span class="cancelled-label">{{ $t('profile.tracking.cancelledNote') }}</span>
    </div>

    <div v-else class="tracker" :class="{ compact }">
        <p v-if="!compact" class="tracker-title">
            {{ $t('profile.tracking.title') }}
            <span class="tracker-live">{{ $t('profile.tracking.live') }}</span>
        </p>

        <div class="status-timeline">
            <div v-for="step in STATUS_ORDER" :key="step" class="timeline-step" :class="{
                done: isStepDone(step, status),
                current: step === status,
            }">
                <div class="timeline-dot" />
                <span class="timeline-label">{{ $t(`admin.statuses.${step}`) }}</span>
                <span v-if="!compact && stepTime(step)" class="timeline-time">{{ stepTime(step) }}</span>
            </div>
        </div>

        <p v-if="!compact" class="tracker-step">
            {{ $t('profile.tracking.step', { n: stepIndex + 1, total: STATUS_ORDER.length }) }}
        </p>
    </div>
</template>

<script setup lang="ts">
import type { OrderStatus, OrderStatusHistoryEntry } from '~/composables/Useprofile'

const props = withDefaults(defineProps<{
    status: OrderStatus
    history?: OrderStatusHistoryEntry[]
    compact?: boolean
}>(), {
    history: () => [],
    compact: false,
})

// STATUS_ORDER ve adım mantığı useOrders.ts'te zaten tanımlı — tekrar etme.
const isStepDone = (step: OrderStatus, current: OrderStatus) => {
    if (current === 'cancelled') return false
    return STATUS_ORDER.indexOf(step) <= STATUS_ORDER.indexOf(current)
}

const stepIndex = computed(() => Math.max(0, STATUS_ORDER.indexOf(props.status)))

const stepTime = (step: OrderStatus) =>
    props.history?.find(h => h.to_status === step)?.at ?? null
</script>

<style scoped>
.tracker {
    font-family: 'Jost', sans-serif;
}

.tracker-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 0.625rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--color-ink);
}

.tracker-live {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.6rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--color-success);
}

.tracker-live::before {
    content: '';
    width: 0.4rem;
    height: 0.4rem;
    border-radius: 50%;
    background: var(--color-success);
    animation: tracker-pulse 1.8s ease-in-out infinite;
}

@keyframes tracker-pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.25;
    }
}

.tracker-step {
    margin: 0.5rem 0 0;
    font-size: 0.68rem;
    color: var(--color-muted);
}

/* ── Timeline — OrderModals.vue'deki admin çizelgesiyle aynı görsel dil ── */
.status-timeline {
    display: flex;
    align-items: flex-start;
    padding: 14px 16px;
    background: var(--color-surface-soft);
    border-radius: 12px;
    overflow-x: auto;
}

.compact .status-timeline {
    padding: 8px 10px;
    border-radius: 8px;
}

.timeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex: 1;
    position: relative;
}

.timeline-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 8px;
    left: calc(50% + 8px);
    right: calc(-50% + 8px);
    height: 2px;
    background: var(--color-border);
}

.compact .timeline-step:not(:last-child)::after {
    top: 5px;
}

.timeline-step.done::after {
    background: var(--color-success);
}

.timeline-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--color-border);
    border: 2px solid var(--color-border);
    z-index: 1;
    transition: all .3s;
}

.compact .timeline-dot {
    width: 10px;
    height: 10px;
    border-width: 2px;
}

.timeline-step.done .timeline-dot {
    background: var(--color-success);
    border-color: var(--color-success);
}

.timeline-step.current .timeline-dot {
    background: var(--color-card);
    border-color: var(--color-success);
    border-width: 3px;
    box-shadow: 0 0 0 3px rgb(var(--rgb-success) / .2);
}

.timeline-label {
    font-size: 0.62rem;
    color: var(--color-muted-light);
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
}

.compact .timeline-label {
    font-size: 0.56rem;
}

.timeline-step.done .timeline-label,
.timeline-step.current .timeline-label {
    color: var(--color-success);
    font-weight: 600;
}

.timeline-time {
    font-size: 0.55rem;
    color: var(--color-muted);
    white-space: nowrap;
}

/* ── Cancelled ─────────────────────────────────────────────── */
.tracker.cancelled {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 10px 14px;
    background: var(--color-surface-soft);
    border-radius: 12px;
    border-left: 3px solid var(--color-danger);
}

.tracker.cancelled.compact {
    padding: 7px 10px;
    border-radius: 8px;
}

.cancelled-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background: var(--color-danger);
    flex-shrink: 0;
}

.cancelled-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--color-danger);
}
</style>
