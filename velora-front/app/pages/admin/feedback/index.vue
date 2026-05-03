<template>
    <div class="feedback-page">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Feedback Management</h1>
                <p class="page-sub">Review and manage guest experiences across all locations.</p>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon--neutral">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <span class="stat-badge stat-badge--growth">
                        {{ stats.growth >= 0 ? '+' : '' }}{{ stats.growth }}% from last month
                    </span>
                </div>
                <p class="stat-label">Total Feedback</p>
                <p class="stat-value">{{ stats.total }}</p>
                <NuxtLink to="/admin/feedback" class="stat-view-link">
                    View All
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" width="12"
                        height="12">
                        <path d="M3 8h10M9 4l4 4-4 4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </NuxtLink>
            </div>

            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon--warning">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <span class="stat-badge stat-badge--warning">Requires attention</span>
                </div>
                <p class="stat-label">Pending Response</p>
                <p class="stat-value">{{ stats.pending }}</p>
                <NuxtLink to="/admin/feedback/pending" class="stat-view-link stat-view-link--warning">
                    View Pending
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" width="12"
                        height="12">
                        <path d="M3 8h10M9 4l4 4-4 4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </NuxtLink>
            </div>

            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon--success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <span class="stat-badge stat-badge--success">{{ stats.resolution_rate }}% resolution rate</span>
                </div>
                <p class="stat-label">Resolved Cases</p>
                <p class="stat-value">{{ stats.resolved }}</p>
                <NuxtLink to="/admin/feedback/resolved" class="stat-view-link stat-view-link--success">
                    View Resolved
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" width="12"
                        height="12">
                        <path d="M3 8h10M9 4l4 4-4 4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </NuxtLink>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-toolbar">
                <span class="table-title">Recent Guest Feedback</span>
                <div class="toolbar-controls">
                    <!-- Search -->
                    <div class="search-wrap">
                        <svg class="search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <circle cx="9" cy="9" r="6" />
                            <path d="M15 15l3 3" stroke-linecap="round" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Filter by user or email..."
                            class="search-input" @input="onSearch" />
                    </div>

                    <!-- Status filter -->
                    <div class="select-wrap">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                            class="select-icon">
                            <path d="M3 5h14M6 10h8M9 15h2" stroke-linecap="round" />
                        </svg>
                        <select v-model="statusFilter" class="filter-select" @change="load(1)">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="resolved">Resolved</option>
                        </select>
                        <svg viewBox="0 0 20 20" fill="currentColor" class="select-chevron" width="14" height="14">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                        </svg>
                    </div>

                    <!-- Type tabs -->
                    <div class="type-tabs">
                        <button v-for="tab in typeTabs" :key="tab.value" class="type-tab"
                            :class="{ active: typeFilter === tab.value }" @click="setType(tab.value)">
                            {{ tab.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Desktop Table -->
            <div v-if="loading" class="skeleton-wrap">
                <div v-for="i in 5" :key="i" class="skeleton-row" />
            </div>

            <template v-else>
                <table class="fb-table desktop-only">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Message Preview</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="fb in feedbacks" :key="fb.id" class="fb-row">
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">
                                        <img v-if="fb.user?.avatar" :src="fb.user.avatar" :alt="fb.user?.name"
                                            class="avatar-img" />
                                        <span v-else class="avatar-initials">{{ initials(fb.user?.name) }}</span>
                                    </div>
                                    <div>
                                        <p class="user-name">{{ fb.user?.name ?? '—' }}</p>
                                        <p class="user-email">{{ fb.user?.email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="type-badge" :class="typeColor(fb.type)">{{ typeLabel(fb.type) }}</span>
                            </td>
                            <td class="subject-cell">{{ fb.subject }}</td>
                            <td class="preview-cell">{{ fb.message }}</td>
                            <td>
                                <div class="status-cell" :class="fb.is_done ? 'status-done' : 'status-pending'">
                                    <svg v-if="fb.is_done" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                        stroke-width="2" width="14" height="14">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    <span v-else class="status-dot" />
                                    {{ fb.is_done ? 'Done' : 'Pending' }}
                                </div>
                            </td>
                            <td class="date-cell">{{ fb.created_at }}</td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-action btn-view" title="View Detail" @click="openDetail(fb)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M2 10s3-7 8-7 8 7 8 7-3 7-8 7-8-7-8-7z" />
                                            <circle cx="10" cy="10" r="2.5" />
                                        </svg>
                                    </button>
                                    <button v-if="!fb.is_done" class="btn-action btn-resolve" title="Mark Resolved"
                                        @click="handleResolve(fb)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M5 10l4 4 6-6" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <button v-else class="btn-action btn-unresolve" title="Reopen"
                                        @click="handleUnresolve(fb)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M4 4l12 12M16 4L4 16" stroke-linecap="round" />
                                        </svg>
                                    </button>
                                    <button class="btn-action btn-delete" title="Delete" @click="openDelete(fb)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M3 5h14M8 5V3h4v2M6 5l1 12h6l1-12" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Mobile Cards -->
                <div class="mobile-cards mobile-only">
                    <div v-for="fb in feedbacks" :key="fb.id" class="mobile-card">
                        <div class="mobile-card-top">
                            <div class="user-cell">
                                <div class="user-avatar">
                                    <img v-if="fb.user?.avatar" :src="fb.user.avatar" :alt="fb.user?.name"
                                        class="avatar-img" />
                                    <span v-else class="avatar-initials">{{ initials(fb.user?.name) }}</span>
                                </div>
                                <div>
                                    <p class="user-name">{{ fb.user?.name ?? '—' }}</p>
                                    <p class="user-email">{{ fb.user?.email ?? '' }}</p>
                                </div>
                            </div>
                            <div class="mobile-badges">
                                <span class="type-badge" :class="typeColor(fb.type)">{{ typeLabel(fb.type) }}</span>
                                <div class="status-cell" :class="fb.is_done ? 'status-done' : 'status-pending'">
                                    <span v-if="!fb.is_done" class="status-dot" />
                                    {{ fb.is_done ? 'Done' : 'Pending' }}
                                </div>
                            </div>
                        </div>
                        <p class="mobile-subject">{{ fb.subject }}</p>
                        <p class="mobile-preview">{{ fb.message }}</p>
                        <div class="mobile-footer">
                            <span class="date-cell">{{ fb.created_at }}</span>
                            <div class="actions-cell">
                                <button class="btn-action btn-view" @click="openDetail(fb)">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                        width="14" height="14">
                                        <path d="M2 10s3-7 8-7 8 7 8 7-3 7-8 7-8-7-8-7z" />
                                        <circle cx="10" cy="10" r="2.5" />
                                    </svg>
                                </button>
                                <button v-if="!fb.is_done" class="btn-action btn-resolve" @click="handleResolve(fb)">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                        width="14" height="14">
                                        <path d="M5 10l4 4 6-6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button v-else class="btn-action btn-unresolve" @click="handleUnresolve(fb)">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                        width="14" height="14">
                                        <path d="M4 4l12 12M16 4L4 16" stroke-linecap="round" />
                                    </svg>
                                </button>
                                <button class="btn-action btn-delete" @click="openDelete(fb)">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                        width="14" height="14">
                                        <path d="M3 5h14M8 5V3h4v2M6 5l1 12h6l1-12" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-if="feedbacks.length === 0" class="empty-state">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.2" width="48"
                        height="48">
                        <path d="M42 30a2 2 0 01-2 2H14l-8 8V10a2 2 0 012-2h28a2 2 0 012 2z" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <p>No feedback found</p>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="pagination">
                    <p class="pagination-info">Showing {{ pagination.from }}–{{ pagination.to }} of {{ pagination.total
                    }} feedback entries</p>
                    <div class="pagination-btns">
                        <button class="pg-btn" :disabled="pagination.current_page === 1"
                            @click="load(pagination.current_page - 1)">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13">
                                <path
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" />
                            </svg>
                        </button>
                        <template v-for="page in visiblePages" :key="page">
                            <span v-if="page === '...'" class="pg-ellipsis">…</span>
                            <button v-else class="pg-btn" :class="{ active: page === pagination.current_page }"
                                @click="load(page as number)">{{ page }}</button>
                        </template>
                        <button class="pg-btn" :disabled="pagination.current_page === pagination.last_page"
                            @click="load(pagination.current_page + 1)">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13">
                                <path
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else-if="feedbacks.length > 0" class="pagination-info-only">
                    Showing {{ feedbacks.length }} of {{ pagination.total }} feedback entries
                </div>
            </template>
        </div>

        <!-- ── MODALS ── -->
        <Transition name="fade">
            <div v-if="activeModal" class="modal-overlay" @click.self="closeModal" />
        </Transition>

        <!-- Detail Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'detail' && selectedFb" class="modal modal-detail">
                <div class="modal-header">
                    <h2 class="modal-title">Feedback Detail</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="detail-user-row">
                        <div class="user-avatar user-avatar--lg">
                            <img v-if="selectedFb.user?.avatar" :src="selectedFb.user.avatar" class="avatar-img" />
                            <span v-else class="avatar-initials">{{ initials(selectedFb.user?.name) }}</span>
                        </div>
                        <div>
                            <p class="user-name">{{ selectedFb.user?.name ?? '—' }}</p>
                            <p class="user-email">{{ selectedFb.user?.email ?? '' }}</p>
                        </div>
                        <span class="type-badge ml-auto" :class="typeColor(selectedFb.type)">{{
                            typeLabel(selectedFb.type)
                        }}</span>
                    </div>

                    <div class="detail-section">
                        <p class="detail-label">Subject</p>
                        <p class="detail-value">{{ selectedFb.subject }}</p>
                    </div>
                    <div class="detail-section">
                        <p class="detail-label">Message</p>
                        <p class="detail-message">{{ detailFb?.message ?? selectedFb.message }}</p>
                    </div>
                    <div class="detail-meta-row">
                        <div class="detail-meta-item">
                            <p class="detail-label">Status</p>
                            <div class="status-cell" :class="selectedFb.is_done ? 'status-done' : 'status-pending'">
                                <span v-if="!selectedFb.is_done" class="status-dot" />
                                {{ selectedFb.is_done ? 'Done' : 'Pending' }}
                            </div>
                        </div>
                        <div class="detail-meta-item">
                            <p class="detail-label">Submitted</p>
                            <p class="detail-value">{{ selectedFb.created_at }}</p>
                        </div>
                        <div v-if="selectedFb.is_done" class="detail-meta-item">
                            <p class="detail-label">Resolved By</p>
                            <p class="detail-value">{{ detailFb?.resolved_by?.name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal">Close</button>
                    <button v-if="!selectedFb.is_done" class="btn-submit" :disabled="actionLoading"
                        @click="handleResolveFromModal">
                        <span v-if="actionLoading" class="spinner" />
                        Mark as Resolved
                    </button>
                    <button v-else class="btn-unresolve-modal" :disabled="actionLoading"
                        @click="handleUnresolveFromModal">
                        <span v-if="actionLoading" class="spinner" />
                        Reopen
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Delete Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'delete' && selectedFb" class="modal modal-delete">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Feedback</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="delete-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36"
                            height="36">
                            <path
                                d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <p class="delete-title">Are you sure?</p>
                    <p class="delete-desc">You're about to permanently delete the feedback from <strong>{{
                        selectedFb.user?.name
                        ?? 'this user' }}</strong>. This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal">Cancel</button>
                    <button class="btn-danger" :disabled="actionLoading" @click="handleDelete">
                        <span v-if="actionLoading" class="spinner" />
                        {{ actionLoading ? 'Deleting…' : 'Delete' }}
                    </button>
                </div>
            </div>
        </Transition>

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
import type { FeedbackItem, FeedbackStats, PaginationMeta } from '~/composables/useFeedback'

definePageMeta({ layout: 'admin', middleware: 'admin' })

const { fetchStats, fetchFeedback, fetchOne, resolve, unresolve, destroy, typeLabel, typeColor } = useFeedback()

// ── State ──────────────────────────────────────────────────────
const loading = ref(true)
const actionLoading = ref(false)
const feedbacks = ref<FeedbackItem[]>([])
const stats = ref<FeedbackStats>({ total: 0, pending: 0, resolved: 0, resolution_rate: 0, growth: 0 })
const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const search = ref('')
const statusFilter = ref('')
const typeFilter = ref('')
const activeModal = ref<string | null>(null)
const selectedFb = ref<FeedbackItem | null>(null)
const detailFb = ref<FeedbackItem | null>(null)
const toast = reactive({ visible: false, message: '', type: 'success' })
let searchTimeout: ReturnType<typeof setTimeout>

const typeTabs = [
    { value: '', label: 'All' },
    { value: 'complaint', label: 'Complaints' },
    { value: 'request', label: 'Requests' },
    { value: 'question', label: 'Questions' },
]

// ── Pagination ─────────────────────────────────────────────────
const visiblePages = computed(() => {
    const cur = pagination.value.current_page
    const last = pagination.value.last_page
    if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
    const pages: (number | string)[] = [1]
    if (cur > 3) pages.push('...')
    for (let i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) pages.push(i)
    if (cur < last - 2) pages.push('...')
    pages.push(last)
    return pages
})

// ── Load ───────────────────────────────────────────────────────
const load = async (page = 1) => {
    loading.value = true
    try {
        const res = await fetchFeedback({
            page,
            per_page: 10,
            search: search.value || undefined,
            type: typeFilter.value || undefined,
            status: statusFilter.value || undefined,
        })
        feedbacks.value = res.data
        pagination.value = {
            current_page: res.current_page,
            last_page: res.last_page,
            total: res.total,
            from: res.from,
            to: res.to,
        }
    } catch {
        showToast('Failed to load feedback', 'error')
    } finally {
        loading.value = false
    }
}

const loadStats = async () => {
    try { stats.value = await fetchStats() } catch { }
}

onMounted(() => { loadStats(); load() })

// ── Filters ────────────────────────────────────────────────────
const onSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => load(1), 400)
}

const setType = (val: string) => {
    typeFilter.value = val
    load(1)
}

// ── Modals ─────────────────────────────────────────────────────
const openDetail = async (fb: FeedbackItem) => {
    selectedFb.value = fb
    detailFb.value = null
    activeModal.value = 'detail'
    try { detailFb.value = await fetchOne(fb.id) } catch { }
}

const openDelete = (fb: FeedbackItem) => {
    selectedFb.value = fb
    activeModal.value = 'delete'
}

const closeModal = () => {
    activeModal.value = null
    selectedFb.value = null
    detailFb.value = null
}

// ── Actions ────────────────────────────────────────────────────
const handleResolve = async (fb: FeedbackItem) => {
    try {
        await resolve(fb.id)
        fb.is_done = true
        fb.status = 'resolved'
        loadStats()
        showToast('Feedback marked as resolved', 'success')
    } catch { showToast('Failed to resolve', 'error') }
}

const handleUnresolve = async (fb: FeedbackItem) => {
    try {
        await unresolve(fb.id)
        fb.is_done = false
        fb.status = 'pending'
        loadStats()
        showToast('Feedback reopened', 'success')
    } catch { showToast('Failed to reopen', 'error') }
}

const handleResolveFromModal = async () => {
    if (!selectedFb.value) return
    actionLoading.value = true
    try {
        await resolve(selectedFb.value.id)
        selectedFb.value.is_done = true
        selectedFb.value.status = 'resolved'
        loadStats()
        load(pagination.value.current_page)
        showToast('Feedback marked as resolved', 'success')
        closeModal()
    } catch { showToast('Failed to resolve', 'error') } finally { actionLoading.value = false }
}

const handleUnresolveFromModal = async () => {
    if (!selectedFb.value) return
    actionLoading.value = true
    try {
        await unresolve(selectedFb.value.id)
        selectedFb.value.is_done = false
        selectedFb.value.status = 'pending'
        loadStats()
        load(pagination.value.current_page)
        showToast('Feedback reopened', 'success')
        closeModal()
    } catch { showToast('Failed to reopen', 'error') } finally { actionLoading.value = false }
}

const handleDelete = async () => {
    if (!selectedFb.value) return
    actionLoading.value = true
    try {
        await destroy(selectedFb.value.id)
        showToast('Feedback deleted', 'success')
        closeModal()
        loadStats()
        if (feedbacks.value.length === 1 && pagination.value.current_page > 1) {
            load(pagination.value.current_page - 1)
        } else {
            load(pagination.value.current_page)
        }
    } catch { showToast('Failed to delete', 'error') } finally { actionLoading.value = false }
}

// ── Helpers ────────────────────────────────────────────────────
const initials = (name?: string) => {
    if (!name) return '?'
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const showToast = (message: string, type: 'success' | 'error' = 'success') => {
    toast.message = message
    toast.type = type
    toast.visible = true
    setTimeout(() => { toast.visible = false }, 3500)
}
</script>

<style scoped>
/* ── Root ──────────────────────────────────────────────────── */
.feedback-page {
    max-width: 1200px;
}

/* ── Header ─────────────────────────────────────────────────── */
.page-header {
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

/* ── Stat Cards ─────────────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 1px 4px rgba(44, 24, 16, 0.06);
}

.stat-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon svg {
    width: 20px;
    height: 20px;
}

.stat-icon--neutral {
    background: #f0ede6;
    color: #8a7060;
}

.stat-icon--warning {
    background: #fdf3e4;
    color: #C8A96E;
}

.stat-icon--success {
    background: #e8f0e4;
    color: #4A6741;
}

.stat-badge {
    font-size: 0.72rem;
    font-weight: 500;
    padding: 3px 8px;
    border-radius: 20px;
}

.stat-badge--growth {
    background: #e8f0e4;
    color: #4A6741;
}

.stat-badge--warning {
    background: #fdf3e4;
    color: #C8A96E;
}

.stat-badge--success {
    background: #e8f0e4;
    color: #4A6741;
}

.stat-label {
    font-size: 0.8rem;
    color: #8a7060;
    margin: 0 0 4px;
}

.stat-value {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    font-weight: 700;
    color: #2C1810;
    margin: 0 0 14px;
    line-height: 1;
}

/* ── Stat View Link ─────────────────────────────────────────── */
.stat-view-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #8a7060;
    text-decoration: none;
    font-family: 'Jost', sans-serif;
    letter-spacing: .02em;
    padding: 5px 10px;
    border-radius: 8px;
    border: 1.5px solid #e8e3dc;
    background: #fafaf8;
    transition: all .15s;
}

.stat-view-link:hover {
    color: #2C1810;
    border-color: #2C1810;
    background: #f0ede6;
}

.stat-view-link--warning:hover {
    color: #C8A96E;
    border-color: #C8A96E;
    background: #fdf3e4;
}

.stat-view-link--success:hover {
    color: #4A6741;
    border-color: #4A6741;
    background: #e8f0e4;
}

/* ── Table Card ─────────────────────────────────────────────── */
.table-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 4px rgba(44, 24, 16, 0.06);
    overflow: hidden;
}

.table-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px 14px;
    gap: 12px;
    flex-wrap: wrap;
    border-bottom: 1px solid #f3f0eb;
}

.table-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.05rem;
    font-weight: 600;
    color: #2C1810;
    white-space: nowrap;
}

