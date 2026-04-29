<template>
    <div class="users-page">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">User Management</h1>
                <p class="page-sub">Oversee staff permissions and luxury clientele profiles.</p>
            </div>
            <span class="last-updated">Last updated: {{ lastUpdated }}</span>
        </div>

        <!-- Stat card (sadece Total Users) -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f0e4">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#4A6741" stroke-width="1.8">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                    </svg>
                </div>
                <div class="stat-body">
                    <p class="stat-label">Total Users</p>
                    <p class="stat-value">{{ statsLoading ? '—' : formatNumber(stats.total) }}</p>
                    <p class="stat-growth positive" v-if="!statsLoading">
                        ↑ {{ stats.growth }}% from last month
                    </p>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-toolbar">
                <span class="card-title">All Users</span>
                <div class="toolbar-right">
                    <div class="search-wrap">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input v-model="search" class="search-input" placeholder="Search by name, email or role…"
                            @input="onSearchInput" />
                        <button v-if="search" class="search-clear" @click="clearSearch">×</button>
                    </div>
                    <div class="filter-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <line x1="4" y1="6" x2="20" y2="6" />
                            <line x1="8" y1="12" x2="16" y2="12" />
                            <line x1="11" y1="18" x2="13" y2="18" />
                        </svg>
                        <select v-model="roleFilter" class="filter-select" @change="loadUsers(1)">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Desktop table -->
            <div class="table-wrap">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>USER IDENTITY</th>
                            <th>ROLE</th>
                            <th>LAST ACTIVITY</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="5">
                                <div class="skeleton-rows">
                                    <div v-for="i in 6" :key="i" class="skeleton-row" />
                                </div>
                            </td>
                        </tr>
                        <template v-else>
                            <tr v-for="user in users" :key="user.id" class="user-row">
                                <td>
                                    <div class="user-identity">
                                        <div class="avatar-wrap">
                                            <div class="avatar-fallback">{{ initials(user.name) }}</div>
                                        </div>
                                        <div>
                                            <p class="user-name">{{ user.name }}</p>
                                            <p class="user-email">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge" :class="'role-' + user.role.toLowerCase()">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="last-activity-cell">{{ user.last_activity }}</td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="icon-btn trash-btn" title="Delete" @click="askDelete(user)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.6">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                                                <path d="M10 11v6M14 11v6" />
                                                <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" />
                                            </svg>
                                        </button>
                                        <div class="dots-wrap">
                                            <button class="icon-btn" @click.stop="toggleMenu(user.id)">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.6">
                                                    <circle cx="12" cy="5" r="1" fill="currentColor" />
                                                    <circle cx="12" cy="12" r="1" fill="currentColor" />
                                                    <circle cx="12" cy="19" r="1" fill="currentColor" />
                                                </svg>
                                            </button>
                                            <div v-if="openMenu === user.id" class="dropdown-menu">
                                                <button class="dropdown-item" @click="openPreview(user.id)">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.6">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                        <circle cx="12" cy="12" r="3" />
                                                    </svg>
                                                    Preview
                                                </button>
                                                <button class="dropdown-item" @click="goOrders(user.id)">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.6">
                                                        <path d="M6 2h12l3 7H3L6 2z" />
                                                        <path d="M3 9v11a2 2 0 002 2h14a2 2 0 002-2V9" />
                                                    </svg>
                                                    Orders
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="5" class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                        <circle cx="12" cy="8" r="4" />
                                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                                    </svg>
                                    <p>No users found</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Mobile list -->
            <div class="mobile-list">
                <template v-if="loading">
                    <div v-for="i in 5" :key="i" class="skeleton-row mobile-skeleton" />
                </template>
                <template v-else>
                    <div v-for="user in users" :key="'m' + user.id" class="mobile-user-card">
                        <div class="avatar-wrap">
                            <div class="avatar-fallback">{{ initials(user.name) }}</div>
                        </div>
                        <div class="mobile-user-info">
                            <p class="user-name">{{ user.name }}</p>
                            <p class="user-email">{{ user.email }}</p>
                            <div class="mobile-badges">
                                <span class="role-badge" :class="'role-' + user.role.toLowerCase()">{{ user.role
                                    }}</span>
                            </div>
                        </div>
                        <div class="dots-wrap">
                            <button class="icon-btn" @click.stop="toggleMenu('m' + user.id)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <circle cx="12" cy="5" r="1" fill="currentColor" />
                                    <circle cx="12" cy="12" r="1" fill="currentColor" />
                                    <circle cx="12" cy="19" r="1" fill="currentColor" />
                                </svg>
                            </button>
                            <div v-if="openMenu === 'm' + user.id" class="dropdown-menu dropdown-left">
                                <button class="dropdown-item" @click="openPreview(user.id)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    Preview
                                </button>
                                <button class="dropdown-item" @click="goOrders(user.id)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <path d="M6 2h12l3 7H3L6 2z" />
                                        <path d="M3 9v11a2 2 0 002 2h14a2 2 0 002-2V9" />
                                    </svg>
                                    Orders
                                </button>
                                <button class="dropdown-item danger-item" @click="askDelete(user)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                                        <path d="M10 11v6M14 11v6" />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-if="users.length === 0" class="empty-msg">No users found</p>
                </template>
            </div>

            <!-- Pagination -->
            <div class="pagination-row" v-if="pagination.last_page > 1">
                <span class="pagination-info">
                    Showing {{ pagination.from }}–{{ pagination.to }} of {{ pagination.total }} users
                </span>
                <div class="pagination-btns">
                    <button class="page-btn" :disabled="pagination.current_page === 1"
                        @click="loadUsers(pagination.current_page - 1)">Previous</button>
                    <button v-for="p in visiblePages" :key="p" class="page-btn"
                        :class="{ active: pagination.current_page === p }" @click="loadUsers(p)">{{ p }}</button>
                    <button class="page-btn" :disabled="pagination.current_page === pagination.last_page"
                        @click="loadUsers(pagination.current_page + 1)">Next</button>
                </div>
            </div>
            <div class="pagination-row simple-info" v-else-if="pagination.total > 0">
                <span class="pagination-info">{{ pagination.total }} users total</span>
            </div>
        </div>

        <!-- Preview Modal -->
        <Teleport to="body">
            <div v-if="previewModal" class="modal-backdrop" @click.self="previewModal = null">
                <div class="modal">
                    <div class="modal-header">
                        <span class="modal-title">User Preview</span>
                        <button class="modal-close" @click="previewModal = null">×</button>
                    </div>
                    <div class="modal-body" v-if="previewLoading">
                        <div class="preview-loading">
                            <div class="skeleton-row"
                                style="height:80px;width:80px;border-radius:50%;margin:0 auto 12px" />
                            <div class="skeleton-row" style="height:20px;width:60%;margin:0 auto 8px" />
                            <div class="skeleton-row" style="height:14px;width:45%;margin:0 auto 20px" />
                            <div class="skeleton-row" style="height:110px;border-radius:12px" />
                        </div>
                    </div>
                    <div class="modal-body" v-else-if="previewData">
                        <div class="preview-avatar-wrap">
                            <div class="preview-avatar-fallback">{{ initials(previewData.name) }}</div>
                        </div>
                        <h2 class="preview-name">{{ previewData.name }}</h2>
                        <p class="preview-email">{{ previewData.email }}</p>
                        <div class="preview-badges">
                            <span class="role-badge" :class="'role-' + previewData.role.toLowerCase()">{{
                                previewData.role
                                }}</span>
                        </div>
                        <div class="preview-details">
                            <div class="detail-row">
                                <span class="detail-label">Last Activity</span>
                                <span class="detail-value">{{ previewData.last_activity }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Member Since</span>
                                <span class="detail-value">{{ previewData.joined }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Orders</span>
                                <span class="detail-value">{{ previewData.order_count ?? 0 }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Verified</span>
                                <span class="detail-value">{{ previewData.is_verified ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button class="btn-secondary" @click="previewModal = null">Close</button>
                            <NuxtLink :to="`/admin/orders?user=${previewData.id}`" class="btn-primary"
                                @click="previewModal = null">
                                View Orders
                            </NuxtLink>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Delete Confirm Modal -->
        <Teleport to="body">
            <div v-if="deleteModal" class="modal-backdrop" @click.self="deleteModal = null">
                <div class="modal modal-sm">
                    <div class="modal-header">
                        <span class="modal-title">Delete User</span>
                        <button class="modal-close" @click="deleteModal = null">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="delete-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="1.6">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                        </div>
                        <p class="delete-msg">
                            Are you sure you want to delete <strong>{{ deleteModal.name }}</strong>?
                            This action cannot be undone.
                        </p>
                        <div class="modal-actions">
                            <button class="btn-secondary" :disabled="deleteLoading" @click="deleteModal = null">
                                Cancel
                            </button>
                            <button class="btn-danger" :disabled="deleteLoading" @click="confirmDelete">
                                <span v-if="deleteLoading">Deleting…</span>
                                <span v-else>Yes, Delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
definePageMeta({
    layout: 'admin' as any,
    middleware: 'admin',
})

const { fetchUsers, fetchUserStats, fetchUser, deleteUser } = useAdmin()

// ── Types ──────────────────────────────────────────────────
interface UserRow {
    id: number
    name: string
    email: string
    role: string
    last_activity: string
    joined: string
    is_verified: boolean
    order_count?: number
}

interface Pagination {
    current_page: number
    last_page: number
    total: number
    from: number
    to: number
}

// ── State ──────────────────────────────────────────────────
const loading = ref(true)
const statsLoading = ref(true)
const users = ref<UserRow[]>([])
const stats = ref({ total: 0, growth: 0 })
const pagination = ref<Pagination>({ current_page: 1, last_page: 1, total: 0, from: 1, to: 0 })

const search = ref('')
const roleFilter = ref('')
const openMenu = ref<string | number | null>(null)

const previewModal = ref(false)
const previewLoading = ref(false)
const previewData = ref<UserRow | null>(null)

const deleteModal = ref<UserRow | null>(null)
const deleteLoading = ref(false)

const lastUpdated = ref('')

// ── Load ───────────────────────────────────────────────────
const loadUsers = async (page = 1) => {
    loading.value = true
    try {
        const res = await fetchUsers({
            search: search.value || undefined,
            role: roleFilter.value || undefined,
            page,
            per_page: 10,
        }) as any

        users.value = res.data
        pagination.value = {
            current_page: res.current_page,
            last_page: res.last_page,
            total: res.total,
            from: res.from ?? 1,
            to: res.to ?? res.data.length,
        }
        lastUpdated.value = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    } catch (e) {
        console.error('Users load error:', e)
    } finally {
        loading.value = false
    }
}

const loadStats = async () => {
    statsLoading.value = true
    try {
        const res = await fetchUserStats() as any
        stats.value = { total: res.total, growth: res.growth }
    } catch (e) {
        console.error('Stats error:', e)
    } finally {
        statsLoading.value = false
    }
}

onMounted(() => {
    loadUsers()
    loadStats()
    document.addEventListener('click', closeMenu)
})

onUnmounted(() => {
    document.removeEventListener('click', closeMenu)
})

// ── Search (debounce) ──────────────────────────────────────
let searchTimer: ReturnType<typeof setTimeout>
const onSearchInput = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => loadUsers(1), 400)
}

const clearSearch = () => {
    search.value = ''
    loadUsers(1)
}

// ── Pagination helper ──────────────────────────────────────
const visiblePages = computed(() => {
    const total = pagination.value.last_page
    const cur = pagination.value.current_page
    if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1)
    const pages: number[] = []
    for (let p = Math.max(1, cur - 1); p <= Math.min(total, cur + 1); p++) pages.push(p)
    return pages
})

// ── Menu ───────────────────────────────────────────────────
const toggleMenu = (id: string | number) => {
    openMenu.value = openMenu.value === id ? null : id
}
const closeMenu = () => { openMenu.value = null }

// ── Preview ────────────────────────────────────────────────
const openPreview = async (id: number) => {
    openMenu.value = null
    previewModal.value = true
    previewLoading.value = true
    previewData.value = null
    try {
        previewData.value = await fetchUser(id) as UserRow
    } catch (e) {
        console.error('Preview error:', e)
        previewModal.value = false
    } finally {
        previewLoading.value = false
    }
}

// ── Delete ─────────────────────────────────────────────────
const askDelete = (user: UserRow) => {
    openMenu.value = null
    deleteModal.value = user
}

const confirmDelete = async () => {
    if (!deleteModal.value) return
    deleteLoading.value = true
    try {
        await deleteUser(deleteModal.value.id)
        users.value = users.value.filter(u => u.id !== deleteModal.value!.id)
        pagination.value.total--
        stats.value.total--
        deleteModal.value = null
    } catch (e: any) {
        alert(e?.data?.message ?? 'Could not delete user.')
    } finally {
        deleteLoading.value = false
    }
}

// ── Orders ─────────────────────────────────────────────────
const goOrders = (id: number) => {
    openMenu.value = null
    navigateTo(`/admin/orders?user=${id}`)
}

// ── Helpers ────────────────────────────────────────────────
const formatNumber = (n: number) => new Intl.NumberFormat().format(n ?? 0)
const initials = (name: string) => name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
</script>

<style scoped>
.users-page {
    max-width: 1200px;
}

/* ── Header ───────────────────────────────────────────────── */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 12px;
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

.last-updated {
    font-size: 0.78rem;
    color: #8a7060;
    background: #F0EDE6;
    border-radius: 99px;
    padding: 5px 12px;
    white-space: nowrap;
    align-self: flex-start;
    margin-top: 6px;
}

/* ── Stat card ────────────────────────────────────────────── */
.stats-row {
    margin-bottom: 24px;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 22px;
    display: inline-flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 4px rgba(44, 24, 16, 0.06);
    min-width: 220px;
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

.stat-icon svg {
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

/* ── Table card ───────────────────────────────────────────── */
.table-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 1px 4px rgba(44, 24, 16, 0.06);
}

.table-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    gap: 12px;
    flex-wrap: wrap;
}

.card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 600;
    color: #2C1810;
}

.toolbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Search */
.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 10px;
    width: 15px;
    height: 15px;
    color: #b0967a;
    pointer-events: none;
}

