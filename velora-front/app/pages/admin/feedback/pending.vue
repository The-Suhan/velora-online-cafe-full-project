<template>
    <div class="feedback-page">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Pending Feedback</h1>
                <p class="page-sub">Unresolved feedback that requires your attention.</p>
            </div>
            <NuxtLink to="/admin/feedback" class="btn-back">
                <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                    <path
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" />
                </svg>
                All Feedback
            </NuxtLink>
        </div>

        <!-- Alert banner -->
        <div v-if="!loading && feedbacks.length > 0" class="alert-banner">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            <span><strong>{{ pagination.total }}</strong> pending {{ pagination.total === 1 ? 'feedback' : 'feedbacks'
                }} require attention.</span>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-toolbar">
                <span class="table-title">Pending Responses</span>
                <div class="toolbar-controls">
                    <div class="search-wrap">
                        <svg class="search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <circle cx="9" cy="9" r="6" />
                            <path d="M15 15l3 3" stroke-linecap="round" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Filter by user or email..."
                            class="search-input" @input="onSearch" />
                    </div>

                    <div class="type-tabs">
                        <button v-for="tab in typeTabs" :key="tab.value" class="type-tab"
                            :class="{ active: typeFilter === tab.value }" @click="setType(tab.value)">
                            {{ tab.label }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="skeleton-wrap">
                <div v-for="i in 5" :key="i" class="skeleton-row" />
            </div>

            <template v-else>
                <!-- Desktop -->
                <table class="fb-table desktop-only">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Message Preview</th>
                            <th>Submitted</th>
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
                            <td class="date-cell">{{ fb.created_at }}</td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-action btn-view" title="View" @click="openDetail(fb)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M2 10s3-7 8-7 8 7 8 7-3 7-8 7-8-7-8-7z" />
                                            <circle cx="10" cy="10" r="2.5" />
                                        </svg>
                                    </button>
                                    <button class="btn-action btn-resolve" title="Mark Resolved"
                                        @click="handleResolve(fb)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M5 10l4 4 6-6" stroke-linecap="round" stroke-linejoin="round" />
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

                <!-- Mobile -->
                <div class="mobile-cards mobile-only">
                    <div v-for="fb in feedbacks" :key="fb.id" class="mobile-card">
                        <div class="mobile-card-top">
                            <div class="user-cell">
                                <div class="user-avatar">
                                    <img v-if="fb.user?.avatar" :src="fb.user.avatar" class="avatar-img" />
                                    <span v-else class="avatar-initials">{{ initials(fb.user?.name) }}</span>
                                </div>
                                <div>
                                    <p class="user-name">{{ fb.user?.name ?? '—' }}</p>
                                    <p class="user-email">{{ fb.user?.email ?? '' }}</p>
                                </div>
                            </div>
                            <span class="type-badge" :class="typeColor(fb.type)">{{ typeLabel(fb.type) }}</span>
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
                                <button class="btn-action btn-resolve" @click="handleResolve(fb)">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                        width="14" height="14">
                                        <path d="M5 10l4 4 6-6" stroke-linecap="round" stroke-linejoin="round" />
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
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <circle cx="36" cy="36" r="8" />
                        <path d="M33 36l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p>No pending feedback — all caught up!</p>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="pagination">
                    <p class="pagination-info">Showing {{ pagination.from }}–{{ pagination.to }} of {{ pagination.total
                        }} entries</p>
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
                    Showing {{ feedbacks.length }} of {{ pagination.total }} pending entries
                </div>
            </template>
        </div>

        <!-- ── MODALS ── -->
        <Transition name="fade">
            <div v-if="activeModal" class="modal-overlay" @click.self="closeModal" />
        </Transition>

        <!-- Detail Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'detail' && selectedFb" class="modal">
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
                    <div class="detail-section">
                        <p class="detail-label">Submitted</p>
                        <p class="detail-value">{{ selectedFb.created_at }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal">Close</button>
                    <button class="btn-submit" :disabled="actionLoading" @click="handleResolveFromModal">
                        <span v-if="actionLoading" class="spinner" />
                        Mark as Resolved
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
                    <p class="delete-desc">You're about to permanently delete this feedback from <strong>{{
                        selectedFb.user?.name ?? 'this user' }}</strong>.</p>
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
import type { FeedbackItem, PaginationMeta } from '~/composables/useFeedback'

