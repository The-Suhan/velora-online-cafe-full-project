<template>
    <div class="orders-page">
        <!-- ── Page Header ──────────────────────────────────────── -->
        <div class="page-header">
            <div class="header-left">
                <NuxtLink to="/admin/orders" class="back-btn">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M13 16l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </NuxtLink>
                <div>
                    <div class="title-row">
                        <span class="status-dot-lg" :class="`dot-${status}`" />
                        <h1 class="page-title">{{ meta.label }}</h1>
                    </div>
                    <p class="page-sub">{{ meta.desc }}</p>
                </div>
            </div>
            <div class="header-actions">
                <!-- Search -->
                <div class="search-wrapper">
                    <svg class="search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="9" cy="9" r="6" />
                        <path d="M15 15l3 3" stroke-linecap="round" />
                    </svg>
                    <input v-model="search" type="text"
                        :placeholder="$t('admin.orders.statusPage.searchPlaceholder', { status: meta.label.toLowerCase() })"
                        class="search-input" @input="onSearch" />
                </div>
                <!-- Date filter -->
                <div class="filter-wrap" @click.stop>
                    <button class="btn-filter" @click="showFilter = !showFilter">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="14"
                            height="14">
                            <polygon points="20 2 2 2 9 10.46 9 17 11 19 11 10.46 20 2" />
                        </svg>
                        {{ $t('admin.orders.statusPage.filter') }}
                    </button>
                    <Transition name="dropdown">
                        <div v-if="showFilter" class="filter-dropdown">
                            <p class="filter-label">{{ $t('admin.orders.statusPage.dateRange') }}</p>
                            <div class="date-inputs">
                                <input v-model="filterFrom" type="date" class="filter-date" @change="applyFilter" />
                                <span class="date-sep">→</span>
                                <input v-model="filterTo" type="date" class="filter-date" @change="applyFilter" />
                            </div>
                            <button class="filter-reset" @click="resetFilter">{{
                                $t('admin.orders.statusPage.resetFilter') }}</button>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <!-- ── Stat Cards Row (nav to other statuses) ─────────────── -->
        <div class="stats-row">
            <NuxtLink to="/admin/orders" class="status-nav-chip chip-all">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="13" height="13">
                    <path d="M5 2h10l2 5H3L5 2z" />
                    <path d="M3 7v9a1 1 0 001 1h12a1 1 0 001-1V7" />
                </svg>
                {{ $t('admin.orders.statusPage.allOrders', { n: stats.total }) }}
            </NuxtLink>
            <NuxtLink v-for="s in otherStatuses" :key="s" :to="`/admin/orders/${s}`" class="status-nav-chip"
                :class="`chip-${s}`">
                <span class="chip-dot" :class="`dot-${s}`" />
                {{ capitalize(s) }} ({{ stats[s] ?? 0 }})
            </NuxtLink>
        </div>

        <!-- ── Current status summary card ───────────────────────── -->
        <div class="summary-card" :class="`summary-${status}`">
            <div class="summary-icon" :class="`sicon-${status}`">
                <slot name="icon" />
            </div>
            <div class="summary-info">
                <p class="summary-label">{{ meta.label }}</p>
                <p class="summary-count">{{ $t('admin.orders.statusPage.ordersCount', { n: pagination.total }) }}</p>
            </div>
            <div class="summary-badge" :class="`sbadge-${status}`">{{ meta.badge }}</div>
        </div>

        <!-- ── Table Card ─────────────────────────────────────────── -->
        <div class="table-card">

            <!-- Skeleton -->
            <div v-if="loading" class="loading-state">
                <div v-for="i in 10" :key="i" class="skeleton-row">
                    <div class="sk-avatar" />
                    <div class="sk-lines">
                        <div class="sk-line w-160" />
                        <div class="sk-line w-100 short" />
                    </div>
                    <div class="sk-line w-80 ml" />
                    <div class="sk-line w-60 ml" />
                    <div class="sk-line w-80 ml" />
                </div>
            </div>

            <template v-else>
                <!-- Desktop Table -->
                <div class="table-wrap desktop-only">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>{{ $t('admin.orders.statusPage.orderIdHeader') }}</th>
                                <th>{{ $t('admin.orders.statusPage.customerHeader') }}</th>
                                <th>{{ $t('admin.orders.statusPage.totalHeader') }}</th>
                                <th>{{ $t('admin.orders.statusPage.itemsHeader') }}</th>
                                <th>{{ $t('admin.orders.statusPage.createdAtHeader') }}</th>
                                <th>{{ $t('admin.orders.statusPage.actionsHeader') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders" :key="order.id" class="order-row">
                                <td class="order-id-td">{{ order.order_number }}</td>
                                <td>
                                    <div class="customer-cell">
                                        <div class="avatar">{{ initials(order.customer?.name) }}</div>
                                        <div>
                                            <p class="cust-name">{{ order.customer?.name ?? '—' }}</p>
                                            <p class="cust-email">{{ order.customer?.email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="price-td">${{ order.total_price.toFixed(2) }}</td>
                                <td class="items-td">{{ order.items_count }}</td>
                                <td class="date-td">
                                    <p>{{ order.created_at }}</p>
                                    <p class="date-time">{{ order.created_at_time }}</p>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="btn-view" @click="openPreviewModal(order)">
                                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                stroke-width="1.8" width="13" height="13">
                                                <path d="M2 10s3-7 8-7 8 7 8 7-3 7-8 7-8-7-8-7z" />
                                                <circle cx="10" cy="10" r="2.5" />
                                            </svg>
                                            {{ $t('admin.orders.statusPage.viewBtn') }}
                                        </button>
                                        <div class="dropdown-wrap">
                                            <button class="btn-dots" @click.stop="toggleDropdown(order.id, $event)">
                                                <svg viewBox="0 0 20 20" fill="currentColor" width="15" height="15">
                                                    <circle cx="10" cy="4" r="1.5" />
                                                    <circle cx="10" cy="10" r="1.5" />
                                                    <circle cx="10" cy="16" r="1.5" />
                                                </svg>
                                            </button>
                                            <Teleport to="body">
                                                <div v-if="openDrop === order.id" class="dropdown-menu"
                                                    :style="{ top: dropPos.top + 'px', right: dropPos.right + 'px' }">
                                                    <button class="dropdown-item" @click="openEditModal(order)">
                                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" width="13" height="13">
                                                            <path d="M13.5 3.5l3 3L7 16H4v-3L13.5 3.5z"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                        {{ $t('admin.orders.statusPage.editStatusBtn') }}
                                                    </button>
                                                    <button class="dropdown-item" @click="openReceiptModal(order)">
                                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" width="13" height="13">
                                                            <path d="M4 2h12v16l-2-1-2 1-2-1-2 1-2-1-2 1V2z"
                                                                stroke-linejoin="round" />
                                                            <path d="M7 7h6M7 10h4" stroke-linecap="round" />
                                                        </svg>
                                                        {{ $t('admin.orders.statusPage.exportReceiptBtn') }}
                                                    </button>
                                                    <button v-if="canCancel(order.status)" class="dropdown-item warning"
                                                        @click="openCancelModal(order)">
                                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" width="13" height="13">
                                                            <circle cx="10" cy="10" r="8" />
                                                            <path d="M13 7l-6 6M7 7l6 6" stroke-linecap="round" />
                                                        </svg>
                                                        {{ $t('admin.orders.statusPage.cancelOrderBtn') }}
                                                    </button>
                                                    <button v-if="canDelete(order.status)" class="dropdown-item danger"
                                                        @click="openDeleteModal(order)">
                                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" width="13" height="13">
                                                            <path d="M3 5h14M8 5V3h4v2M6 5l1 12h6l1-12"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                        {{ $t('admin.orders.statusPage.deleteBtn') }}
                                                    </button>
                                                </div>
                                            </Teleport>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="mobile-cards mobile-only">
                    <div v-for="order in orders" :key="order.id" class="mobile-card">
                        <div class="mobile-top">
                            <div class="mobile-left">
                                <div class="avatar">{{ initials(order.customer?.name) }}</div>
                                <div>
                                    <p class="cust-name">{{ order.customer?.name ?? '—' }}</p>
                                    <p class="order-id-sm">{{ order.order_number }}</p>
                                </div>
                            </div>
                            <span class="price-badge">${{ order.total_price.toFixed(2) }}</span>
                        </div>
                        <div class="mobile-bottom">
                            <span class="mobile-meta">{{
                                $t('admin.orders.statusPage.mobileItemsMeta', {
                                    items: order.items_count,
                                    date: order.created_at
                                })
                            }}</span>
                            <div class="mobile-actions">
                                <button class="btn-view-sm" @click="openPreviewModal(order)">
                                    {{ $t('admin.orders.statusPage.viewBtn') }}
                                </button>
                                <div class="dropdown-wrap">
                                    <button class="btn-dots" @click.stop="toggleDropdown(order.id, $event)">
                                        <svg viewBox="0 0 20 20" fill="currentColor" width="15" height="15">
                                            <circle cx="10" cy="4" r="1.5" />
                                            <circle cx="10" cy="10" r="1.5" />
                                            <circle cx="10" cy="16" r="1.5" />
                                        </svg>
                                    </button>
                                    <Teleport to="body">
                                        <div v-if="openDrop === order.id" class="dropdown-menu"
                                            :style="{ top: dropPos.top + 'px', right: dropPos.right + 'px' }">
                                            <button class="dropdown-item" @click="openEditModal(order)">
                                                {{ $t('admin.orders.statusPage.editStatusBtn') }}
                                            </button>
                                            <button class="dropdown-item" @click="openReceiptModal(order)">
                                                {{ $t('admin.orders.statusPage.exportReceiptBtn') }}
                                            </button>
                                            <button v-if="canCancel(order.status)" class="dropdown-item warning"
                                                @click="openCancelModal(order)">
                                                {{ $t('admin.orders.statusPage.cancelOrderBtn') }}
                                            </button>
                                            <button v-if="canDelete(order.status)" class="dropdown-item danger"
                                                @click="openDeleteModal(order)">
                                                {{ $t('admin.orders.statusPage.deleteBtn') }}
                                            </button>
                                        </div>
                                    </Teleport>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-if="orders.length === 0" class="empty-state">
                    <div class="empty-icon-wrap" :class="`eicon-${status}`">
                        <slot name="empty-icon">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" width="40"
                                height="40">
                                <path d="M12 4L6 12v28a4 4 0 004 4h28a4 4 0 004-4V12l-6-8z" />
                                <line x1="6" y1="12" x2="42" y2="12" />
                                <path d="M24 20v8M24 32h.01" stroke-linecap="round" />
                            </svg>
                        </slot>
                    </div>
                    <p class="empty-title">{{ $t('admin.orders.statusPage.emptyTitle', {
                        status:
                            meta.label.toLowerCase()
                    }) }}</p>
                    <p class="empty-sub">{{ meta.emptyMsg }}</p>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="pagination">
                    <p class="pagination-info">{{
                        $t('admin.orders.statusPage.showingOf', {
                            from: pagination.from,
                            to: pagination.to,
                            total: pagination.total
                        })
                    }}</p>
                    <div class="pagination-btns">
                        <button class="pg-btn" :disabled="pagination.current_page === 1"
                            @click="goToPage(pagination.current_page - 1)">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13">
                                <path
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" />
                            </svg>
                        </button>
                        <template v-for="page in visiblePages" :key="page">
                            <span v-if="page === '...'" class="pg-ellipsis">…</span>
                            <button v-else class="pg-btn" :class="{ active: page === pagination.current_page }"
                                @click="goToPage(page as number)">{{ page }}</button>
                        </template>
                        <button class="pg-btn" :disabled="pagination.current_page === pagination.last_page"
                            @click="goToPage(pagination.current_page + 1)">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13">
                                <path
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else-if="orders.length > 0" class="pg-simple">{{
                    $t('admin.orders.statusPage.showingAll', {
                        n: orders.length,
                        total: pagination.total
                    })
                }}</div>
            </template>
        </div>

        <!-- ── Modals ──────────────────────────────────────────────── -->
        <OrderModals :active-modal="activeModal" :selected-order="selectedOrder" :detail-loading="detailLoading"
            :submitting="submitting" :pdf-loading="pdfLoading" :edit-form="editForm" :edit-errors="editErrors"
            @close-modal="closeModal" @open-receipt="openReceiptModal" @open-edit="openEditModal"
            @submit-edit="submitEdit" @submit-cancel="submitCancel" @submit-delete="submitDelete"
            @download-pdf="downloadPdf" />

        <!-- Toast -->
        <Transition name="toast">
            <div v-if="toast.visible" class="toast" :class="toast.type">
                <svg v-if="toast.type === 'success'" viewBox="0 0 20 20" fill="currentColor" width="15" height="15">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
                <svg v-else viewBox="0 0 20 20" fill="currentColor" width="15" height="15">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                {{ toast.message }}
            </div>
        </Transition>

    </div>
</template>

<script setup lang="ts">
import { useOrders, type OrderStatus } from '~/composables/useOrders'

const props = defineProps<{
    status: OrderStatus
    meta: {
        label: string
        desc: string
        badge: string
        emptyMsg: string
    }
}>()

const ALL_STATUSES: OrderStatus[] = ['pending', 'preparing', 'ready', 'delivered', 'cancelled']
const otherStatuses = computed(() => ALL_STATUSES.filter(s => s !== props.status))

const {
    orders, stats, loading, detailLoading, search, filterFrom, filterTo,
    showFilter, pagination, activeModal, selectedOrder, openDrop, dropPos,
    submitting, pdfLoading, editForm, editErrors, toast, visiblePages,
    loadOrders, loadStats, onSearch, applyFilter, resetFilter, goToPage,
    toggleDropdown, openPreviewModal, openEditModal, openReceiptModal,
    openCancelModal, openDeleteModal, closeModal,
    submitEdit, submitCancel, submitDelete, downloadPdf,
    capitalize, initials, canCancel, canDelete, handleOutsideClick,
} = useOrders(props.status)

onMounted(async () => {
    await Promise.all([loadOrders(), loadStats()])
    document.addEventListener('click', handleOutsideClick)
})
onUnmounted(() => document.removeEventListener('click', handleOutsideClick))
</script>

<style scoped>
.orders-page {
    padding: 28px 24px;
    max-width: 100%;
    min-height: 100%;
}

/* ── Header ─────────────────────────────────────────────────── */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.back-btn {
    width: 34px;
    height: 34px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    text-decoration: none;
    flex-shrink: 0;
    transition: all .15s;
}

.back-btn:hover {
    border-color: #4A6741;
    color: #4A6741;
}

.title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 3px;
}

.status-dot-lg {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dot-pending {
    background: #d97706;
}

.dot-preparing {
    background: #3b82f6;
}

.dot-ready {
    background: #16a34a;
}

.dot-delivered {
    background: #4A6741;
}

.dot-cancelled {
    background: #dc2626;
}

.page-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.75rem;
    font-weight: 600;
    color: #2C1810;
    margin: 0;
}

.page-sub {
    font-size: 0.83rem;
    color: #8a7060;
    margin: 0;
    padding-left: 18px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.search-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 14px;
    height: 14px;
    color: #9ca3af;
}

.search-input {
    padding: 9px 14px 9px 32px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.84rem;
    width: 210px;
    background: #fff;
    outline: none;
    color: #1a1a1a;
    font-family: 'Jost', sans-serif;
    transition: border-color .2s;
}

.search-input:focus {
    border-color: #4A6741;
}

.filter-wrap {
    position: relative;
}

.btn-filter {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 9px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
    font-size: 0.84rem;
    color: #374151;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    transition: border-color .2s;
}

.btn-filter:hover {
    border-color: #4A6741;
}

.filter-dropdown {
    position: absolute;
    right: 0;
    top: calc(100% + 6px);
    z-index: 80;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    min-width: 200px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
}

.filter-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6b7280;
    margin: 0 0 8px;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.date-inputs {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
}

.filter-date {
    flex: 1;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 6px 8px;
    font-size: 0.77rem;
    color: #1a1a1a;
    outline: none;
    font-family: 'Jost', sans-serif;
}

.date-sep {
    font-size: 0.8rem;
    color: #9ca3af;
    flex-shrink: 0;
}

.filter-reset {
    width: 100%;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    padding: 7px;
    font-size: 0.81rem;
    color: #6b7280;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
}

.filter-reset:hover {
    background: #e5e7eb;
}

/* ── Status nav chips ────────────────────────────────────────── */
.stats-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.status-nav-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: all .15s;
    cursor: pointer;
    white-space: nowrap;
}

.chip-all {
    background: #fdf3e4;
    color: #8B6914;
    border-color: #f0d9a0;
}

.chip-all:hover {
    border-color: #C8A96E;
}

.chip-pending {
    background: #fef3c7;
    color: #92400e;
    border-color: #fde68a;
}

.chip-pending:hover {
    border-color: #d97706;
}

.chip-preparing {
    background: #dbeafe;
    color: #1d4ed8;
    border-color: #bfdbfe;
}

.chip-preparing:hover {
    border-color: #3b82f6;
}

.chip-ready {
    background: #dcfce7;
    color: #166534;
    border-color: #bbf7d0;
}

.chip-ready:hover {
    border-color: #16a34a;
}

.chip-delivered {
    background: #e8f0e4;
    color: #4A6741;
    border-color: #c6d9be;
}

.chip-delivered:hover {
    border-color: #4A6741;
}

.chip-cancelled {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fecaca;
}

.chip-cancelled:hover {
    border-color: #dc2626;
}

.chip-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* ── Summary card ────────────────────────────────────────────── */
.summary-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    border-radius: 14px;
    margin-bottom: 16px;
    border: 1.5px solid transparent;
}

.summary-pending {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border-color: #fde68a;
}

.summary-preparing {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-color: #bfdbfe;
}

.summary-ready {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-color: #bbf7d0;
}

.summary-delivered {
    background: linear-gradient(135deg, #f0f4ec, #e8f0e4);
    border-color: #c6d9be;
}

.summary-cancelled {
    background: linear-gradient(135deg, #fff5f5, #fee2e2);
    border-color: #fecaca;
}

.summary-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sicon-pending {
    background: rgba(217, 119, 6, .12);
    color: #d97706;
}

.sicon-preparing {
    background: rgba(59, 130, 246, .12);
    color: #3b82f6;
}

.sicon-ready {
    background: rgba(22, 163, 74, .12);
    color: #16a34a;
}

.sicon-delivered {
    background: rgba(74, 103, 65, .12);
    color: #4A6741;
}

.sicon-cancelled {
    background: rgba(220, 38, 38, .12);
    color: #dc2626;
}

.summary-info {
    flex: 1;
}

.summary-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #6b7280;
    margin: 0 0 2px;
}

.summary-count {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #2C1810;
    margin: 0;
    line-height: 1;
}

.summary-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 99px;
}

.sbadge-pending {
    background: #fef3c7;
    color: #92400e;
}

.sbadge-preparing {
    background: #dbeafe;
    color: #1d4ed8;
}

.sbadge-ready {
    background: #dcfce7;
    color: #166534;
}

.sbadge-delivered {
    background: #e8f0e4;
    color: #4A6741;
}

.sbadge-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

/* ── Table Card ─────────────────────────────────────────────── */
.table-card {
    background: #fff;
    border: 1.5px solid #f0f0f0;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(44, 24, 16, .04);
    overflow: hidden;
}

.table-wrap {
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 620px;
}

.orders-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 0.72rem;
    font-weight: 600;
    color: #8a7060;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1.5px solid #f3f4f6;
    background: #fafafa;
    white-space: nowrap;
}