.search-input {
    border: 1.5px solid #E8E2D9;
    border-radius: 10px;
    padding: 7px 32px 7px 32px;
    font-family: 'Jost', sans-serif;
    font-size: 0.8rem;
    color: #2C1810;
    background: #FAFAF8;
    outline: none;
    width: 220px;
    transition: border-color 0.15s;
}

.search-input:focus {
    border-color: #C8A96E;
}

.search-input::placeholder {
    color: #b0967a;
}

.search-clear {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    font-size: 1rem;
    color: #b0967a;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

/* Filter */
.filter-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    border: 1.5px solid #E8E2D9;
    border-radius: 10px;
    padding: 6px 10px;
    background: #FAFAF8;
}

.filter-wrap svg {
    width: 15px;
    height: 15px;
    color: #b0967a;
}

.filter-select {
    border: none;
    background: none;
    font-family: 'Jost', sans-serif;
    font-size: 0.8rem;
    color: #2C1810;
    outline: none;
    cursor: pointer;
}

/* ── Desktop table ────────────────────────────────────────── */
.table-wrap {
    overflow-x: auto;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.84rem;
}

.users-table thead th {
    padding: 10px 12px;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 600;
    color: #b0967a;
    letter-spacing: 0.05em;
    border-bottom: 1.5px solid #F0EDE6;
    white-space: nowrap;
}

