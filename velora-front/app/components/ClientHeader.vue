<template>
    <header class="velora-header">
        <div class="header-inner">

            <!-- Logo -->
            <NuxtLink to="/" class="header-logo">
                <img src="/logo-black.png" alt="Velaro Café" class="logo-img" />
                <div class="logo-text">
                    <span class="logo-name">Velora</span>
                    <span class="logo-sub">Taste the calm</span>
                </div>
            </NuxtLink>

            <!-- Desktop Nav (hidden on mobile/tablet) -->
            <nav class="header-nav desktop-only">
                <NuxtLink to="/" class="nav-link" exact-active-class="nav-link--active">{{ $t('nav.home') }}</NuxtLink>
                <NuxtLink to="/categories" class="nav-link" active-class="nav-link--active">{{ $t('nav.categories') }}
                </NuxtLink>
                <NuxtLink to="/profile" class="nav-link" active-class="nav-link--active">{{ $t('nav.profile') }}
                </NuxtLink>
            </nav>

            <!-- Right Actions -->
            <div class="header-actions">

                <!-- Search (always visible) -->
                <button class="icon-btn" aria-label="Search" @click="searchOpen = !searchOpen"
                    v-if="route.path === '/'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                </button>

                <!-- Language Switcher -->
                <LanguageSwitcher class="header-lang-switcher" />

                <!-- Feedback button -->
                <button class="icon-btn feedback-btn" aria-label="Feedback" @click="feedbackOpen = true"
                    v-if="isLoggedIn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        <line x1="9" y1="10" x2="15" y2="10" />
                        <line x1="9" y1="14" x2="13" y2="14" />
                    </svg>
                </button>

                <!-- Bag button — desktop: standalone dark pill | mobile: icon only -->
                <NuxtLink to="/bag" class="bag-btn" aria-label="Bag">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <path d="M16 10a4 4 0 0 1-8 0" />
                    </svg>
                    <span v-if="bagCount > 0" class="bag-badge">{{ bagCount }}</span>
                </NuxtLink>
            </div>
        </div>

        <!-- Search Bar Dropdown -->
        <Transition name="search-drop">
            <div v-if="searchOpen && route.path === '/'" class="search-bar-wrap">
                <div class="search-bar-inner">
                    <svg class="search-bar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input ref="searchInput" v-model="searchQuery" class="search-bar-input" type="text"
                        :placeholder="$t('header.searchPlaceholder')" @keydown.escape="searchOpen = false"
                        @keydown.enter="handleSearch" />
                    <button class="search-close-btn" @click="searchOpen = false" aria-label="Close search">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Feedback Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="feedbackOpen" class="fixed inset-0 bg-[rgba(44,24,16,0.45)] backdrop-blur-sm z-[200]"
                    @click.self="feedbackOpen = false" />
            </Transition>

            <Transition name="modal-pop">
                <div v-if="feedbackOpen" class="fixed z-[201] bg-white rounded-2xl shadow-2xl w-[92%] max-w-[440px]"
                    style="top: 80px; left: 50%; transform: translateX(-50%);">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-[#F3F4F6]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-[#F5EFEA] flex items-center justify-center">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C8A96A"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[#2C1A14] font-semibold text-sm">{{ $t('header.feedback.sendTitle') }}
                                </p>
                                <p class="text-[#8a7060] text-[11px]">{{ $t('header.feedback.sendSubtitle') }}</p>
                            </div>
                        </div>
                        <button @click="feedbackOpen = false"
                            class="w-7 h-7 bg-[#F3F4F6] rounded-lg flex items-center justify-center text-[#6b7280] hover:bg-[#E5E7EB] transition-colors">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13">
                                <path
                                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Success state -->
                    <div v-if="submitted" class="px-5 py-10 text-center">
                        <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" width="26" height="26">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </div>
                        <p class="text-[#2C1A14] font-semibold">{{ $t('header.feedback.successTitle') }}</p>
                        <p class="text-[#8a7060] text-sm mt-1">{{ $t('header.feedback.successDesc') }}</p>
                    </div>

                    <!-- Form -->
                    <div v-else class="px-5 py-4 space-y-3">
                        <!-- Type selector -->
                        <div class="flex gap-2">
                            <button v-for="t in [
                                { key: 'complaint', label: $t('header.feedback.typeComplaint') },
                                { key: 'request', label: $t('header.feedback.typeRequest') },
                                { key: 'question', label: $t('header.feedback.typeQuestion') }
                            ]" :key="t.key" @click="feedbackForm.type = t.key"
                                :class="feedbackForm.type === t.key
                                    ? 'bg-[#2C1A14] text-[#C8A96A] border-[#2C1A14]'
                                    : 'bg-white text-[#7A6558] border-[#E0D5CC] hover:border-[#2C1A14] hover:text-[#2C1A14]'"
                                class="flex-1 py-1.5 text-xs font-medium border rounded-lg transition-colors">
                                {{ t.label }}
                            </button>
                        </div>

                        <!-- Subject -->
                        <input v-model="feedbackForm.subject" :placeholder="$t('header.feedback.subjectPlaceholder')"
                            class="w-full px-3 py-2.5 text-sm border border-[#E0D5CC] rounded-xl outline-none focus:border-[#2C1A14] transition-colors" />

                        <!-- Message -->
                        <textarea v-model="feedbackForm.message" :placeholder="$t('header.feedback.messagePlaceholder')"
                            rows="4"
                            class="w-full px-3 py-2.5 text-sm border border-[#E0D5CC] rounded-xl outline-none focus:border-[#2C1A14] transition-colors resize-none" />

                        <!-- Footer -->
                        <div class="flex gap-2 pt-1">
                            <button @click="feedbackOpen = false"
                                class="flex-1 py-2.5 border border-[#E0D5CC] text-[#7A6558] text-sm rounded-xl hover:bg-[#F5EFEA] transition-colors">
                                {{ $t('header.feedback.cancel') }}
                            </button>
                            <button @click="handleFeedbackSubmit"
                                :disabled="submitting || !feedbackForm.subject.trim() || !feedbackForm.message.trim()"
                                class="flex-1 py-2.5 bg-[#2C1A14] text-[#C8A96A] text-sm rounded-xl hover:bg-[#3d2416] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ submitting ? $t('header.feedback.sending') : $t('header.feedback.submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </header>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useSearch } from '~/composables/useSearch'