definePageMeta({ layout: 'admin', middleware: 'admin' })

const { fetchPending, fetchOne, resolve, destroy, typeLabel, typeColor } = useFeedback()

// ── State ──────────────────────────────────────────────────────
const loading = ref(true)
const actionLoading = ref(false)
const feedbacks = ref<FeedbackItem[]>([])
const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const search = ref('')
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

const load = async (page = 1) => {
    loading.value = true
    try {
        const res = await fetchPending({
            page,
            per_page: 10,
            search: search.value || undefined,
            type: typeFilter.value || undefined,
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

onMounted(() => load())

const onSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => load(1), 400)
}

const setType = (val: string) => {
    typeFilter.value = val
    load(1)
}

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

const handleResolve = async (fb: FeedbackItem) => {
    try {
        await resolve(fb.id)
        feedbacks.value = feedbacks.value.filter(f => f.id !== fb.id)
        pagination.value.total = Math.max(0, pagination.value.total - 1)
        showToast('Feedback resolved', 'success')
    } catch { showToast('Failed to resolve', 'error') }
}

const handleResolveFromModal = async () => {
    if (!selectedFb.value) return
    actionLoading.value = true
    try {
        await resolve(selectedFb.value.id)
        feedbacks.value = feedbacks.value.filter(f => f.id !== selectedFb.value!.id)
        pagination.value.total = Math.max(0, pagination.value.total - 1)
        showToast('Feedback resolved', 'success')
        closeModal()
    } catch { showToast('Failed to resolve', 'error') } finally { actionLoading.value = false }
}

const handleDelete = async () => {
    if (!selectedFb.value) return
    actionLoading.value = true
    try {
        await destroy(selectedFb.value.id)
        feedbacks.value = feedbacks.value.filter(f => f.id !== selectedFb.value!.id)
        pagination.value.total = Math.max(0, pagination.value.total - 1)
        showToast('Feedback deleted', 'success')
        closeModal()
    } catch { showToast('Failed to delete', 'error') } finally { actionLoading.value = false }
}

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
.feedback-page {
    max-width: 1200px;
}

.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 16px;
    flex-wrap: wrap;
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

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1.5px solid #e8e3dc;
    border-radius: 10px;
    background: #fff;
    color: #2C1810;
    font-size: 0.82rem;
    font-weight: 600;
    font-family: 'Jost', sans-serif;
    text-decoration: none;
    transition: all .15s;
    white-space: nowrap;
}

.btn-back:hover {
    background: #f0ede6;
}

/* Alert banner */
.alert-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fdf3e4;
    border: 1px solid #f0d9a0;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 16px;
    font-size: 0.875rem;
    color: #7a5c1e;
}

/* Table Card */
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
    font-family: 'Jost', sans-serif;
    transition: border-color .2s;
}

.search-input:focus {
    border-color: #C8A96E;
}

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
    font-family: 'Jost', sans-serif;
    transition: all .15s;
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

.subject-cell {
    font-size: 0.85rem;
    font-weight: 500;
    color: #2C1810;
    max-width: 160px;
}

.preview-cell {
    font-size: 0.82rem;
    color: #8a7060;
    max-width: 220px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.date-cell {
    font-size: 0.78rem;
    color: #b0967a;
    white-space: nowrap;
}

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

.btn-delete:hover {
    border-color: #dc2626;
    color: #dc2626;
    background: #fee2e2;
}

/* Mobile */
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

/* Skeleton */
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

/* Empty */
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

/* Pagination */
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
    border-color: #C8A96E;
    color: #C8A96E;
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

/* Modal */
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

.desktop-only {
    display: table;
}

.mobile-only {
    display: none;
}

@media (max-width: 680px) {
    .desktop-only {
        display: none;
    }

    .mobile-only {
        display: flex;
        flex-direction: column;
    }

    .table-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .search-input {
        width: 100%;
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