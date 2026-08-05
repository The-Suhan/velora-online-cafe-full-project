<template>
    <!-- Overlay -->
    <Transition name="fade">
        <div v-if="activeModal" class="modal-overlay" @click.self="closeModal" />
    </Transition>

    <!-- ── PREVIEW Modal ─────────────────────────────────────── -->
    <Transition name="modal-slide">
        <div v-if="activeModal === 'preview' && selectedOrder" class="modal modal-preview">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title">{{ $t('admin.orders.modals.preview.title') }}</h2>
                    <p class="modal-sub">{{ selectedOrder.order_number }}</p>
                </div>
                <button class="modal-close" @click="closeModal">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path
                            d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <!-- Status timeline -->
                <div class="status-timeline">
                    <div v-for="step in statusSteps" :key="step.key" class="timeline-step" :class="{
                        done: isStepDone(step.key, selectedOrder.status),
                        current: step.key === selectedOrder.status,
                        cancelled: selectedOrder.status === 'cancelled'
                    }">
                        <div class="timeline-dot" />
                        <span class="timeline-label">{{ step.label }}</span>
                    </div>
                </div>

                <!-- Customer -->
                <div class="preview-section">
                    <p class="section-title">{{ $t('admin.orders.modals.preview.customer') }}</p>
                    <div class="customer-preview">
                        <div class="avatar avatar-lg">{{ initials(selectedOrder.customer?.name) }}</div>
                        <div>
                            <p class="cust-name">{{ selectedOrder.customer?.name ?? '—' }}</p>
                            <p class="cust-email">{{ selectedOrder.customer?.email ?? '' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Delivery Info -->
                <div class="preview-section">
                    <p class="section-title">{{ $t('admin.orders.modals.preview.delivery') }}</p>
                    <div class="delivery-info">
                        <span class="delivery-badge"
                            :class="selectedOrder.delivery_type === 'delivery' ? 'badge-delivery' : 'badge-pickup'">
                            <svg v-if="selectedOrder.delivery_type === 'delivery'" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" width="13" height="13">
                                <rect x="1" y="3" width="15" height="13" rx="1" />
                                <path d="M16 8h4l3 5v4h-7V8z" stroke-linecap="round" stroke-linejoin="round" />
                                <circle cx="5.5" cy="18.5" r="2.5" />
                                <circle cx="18.5" cy="18.5" r="2.5" />
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                width="13" height="13">
                                <path d="M6 2h12l3 7H3L6 2z" />
                                <path d="M3 9v11a2 2 0 002 2h14a2 2 0 002-2V9" />
                            </svg>
                            {{ selectedOrder.delivery_type === 'delivery' ? $t('admin.orders.deliveryType.delivery') :
                                $t('admin.orders.deliveryType.pickup') }}
                        </span>
                        <div class="delivery-details">
                            <div v-if="selectedOrder.address" class="delivery-row">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" width="13"
                                    height="13" style="flex-shrink:0;color:var(--color-muted)">
                                    <path d="M10 2a6 6 0 00-6 6c0 4 6 10 6 10s6-6 6-10a6 6 0 00-6-6z"
                                        stroke-linecap="round" />
                                    <circle cx="10" cy="8" r="2" />
                                </svg>
                                <span>{{ selectedOrder.address }}</span>
                            </div>
                            <div v-if="selectedOrder.phone" class="delivery-row">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" width="13"
                                    height="13" style="flex-shrink:0;color:var(--color-muted)">
                                    <path
                                        d="M3 4a1 1 0 011-1h2.5l1 3-1.5 1.5a11 11 0 005.5 5.5L13 11.5l3 1V15a1 1 0 01-1 1A13 13 0 013 4z"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>{{ selectedOrder.phone }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="preview-section">
                    <p class="section-title">{{ $t('admin.orders.modals.preview.items') }}</p>
                    <div v-if="detailLoading" class="items-loading">
                        <div v-for="i in 3" :key="i" class="skeleton-item" />
                    </div>
                    <div v-else class="items-list">
                        <div v-for="item in selectedOrder.items" :key="item.id" class="item-row">
                            <div class="item-img-wrap">
                                <img v-if="item.product?.image_url" :src="resolveUrl(item.product.image_url)"
                                    :alt="item.product.name" class="item-img" />
                                <div v-else class="item-img-placeholder">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        width="14" height="14">
                                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="item-info">
                                <p class="item-name">{{ item.product?.name ??
                                    $t('admin.orders.modals.preview.unknownProduct') }}</p>
                                <p class="item-unit">{{ $t('admin.orders.modals.preview.eachPrice', {
                                    price:
                                        item.price.toFixed(2)
                                }) }}</p>
                            </div>
                            <div class="item-right">
                                <span class="item-qty">×{{ item.quantity }}</span>
                                <span class="item-subtotal">${{ item.subtotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="selectedOrder.delivery_type === 'delivery'" class="delivery-fee-row">
                        <span>{{ $t('admin.orders.modals.receipt.deliveryFee') }}</span>
                        <span>+${{ props.deliveryFee.toFixed(2) }}</span>
                    </div>
                    <div class="order-total-row">
                        <span>{{ $t('admin.orders.modals.preview.total') }}</span>
                        <span class="order-total-price">
                            ${{ (selectedOrder.total_price + (selectedOrder.delivery_type === 'delivery' ?
                                props.deliveryFee : 0)).toFixed(2) }}
                        </span>
                    </div>
                </div>

                <!-- Note -->
                <div v-if="selectedOrder.note" class="preview-section">
                    <p class="section-title">{{ $t('admin.orders.modals.preview.note') }}</p>
                    <p class="note-text">{{ selectedOrder.note }}</p>
                </div>

                <!-- Meta -->
                <div class="preview-section">
                    <div class="meta-row">
                        <span class="meta-key">{{ $t('admin.orders.modals.preview.created') }}</span>
                        <span class="meta-val">{{ selectedOrder.created_at }} {{ selectedOrder.created_at_time }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">{{ $t('admin.orders.modals.preview.updated') }}</span>
                        <span class="meta-val">{{ selectedOrder.updated_at }}</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-secondary" @click="$emit('openReceipt', selectedOrder)">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="13"
                        height="13">
                        <path d="M4 2h12v16l-2-1-2 1-2-1-2 1-2-1-2 1V2z" stroke-linejoin="round" />
                    </svg>
                    {{ $t('admin.orders.statusPage.exportReceiptBtn') }}
                </button>
                <button class="btn-submit" @click="$emit('openEdit', selectedOrder)">
                    {{ $t('admin.orders.statusPage.editStatusBtn') }}
                </button>
            </div>
        </div>
    </Transition>

    <!-- ── EDIT STATUS Modal ──────────────────────────────────── -->
    <Transition name="modal-slide">
        <div v-if="activeModal === 'edit' && selectedOrder" class="modal modal-edit">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title">{{ $t('admin.orders.modals.edit.title') }}</h2>
                    <p class="modal-sub">{{ selectedOrder.order_number }}</p>
                </div>
                <button class="modal-close" @click="closeModal">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path
                            d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ $t('admin.orders.modals.edit.currentStatus') }}</label>
                    <span class="status-badge" :class="`status-${selectedOrder.status}`" style="display:inline-flex">
                        <span class="status-dot" :class="`dot-${selectedOrder.status}`" />
                        {{ capitalize(selectedOrder.status) }}
                    </span>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        {{ $t('admin.orders.modals.edit.changeTo') }} <span class="required">*</span>
                    </label>
                    <div v-if="selectedOrder.allowed_next.length === 0" class="terminal-msg">
                        {{ $t('admin.orders.modals.edit.terminalMsg') }}
                    </div>
                    <div v-else class="status-options">
                        <button v-for="s in selectedOrder.allowed_next" :key="s" class="status-option"
                            :class="{ selected: editForm.status === s, [`opt-${s}`]: true }"
                            @click="editForm.status = s">
                            <span class="status-dot" :class="`dot-${s}`" />
                            {{ capitalize(s) }}
                        </button>
                    </div>
                    <p v-if="editErrors.status" class="form-error">{{ editErrors.status }}</p>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ $t('admin.orders.modals.edit.noteLabel') }}</label>
                    <textarea v-model="editForm.note" class="form-input form-textarea" rows="3"
                        :placeholder="$t('admin.orders.modals.edit.notePlaceholder')" />
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" @click="closeModal">{{ $t('admin.common.cancel') }}</button>
                <button class="btn-submit" :disabled="submitting || selectedOrder.allowed_next.length === 0"
                    @click="$emit('submitEdit')">
                    <span v-if="submitting" class="spinner" />
                    {{ submitting ? $t('admin.common.saving') : $t('admin.common.save') }}
                </button>
            </div>
        </div>
    </Transition>

    <!-- ── CANCEL Modal ───────────────────────────────────────── -->
    <Transition name="modal-slide">
        <div v-if="activeModal === 'cancel' && selectedOrder" class="modal modal-sm">
            <div class="modal-header">
                <h2 class="modal-title">{{ $t('admin.orders.modals.cancel.title') }}</h2>
                <button class="modal-close" @click="closeModal">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path
                            d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirm-icon warning-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36"
                        height="36">
                        <path
                            d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <p class="confirm-title">{{ $t('admin.orders.modals.cancel.confirmTitle') }}</p>
                <p class="confirm-desc">
                    {{ $t('admin.orders.modals.cancel.confirmDesc', {
                        order: selectedOrder.order_number,
                        customer: selectedOrder.customer?.name
                    }) }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" @click="closeModal">{{ $t('admin.orders.modals.cancel.goBack') }}</button>
                <button class="btn-warning" :disabled="submitting" @click="$emit('submitCancel')">
                    <span v-if="submitting" class="spinner" />
                    {{ submitting ? $t('admin.orders.modals.cancel.cancelling') :
                        $t('admin.orders.statusPage.cancelOrderBtn') }}
                </button>
            </div>
        </div>
    </Transition>

    <!-- ── DELETE Modal ───────────────────────────────────────── -->
    <Transition name="modal-slide">
        <div v-if="activeModal === 'delete' && selectedOrder" class="modal modal-sm">
            <div class="modal-header">
                <h2 class="modal-title">{{ $t('admin.orders.modals.delete.title') }}</h2>
                <button class="modal-close" @click="closeModal">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path
                            d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirm-icon danger-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36"
                        height="36">
                        <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <p class="confirm-title">{{ $t('admin.common.areYouSure') }}</p>
                <p class="confirm-desc">
                    {{ $t('admin.orders.modals.delete.confirmDesc', { order: selectedOrder.order_number }) }}
                    <span class="delete-warning">{{ $t('admin.orders.modals.delete.deleteWarning') }}</span>
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" @click="closeModal">{{ $t('admin.common.cancel') }}</button>
                <button class="btn-danger" :disabled="submitting" @click="$emit('submitDelete')">
                    <span v-if="submitting" class="spinner" />
                    {{ submitting ? $t('admin.common.deleting') : $t('admin.common.delete') }}
                </button>
            </div>
        </div>
    </Transition>

    <!-- ── RECEIPT Modal ──────────────────────────────────────── -->
    <Transition name="modal-slide">
        <div v-if="activeModal === 'receipt' && selectedOrder" class="modal modal-receipt">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title">{{ $t('admin.orders.modals.receipt.title') }}</h2>
                    <p class="modal-sub">{{ selectedOrder.order_number }}</p>
                </div>
                <button class="modal-close" @click="closeModal">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path
                            d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
            <div class="modal-body receipt-body">
                <div class="receipt-paper">
                    <!-- Header -->
                    <div class="receipt-header">
                        <div class="receipt-logo">☕</div>
                        <h2 class="receipt-brand">VELORA CAFÉ</h2>
                        <p class="receipt-tagline">{{ $t('admin.orders.modals.receipt.tagline') }}</p>
                    </div>
                    <div class="receipt-divider"><span>· · · · · · · · · · · · · · · · · · ·</span></div>
                    <!-- Order Info -->
                    <div class="receipt-info">
                        <div class="receipt-info-row">
                            <span>{{ $t('admin.orders.modals.receipt.order') }}</span>
                            <span>{{ selectedOrder.order_number }}</span>
                        </div>
                        <div class="receipt-info-row">
                            <span>{{ $t('admin.orders.modals.receipt.date') }}</span>
                            <span>{{ selectedOrder.created_at }} {{ selectedOrder.created_at_time }}</span>
                        </div>
                        <div class="receipt-info-row">
                            <span>{{ $t('admin.orders.modals.receipt.customer') }}</span>
                            <span>{{ selectedOrder.customer?.name ?? '—' }}</span>
                        </div>
                        <div class="receipt-info-row">
                            <span>{{ $t('admin.orders.modals.receipt.status') }}</span>
                            <span class="receipt-status">{{ capitalize(selectedOrder.status) }}</span>
                        </div>
                        <!-- Delivery type -->
                        <div class="receipt-info-row">
                            <span>{{ $t('admin.orders.modals.receipt.deliveryType') }}</span>
                            <span class="receipt-status">{{ selectedOrder.delivery_type === 'delivery' ?
                                $t('admin.orders.deliveryType.delivery') : $t('admin.orders.deliveryType.pickup')
                                }}</span>
                        </div>
                        <div v-if="selectedOrder.address" class="receipt-info-row">
                            <span>{{ $t('admin.orders.modals.receipt.address') }}</span>
                            <span>{{ selectedOrder.address }}</span>
                        </div>
                        <div v-if="selectedOrder.phone" class="receipt-info-row">
                            <span>{{ $t('admin.orders.modals.receipt.phone') }}</span>
                            <span>{{ selectedOrder.phone }}</span>
                        </div>
                    </div>
                    <div class="receipt-divider"><span>· · · · · · · · · · · · · · · · · · ·</span></div>
                    <!-- Items -->
                    <div class="receipt-items">
                        <div v-if="detailLoading" class="receipt-loading">
                            {{ $t('admin.orders.modals.receipt.loadingItems') }}
                        </div>
                        <template v-else>
                            <div class="receipt-items-header">
                                <span>{{ $t('admin.orders.modals.receipt.itemCol') }}</span>
                                <span>{{ $t('admin.orders.modals.receipt.qtyCol') }}</span>
                                <span>{{ $t('admin.orders.modals.receipt.priceCol') }}</span>
                            </div>
                            <div v-for="item in selectedOrder.items" :key="item.id" class="receipt-item-row">
                                <span class="receipt-item-name">{{ item.product?.name ??
                                    $t('admin.orders.modals.receipt.defaultItem') }}</span>
                                <span class="receipt-item-qty">×{{ item.quantity }}</span>
                                <span class="receipt-item-price">${{ item.subtotal.toFixed(2) }}</span>
                            </div>
                            <div v-if="selectedOrder.delivery_type === 'delivery'"
                                class="receipt-item-row receipt-delivery-row">
                                <span class="receipt-item-name" style="font-style:italic;color:var(--color-muted);">
                                    {{ $t('admin.orders.modals.receipt.deliveryFee') }}
                                </span>
                                <span class="receipt-item-qty">×1</span>
                                <span class="receipt-item-price">${{ props.deliveryFee.toFixed(2) }}</span>
                            </div>
                        </template>
                    </div>
                    <div class="receipt-divider"><span>· · · · · · · · · · · · · · · · · · ·</span></div>
                    <div class="receipt-total">
                        <span>{{ $t('admin.orders.modals.receipt.totalLabel') }}</span>
                        <span>${{ (selectedOrder.total_price + (selectedOrder.delivery_type === 'delivery' ?
                            props.deliveryFee :
                            0)).toFixed(2) }}</span>
                    </div>
                    <div v-if="selectedOrder.note" class="receipt-note">
                        <p class="receipt-note-label">{{ $t('admin.orders.modals.preview.note') }}</p>
                        <p class="receipt-note-text">{{ selectedOrder.note }}</p>
                    </div>
                    <div class="receipt-divider"><span>· · · · · · · · · · · · · · · · · · ·</span></div>
                    <div class="receipt-footer">
                        <p class="receipt-quote">{{ $t('admin.orders.modals.receipt.quote') }}</p>
                        <p class="receipt-thanks">{{ $t('admin.orders.modals.receipt.thanks') }}</p>
                        <p class="receipt-website">velora.cafe</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" @click="closeModal">{{ $t('admin.common.close') }}</button>
                <button class="btn-submit" :disabled="pdfLoading" @click="$emit('downloadPdf')">
                    <span v-if="pdfLoading" class="spinner" />
                    <svg v-else viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="13"
                        height="13">
                        <path d="M10 3v10M6 9l4 4 4-4" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M3 17h14" stroke-linecap="round" />
                    </svg>
                    {{ pdfLoading ? $t('admin.orders.modals.receipt.generating') :
                        $t('admin.orders.modals.receipt.downloadPdf') }}
                </button>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import type { Order, OrderStatus } from '~/composables/useOrders'
import { STATUS_ORDER } from '~/composables/useOrders'

const props = defineProps<{
    activeModal: string | null
    selectedOrder: Order | null
    detailLoading: boolean
    submitting: boolean
    pdfLoading: boolean
    editForm: { status: OrderStatus | ''; note: string }
    editErrors: { status: string }
    deliveryFee: number
}>()

const statusSteps = [
    { key: 'pending' as OrderStatus, label: 'Pending' },
    { key: 'preparing' as OrderStatus, label: 'Preparing' },
    { key: 'ready' as OrderStatus, label: 'Ready' },
    { key: 'delivered' as OrderStatus, label: 'Delivered' },
]

const capitalize = (s: string) => s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
const initials = (name?: string | null) => {
    if (!name) return '?'
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
}

const { resolveUrl } = useMediaUrl()

const isStepDone = (stepKey: OrderStatus, currentStatus: OrderStatus) => {
    if (currentStatus === 'cancelled') return false
    return STATUS_ORDER.indexOf(stepKey) <= STATUS_ORDER.indexOf(currentStatus)
}

const emit = defineEmits([
    'closeModal',
    'openReceipt',
    'openEdit',
    'submitEdit',
    'submitCancel',
    'submitDelete',
    'downloadPdf'
])

function closeModal() {
    emit('closeModal')
}
</script>

<style scoped>
/* ── Overlay ─────────────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgb(var(--rgb-scrim) / .5);
    backdrop-filter: blur(3px);
    z-index: 200;
}

/* ── Modal Base ──────────────────────────────────────────── */
.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 92%;
    max-width: 520px;
    max-height: 92vh;
    background: var(--color-card);
    border-radius: 20px;
    box-shadow: 0 24px 60px rgb(var(--rgb-primary) / .2);
    z-index: 300;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.modal-preview {
    max-width: 560px;
}

.modal-edit {
    max-width: 460px;
}

.modal-sm {
    max-width: 420px;
}

.modal-receipt {
    max-width: 480px;
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1.5px solid var(--color-gray-96);
    flex-shrink: 0;
}

.modal-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--color-primary);
    margin: 0 0 2px;
}

.modal-sub {
    font-size: 0.75rem;
    color: var(--color-muted);
    margin: 0;
    font-family: 'Courier New', monospace;
}

.modal-close {
    width: 28px;
    height: 28px;
    border: none;
    background: var(--color-gray-96);
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--color-gray-46);
    flex-shrink: 0;
    transition: background .15s;
}