defineProps({
    bagCount: { type: Number, default: 0 }
})
defineEmits(['toggle-menu'])

const route = useRoute()
const { searchQuery, searchOpen } = useSearch()
const searchInput = ref(null)

watch(searchOpen, async (val) => {
    if (val) {
        await nextTick()
        searchInput.value?.focus()
    } else {
        searchQuery.value = ''
    }
})

function handleSearch() {
    if (!searchQuery.value.trim()) return
    console.log('search:', searchQuery.value)
}

const { isLoggedIn } = useAuth()
const feedbackOpen = ref(false)
const feedbackForm = ref({ type: 'complaint', subject: '', message: '' })
const submitting = ref(false)
const submitted = ref(false)

const { submitFeedback } = useProfile()

async function handleFeedbackSubmit() {
    if (!feedbackForm.value.subject.trim() || !feedbackForm.value.message.trim()) return
    submitting.value = true
    try {
        await submitFeedback(feedbackForm.value)
        submitted.value = true
        feedbackForm.value = { type: 'complaint', subject: '', message: '' }
        setTimeout(() => {
            submitted.value = false
            feedbackOpen.value = false
        }, 2000)
    } catch {
    } finally {
        submitting.value = false
    }
}

watch(feedbackOpen, (val) => {
    if (!val) {
        submitted.value = false
        feedbackForm.value = { type: 'complaint', subject: '', message: '' }
    }
})
</script>

<style scoped>
.feedback-btn {
    color: #5a3e2b;
    margin-right: 0.1rem;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity .2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.modal-pop-enter-active {
    transition: all .25s cubic-bezier(.34, 1.56, .64, 1);
}

.modal-pop-leave-active {
    transition: all .15s ease;
}

.modal-pop-enter-from {
    opacity: 0;
    transform: translateX(-50%) scale(.95) translateY(-8px);
}

.modal-pop-leave-to {
    opacity: 0;
    transform: translateX(-50%) scale(.95) translateY(-8px);
}

.velora-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #f5f0e8;
    border-bottom: 1px solid rgba(26, 10, 0, 0.08);
}

