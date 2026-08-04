<template>
    <div class="orders-page">
        <!-- ── Page Header ──────────────────────────────────────── -->
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $t('admin.orders.title') }}</h1>
                <p class="page-sub">{{ $t('admin.orders.subtitle') }}</p>
            </div>
            <div class="header-actions">
                <!-- Search -->
                <div class="search-wrapper">
                    <svg class="search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="9" cy="9" r="6" />
                        <path d="M15 15l3 3" stroke-linecap="round" />
                    </svg>
                    <input v-model="search" type="text" :placeholder="$t('admin.orders.searchPlaceholder')"
                        class="search-input" @input="onSearch" />
                </div>
                <!-- Filter -->
                <div class="filter-wrap" @click.stop>
                    <button class="btn-filter" @click="showFilter = !showFilter">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="14"
                            height="14">
                            <polygon points="20 2 2 2 9 10.46 9 17 11 19 11 10.46 20 2" />
                        </svg>
                        {{ $t('admin.common.filter') }}
                    </button>
                    <Transition name="dropdown">
                        <div v-if="showFilter" class="filter-dropdown">
                            <p class="filter-label">{{ $t('admin.orders.filterStatus') }}</p>
                            <select v-model="filterStatus" class="filter-select" @change="applyFilter">
                                <option value="">{{ $t('admin.orders.allStatuses') }}</option>
                                <option v-for="s in statuses" :key="s" :value="s">
                                    {{ $t(`admin.statuses.${s}`) }}
                                </option>
                            </select>
                            <p class="filter-label mt">{{ $t('admin.orders.filterDateRange') }}</p>
                            <div class="date-inputs">
                                <input v-model="filterFrom" type="date" class="filter-date" @change="applyFilter" />
                                <span class="date-sep">→</span>
                                <input v-model="filterTo" type="date" class="filter-date" @change="applyFilter" />
                            </div>
                            <button class="filter-reset" @click="resetFilter">{{ $t('admin.common.reset') }}</button>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <!-- ── Stat Cards ─────────────────────────────────────────── -->
        <div class="stats-grid">
            <!-- Total / All -->
            <NuxtLink to="/admin/orders" class="stat-card" :class="{ 'active-card': !filterStatus }">
                <div class="stat-icon stat-icon--total">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="20"
                        height="20">
                        <path d="M6 2h12l3 7H3L6 2z" />
                        <path d="M3 9v11a2 2 0 002 2h14a2 2 0 002-2V9" />
                    </svg>
                </div>
                <div class="stat-info">
                    <p class="stat-label">{{ $t('admin.orders.allOrders') }}</p>
                    <p class="stat-value">{{ stats.total ?? 0 }}</p>
                    <p class="stat-growth" :class="(stats.growth ?? 0) >= 0 ? 'positive' : 'negative'">
                        {{ (stats.growth ?? 0) >= 0 ? '↑' : '↓' }} {{ Math.abs(stats.growth ?? 0) }}% {{
                            $t('admin.orders.thisWeek') }}
                    </p>
                </div>
            </NuxtLink>

            <!-- Status cards -->
            <NuxtLink v-for="s in statuses" :key="s" :to="`/admin/orders/${s}`" class="stat-card"
                :class="{ 'active-card': filterStatus === s }">
                <div class="stat-icon" :class="`stat-icon--${s}`">
                    <!-- pending -->
                    <svg v-if="s === 'pending'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        width="20" height="20">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 6v6l4 2" stroke-linecap="round" />
                    </svg>
                    <!-- preparing -->
                    <svg v-else-if="s === 'preparing'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" width="20" height="20">
                        <path d="M12 2a10 10 0 00-6.88 17.26L12 22l6.88-2.74A10 10 0 0012 2z" />
                        <path d="M12 8v4l2 2" stroke-linecap="round" />
                    </svg>
                    <!-- ready -->
                    <svg v-else-if="s === 'ready'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" width="20" height="20">
                        <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <!-- delivered -->
                    <svg v-else-if="s === 'delivered'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" width="20" height="20">
                        <rect x="1" y="3" width="15" height="13" rx="1" />
                        <path d="M16 8h4l3 5v4h-7V8z" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="5.5" cy="18.5" r="2.5" />
                        <circle cx="18.5" cy="18.5" r="2.5" />
                    </svg>
                    <!-- cancelled -->
                    <svg v-else-if="s === 'cancelled'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" width="20" height="20">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M15 9l-6 6M9 9l6 6" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="stat-info">
                    <p class="stat-label">{{ $t(`admin.statuses.${s}`) }}</p>
                    <p class="stat-value">{{ stats[s] ?? 0 }}</p>
                    <span class="stat-badge" :class="`badge--${s}`">{{ $t(STATUS_META[s].badge) }}</span>
                </div>
            </NuxtLink>
        </div>

        <!-- ── Table Card ─────────────────────────────────────────── -->
        <div class="table-card">
            <!-- Active filter pill -->
            <div v-if="filterStatus" class="active-filter-bar">
                <span class="filter-pill">
                    <span class="status-dot" :class="`dot-${filterStatus}`" />
                    {{ $t(`admin.statuses.${filterStatus}`) }}
                    <button class="pill-remove" @click="setStatusFilter('')">×</button>
                </span>
                <span class="filter-count">{{ pagination.total }} {{ $t('admin.orders.ordersTotal') }}</span>
            </div>

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
                                <th>{{ $t('admin.orders.orderId') }}</th>
                                <th>{{ $t('admin.orders.customer') }}</th>
                                <th>{{ $t('admin.orders.total') }}</th>
                                <th>{{ $t('admin.common.status') }}</th>
                                <th>{{ $t('admin.orders.items') }}</th>
                                <th>{{ $t('admin.common.createdAt') }}</th>
                                <th>{{ $t('admin.common.actions') }}</th>
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
                                <td class="price-td">
                                    ${{ displayPrice(order).toFixed(2) }}
                                    <span v-if="order.delivery_type === 'delivery'" class="delivery-tag">
                                        +${{ DELIVERY_FEE }} delivery
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge" :class="`status-${order.status}`">
                                        <span class="status-dot" :class="`dot-${order.status}`" />
                                        {{ $t(`admin.statuses.${order.status}`) }}
                                    </span>
                                </td>
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
                                            {{ $t('admin.orders.view') }}
                                        </button>
                                        <!-- 3-dot dropdown -->
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
                                                        {{ $t('admin.orders.editStatus') }}
                                                    </button>
                                                    <button class="dropdown-item" @click="openReceiptModal(order)">
                                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" width="13" height="13">
                                                            <path d="M4 2h12v16l-2-1-2 1-2-1-2 1-2-1-2 1V2z"
                                                                stroke-linejoin="round" />
                                                            <path d="M7 7h6M7 10h4" stroke-linecap="round" />
                                                        </svg>
                                                        {{ $t('admin.orders.exportReceipt') }}
                                                    </button>
                                                    <button v-if="canCancel(order.status)" class="dropdown-item warning"
                                                        @click="openCancelModal(order)">
                                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" width="13" height="13">
                                                            <circle cx="10" cy="10" r="8" />
                                                            <path d="M13 7l-6 6M7 7l6 6" stroke-linecap="round" />
                                                        </svg>
                                                        {{ $t('admin.orders.cancelOrder') }}
                                                    </button>
                                                    <button v-if="canDelete(order.status)" class="dropdown-item danger"
                                                        @click="openDeleteModal(order)">
                                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" width="13" height="13">
                                                            <path d="M3 5h14M8 5V3h4v2M6 5l1 12h6l1-12"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                        {{ $t('admin.common.delete') }}
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
                            <span class="status-badge" :class="`status-${order.status}`">
                                <span class="status-dot" :class="`dot-${order.status}`" />
                                {{ $t(`admin.statuses.${order.status}`) }}
                            </span>
                        </div>
                        <div class="mobile-bottom">
                            <span class="mobile-price">
                                ${{ displayPrice(order).toFixed(2) }}
                                <span v-if="order.delivery_type === 'delivery'" class="delivery-tag">+${{ DELIVERY_FEE
                                }}</span>
                            </span>
                            <span class="mobile-meta">{{ order.items_count }} {{ $t('admin.orders.items') }} · {{
                                order.created_at }}</span>
                            <div class="mobile-actions">
                                <button class="btn-view-sm" @click="openPreviewModal(order)">{{ $t('admin.orders.view')
                                    }}</button>
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
                                            <button class="dropdown-item" @click="openEditModal(order)">{{
                                                $t('admin.orders.editStatus') }}</button>
                                            <button class="dropdown-item" @click="openReceiptModal(order)">{{
                                                $t('admin.orders.exportReceipt') }}</button>
                                            <button v-if="canCancel(order.status)" class="dropdown-item warning"
                                                @click="openCancelModal(order)">{{ $t('admin.orders.cancelOrder')
                                                }}</button>
                                            <button v-if="canDelete(order.status)" class="dropdown-item danger"
                                                @click="openDeleteModal(order)">{{ $t('admin.common.delete') }}</button>
                                        </div>
                                    </Teleport>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-if="orders.length === 0" class="empty-state">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" width="44" height="44"
                        class="empty-icon">
                        <path d="M12 4L6 12v28a4 4 0 004 4h28a4 4 0 004-4V12l-6-8z" />
                        <line x1="6" y1="12" x2="42" y2="12" />
                        <path d="M24 20v8M24 32h.01" stroke-linecap="round" />
                    </svg>
                    <p>{{ $t('admin.orders.noOrders') }}</p>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="pagination">
                    <p class="pagination-info">{{ $t('admin.orders.showingOf', {
                        from: pagination.from, to:
                            pagination.to, total: pagination.total
                    }) }}</p>
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
                <div v-else-if="orders.length > 0" class="pg-simple">{{ $t('admin.orders.showingOf', {
                    from: 1, to:
                        orders.length,
                    total: pagination.total
                }) }}</div>
            </template>
        </div>

        <!-- ── Modals ──────────────────────────────────────────────── -->
        <OrderModals :active-modal="activeModal" :selected-order="selectedOrder" :detail-loading="detailLoading"
            :submitting="submitting" :pdf-loading="pdfLoading" :edit-form="editForm" :edit-errors="editErrors"
            :delivery-fee="DELIVERY_FEE" @close-modal="closeModal" @open-receipt="openReceiptModal"
            @open-edit="openEditModal" @submit-edit="submitEdit" @submit-cancel="submitCancel"
            @submit-delete="submitDelete" @download-pdf="downloadPdf" />

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
useHead({ title: 'Velora — Admin Orders' })
import { useOrders, STATUS_META, type OrderStatus } from '~/composables/useOrders'