.modal-close:hover {
    background: var(--color-border);
}

.modal-body {
    padding: 20px 24px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    padding: 14px 24px;
    border-top: 1.5px solid var(--color-gray-96);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-shrink: 0;
}


.delivery-fee-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 12px;
    font-size: 0.82rem;
    color: var(--color-muted);
    font-style: italic;
    background: var(--color-surface-soft);
    border-radius: 8px;
    margin-top: 6px;
}

/* ── Status Timeline ─────────────────────────────────────── */
.status-timeline {
    display: flex;
    align-items: center;
    margin-bottom: 22px;
    padding: 14px 16px;
    background: var(--color-surface-soft);
    border-radius: 12px;
    overflow-x: auto;
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

.timeline-step.cancelled .timeline-dot {
    background: var(--color-danger);
    border-color: var(--color-danger);
}

.timeline-label {
    font-size: 0.62rem;
    color: var(--color-muted-light);
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
}

.timeline-step.done .timeline-label,
.timeline-step.current .timeline-label {
    color: var(--color-success);
    font-weight: 600;
}

/* ── Preview sections ────────────────────────────────────── */
.preview-section {
    margin-bottom: 18px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--color-gray-96);
}

.preview-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--color-muted);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 10px;
}

.customer-preview {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-accent), var(--color-gold-31));
    color: var(--color-white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 700;
    flex-shrink: 0;
}