.header-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    height: 72px;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

/* ── Logo ── */
.header-logo {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    text-decoration: none;
    flex-shrink: 0;
}

.logo-img {
    width: 52px;
    height: 52px;
    border-radius: 50%;
}

.logo-text {
    display: flex;
    flex-direction: column;
    line-height: 1;
}

.logo-name {
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a0a00;
    letter-spacing: 0.01em;
}

.logo-sub {
    font-family: 'Georgia', serif;
    font-size: 0.52rem;
    color: #8a6a50;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-top: 3px;
}

/* ── Desktop Nav ── */
.desktop-only {
    display: flex;
}

.header-nav {
    align-items: center;
    gap: 0.1rem;
    margin-left: auto;
}

.nav-link {
    font-family: 'Georgia', serif;
    font-size: 0.78rem;
    font-weight: 500;
    color: #5a3e2b;
    text-decoration: none;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.45rem 0.95rem;
    border-radius: 4px;
    transition: color 0.2s, background 0.2s;
}

.nav-link:hover {
    color: #1a0a00;
    background: rgba(26, 10, 0, 0.05);
}

.nav-link--active {
    color: #1a0a00;
    font-weight: 700;
}

/* ── Actions ── */
.header-actions {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-left: auto;
}

/* when nav is present push actions flush */
.header-nav.desktop-only+.header-actions {
    margin-left: -1rem;
}

/* Search icon btn */
.icon-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    color: #1a0a00;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s;
}

.icon-btn:hover {
    background: rgba(26, 10, 0, 0.07);
}

/* ── Bag button ── */
.bag-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #f5f0e8;
    background: #1a0a00;
    border-radius: 8px;
    transition: background 0.2s;

    /* desktop size */
    width: 46px;
    height: 38px;
}

.bag-btn:hover {
    background: #2d1200;
}

.bag-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 18px;
    height: 18px;
    background: #c8a97e;
    color: #1a0a00;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: sans-serif;
}

/* ── Burger ── */
.burger-btn {
    display: none;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border: 1.5px solid rgba(26, 10, 0, 0.2);
    background: transparent;
    color: #1a0a00;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
}

.burger-btn:hover {
    background: rgba(26, 10, 0, 0.05);
    border-color: rgba(26, 10, 0, 0.35);
}

/* ── Search Bar ── */
.search-bar-wrap {
    border-top: 1px solid rgba(26, 10, 0, 0.08);
    background: #f5f0e8;
}

.search-bar-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0.75rem 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.search-bar-icon {
    color: #8a6a50;
    flex-shrink: 0;
}

.search-bar-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    font-family: 'Georgia', serif;
    font-size: 1rem;
    color: #1a0a00;
    letter-spacing: 0.02em;
}

.search-bar-input::placeholder {
    color: #b0957a;
}

.search-close-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #8a6a50;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.search-close-btn:hover {
    background: rgba(26, 10, 0, 0.07);
    color: #1a0a00;
}

/* ── Transitions ── */
.search-drop-enter-active,
.search-drop-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.search-drop-enter-from,
.search-drop-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* ── Responsive ── */
@media (max-width: 860px) {
    .desktop-only {
        display: none !important;
    }

    .burger-btn {
        display: flex;
    }

    .header-inner {
        padding: 0 1.25rem;
        gap: 0;
        justify-content: space-between;
    }

    .header-actions {
        margin-left: 0;
        gap: 0.15rem;
    }

    .bag-btn {
        width: 40px;
        height: 40px;
        background: transparent;
        color: #1a0a00;
        border-radius: 50%;
        border: none;
    }

    .bag-btn:hover {
        background: rgba(26, 10, 0, 0.07);
    }

    .search-bar-inner {
        padding: 0.75rem 1.25rem;
    }
}

@media (max-width: 480px) {
    .logo-sub {
        display: none;
    }
}
</style>