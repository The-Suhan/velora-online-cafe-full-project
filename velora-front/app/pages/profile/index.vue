<template>
    <div class="min-h-screen bg-[#F5EFEA]">

        <!-- Top Header -->
        <div class="bg-[#2C1A14] px-6 pt-8 pb-14">
            <h1 class="text-[#c8b89a] text-3xl font-light leading-tight">
                {{ $t('profile.welcome') }}<br />
                <em class="text-[#C8A96A] not-italic font-normal text-4xl">
                    {{ firstName }}.
                </em>
            </h1>
        </div>

        <!-- Content -->
        <div class="px-4 -mt-8 pb-10 max-w-5xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-[240px_1fr] gap-4">

                <!-- Left column -->
                <div class="flex flex-col gap-3">

                    <!-- Profile card -->
                    <div class="bg-white rounded-2xl border border-[#E0D5CC] p-5">
                        <div
                            class="w-16 h-16 rounded-full bg-[#2C1A14] flex items-center justify-center text-[#C8A96A] text-xl font-medium mx-auto mb-3">
                            {{ initials }}
                        </div>
                        <p class="text-center text-[#2C1A14] font-medium text-sm">{{ authUser?.name }}</p>
                        <p class="text-center text-[#7A6558] text-xs mt-1">{{ authUser?.email }}</p>

                        <div class="flex justify-around mt-4 pt-4 border-t border-[#E0D5CC]">
                            <div class="text-center">
                                <p class="text-[#2C1A14] font-medium text-lg">{{ orders?.total ?? '–' }}</p>
                                <p class="text-[#7A6558] text-[10px] uppercase tracking-wider mt-0.5">{{
                                    $t('profile.orders') }}</p>
                            </div>
                        </div>

                        <NuxtLink to="/profile/edit"
                            class="mt-4 flex items-center justify-center gap-2 w-full py-2.5 border border-[#2C1A14] rounded-lg text-[#2C1A14] text-sm hover:bg-[#F5EFEA] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            {{ $t('profile.editProfile') }}
                        </NuxtLink>

                        <button @click="handleLogout"
                            class="mt-2 flex items-center justify-center gap-2 w-full py-2.5 border border-[#E0D5CC] rounded-lg text-[#7A6558] text-sm hover:bg-[#F5EFEA] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
                            </svg>
                            {{ $t('profile.signOut') }}
                        </button>
                    </div>
                </div>

                <!-- Right: Orders panel -->
                <div class="bg-white rounded-2xl border border-[#E0D5CC] overflow-hidden">
                    <!-- Tabs -->
                    <div class="flex border-b border-[#E0D5CC] px-5">
                        <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key" :class="[
                            'py-3.5 px-4 text-sm border-b-2 -mb-px transition-colors',
                            activeTab === tab.key
                                ? 'text-[#2C1A14] border-[#C8A96A] font-medium'
                                : 'text-[#7A6558] border-transparent hover:text-[#2C1A14]'
                        ]">{{ tab.label }}</button>
                    </div>

                    <!-- Orders tab -->
                    <div v-if="activeTab === 'orders'">
                        <div class="flex justify-between items-center px-5 py-3">
                            <span class="text-sm font-medium text-[#2C1A14]">{{ $t('profile.recentOrders') }}</span>
                            <span class="text-xs text-[#7A6558] uppercase tracking-wider">
                                {{ $t('profile.showing', {
                                    shown: orders?.data?.length ?? 0, total: orders?.total ?? 0
                                }) }}
                            </span>
                        </div>

                        <!-- Loading skeleton -->
                        <div v-if="loadingOrders" class="divide-y divide-[#E0D5CC]">
                            <div v-for="i in 5" :key="i" class="flex items-center gap-3 px-5 py-4 animate-pulse">
                                <div class="w-9 h-10 bg-[#F5EFEA] rounded-lg flex-shrink-0"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 bg-[#F5EFEA] rounded w-2/3"></div>
                                    <div class="h-3 bg-[#F5EFEA] rounded w-1/3"></div>
                                </div>
                                <div class="h-3 bg-[#F5EFEA] rounded w-12"></div>
                            </div>
                        </div>

                        <!-- Empty -->
                        <div v-else-if="!orders?.data?.length" class="py-16 text-center">
                            <p class="text-[#7A6558] text-sm">{{ $t('profile.noOrders') }}</p>
                            <NuxtLink to="/menu"
                                class="mt-3 inline-block text-[#C8A96A] text-sm underline underline-offset-2">
                                Browse the menu
                            </NuxtLink>
                        </div>

                        <!-- Order list -->
                        <div v-else class="divide-y divide-[#E0D5CC]">
                            <div v-for="order in orders.data" :key="order.id"
                                class="flex items-center gap-3 px-5 py-3.5">
                                <!-- Date -->
                                <div class="w-9 text-center flex-shrink-0">
                                    <p class="text-[#2C1A14] font-medium text-lg leading-none">{{
                                        formatDay(order.created_at) }}</p>
                                    <p class="text-[#7A6558] text-[10px] uppercase tracking-wide mt-0.5">{{
                                        formatMonth(order.created_at) }}</p>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-[#2C1A14] font-medium text-sm truncate">
                                        {{ getProductName(order.items[0]) }}
                                        <span v-if="order.items.length > 1" class="text-[#7A6558] font-normal text-xs">
                                            +{{ order.items.length - 1 }} {{ $t('profile.more') }}
                                        </span>
                                    </p>
                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                        <span :class="statusClass(order.status)"
                                            class="text-[10px] font-medium px-2 py-0.5 rounded-full">
                                            {{ statusLabel(order.status) }}
                                        </span>
                                        <span class="text-[#7A6558] text-[10px] uppercase">
                                            {{ order.delivery_type === 'delivery' ? $t('bag.delivery') :
                                                $t('bag.pickup') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Price + actions -->
                                <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                                    <span class="text-[#2C1A14] font-medium text-sm">${{ order.total_price.toFixed(2)
                                    }}</span>
                                    <div class="flex gap-1.5">
                                        <button v-if="order.status === 'pending'" @click="handleCancel(order.id)"
                                            :disabled="cancellingId === order.id"
                                            class="text-[10px] px-2.5 py-1 border border-red-200 text-red-500 rounded-md hover:bg-red-50 transition-colors disabled:opacity-50">
                                            {{ cancellingId === order.id ? '...' : $t('profile.cancel') }}
                                        </button>
                                        <button @click="openDetail(order.id)"
                                            class="text-[10px] px-2.5 py-1 border border-[#E0D5CC] text-[#7A6558] rounded-md hover:border-[#2C1A14] hover:text-[#2C1A14] transition-colors">
                                            {{ $t('profile.details') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="orders && orders.last_page > 1"
                            class="flex justify-center gap-2 py-4 border-t border-[#E0D5CC]">
                            <button v-for="p in orders.last_page" :key="p" @click="loadOrders(p)" :class="[
                                'w-8 h-8 rounded-lg text-xs border transition-colors',
                                currentPage === p
                                    ? 'bg-[#2C1A14] text-white border-[#2C1A14]'
                                    : 'border-[#E0D5CC] text-[#7A6558] hover:border-[#2C1A14] hover:text-[#2C1A14]'
                            ]">{{ p }}</button>
                        </div>
                    </div>

                    <!-- Other tabs placeholder -->
                    <!-- Other tabs placeholder -->
                    <div v-else-if="activeTab === 'favorites'">
                        <div class="flex justify-between items-center px-5 py-3">
                            <span class="text-sm font-medium text-[#2C1A14]">{{ $t('profile.ratedProducts') }}</span>
                            <span class="text-xs text-[#7A6558] uppercase tracking-wider">
                                {{ $t('home.itemsCount', favorites.length, { named: { n: favorites.length } }) }}
                            </span>
                        </div>

                        <!-- Loading -->
                        <div v-if="loadingFavorites" class="divide-y divide-[#E0D5CC]">
                            <div v-for="i in 4" :key="i" class="flex items-center gap-3 px-5 py-4 animate-pulse">
                                <div class="w-10 h-10 bg-[#F5EFEA] rounded-lg flex-shrink-0"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 bg-[#F5EFEA] rounded w-2/3"></div>
                                    <div class="h-3 bg-[#F5EFEA] rounded w-1/3"></div>
                                </div>
                                <div class="h-3 bg-[#F5EFEA] rounded w-10"></div>
                            </div>
                        </div>

                        <!-- Empty -->
                        <div v-else-if="!favorites.length" class="py-16 text-center">
                            <p class="text-[#7A6558] text-sm">{{ $t('profile.noRatings') }}</p>
                            <NuxtLink to="/menu"
                                class="mt-3 inline-block text-[#C8A96A] text-sm underline underline-offset-2">
                                {{ $t('profile.browseMenu') }}
                            </NuxtLink>
                        </div>

                        <!-- List -->
                        <div v-else class="divide-y divide-[#E0D5CC]">
                            <div v-for="item in favorites" :key="item.product_id"
                                class="flex items-center gap-3 px-5 py-3.5">
                                <!-- Image -->
                                <div class="w-10 h-10 rounded-lg bg-[#F0EDE6] flex-shrink-0 overflow-hidden">
                                    <img v-if="item.image_url" :src="resolveImageUrl(item.image_url)"
                                        :alt="item.product_name" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-[#b0967a]">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                            width="16" height="16">
                                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-[#2C1A14] font-medium text-sm truncate">{{ item.product_name }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span v-if="item.category"
                                            class="text-[#7A6558] text-[10px] uppercase tracking-wider">
                                            {{ item.category }}
                                        </span>
                                        <span class="text-[#b0967a] text-[10px]">{{ item.rated_at }}</span>
                                    </div>
                                </div>

                                <!-- Score + price -->
                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                    <div class="flex items-center gap-1">
                                        <svg viewBox="0 0 24 24" fill="#C8A96A" width="12" height="12">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <span class="text-[#2C1A14] font-medium text-sm">{{ item.score }}</span>
                                    </div>
                                    <span class="text-[#7A6558] text-xs">${{ item.price.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="activeTab === 'feedback'">
                        <div class="flex justify-between items-center px-5 py-3">
                            <span class="text-sm font-medium text-[#2C1A14]">{{ $t('profile.myFeedback') }}</span>
                            <button @click="showFeedbackForm = !showFeedbackForm"
                                class="text-xs px-3 py-1.5 bg-[#2C1A14] text-[#C8A96A] rounded-lg hover:bg-[#3d2416] transition-colors">
                                + {{ $t('profile.new') }}
                            </button>
                        </div>

                        <!-- New feedback form -->
                        <div v-if="showFeedbackForm"
                            class="mx-5 mb-4 p-4 bg-[#FAFAF8] rounded-xl border border-[#E0D5CC] space-y-3">
                            <div class="flex gap-2">
                                <button v-for="type in feedbackTypes" :key="type.key"
                                    @click="feedbackForm.type = type.key" :class="feedbackForm.type === type.key
                                        ? 'bg-[#2C1A14] text-[#C8A96A]'
                                        : 'border border-[#E0D5CC] text-[#7A6558] hover:border-[#2C1A14]'"
                                    class="px-3 py-1 rounded-lg text-xs capitalize transition-colors">
                                    {{ type.label }}
                                </button>
                            </div>
                            <input v-model="feedbackForm.subject"
                                :placeholder="$t('header.feedback.subjectPlaceholder')"
                                class="w-full px-3 py-2 text-sm border border-[#E0D5CC] rounded-lg outline-none focus:border-[#2C1A14] bg-white" />
                            <textarea v-model="feedbackForm.message"
                                :placeholder="$t('header.feedback.messagePlaceholder')" rows="3"
                                class="w-full px-3 py-2 text-sm border border-[#E0D5CC] rounded-lg outline-none focus:border-[#2C1A14] bg-white resize-none" />
                            <div class="flex gap-2 justify-end">
                                <button @click="showFeedbackForm = false"
                                    class="text-xs px-3 py-1.5 border border-[#E0D5CC] text-[#7A6558] rounded-lg hover:bg-[#F5EFEA] transition-colors">
                                    {{ $t('header.feedback.cancel') }}
                                </button>
                                <button @click="handleSubmitFeedback" :disabled="submittingFeedback"
                                    class="text-xs px-3 py-1.5 bg-[#2C1A14] text-[#C8A96A] rounded-lg hover:bg-[#3d2416] transition-colors disabled:opacity-50">
                                    {{ submittingFeedback ? $t('header.feedback.sending') : $t('header.feedback.submit')
                                    }}
                                </button>
                            </div>
                        </div>

                        <!-- Loading -->
                        <div v-if="loadingFeedback" class="divide-y divide-[#E0D5CC]">
                            <div v-for="i in 3" :key="i" class="flex items-center gap-3 px-5 py-4 animate-pulse">
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 bg-[#F5EFEA] rounded w-2/3"></div>
                                    <div class="h-3 bg-[#F5EFEA] rounded w-1/3"></div>
                                </div>
                                <div class="h-5 bg-[#F5EFEA] rounded w-16"></div>
                            </div>
                        </div>

                        <!-- Empty -->
                        <div v-else-if="!feedbacks.length && !showFeedbackForm" class="py-16 text-center">
                            <p class="text-[#7A6558] text-sm">{{ $t('profile.noFeedback') }}</p>
                            <button @click="showFeedbackForm = true"
                                class="mt-3 text-[#C8A96A] text-sm underline underline-offset-2">
                                {{ $t('profile.sendFirstFeedback') }}
                            </button>
                        </div>

                        <!-- List -->
                        <div v-else-if="feedbacks.length" class="divide-y divide-[#E0D5CC]">
                            <div v-for="fb in feedbacks" :key="fb.id" class="px-5 py-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span :class="{
                                                'bg-red-50 text-red-600': fb.type === 'complaint',
                                                'bg-blue-50 text-blue-600': fb.type === 'request',
                                                'bg-amber-50 text-amber-700': fb.type === 'question',
                                            }" class="text-[10px] font-semibold px-2 py-0.5 rounded-full capitalize">
                                                {{ $t(`header.feedback.type${fb.type.charAt(0).toUpperCase() + fb.type.slice(1)}`) }}
                                            </span>
                                            <span class="text-[#b0967a] text-[10px]">{{ fb.created_at }}</span>
                                        </div>
                                        <p class="text-[#2C1A14] font-medium text-sm truncate">{{ fb.subject }}</p>
                                        <p class="text-[#7A6558] text-xs mt-0.5 line-clamp-2">{{ fb.message }}</p>
                                    </div>
                                    <span :class="fb.is_done
                                        ? 'bg-green-50 text-green-700'
                                        : 'bg-amber-50 text-amber-700'"
                                        class="text-[10px] font-semibold px-2 py-1 rounded-full flex-shrink-0 whitespace-nowrap">
                                        {{ fb.is_done ? $t('profile.resolved') : $t('admin.statuses.pending') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-16 text-center text-[#7A6558] text-sm">{{ $t('profile.comingSoon') }}</div>
                </div>
            </div>
        </div>
    </div>

    <ClientOrderModal v-model="modalOpen" :order="modalOrder" :loading="modalLoading"
        @cancel="(id) => { handleCancel(id); modalOpen = false }" />
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'client' })

const { user: authUser, logout, fetchMe } = useAuth()
const { fetchMyOrders, cancelOrder, fetchOrder, fetchMyFavorites, fetchMyFeedback, submitFeedback } = useProfile()

// Modal state
const modalOpen = ref(false)
const modalOrder = ref<any>(null)
const modalLoading = ref(false)

const orders = ref<any>(null)
const loadingOrders = ref(true)
const currentPage = ref(1)
const cancellingId = ref<number | null>(null)
const activeTab = ref('orders')
const favorites = ref<any[]>([])
const loadingFavorites = ref(false)

const { t, locale } = useI18n()

function getProductName(item: any): string {
    const tr = item.product_translations
    if (!tr) return item.product_name ?? ''
    const entry = Array.isArray(tr) ? tr.find((x: any) => x.locale === locale.value) : tr[locale.value]
    return entry?.name ?? item.product_name ?? ''
}

const tabs = computed(() => [
    { key: 'orders', label: t('profile.tabOrders') },
    { key: 'favorites', label: t('profile.tabFavorites') },
    { key: 'feedback', label: t('profile.tabFeedback') },
])

async function openDetail(orderId: number) {
    modalOpen.value = true
    modalLoading.value = true
    modalOrder.value = null
    try {
        modalOrder.value = await fetchOrder(orderId)
    } finally {
        modalLoading.value = false
    }
}
async function loadFavorites() {
    if (favorites.value.length) return
    loadingFavorites.value = true
    try {
        favorites.value = await fetchMyFavorites()
    } catch {
        favorites.value = []
    } finally {
        loadingFavorites.value = false
    }
}

// authUser artık AuthUser | null — as any gerek yok
const firstName = computed(() => authUser.value?.name?.split(' ')[0] ?? '')

const initials = computed(() =>
    (authUser.value?.name ?? '')
        .split(' ')
        .map((w: string) => w[0])
        .join('')
        .slice(0, 2)
        .toUpperCase()
)

const totalSpent = computed(() => {
    if (!orders.value?.data?.length) return '0'
    return orders.value.data
        .reduce((sum: number, o: any) => sum + o.total_price, 0)
        .toFixed(0)
})

async function loadOrders(page = 1) {
    loadingOrders.value = true
    currentPage.value = page
    try {
        orders.value = await fetchMyOrders(page, 8)
    } catch {
        orders.value = null
    } finally {
        loadingOrders.value = false
    }
}

async function handleCancel(id: number) {
    if (!confirm(t('profile.cancelConfirm'))) return
    cancellingId.value = id
    try {
        await cancelOrder(id)
        await loadOrders(currentPage.value)
    } finally {
        cancellingId.value = null
    }
}

async function handleLogout() {
    await logout()
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
    const map: Record<string, string> = {
        pending: t('admin.statuses.pending'),
        preparing: t('admin.statuses.preparing'),
        ready: t('admin.statuses.ready'),
        delivered: t('admin.statuses.delivered'),
        cancelled: t('admin.statuses.cancelled'),
    }
    return map[status] ?? status
}

const feedbacks = ref<any[]>([])
const loadingFeedback = ref(false)
const showFeedbackForm = ref(false)
const feedbackForm = ref({ type: 'complaint', subject: '', message: '' })
const feedbackTypes = computed(() => [
    { key: 'complaint', label: t('header.feedback.typeComplaint') },
    { key: 'request', label: t('header.feedback.typeRequest') },
    { key: 'question', label: t('header.feedback.typeQuestion') },
])
const submittingFeedback = ref(false)

async function loadFeedback() {
    if (feedbacks.value.length) return
    loadingFeedback.value = true
    try {
        feedbacks.value = await fetchMyFeedback()
    } catch {
        feedbacks.value = []
    } finally {
        loadingFeedback.value = false
    }
}

async function handleSubmitFeedback() {
    if (!feedbackForm.value.subject.trim() || !feedbackForm.value.message.trim()) return
    submittingFeedback.value = true
    try {
        const res: any = await submitFeedback(feedbackForm.value)
        feedbacks.value.unshift(res.feedback)
        feedbackForm.value = { type: 'complaint', subject: '', message: '' }
        showFeedbackForm.value = false
    } catch {
    } finally {
        submittingFeedback.value = false
    }
}


const config = useRuntimeConfig()
const BACKEND_BASE = (config.public.apiBase as string).replace(/\/api\/?$/, '')
function resolveImageUrl(url: string | null) {
    if (!url) return null
    if (url.startsWith('http')) return url
    return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
}

onMounted(async () => {
    await fetchMe()
    loadOrders()
})

watch(activeTab, (tab) => {
    if (tab === 'favorites') loadFavorites()
    if (tab === 'feedback') loadFeedback()
})
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