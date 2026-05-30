<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="modelValue" class="fixed inset-0 bg-[rgba(44,24,16,0.5)] backdrop-blur-sm z-50"
                @click.self="$emit('update:modelValue', false)" />
        </Transition>

        <Transition name="modal-slide">
            <div v-if="modelValue"
                class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[92%] max-w-[520px] bg-white rounded-2xl shadow-2xl z-50 flex flex-col"
                style="max-height: min(85vh, calc(100vh - 120px)); top: calc(50% + 30px);">

                <!-- Header -->
                <div class="flex items-start justify-between px-6 py-4 border-b border-[#F3F4F6]">
                    <div>
                        <h2 class="text-[#2C1A14] font-semibold text-lg">Order Details</h2>
                        <p v-if="order" class="text-[#8a7060] text-xs font-mono mt-0.5">#{{ order.order_no }}</p>
                    </div>
                    <button @click="$emit('update:modelValue', false)"
                        class="w-7 h-7 bg-[#F3F4F6] rounded-lg flex items-center justify-center text-[#6b7280] hover:bg-[#E5E7EB] transition-colors">
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
                        <div v-for="i in 4" :key="i" class="h-12 bg-[#F5EFEA] rounded-xl animate-pulse" />
                    </div>

                    <template v-else-if="order">
                        <!-- Status + type -->
                        <div class="flex items-center gap-3 flex-wrap">
                            <span :class="statusClass(order.status)"
                                class="text-xs font-semibold px-3 py-1 rounded-full">
                                {{ statusLabel(order.status) }}
                            </span>
                            <span class="text-xs text-[#7A6558] uppercase tracking-wider">
                                {{ order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup' }}
                            </span>
                            <span class="text-xs text-[#b0967a] ml-auto">
                                {{ formatDay(order.created_at) }} {{ formatMonth(order.created_at) }}
                            </span>
                        </div>

                        <!-- Items -->
                        <div>
                            <p class="text-[10px] font-bold text-[#8a7060] uppercase tracking-wider mb-3">Items</p>
                            <div class="space-y-2">
                                <div v-for="item in order.items" :key="item.product_id"
                                    class="flex items-center gap-3 p-3 bg-[#FAFAF8] rounded-xl">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-[#F0EDE6] flex items-center justify-center overflow-hidden flex-shrink-0">
                                        <img v-if="item.image_url" :src="resolveUrl(item.image_url)"
                                            :alt="item.product_name" class="w-full h-full object-cover" />
                                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="#b0967a" stroke-width="1.5"
                                            width="16" height="16">
                                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-[#2C1A14] truncate">{{ item.product_name }}
                                        </p>
                                        <p class="text-xs text-[#8a7060]">${{ item.price.toFixed(2) }} each</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-xs text-[#8a7060]">×{{ item.quantity }}</p>
                                        <p class="text-sm font-bold text-[#4A6741]">${{ item.subtotal.toFixed(2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="flex items-center justify-between bg-[#2C1A14] text-[#C8A96A] px-4 py-3 rounded-xl">
                            <span class="text-sm font-bold">Total</span>
                            <span class="text-lg font-semibold">${{ order.total_price.toFixed(2) }}</span>
                        </div>

                        <!-- Note -->
                        <div v-if="order.note" class="p-3 bg-[#FAFAF8] rounded-xl border-l-4 border-[#C8A96A]">
                            <p class="text-[10px] uppercase tracking-wider text-[#8a7060] mb-1">Note</p>
                            <p class="text-sm text-[#6b7280]">{{ order.note }}</p>
                        </div>

                        <!-- Delivery info -->
                        <div v-if="order.delivery_type === 'delivery' && (order.address || order.phone)"
                            class="p-3 bg-[#FAFAF8] rounded-xl space-y-1">
                            <p class="text-[10px] uppercase tracking-wider text-[#8a7060] mb-2">Delivery Info</p>
                            <p v-if="order.address" class="text-sm text-[#2C1A14]">📍 {{ order.address }}</p>
                            <p v-if="order.phone" class="text-sm text-[#2C1A14]">📞 {{ order.phone }}</p>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-[#F3F4F6] flex justify-between items-center gap-3">
                    <button v-if="order?.status === 'pending'" @click="$emit('cancel', order.id)"
                        class="text-sm px-4 py-2 border border-red-200 text-red-500 rounded-xl hover:bg-red-50 transition-colors">
                        Cancel Order
                    </button>
                    <div v-else />
                    <button @click="$emit('update:modelValue', false)"
                        class="text-sm px-5 py-2 bg-[#2C1A14] text-[#C8A96A] rounded-xl hover:bg-[#3d2416] transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import type { Order } from '~/composables/useProfile'

defineProps<{
    modelValue: boolean
    order: Order | null
    loading: boolean
}>()

defineEmits<{
    'update:modelValue': [value: boolean]
    'cancel': [id: number]
}>()

const config = useRuntimeConfig()
const BACKEND_BASE = (config.public.apiBase as string).replace(/\/api\/?$/, '')
function resolveUrl(url: string | null | undefined) {
    if (!url) return null
    if (url.startsWith('http')) return url
    return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
}

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
        pending: 'Pending', preparing: 'Preparing',
        ready: 'Ready', delivered: 'Delivered', cancelled: 'Cancelled',
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