.order-row td {
    padding: 13px 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.order-row:last-child td {
    border-bottom: none;
}

.order-row:hover td {
    background: #fafaf8;
}

.order-id-td {
    font-size: 0.8rem;
    font-weight: 600;
    color: #2C1810;
    font-family: 'Courier New', monospace;
    white-space: nowrap;
}

.customer-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #C8A96E, #8B6914);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.68rem;
    font-weight: 700;
    flex-shrink: 0;
}

.cust-name {
    font-size: 0.84rem;
    font-weight: 600;
    color: #2C1810;
    margin: 0 0 1px;
}

.cust-email {
    font-size: 0.72rem;
    color: #8a7060;
    margin: 0;
}

.price-td {
    font-size: 0.86rem;
    font-weight: 600;
    color: #4A6741;
    white-space: nowrap;
}

.items-td {
    font-size: 0.82rem;
    color: #2C1810;
    text-align: center;
}

.date-td {
    font-size: 0.78rem;
    color: #2C1810;
    white-space: nowrap;
}

.date-time {
    font-size: 0.7rem;
    color: #8a7060;
    margin-top: 1px;
}

/* Actions */
.actions-cell {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-view {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 11px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    font-size: 0.76rem;
    font-weight: 600;
    color: #2C1810;
    cursor: pointer;
    transition: all .15s;
    font-family: 'Jost', sans-serif;
    white-space: nowrap;
}

.btn-view:hover {
    border-color: #4A6741;
    color: #4A6741;
    background: #f0f4ec;
}

.btn-view-sm {
    padding: 5px 10px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    font-size: 0.76rem;
    font-weight: 600;
    color: #2C1810;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    transition: all .15s;
}

.btn-view-sm:hover {
    border-color: #4A6741;
    color: #4A6741;
}

.btn-dots {
    width: 30px;
    height: 30px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #8a7060;
    transition: all .15s;
}

.btn-dots:hover {
    border-color: #4A6741;
    color: #4A6741;
}

.dropdown-wrap {
    position: relative;
}

.dropdown-menu {
    position: fixed;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
    z-index: 9000;
    min-width: 158px;
    overflow: hidden;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 9px 13px;
    font-size: 0.82rem;
    color: #374151;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: background .15s;
    font-family: 'Jost', sans-serif;
}

.dropdown-item:hover {
    background: #fafafa;
}

.dropdown-item.warning {
    color: #d97706;
}

.dropdown-item.warning:hover {
    background: #fffbeb;
}

.dropdown-item.danger {
    color: #dc2626;
}

.dropdown-item.danger:hover {
    background: #fef2f2;
}

/* Skeleton */
.loading-state {
    padding: 8px 16px;
}

.skeleton-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 13px 0;
    border-bottom: 1px solid #f3f4f6;
}