definePageMeta({ layout: 'admin' as any, middleware: 'admin' })

const statuses: OrderStatus[] = ['pending', 'preparing', 'ready', 'delivered', 'cancelled']

const {
    orders, stats, loading, detailLoading, search, filterStatus, filterFrom, filterTo,
    showFilter, pagination, activeModal, selectedOrder, openDrop, dropPos,
    submitting, pdfLoading, editForm, editErrors, toast, visiblePages,
    loadOrders, loadStats, onSearch, setStatusFilter, applyFilter, resetFilter, goToPage,
    toggleDropdown, openPreviewModal, openEditModal, openReceiptModal, openCancelModal,
    openDeleteModal, closeModal, submitEdit, submitCancel, submitDelete, downloadPdf,
    capitalize, initials, canCancel, canDelete, handleOutsideClick, DELIVERY_FEE, displayPrice
} = useOrders()

onMounted(async () => {
    await Promise.all([loadOrders(), loadStats()])
    document.addEventListener('click', handleOutsideClick)
})
onUnmounted(() => document.removeEventListener('click', handleOutsideClick))
</script>

<style scoped>
/* ── Root ──────────────────────────────────────────────────── */
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
    margin-bottom: 22px;
    flex-wrap: wrap;
}

.page-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--color-primary);
    margin: 0 0 4px;
}