.avatar-lg {
    width: 42px;
    height: 42px;
    font-size: 0.82rem;
}

.cust-name {
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--color-primary);
    margin: 0 0 1px;
}

.cust-email {
    font-size: 0.73rem;
    color: var(--color-muted);
    margin: 0;
}

/* ── Delivery Info ───────────────────────────────────────── */
.delivery-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.delivery-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
    align-self: flex-start;
}

.badge-delivery {
    background: var(--color-blue-97);
    color: var(--color-blue-48);
    border: 1.5px solid var(--color-blue-87);
}

.badge-pickup {
    background: var(--color-brown-94);
    color: var(--color-gold-31);
    border: 1.5px solid var(--color-gold-78);
}

.delivery-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 10px 12px;
    background: var(--color-surface-soft);
    border-radius: 8px;
    border-left: 3px solid var(--color-success);
}

.delivery-row {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    font-size: 0.82rem;
    color: var(--color-gray-27);
    line-height: 1.4;
}

/* ── Items ───────────────────────────────────────────────── */
.items-loading {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.skeleton-item {
    height: 50px;
    background: var(--color-surface-alt);
    border-radius: 10px;
    animation: shimmer 1.2s infinite;
}

@keyframes shimmer {

    0%,
    100% {
        opacity: 1
    }

    50% {
        opacity: .5
    }
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.item-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: var(--color-surface-soft);
    border-radius: 10px;
}

.item-img-wrap {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--color-surface-alt);
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-img-placeholder {
    color: var(--color-muted-light);
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--color-primary);
    margin: 0 0 2px;
}