.sk-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f0ede6;
    flex-shrink: 0;
    animation: shimmer 1.2s infinite;
}

.sk-lines {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.sk-line {
    height: 11px;
    border-radius: 6px;
    background: #f0ede6;
    animation: shimmer 1.2s infinite;
}

.w-160 {
    width: 160px;
}

.w-100 {
    width: 100px;
}

.w-80 {
    width: 80px;
}

.w-60 {
    width: 60px;
}

.short {
    height: 9px;
}

.ml {
    margin-left: auto;
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

/* Mobile cards */
.mobile-cards {
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mobile-card {
    border: 1.5px solid #f3f4f6;
    border-radius: 12px;
    padding: 13px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.mobile-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.mobile-left {
    display: flex;
    align-items: center;
    gap: 9px;
    flex: 1;
    min-width: 0;
}

.order-id-sm {
    font-size: 0.7rem;
    color: #8a7060;
    font-family: 'Courier New', monospace;
    margin-top: 1px;
}

.price-badge {
    font-size: 0.88rem;
    font-weight: 700;
    color: #4A6741;
    flex-shrink: 0;
}

.mobile-bottom {
    display: flex;
    align-items: center;
    gap: 8px;
}

.mobile-meta {
    font-size: 0.73rem;
    color: #8a7060;
    flex: 1;
}

.mobile-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-left: auto;
}

/* Empty */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    gap: 12px;
}

.empty-icon-wrap {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 4px;
}

.eicon-pending {
    background: #fef3c7;
    color: #d97706;
}

.eicon-preparing {
    background: #dbeafe;
    color: #3b82f6;
}

.eicon-ready {
    background: #dcfce7;
    color: #16a34a;
}

.eicon-delivered {
    background: #e8f0e4;
    color: #4A6741;
}

.eicon-cancelled {
    background: #fee2e2;
    color: #dc2626;
}

.empty-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 600;
    color: #2C1810;
    margin: 0;
}

.empty-sub {
    font-size: 0.83rem;
    color: #b0967a;
    margin: 0;
    text-align: center;
}

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 20px;
    border-top: 1.5px solid #f3f4f6;
    flex-wrap: wrap;
    gap: 10px;
}

