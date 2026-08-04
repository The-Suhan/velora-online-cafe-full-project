<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="modelValue" class="fixed inset-0 bg-[rgb(var(--rgb-primary) / 0.5)] backdrop-blur-sm z-50"
                @click.self="$emit('update:modelValue', false)" />
        </Transition>

        <Transition name="modal-slide">
            <div v-if="modelValue"
                class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[92%] max-w-[520px] bg-white rounded-2xl shadow-2xl z-50 flex flex-col"
                style="max-height: min(85vh, calc(100vh - 120px)); top: calc(50% + 30px);">

                <!-- Header -->
                <div class="flex items-start justify-between px-6 py-4 border-b border-[var(--color-gray-96)]">
                    <div>
                        <h2 class="text-[var(--color-primary-deep)] font-semibold text-lg">{{ $t('profile.orderDetail.title') }}</h2>
                        <p v-if="order" class="text-[var(--color-muted)] text-xs font-mono mt-0.5">#{{ order.order_no }}</p>
                    </div>
                    <button @click="$emit('update:modelValue', false)"
                        class="w-7 h-7 bg-[var(--color-gray-96)] rounded-lg flex items-center justify-center text-[var(--color-gray-46)] hover:bg-[var(--color-border)] transition-colors"
                        :aria-label="$t('profile.orderDetail.close')">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                    <!-- Loading -->
                    <div v-if="loading" class="space-y-3">
                        <div v-for="i in 4" :key="i" class="h-12 bg-[var(--color-surface)] rounded-xl animate-pulse" />
                    </div>

                    <template v-else-if="order">
                        <!-- Status + type -->
                        <div class="flex items-center gap-3 flex-wrap">
                            <span :class="statusClass(order.status)"
                                class="text-xs font-semibold px-3 py-1 rounded-full">
                                {{ statusLabel(order.status) }}
                            </span>
                            <span class="text-xs text-[var(--color-muted-dark)] uppercase tracking-wider">
                                {{ order.delivery_type === 'delivery' ? $t('profile.orderDetail.delivery') :
                                    $t('profile.orderDetail.pickup') }}
                            </span>
                            <span class="text-xs text-[var(--color-muted-light)] ml-auto">
                                {{ formatDay(order.created_at) }} {{ formatMonth(order.created_at) }}
                            </span>
                        </div>

                        <!-- Items -->
                        <div>
                            <p class="text-[10px] font-bold text-[var(--color-muted)] uppercase tracking-wider mb-3">{{
                                $t('profile.orderDetail.items') }}</p>
                            <div class="space-y-2">
                                <div v-for="item in order.items" :key="item.product_id"
                                    class="flex items-center gap-3 p-3 bg-[var(--color-surface-soft)] rounded-xl">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-[var(--color-surface-alt)] flex items-center justify-center overflow-hidden flex-shrink-0">
                                        <img v-if="item.image_url" :src="resolveUrl(item.image_url)"
                                            :alt="getProductName(item)" class="w-full h-full object-cover" />
                                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="var(--color-muted-light)" stroke-width="1.5"
                                            width="16" height="16">
                                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-[var(--color-primary-deep)] truncate">{{ getProductName(item)
                                            }}</p>
                                        <p class="text-xs text-[var(--color-muted)]">${{ item.price.toFixed(2) }} {{
                                            $t('profile.orderDetail.each') }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-xs text-[var(--color-muted)]">×{{ item.quantity }}</p>
                                        <p class="text-sm font-bold text-[var(--color-success)]">${{ item.subtotal.toFixed(2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="flex items-center justify-between bg-[var(--color-primary-deep)] text-[var(--color-accent-warm)] px-4 py-3 rounded-xl">
                            <span class="text-sm font-bold">{{ $t('profile.orderDetail.total') }}</span>
                            <span class="text-lg font-semibold">${{ order.total_price.toFixed(2) }}</span>
                        </div>

                        <!-- Note -->
                        <div v-if="order.note" class="p-3 bg-[var(--color-surface-soft)] rounded-xl border-l-4 border-[var(--color-accent-warm)]">
                            <p class="text-[10px] uppercase tracking-wider text-[var(--color-muted)] mb-1">{{
                                $t('profile.orderDetail.note') }}</p>
                            <p class="text-sm text-[var(--color-gray-46)]">{{ order.note }}</p>
                        </div>

                        <!-- Delivery info -->
                        <div class="p-3 bg-[var(--color-surface-soft)] rounded-xl space-y-1">
                            <p class="text-[10px] uppercase tracking-wider text-[var(--color-muted)] mb-2">{{
                                $t('profile.orderDetail.contactInfo') }}</p>
                            <p v-if="order.address" class="text-sm text-[var(--color-primary-deep)]">📍 {{ order.address }}</p>
                            <p class="text-sm text-[var(--color-primary-deep)]">📞 {{ order.phone }}</p>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-[var(--color-gray-96)] flex justify-between items-center gap-3">
                    <button v-if="order?.status === 'pending'" @click="$emit('cancel', order.id)"
                        class="text-sm px-4 py-2 border border-red-200 text-red-500 rounded-xl hover:bg-red-50 transition-colors">
                        {{ $t('profile.orderDetail.cancelOrder') }}
                    </button>
                    <div v-else />
                    <button @click="$emit('update:modelValue', false)"
                        class="text-sm px-5 py-2 bg-[var(--color-primary-deep)] text-[var(--color-accent-warm)] rounded-xl hover:bg-[var(--color-coffee-16)] transition-colors">
                        {{ $t('profile.orderDetail.close') }}
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import type { Order } from '~/composables/useProfile'
const { locale, t } = useI18n()

function getProductName(item: any): string {
    const tr = item.product_translations
    if (!tr) return item.product_name ?? ''
    const entry = Array.isArray(tr)
        ? tr.find((x: any) => x.locale === locale.value)
        : tr[locale.value]
    return entry?.name ?? item.product_name ?? ''
}

defineProps<{
    modelValue: boolean
    order: Order | null
    loading: boolean
}>()

defineEmits<{
    'update:modelValue': [value: boolean]
    'cancel': [id: number]
}>()

const { resolveUrl } = useMediaUrl()

function formatDay(dateStr: string) {
    return new Date(dateStr).getDate().toString().padStart(2, '0')
}
function formatMonth(dateStr: string) {
    return new Date(dateStr).toLocaleString('en', { month: 'short' }).toUpperCase()
}
function statusClass(status: string) {
    return ({
        pending: 'bg-amber-50 text-amber-700',
        preparing: 'bg-blue-50 text-blue-700',
        ready: 'bg-purple-50 text-purple-700',
        delivered: 'bg-green-50 text-green-700',
        cancelled: 'bg-red-50 text-red-600',
    } as Record<string, string>)[status] ?? 'bg-gray-100 text-gray-600'
}
function statusLabel(status: string) {
    return ({
        pending: t('admin.statuses.pending'),
        preparing: t('admin.statuses.preparing'),
        ready: t('admin.statuses.ready'),
        delivered: t('admin.statuses.delivered'),
        cancelled: t('admin.statuses.cancelled'),
    } as Record<string, string>)[status] ?? status
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity .2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.modal-slide-enter-active {
    transition: all .28s cubic-bezier(.34, 1.56, .64, 1);
}

.modal-slide-leave-active {
    transition: all .18s ease;
}

.modal-slide-enter-from {
    opacity: 0;
    transform: translate(-50%, -46%) scale(.95);
}

.modal-slide-leave-to {
    opacity: 0;
    transform: translate(-50%, -54%) scale(.95);
}
</style>