.page-sub {
    font-size: 0.84rem;
    color: var(--color-muted);
    margin: 0;
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
    color: var(--color-gray-65);
}

.search-input {
    padding: 9px 14px 9px 32px;
    border: 1.5px solid var(--color-border);
    border-radius: 10px;
    font-size: 0.84rem;
    width: 220px;
    background: var(--color-white);
    outline: none;
    color: var(--color-ink);
    font-family: 'Jost', sans-serif;
    transition: border-color .2s;
}

.search-input:focus {
    border-color: var(--color-success);
}

.filter-wrap {
    position: relative;
}

.btn-filter {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 9px 14px;
    border: 1.5px solid var(--color-border);
    border-radius: 10px;
    background: var(--color-white);
    font-size: 0.84rem;
    color: var(--color-gray-27);
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    transition: border-color .2s;
}

.btn-filter:hover {
    border-color: var(--color-success);
}

.filter-dropdown {
    position: absolute;
    right: 0;
    top: calc(100% + 6px);
    z-index: 80;
    background: var(--color-white);
    border: 1.5px solid var(--color-border);
    border-radius: 12px;
    padding: 16px;
    min-width: 210px;
    box-shadow: 0 8px 24px rgb(var(--rgb-black) / .12);
}

.filter-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--color-gray-46);
    margin: 0 0 6px;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.filter-label.mt {
    margin-top: 12px;
}

.filter-select {
    width: 100%;
    background: var(--color-gray-98-2);
    border: 1.5px solid var(--color-border);
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 0.83rem;
    color: var(--color-ink);
    outline: none;
    font-family: 'Jost', sans-serif;
}

.date-inputs {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
}