.user-row td {
    padding: 12px;
    border-bottom: 1px solid #F8F5F0;
    vertical-align: middle;
}

.user-row:last-child td {
    border-bottom: none;
}

.user-row:hover td {
    background: #FAFAF8;
}

.user-identity {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar-wrap {
    position: relative;
    flex-shrink: 0;
}

.avatar-fallback {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #F0EDE6;
    color: #8a7060;
    font-family: 'Cormorant Garamond', serif;
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-name {
    font-size: 0.85rem;
    font-weight: 500;
    color: #2C1810;
    margin: 0 0 2px;
}

.user-email {
    font-size: 0.75rem;
    color: #8a7060;
    margin: 0;
}

.last-activity-cell {
    color: #8a7060;
    font-size: 0.8rem;
    white-space: nowrap;
}

/* Badges */
.role-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    padding: 3px 10px;
    border-radius: 99px;
    text-transform: uppercase;
}

.role-admin {
    background: #2C1810;
    color: #C8A96E;
}

.role-customer {
    background: #F0EDE6;
    color: #8a7060;
}

.role-manager {
    background: #fdf3e4;
    color: #C8A96E;
    border: 1px solid #e8d4a0;
}

.role-editor {
    background: #e8f0e4;
    color: #4A6741;
}

.role-staff {
    background: #F0EDE6;
    color: #8a7060;
}


/* Actions */
.actions-cell {
    display: flex;
    align-items: center;
    gap: 4px;
}

.icon-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    color: #8a7060;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s, color 0.15s;
}