.item-unit {
    font-size: 0.72rem;
    color: var(--color-muted);
    margin: 0;
}

.item-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
    flex-shrink: 0;
}

.item-qty {
    font-size: 0.72rem;
    color: var(--color-muted);
}

.item-subtotal {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--color-success);
}

.order-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 11px 12px;
    background: var(--color-brand-surface);
    border-radius: 10px;
    margin-top: 8px;
    color: var(--color-accent);
    font-weight: 700;
    font-size: 0.88rem;
}

.order-total-price {
    font-size: 1.05rem;
    font-family: 'Cormorant Garamond', serif;
}

.note-text {
    font-size: 0.83rem;
    color: var(--color-gray-46);
    background: var(--color-surface-soft);
    padding: 10px 12px;
    border-radius: 8px;
    border-left: 3px solid var(--color-accent);
    line-height: 1.5;
    margin: 0;
}

.meta-row {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
}

.meta-key {
    font-size: 0.76rem;
    color: var(--color-muted-light);
    font-weight: 500;
}

.meta-val {
    font-size: 0.76rem;
    color: var(--color-primary);
    font-weight: 500;
}

/* ── Edit modal ──────────────────────────────────────────── */
.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    font-size: 0.81rem;
    font-weight: 600;
    color: var(--color-primary);
    margin-bottom: 7px;
}