.filter-date {
    flex: 1;
    background: var(--color-gray-98-2);
    border: 1.5px solid var(--color-border);
    border-radius: 8px;
    padding: 6px 8px;
    font-size: 0.77rem;
    color: var(--color-ink);
    outline: none;
    font-family: 'Jost', sans-serif;
}

.date-sep {
    font-size: 0.8rem;
    color: var(--color-gray-65);
    flex-shrink: 0;
}

.filter-reset {
    width: 100%;
    background: var(--color-gray-96);
    border: none;
    border-radius: 8px;
    padding: 7px;
    font-size: 0.81rem;
    color: var(--color-gray-46);
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    margin-top: 4px;
}

.filter-reset:hover {
    background: var(--color-border);
}

/* ── Stats Grid ──────────────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    margin-bottom: 18px;
}

.stat-card {
    background: var(--color-white);
    border: 1.5px solid var(--color-gray-94);
    border-radius: 14px;
    padding: 14px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 1px 4px rgb(var(--rgb-primary) / .04);
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
}

.stat-card:hover {
    border-color: var(--color-accent);
    box-shadow: 0 4px 12px rgb(var(--rgb-accent) / .15);
    transform: translateY(-1px);
}

.stat-card.active-card {
    border-color: var(--color-success);
    background: var(--color-green-94);
    box-shadow: 0 4px 12px rgb(var(--rgb-success) / .15);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon--total {
    background: var(--color-brown-94);
    color: var(--color-accent);
}

.stat-icon--pending {
    background: var(--color-gold-95);
    color: var(--color-warning);
}

.stat-icon--preparing {
    background: var(--color-blue-97);
    color: var(--color-info);
}

.stat-icon--ready {
    background: var(--color-green-97);
    color: var(--color-ready);
}

.stat-icon--delivered {
    background: var(--color-green-92);
    color: var(--color-success);
}

.stat-icon--cancelled {
    background: var(--color-red-97);
    color: var(--color-danger);
}


.stat-label {
    font-size: 0.68rem;
    color: var(--color-muted);
    font-weight: 500;
    margin: 0 0 1px;
}

.stat-value {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.45rem;
    font-weight: 600;
    color: var(--color-primary);
    line-height: 1;
    margin: 0 0 4px;
}

.stat-growth {
    font-size: 0.67rem;
    font-weight: 500;
    margin: 0;
}

.stat-growth.positive {
    color: var(--color-success);
}

.stat-growth.negative {
    color: var(--color-danger);
}

.stat-badge {
    display: inline-block;
    font-size: 0.63rem;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 99px;
}

.badge--pending {
    background: var(--color-gold-89);
    color: var(--color-coffee-31);
}

.badge--preparing {
    background: var(--color-blue-93);
    color: var(--color-blue-48);
}

.badge--ready {
    background: var(--color-green-93);
    color: var(--color-green-24);
}

.badge--delivered {
    background: var(--color-green-92);
    color: var(--color-success);
}

.badge--cancelled {
    background: var(--color-red-94);
    color: var(--color-red-35);
}

/* ── Table Card ─────────────────────────────────────────────── */
.table-card {
    background: var(--color-white);
    border: 1.5px solid var(--color-gray-94);
    border-radius: 14px;
    box-shadow: 0 1px 4px rgb(var(--rgb-primary) / .04);
    overflow: hidden;
}

.active-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-bottom: 1.5px solid var(--color-gray-96);
    background: var(--color-gray-98);
}

.filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--color-primary);
    color: var(--color-white);
    font-size: 0.76rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 99px;
}

.pill-remove {
    background: none;
    border: none;
    color: rgb(var(--rgb-white) / .7);
    cursor: pointer;
    font-size: 1rem;
    padding: 0;
    line-height: 1;
}

.pill-remove:hover {
    color: var(--color-white);
}

.filter-count {
    font-size: 0.76rem;
    color: var(--color-muted);
}

.table-wrap {
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}

.orders-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--color-muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1.5px solid var(--color-gray-96);
    background: var(--color-gray-98);
    white-space: nowrap;
}

.order-row td {
    padding: 13px 16px;
    border-bottom: 1px solid var(--color-gray-96);
    vertical-align: middle;
}

.order-row:last-child td {
    border-bottom: none;
}

.order-row:hover td {
    background: var(--color-surface-soft);
}

.order-id-td {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--color-primary);
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
    background: linear-gradient(135deg, var(--color-accent), var(--color-gold-31));
    color: var(--color-white);
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
    color: var(--color-primary);
    margin: 0 0 1px;
}

.cust-email {
    font-size: 0.72rem;
    color: var(--color-muted);
    margin: 0;
}

