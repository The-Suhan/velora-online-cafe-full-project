<template>
    <div class="category-detail-page">

        <!-- Header skeleton -->
        <div v-if="loading" class="page-header">
            <div class="header-inner">
                <div class="skeleton-breadcrumb"></div>
                <div class="skeleton-title"></div>
            </div>
        </div>

        <!-- Header -->
        <div v-else-if="category" class="page-header" :style="headerStyle">
            <div class="header-bg-overlay"></div>
            <div class="header-inner">
                <nav class="breadcrumb">
                    <NuxtLink to="/categories" class="breadcrumb-link">
                        <svg viewBox="0 0 20 20" fill="none" class="back-icon">
                            <path d="M13 16l-5-6 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        Tüm Kategoriler
                    </NuxtLink>
                </nav>
                <h1 class="page-title">{{ category.name }}</h1>
                <p v-if="category.description" class="page-desc">{{ category.description }}</p>
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrap">

            <!-- Subcategories -->
            <template v-if="!loading && subcategories.length > 0">
                <div class="sub-grid">
                    <NuxtLink v-for="(sub, index) in subcategories" :key="sub.id" :to="`/categories/${sub.id}/products`"
                        class="sub-card" :style="{ '--delay': `${index * 55}ms` }">
                        <div class="sub-image-wrap">
                            <img v-if="sub.image_url" :src="resolveUrl(sub.image_url)" :alt="sub.name" class="sub-image"
                                loading="lazy" />
                            <div v-else class="sub-image-placeholder">
                                <svg viewBox="0 0 40 40" fill="none" class="placeholder-icon">
                                    <path d="M8 28 Q20 12 32 28" stroke="#C8A96A" stroke-width="2"
                                        stroke-linecap="round" fill="none" />
                                    <circle cx="20" cy="18" r="4" fill="#C8A96A" opacity="0.5" />
                                </svg>
                            </div>
                        </div>
                        <div class="sub-body">
                            <span class="sub-name">{{ sub.name }}</span>
                            <svg class="sub-arrow" viewBox="0 0 20 20" fill="none">
                                <path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </NuxtLink>
                </div>
            </template>

            <!-- No subcategories -->
            <template v-else-if="!loading && subcategories.length === 0 && category">
                <div class="no-sub-state">
                    <svg class="empty-icon" viewBox="0 0 80 80" fill="none">
                        <circle cx="40" cy="40" r="36" stroke="#C8A96A" stroke-width="2.5" stroke-dasharray="6 4" />
                        <path d="M28 30 Q40 18 52 30" stroke="#C8A96A" stroke-width="2.5" stroke-linecap="round"
                            fill="none" />
                        <circle cx="33" cy="38" r="3" fill="#C8A96A" />
                        <circle cx="47" cy="38" r="3" fill="#C8A96A" />
                        <path d="M33 54 Q40 48 47 54" stroke="#C8A96A" stroke-width="2.5" stroke-linecap="round"
                            fill="none" />
                    </svg>
                    <p class="empty-title">Alt kategori bulunamadı</p>
                    <NuxtLink :to="`/categories/${categoryId}/products`" class="view-products-btn">
                        Ürünleri Gör
                        <svg viewBox="0 0 20 20" fill="none" style="width:14px;height:14px;display:inline">
                            <path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </NuxtLink>
                </div>
            </template>

            <!-- Loading skeleton grid -->
            <template v-else-if="loading">
                <div class="section-label skeleton-line short"></div>
                <div class="sub-grid">
                    <div v-for="n in 6" :key="n" class="sub-card skeleton-card">
                        <div class="skeleton-sub-img"></div>
                        <div class="skeleton-sub-text"></div>
                    </div>
                </div>
            </template>

        </div>
    </div>
</template>

<script setup>
definePageMeta({ layout: 'client', middleware: 'auth' })

const route = useRoute()
const config = useRuntimeConfig()
const categoryId = route.params.id

const BACKEND_BASE = config.public.apiBase.replace(/\/api\/?$/, '')

const resolveUrl = (url) => {
    if (!url) return null
    if (url.startsWith('http')) return url
    return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
}

const api = useApi()
const loading = ref(true)
const allCategories = ref([])

async function load() {
    loading.value = true
    try {
        allCategories.value = await api('/categories') ?? []
    } catch (e) {
        console.error('[category detail] load error:', e)
    } finally {
        loading.value = false
    }
}

const category = computed(() => {
    if (!allCategories.value?.length) return null
    const top = allCategories.value.find(c => String(c.id) === String(categoryId))
    if (top) return top
    for (const parent of allCategories.value) {
        const child = parent.children?.find(c => String(c.id) === String(categoryId))
        if (child) return child
    }
    return null
})

const subcategories = computed(() => category.value?.children ?? [])

const headerStyle = computed(() => {
    if (category.value?.image_url) {
        return { '--header-img': `url(${resolveUrl(category.value.image_url)})` }
    }
    return {}
})