.required {
    color: var(--color-danger);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.74rem;
    font-weight: 600;
}

.status-pending {
    background: var(--color-gold-89);
    color: var(--color-coffee-31);
}

.status-preparing {
    background: var(--color-blue-93);
    color: var(--color-blue-48);
}

.status-ready {
    background: var(--color-green-93);
    color: var(--color-green-24);
}

.status-delivered {
    background: var(--color-green-92);
    color: var(--color-success);
}

.status-cancelled {
    background: var(--color-red-94);
    color: var(--color-red-35);
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dot-pending {
    background: var(--color-warning);
}

.dot-preparing {
    background: var(--color-info);
}

.dot-ready {
    background: var(--color-ready);
}

.dot-delivered {
    background: var(--color-success);
}

.dot-cancelled {
    background: var(--color-danger);
}

.terminal-msg {
    font-size: 0.83rem;
    color: var(--color-muted);
    background: var(--color-gray-97-2);
    padding: 12px;
    border-radius: 8px;
    border: 1.5px solid var(--color-surface-alt);
}

.status-options {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.status-option {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1.5px solid var(--color-border);
    border-radius: 10px;
    background: var(--color-card);
    font-size: 0.83rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    font-family: 'Jost', sans-serif;
    color: var(--color-gray-27);
}

.status-option.selected.opt-preparing {
    background: var(--color-blue-93);
    border-color: var(--color-info);
    color: var(--color-blue-48);
}

.status-option.selected.opt-ready {
    background: var(--color-green-93);
    border-color: var(--color-ready);
    color: var(--color-green-24);
}

.status-option.selected.opt-delivered {
    background: var(--color-green-92);
    border-color: var(--color-success);
    color: var(--color-success);
}

.status-option.selected.opt-cancelled {
    background: var(--color-red-94);
    border-color: var(--color-danger);
    color: var(--color-red-35);
}

.form-input {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid var(--color-border);
    border-radius: 10px;
    font-size: 0.875rem;
    color: var(--color-ink);
    background: var(--color-card);
    outline: none;
    transition: border-color .2s;
    box-sizing: border-box;
    font-family: 'Jost', sans-serif;
}

.form-input:focus {
    border-color: var(--color-success);
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-error {
    font-size: 0.77rem;
    color: var(--color-danger);
    margin-top: 4px;
}

/* ── Confirm modals ──────────────────────────────────────── */
.confirm-icon {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
}

.warning-icon {
    background: var(--color-gold-96);
    color: var(--color-warning);
}

.danger-icon {
    background: var(--color-red-97);
    color: var(--color-danger);
}

.confirm-title {
    text-align: center;
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem;
    font-weight: 600;
    color: var(--color-primary);
    margin-bottom: 8px;
}

.confirm-desc {
    text-align: center;
    font-size: 0.86rem;
    color: var(--color-gray-46);
    line-height: 1.5;
}

.delete-warning {
    display: block;
    margin-top: 8px;
    color: var(--color-danger);
    font-size: 0.79rem;
    font-weight: 500;
}

/* ── Receipt ─────────────────────────────────────────────── */
.receipt-body {
    background: var(--color-surface-alt);
    padding: 20px;
}

.receipt-paper {
    background: var(--color-card);
    border-radius: 4px;
    padding: 26px 22px;
    max-width: 300px;
    margin: 0 auto;
    font-family: 'Courier New', monospace;
    box-shadow: 0 4px 20px rgb(var(--rgb-primary) / .1);
    position: relative;
}

.receipt-paper::before,
.receipt-paper::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    height: 8px;
    background: repeating-linear-gradient(90deg, transparent, transparent 8px, var(--color-surface-alt) 8px, var(--color-surface-alt) 16px);
}

.receipt-paper::before {
    top: -8px;
}

.receipt-paper::after {
    bottom: -8px;
}

.receipt-header {
    text-align: center;
    margin-bottom: 10px;
}

.receipt-logo {
    font-size: 1.8rem;
    margin-bottom: 6px;
}

.receipt-brand {
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 4px;
    color: var(--color-primary);
    margin: 0 0 3px;
}

.receipt-tagline {
    font-size: 0.68rem;
    color: var(--color-muted);
    font-style: italic;
    margin: 0;
}

.receipt-divider {
    text-align: center;
    color: var(--color-brown-75-2);
    font-size: 0.65rem;
    margin: 8px 0;
    letter-spacing: 2px;
    overflow: hidden;
}

.receipt-info {
    margin-bottom: 2px;
}

.receipt-info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 3px 0;
    font-size: 0.75rem;
    color: var(--color-primary);
    gap: 8px;
}

.receipt-info-row span:first-child {
    color: var(--color-muted);
    flex-shrink: 0;
}

.receipt-info-row span:last-child {
    text-align: right;
}

.receipt-status {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.66rem !important;
}

.receipt-items-header {
    display: flex;
    justify-content: space-between;
    font-size: 0.62rem;
    color: var(--color-muted);
    text-transform: uppercase;
    letter-spacing: .04em;
    padding-bottom: 5px;
    border-bottom: 1px dashed var(--color-border);
    margin-bottom: 5px;
}

.receipt-item-row {
    display: flex;
    align-items: flex-start;
    gap: 4px;
    padding: 3px 0;
    font-size: 0.75rem;
    color: var(--color-primary);
}

.receipt-item-name {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.receipt-item-qty {
    color: var(--color-muted);
    width: 26px;
    text-align: center;
    flex-shrink: 0;
}

.receipt-item-price {
    font-weight: 700;
    flex-shrink: 0;
}

.receipt-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-weight: 800;
    font-size: 0.95rem;
    color: var(--color-primary);
    letter-spacing: 1px;
}

.receipt-note {
    margin-top: 8px;
    padding: 7px 9px;
    background: var(--color-surface-soft);
    border-radius: 3px;
}

.receipt-note-label {
    font-size: 0.62rem;
    color: var(--color-muted);
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 2px;
}

.receipt-note-text {
    font-size: 0.74rem;
    color: var(--color-gray-46);
    font-style: italic;
    line-height: 1.4;
}

.receipt-footer {
    text-align: center;
    padding-top: 2px;
}

.receipt-quote {
    font-style: italic;
    font-size: 0.68rem;
    color: var(--color-muted);
    line-height: 1.4;
    margin: 0 0 6px;
}

.receipt-thanks {
    font-size: 0.74rem;
    color: var(--color-primary);
    font-weight: 700;
    margin: 0 0 3px;
}

.receipt-website {
    font-size: 0.6rem;
    color: var(--color-muted-light);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin: 0;
}

.receipt-loading {
    font-size: 0.78rem;
    color: var(--color-muted);
    text-align: center;
    padding: 10px 0;
}

/* ── Buttons ─────────────────────────────────────────────── */
.btn-cancel {
    padding: 9px 18px;
    border: 1.5px solid var(--color-border);
    border-radius: 10px;
    background: var(--color-card);
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--color-gray-27);
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    transition: background .15s;
}