.price-td {
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--color-success);
    white-space: nowrap;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 0.73rem;
    font-weight: 600;
    white-space: nowrap;
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

.items-td {
    font-size: 0.82rem;
    color: var(--color-primary);
    text-align: center;
}

.date-td {
    font-size: 0.78rem;
    color: var(--color-primary);
    white-space: nowrap;
}

.date-time {
    font-size: 0.7rem;
    color: var(--color-muted);
    margin-top: 1px;
}

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
    border: 1.5px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-white);
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--color-primary);
    cursor: pointer;
    transition: all .15s;
    font-family: 'Jost', sans-serif;
    white-space: nowrap;
}

.btn-view:hover {
    border-color: var(--color-success);
    color: var(--color-success);
    background: var(--color-green-94);
}

.btn-view-sm {
    padding: 5px 10px;
    border: 1.5px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-white);
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--color-primary);
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    transition: all .15s;
}

.btn-view-sm:hover {
    border-color: var(--color-success);
    color: var(--color-success);
}

.btn-dots {
    width: 30px;
    height: 30px;
    border: 1.5px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-white);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--color-muted);
    transition: all .15s;
}

.btn-dots:hover {
    border-color: var(--color-success);
    color: var(--color-success);
}

.dropdown-wrap {
    position: relative;
}

.dropdown-menu {
    position: fixed;
    background: var(--color-white);
    border: 1.5px solid var(--color-border);
    border-radius: 10px;
    box-shadow: 0 8px 24px rgb(var(--rgb-black) / .12);
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
    color: var(--color-gray-27);
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: background .15s;
    font-family: 'Jost', sans-serif;
}

.dropdown-item:hover {
    background: var(--color-gray-98);
}

.dropdown-item.warning {
    color: var(--color-warning);
}

.dropdown-item.warning:hover {
    background: var(--color-gold-96);
}

.dropdown-item.danger {
    color: var(--color-danger);
}

.dropdown-item.danger:hover {
    background: var(--color-red-97);
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
    border-bottom: 1px solid var(--color-gray-96);
}

.sk-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--color-surface-alt);
    flex-shrink: 0;
    animation: shimmer 1.2s infinite;
}

.sk-lines {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.delivery-tag {
    display: block;
    font-size: 0.63rem;
    font-weight: 500;
    color: var(--color-muted);
    margin-top: 2px;
}

.sk-line {
    height: 11px;
    border-radius: 6px;
    background: var(--color-surface-alt);
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
    border: 1.5px solid var(--color-gray-96);
    border-radius: 12px;
    padding: 13px;
    display: flex;
    flex-direction: column;
    gap: 11px;
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
    color: var(--color-muted);
    font-family: 'Courier New', monospace;
    margin-top: 1px;
}

.mobile-bottom {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.mobile-price {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--color-success);
}

.mobile-meta {
    font-size: 0.73rem;
    color: var(--color-muted);
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
    padding: 56px 20px;
    color: var(--color-muted-light);
    gap: 10px;
}

.empty-icon {
    opacity: .3;
}

.empty-state p {
    font-size: 0.88rem;
}

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 20px;
    border-top: 1.5px solid var(--color-gray-96);
    flex-wrap: wrap;
    gap: 10px;
}

.pagination-info {
    font-size: 0.8rem;
    color: var(--color-muted);
}

.pg-simple {
    padding: 13px 20px;
    font-size: 0.8rem;
    color: var(--color-muted);
    border-top: 1.5px solid var(--color-gray-96);
}

.pagination-btns {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pg-btn {
    width: 32px;
    height: 32px;
    border: 1.5px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-white);
    color: var(--color-gray-27);
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s;
    font-family: 'Jost', sans-serif;
}

.pg-btn:hover:not(:disabled) {
    border-color: var(--color-success);
    color: var(--color-success);
}

.pg-btn.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: var(--color-white);
}

.pg-btn:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.pg-ellipsis {
    padding: 0 4px;
    color: var(--color-gray-65);
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
    box-shadow: 0 8px 24px rgb(var(--rgb-black) / .15);
    z-index: 9999;
    font-family: 'Jost', sans-serif;
}

.toast.success {
    background: var(--color-primary);
    color: var(--color-accent);
}

.toast.error {
    background: var(--color-danger);
    color: var(--color-white);
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

@media (max-width: 1280px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
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

    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .stat-card {
        flex-direction: column;
        text-align: center;
        gap: 6px;
        padding: 12px 8px;
    }

    .stat-badge {
        display: none;
    }

    .stat-value {
        font-size: 1.2rem;
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
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>