.toolbar-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Search */
.search-wrap {
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
    padding: 8px 12px 8px 32px;
    border: 1.5px solid #e8e3dc;
    border-radius: 10px;
    font-size: 0.82rem;
    width: 200px;
    outline: none;
    color: #2C1810;
    background: #fafaf8;
    transition: border-color .2s;
    font-family: 'Jost', sans-serif;
}

.search-input:focus {
    border-color: #4A6741;
}

/* Select */
.select-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.select-icon {
    position: absolute;
    left: 9px;
    width: 14px;
    height: 14px;
    color: #9ca3af;
    pointer-events: none;
}

.filter-select {
    appearance: none;
    padding: 8px 28px 8px 30px;
    border: 1.5px solid #e8e3dc;
    border-radius: 10px;
    font-size: 0.82rem;
    color: #2C1810;
    background: #fafaf8;
    outline: none;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    transition: border-color .2s;
}

.filter-select:focus {
    border-color: #4A6741;
}

.select-chevron {
    position: absolute;
    right: 8px;
    color: #9ca3af;
    pointer-events: none;
}

/* Type tabs */
.type-tabs {
    display: flex;
    gap: 2px;
    background: #f0ede6;
    border-radius: 10px;
    padding: 3px;
}

.type-tab {
    padding: 5px 12px;
    border: none;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 500;
    cursor: pointer;
    background: none;
    color: #8a7060;
    transition: all .15s;
    font-family: 'Jost', sans-serif;
    white-space: nowrap;
}