.pagination-info {
    font-size: 0.8rem;
    color: #8a7060;
}

.pg-simple {
    padding: 13px 20px;
    font-size: 0.8rem;
    color: #8a7060;
    border-top: 1.5px solid #f3f4f6;
}

.pagination-btns {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pg-btn {
    width: 32px;
    height: 32px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    color: #374151;
    font-size: 0.82rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s;
    font-family: 'Jost', sans-serif;
}

.pg-btn:hover:not(:disabled) {
    border-color: #4A6741;
    color: #4A6741;
}

.pg-btn.active {
    background: #2C1810;
    border-color: #2C1810;
    color: #fff;
}

.pg-btn:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.pg-ellipsis {
    padding: 0 4px;
    color: #9ca3af;
    font-size: 0.88rem;
}

/* Toast */
.toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 11px 16px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
    z-index: 9999;
    font-family: 'Jost', sans-serif;
}

.toast.success {
    background: #2C1810;
    color: #C8A96E;
}

.toast.error {
    background: #dc2626;
    color: #fff;
}

/* Transitions */
.toast-enter-active {
    transition: all .3s cubic-bezier(.34, 1.56, .64, 1);
}

.toast-leave-active {
    transition: all .2s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(12px);
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity .15s, transform .15s;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

/* Responsive */
.desktop-only {
    display: block;
}

.mobile-only {
    display: none;
}

@media (max-width: 768px) {
    .orders-page {
        padding: 16px 12px;
    }

    .page-header {
        flex-direction: column;
    }

    .search-input {
        width: 100%;
    }

    .header-actions {
        width: 100%;
    }

    .search-wrapper {
        flex: 1;
    }

    .stats-row {
        gap: 6px;
    }

    .status-nav-chip {
        font-size: 0.72rem;
        padding: 5px 10px;
    }

    .desktop-only {
        display: none;
    }

    .mobile-only {
        display: flex;
    }

    .pagination {
        flex-direction: column;
        align-items: flex-start;
    }

    .toast {
        bottom: 80px;
        left: 12px;
        right: 12px;
    }

    .summary-card {
        padding: 13px 14px;
    }
}
</style>