.icon-btn:hover {
    background: #F0EDE6;
    color: #2C1810;
}

.icon-btn svg {
    width: 16px;
    height: 16px;
}

.trash-btn:hover {
    background: #fdecea;
    color: #c0392b;
}

/* Dropdown */
.dots-wrap {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 4px);
    background: #fff;
    border: 1.5px solid #E8E2D9;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(44, 24, 16, 0.12);
    z-index: 100;
    min-width: 140px;
    overflow: hidden;
    animation: dropIn 0.12s ease;
}

.dropdown-menu.dropdown-left {
    right: 0;
}

@keyframes dropIn {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: none;
    border: none;
    font-family: 'Jost', sans-serif;
    font-size: 0.82rem;
    color: #2C1810;
    cursor: pointer;
    text-align: left;
    transition: background 0.12s;
}

.dropdown-item:hover {
    background: #F8F5F0;
}

.dropdown-item svg {
    width: 15px;
    height: 15px;
    color: #8a7060;
}

.danger-item {
    color: #c0392b;
}

.danger-item svg {
    color: #c0392b;
}

.danger-item:hover {
    background: #fdecea;
}

/* Skeleton */
.skeleton-rows {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 12px 0;
}

.skeleton-row {
    height: 52px;
    background: linear-gradient(90deg, #F0EDE6 25%, #e8e2d9 50%, #F0EDE6 75%);
    background-size: 800px;
    border-radius: 10px;
    animation: shimmer 1.2s infinite;
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
    text-align: center;
    padding: 40px;
    color: #b0967a;
}

.empty-state svg {
    width: 40px;
    height: 40px;
    opacity: 0.4;
    margin: 0 auto 8px;
    display: block;
}

.empty-state p {
    font-size: 0.84rem;
    margin: 0;
}

.empty-msg {
    font-size: 0.82rem;
    color: #b0967a;
    text-align: center;
    padding: 20px 0;
}

/* ── Pagination ────────────────────────────────────────────── */
.pagination-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1.5px solid #F0EDE6;
    margin-top: 4px;
    flex-wrap: wrap;
    gap: 10px;
}