.type-tab.active {
    background: #fff;
    color: #2C1810;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
}

/* Table */
.fb-table {
    width: 100%;
    border-collapse: collapse;
}

.fb-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 0.72rem;
    font-weight: 600;
    color: #8a7060;
    text-transform: uppercase;
    letter-spacing: .06em;
    background: #fafaf8;
    border-bottom: 1px solid #f0ede6;
}

.fb-row td {
    padding: 14px 16px;
    border-bottom: 1px solid #f3f0eb;
    vertical-align: middle;
}

.fb-row:last-child td {
    border-bottom: none;
}

.fb-row:hover td {
    background: #fafaf8;
}

/* User cell */
.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: #f0ede6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-avatar--lg {
    width: 44px;
    height: 44px;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initials {
    font-size: 0.7rem;
    font-weight: 700;
    color: #8a7060;
    letter-spacing: .02em;
}

.user-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #2C1810;
    margin: 0 0 1px;
    white-space: nowrap;
}

.user-email {
    font-size: 0.72rem;
    color: #b0967a;
    margin: 0;
    white-space: nowrap;
}

/* Type badge */
.type-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.73rem;
    font-weight: 600;
}

.type-complaint {
    background: #fee2e2;
    color: #dc2626;
}

.type-request {
    background: #fdf3e4;
    color: #C8A96E;
}