.btn-cancel:hover {
    background: var(--color-gray-98-2);
}

.btn-submit {
    padding: 9px 18px;
    border: none;
    border-radius: 10px;
    background: var(--color-brand-surface);
    color: var(--color-accent);
    font-size: 0.86rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
    display: flex;
    align-items: center;
    gap: 7px;
    font-family: 'Jost', sans-serif;
}

.btn-submit:hover {
    background: var(--color-coffee-16);
}

.btn-submit:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.btn-secondary {
    padding: 9px 14px;
    border: 1.5px solid var(--color-border);
    border-radius: 10px;
    background: var(--color-card);
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--color-gray-27);
    cursor: pointer;
    transition: all .15s;
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: 'Jost', sans-serif;
}

.btn-secondary:hover {
    border-color: var(--color-success);
    color: var(--color-success);
}

.btn-danger {
    padding: 9px 18px;
    border: none;
    border-radius: 10px;
    background: var(--color-danger);
    color: var(--color-white);
    font-size: 0.86rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
    display: flex;
    align-items: center;
    gap: 7px;
    font-family: 'Jost', sans-serif;
}

.btn-danger:hover:not(:disabled) {
    background: var(--color-red-42);
}

.btn-danger:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.btn-warning {
    padding: 9px 18px;
    border: none;
    border-radius: 10px;
    background: var(--color-warning);
    color: var(--color-white);
    font-size: 0.86rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
    display: flex;
    align-items: center;
    gap: 7px;
    font-family: 'Jost', sans-serif;
}

.btn-warning:hover:not(:disabled) {
    background: var(--color-brown-37);
}

.btn-warning:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.spinner {
    width: 13px;
    height: 13px;
    border: 2px solid rgb(var(--rgb-white) / .3);
    border-top-color: var(--color-white);
    border-radius: 50%;
    animation: spin .6s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* ── Transitions ─────────────────────────────────────────── */
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

@media (max-width: 640px) {
    .modal {
        width: 96%;
        max-height: 96vh;
    }

    .modal-body {
        padding: 16px;
    }

    .modal-header {
        padding: 16px 16px 12px;
    }

    .modal-footer {
        padding: 12px 16px;
    }
}
</style>