.simple-info {
    justify-content: flex-start;
    border-top: none;
    padding-top: 12px;
}

.pagination-info {
    font-size: 0.78rem;
    color: #8a7060;
}

.pagination-btns {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.page-btn {
    background: none;
    border: 1.5px solid #E8E2D9;
    border-radius: 8px;
    padding: 5px 12px;
    font-family: 'Jost', sans-serif;
    font-size: 0.78rem;
    color: #8a7060;
    cursor: pointer;
    transition: all 0.15s;
}

.page-btn:hover:not(:disabled) {
    border-color: #C8A96E;
    color: #2C1810;
}

.page-btn.active {
    background: #2C1810;
    color: #C8A96E;
    border-color: #2C1810;
    font-weight: 600;
}

.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Mobile list ──────────────────────────────────────────── */
.mobile-list {
    display: none;
    flex-direction: column;
    gap: 0;
}

.mobile-skeleton {
    margin-bottom: 10px;
}

.mobile-user-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 4px;
    border-bottom: 1px solid #F0EDE6;
}

.mobile-user-card:last-child {
    border-bottom: none;
}

.mobile-user-info {
    flex: 1;
    min-width: 0;
}

.mobile-user-info .user-email {
    margin-bottom: 6px;
}

.mobile-badges {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

/* ── Modal ────────────────────────────────────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(44, 24, 16, 0.4);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: fadeIn 0.15s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

.modal {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 20px 60px rgba(44, 24, 16, 0.2);
    animation: slideUp 0.2s ease;
}

.modal-sm {
    max-width: 340px;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px 0;
}

.modal-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 600;
    color: #2C1810;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.4rem;
    color: #8a7060;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
}

.modal-body {
    padding: 20px;
}

/* Preview */
.preview-avatar-wrap {
    width: 80px;
    margin: 0 auto 12px;
}

.preview-avatar-fallback {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #F0EDE6;
    color: #8a7060;
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.6rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    font-weight: 600;
    color: #2C1810;
    text-align: center;
    margin: 0 0 4px;
}

.preview-email {
    font-size: 0.8rem;
    color: #8a7060;
    text-align: center;
    margin: 0 0 14px;
}

.preview-badges {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 20px;
}

.preview-details {
    border: 1.5px solid #F0EDE6;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 20px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 14px;
    border-bottom: 1px solid #F0EDE6;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-size: 0.78rem;
    color: #8a7060;
}

.detail-value {
    font-size: 0.82rem;
    font-weight: 500;
    color: #2C1810;
}

.preview-loading {
    padding: 8px 0;
}

/* Delete modal */
.delete-icon-wrap {
    width: 48px;
    height: 48px;
    background: #fdecea;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.delete-icon-wrap svg {
    width: 24px;
    height: 24px;
}

.delete-msg {
    font-size: 0.84rem;
    color: #8a7060;
    line-height: 1.6;
    margin: 0 0 20px;
    text-align: center;
}

.delete-msg strong {
    color: #2C1810;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-secondary {
    background: #F0EDE6;
    border: none;
    border-radius: 10px;
    padding: 9px 18px;
    font-family: 'Jost', sans-serif;
    font-size: 0.84rem;
    color: #8a7060;
    cursor: pointer;
    transition: background 0.15s;
}

.btn-secondary:hover:not(:disabled) {
    background: #E8E2D9;
}

.btn-secondary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary {
    background: #2C1810;
    border: none;
    border-radius: 10px;
    padding: 9px 18px;
    font-family: 'Jost', sans-serif;
    font-size: 0.84rem;
    color: #C8A96E;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.15s;
    display: inline-flex;
    align-items: center;
}

.btn-primary:hover {
    opacity: 0.85;
}

.btn-danger {
    background: #c0392b;
    border: none;
    border-radius: 10px;
    padding: 9px 18px;
    font-family: 'Jost', sans-serif;
    font-size: 0.84rem;
    color: #fff;
    cursor: pointer;
    transition: opacity 0.15s;
}

.btn-danger:hover:not(:disabled) {
    opacity: 0.85;
}

.btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ── Responsive ───────────────────────────────────────────── */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.4rem;
    }

    .stat-value {
        font-size: 1.3rem;
    }

    .table-wrap {
        display: none;
    }

    .mobile-list {
        display: flex;
    }

    .table-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .toolbar-right {
        width: 100%;
        flex-direction: column;
    }

    .search-wrap {
        width: 100%;
    }

    .search-input {
        width: 100%;
    }

    .filter-wrap {
        width: 100%;
    }

    .filter-select {
        flex: 1;
    }

    .pagination-row {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .stat-card {
        padding: 12px 16px;
        min-width: unset;
        width: 100%;
    }

    .stat-icon {
        width: 38px;
        height: 38px;
    }

    .page-header {
        flex-direction: column;
    }

    .last-updated {
        align-self: flex-start;
        margin-top: 0;
    }
}
</style>