useHead({ title: computed(() => category.value?.name ?? 'Kategori') })

onMounted(load)
</script>

<style scoped>
.category-detail-page {
    min-height: 100vh;
    background-color: #F5EFEA;
    padding-bottom: 2rem;
}

/* ── Header ── */
.page-header {
    background: #2C1A14;
    padding: 1.75rem 1.5rem 2rem;
    position: relative;
    overflow: hidden;
    min-height: 140px;
}

.page-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: var(--header-img, none);
    background-size: cover;
    background-position: center;
    opacity: 0.18;
    pointer-events: none;
}

.header-bg-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(44, 26, 20, 0.85) 0%, rgba(44, 26, 20, 0.5) 100%);
    pointer-events: none;
}

.header-inner {
    max-width: 1280px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.breadcrumb {
    margin-bottom: 0.75rem;
}

.breadcrumb-link {
    color: #C8A96A;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: color 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}

.breadcrumb-link:hover {
    color: #F5EFEA;
}

.back-icon {
    width: 16px;
    height: 16px;
}

.page-title {
    font-family: 'Georgia', serif;
    font-size: 1.85rem;
    font-weight: 700;
    color: #F5EFEA;
    margin: 0 0 0.35rem;
    letter-spacing: -0.5px;
}

.page-desc {
    color: rgba(245, 239, 234, 0.7);
    font-size: 0.88rem;
    margin: 0;
    font-style: italic;
    max-width: 480px;
}

/* ── Content ── */
.content-wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
}

.section-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #7a5c4a;
    margin-bottom: 1rem;
}

/* ── Sub Grid ── */
.sub-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.875rem;
}

@media (min-width: 480px) {
    .sub-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 768px) {
    .sub-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (min-width: 1024px) {
    .sub-grid {
        grid-template-columns: repeat(5, 1fr);
        gap: 1.1rem;
    }
}

/* ── Sub Card ── */
.sub-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(44, 26, 20, 0.07);
    border: 1.5px solid rgba(44, 26, 20, 0.06);
    transition: transform 0.22s ease, box-shadow 0.22s ease;
    animation: fadeUp 0.38s ease both;
    animation-delay: var(--delay, 0ms);
}

.sub-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(44, 26, 20, 0.14);
}

.sub-card:hover .sub-arrow {
    transform: translateX(3px);
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(14px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.sub-image-wrap {
    aspect-ratio: 4 / 3;
    position: relative;
    background: #F5EFEA;
    overflow: hidden;
}

.sub-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.sub-card:hover .sub-image {
    transform: scale(1.06);
}

.sub-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #F5EFEA 0%, #ede3d8 100%);
}

.placeholder-icon {
    width: 40px;
    height: 40px;
    opacity: 0.6;
}

.sub-body {
    padding: 0.65rem 0.85rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.4rem;
}

.sub-name {
    font-size: 0.83rem;
    font-weight: 600;
    color: #2C1A14;
    line-height: 1.3;
}

.sub-arrow {
    width: 16px;
    height: 16px;
    color: #C8A96A;
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

/* ── No Sub / Empty ── */
.no-sub-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 4rem 1rem;
    gap: 0.75rem;
    text-align: center;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin-bottom: 0.5rem;
    opacity: 0.85;
}

.empty-title {
    font-family: 'Georgia', serif;
    font-size: 1.1rem;
    color: #2C1A14;
    margin: 0;
    font-weight: 600;
}

.view-products-btn {
    background: #2C1A14;
    color: #F5EFEA;
    text-decoration: none;
    padding: 0.65rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.5rem;
    transition: background 0.2s, color 0.2s;
}

.view-products-btn:hover {
    background: #C8A96A;
    color: #2C1A14;
}

/* ── Skeleton ── */
.skeleton-breadcrumb {
    height: 14px;
    width: 120px;
    background: rgba(200, 169, 106, 0.3);
    border-radius: 4px;
    margin-bottom: 0.75rem;
    animation: pulse 1.4s infinite;
}

.skeleton-title {
    height: 34px;
    width: 200px;
    background: rgba(245, 239, 234, 0.2);
    border-radius: 6px;
    animation: pulse 1.4s infinite;
}

.skeleton-line {
    height: 12px;
    width: 100px;
    background: #e8ddd6;
    border-radius: 4px;
    animation: pulse 1.4s infinite;
}

.skeleton-card {
    pointer-events: none;
    animation: none;
}

.skeleton-sub-img {
    aspect-ratio: 1;
    background: linear-gradient(90deg, #e8ddd6 25%, #f0e8e0 50%, #e8ddd6 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
}

.skeleton-sub-text {
    height: 2.4rem;
    margin: 0.65rem 0.85rem;
    background: linear-gradient(90deg, #e8ddd6 25%, #f0e8e0 50%, #e8ddd6 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 5px;
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}
</style>