.type-question {
    background: #e8f0e4;
    color: #4A6741;
}

/* Subject / Preview */
.subject-cell {
    font-size: 0.85rem;
    font-weight: 500;
    color: #2C1810;
    max-width: 160px;
}

.preview-cell {
    font-size: 0.82rem;
    color: #8a7060;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Status */
.status-cell {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.78rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-done {
    color: #4A6741;
}

.status-pending {
    color: #8a7060;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #d1cfc9;
    display: inline-block;
}

.date-cell {
    font-size: 0.78rem;
    color: #b0967a;
    white-space: nowrap;
}

/* Actions */
.actions-cell {
    display: flex;
    align-items: center;
    gap: 4px;
}

.btn-action {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: 1.5px solid #e8e3dc;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .15s;
    color: #8a7060;
}

.btn-view:hover {
    border-color: #2C1810;
    color: #2C1810;
}

.btn-resolve:hover {
    border-color: #4A6741;
    color: #4A6741;
    background: #e8f0e4;
}

.btn-unresolve:hover {
    border-color: #C8A96E;
    color: #C8A96E;
    background: #fdf3e4;
}

.btn-delete:hover {
    border-color: #dc2626;
    color: #dc2626;
    background: #fee2e2;
}

/* ── Mobile ──────────────────────────────────────────────────── */
.mobile-cards {
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mobile-card {
    border: 1.5px solid #f0ede6;
    border-radius: 12px;
    padding: 14px;
}

.mobile-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    gap: 8px;
}

.mobile-badges {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.mobile-subject {
    font-size: 0.875rem;
    font-weight: 600;
    color: #2C1810;
    margin: 0 0 4px;
}

.mobile-preview {
    font-size: 0.78rem;
    color: #8a7060;
    margin: 0 0 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mobile-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ── Skeleton ────────────────────────────────────────────────── */
.skeleton-wrap {
    padding: 8px 16px;
}

.skeleton-row {
    height: 58px;
    background: linear-gradient(90deg, #f0ede6 25%, #e8e3dc 50%, #f0ede6 75%);
    border-radius: 10px;
    margin-bottom: 8px;
    animation: shimmer 1.2s infinite;
    background-size: 400px;
}

@keyframes shimmer {
    0% {
        background-position: -400px 0;
    }

    100% {
        background-position: 400px 0;
    }
}

/* ── Empty ───────────────────────────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #c8bfb0;
    gap: 12px;
}

.empty-state p {
    font-size: 0.875rem;
}

/* ── Pagination ──────────────────────────────────────────────── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-top: 1px solid #f0ede6;
    flex-wrap: wrap;
    gap: 10px;
}

.pagination-info {
    font-size: 0.78rem;
    color: #8a7060;
}

.pagination-info-only {
    padding: 14px 20px;
    font-size: 0.78rem;
    color: #8a7060;
    border-top: 1px solid #f0ede6;
}

.pagination-btns {
    display: flex;
    align-items: center;
    gap: 3px;
}

.pg-btn {
    width: 32px;
    height: 32px;
    border: 1.5px solid #e8e3dc;
    border-radius: 8px;
    background: #fff;
    color: #2C1810;
    font-size: 0.8rem;
    font-weight: 500;
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
    opacity: .35;
    cursor: not-allowed;
}

.pg-ellipsis {
    padding: 0 4px;
    color: #b0967a;
    font-size: 0.85rem;
}

/* ── Modal ───────────────────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(44, 24, 16, .45);
    backdrop-filter: blur(2px);
    z-index: 200;
}

.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 24px 60px rgba(44, 24, 16, .18);
    z-index: 300;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.modal-delete {
    max-width: 420px;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f0ede6;
    flex-shrink: 0;
}

.modal-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 600;
    color: #2C1810;
}

.modal-close {
    width: 30px;
    height: 30px;
    border: none;
    background: #f0ede6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #8a7060;
    transition: background .15s;
}

.modal-close:hover {
    background: #e8e3dc;
}

.modal-body {
    padding: 20px 24px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #f0ede6;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-shrink: 0;
}

/* Detail modal content */
.detail-user-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.ml-auto {
    margin-left: auto;
}

.detail-section {
    margin-bottom: 16px;
}

.detail-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #b0967a;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin: 0 0 4px;
}

.detail-value {
    font-size: 0.875rem;
    color: #2C1810;
    font-weight: 500;
    margin: 0;
}

.detail-message {
    font-size: 0.875rem;
    color: #5a4a3a;
    line-height: 1.6;
    margin: 0;
    background: #fafaf8;
    border-radius: 10px;
    padding: 12px 14px;
    border: 1px solid #f0ede6;
}

.detail-meta-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 4px;
}

.detail-meta-item {
    flex: 1;
    min-width: 90px;
}

/* Delete modal */
.delete-icon-wrap {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: #fee2e2;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    color: #dc2626;
}

.delete-title {
    text-align: center;
    font-size: 1rem;
    font-weight: 700;
    color: #2C1810;
    margin-bottom: 8px;
}

.delete-desc {
    text-align: center;
    font-size: 0.875rem;
    color: #8a7060;
    line-height: 1.5;
}

/* Buttons */
.btn-cancel {
    padding: 9px 18px;
    border: 1.5px solid #e8e3dc;
    border-radius: 10px;
    background: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    color: #2C1810;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    transition: all .15s;
}

.btn-cancel:hover {
    background: #fafaf8;
}

.btn-submit {
    padding: 9px 18px;
    border: none;
    border-radius: 10px;
    background: #4A6741;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background .15s;
}

.btn-submit:hover {
    background: #3a5232;
}

.btn-submit:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.btn-unresolve-modal {
    padding: 9px 18px;
    border: 1.5px solid #C8A96E;
    border-radius: 10px;
    background: #fff;
    color: #C8A96E;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all .15s;
}

.btn-unresolve-modal:hover {
    background: #fdf3e4;
}

.btn-unresolve-modal:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.btn-danger {
    padding: 9px 18px;
    border: none;
    border-radius: 10px;
    background: #dc2626;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Jost', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background .15s;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-danger:disabled {
    opacity: .6;
    cursor: not-allowed;
}

/* Spinner */
.spinner {
    width: 13px;
    height: 13px;
    border: 2px solid rgba(255, 255, 255, .3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin .6s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* ── Toast ───────────────────────────────────────────────────── */
.toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    font-family: 'Jost', sans-serif;
    box-shadow: 0 8px 24px rgba(44, 24, 16, .18);
    z-index: 9999;
}

.toast.success {
    background: #2C1810;
    color: #fff;
}

.toast.error {
    background: #dc2626;
    color: #fff;
}

/* ── Transitions ─────────────────────────────────────────────── */
.fade-enter-active,
.fade-leave-active {
    transition: opacity .2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.modal-slide-enter-active {
    transition: all .25s cubic-bezier(.34, 1.56, .64, 1);
}

.modal-slide-leave-active {
    transition: all .18s ease;
}

.modal-slide-enter-from {
    opacity: 0;
    transform: translate(-50%, -46%) scale(.96);
}

.modal-slide-leave-to {
    opacity: 0;
    transform: translate(-50%, -54%) scale(.96);
}

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

/* ── Responsive ──────────────────────────────────────────────── */
.desktop-only {
    display: table;
}

.mobile-only {
    display: none;
}

@media (max-width: 900px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .table-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .toolbar-controls {
        width: 100%;
    }

    .search-input {
        width: 100%;
    }
}

@media (max-width: 680px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .desktop-only {
        display: none;
    }

    .mobile-only {
        display: flex;
        flex-direction: column;
    }

    .type-tabs {
        overflow-x: auto;
        width: 100%;
    }

    .toast {
        bottom: 80px;
        left: 12px;
        right: 12px;
    }

    .modal {
        width: 95%;
        max-height: 95vh;
    }
}
</style>