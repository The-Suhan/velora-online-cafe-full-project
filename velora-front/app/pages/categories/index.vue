<template>
  <div class="categories-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-inner">
        <h1 class="page-title">Kategoriler</h1>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid-container">
      <div v-for="n in 8" :key="n" class="category-card skeleton">
        <div class="skeleton-img"></div>
        <div class="skeleton-text"></div>
      </div>
    </div>

    <!-- Error / Empty State -->
    <div v-else-if="!categories || categories.length === 0" class="empty-state">
      <svg class="empty-icon" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="40" cy="40" r="36" stroke="#C8A96A" stroke-width="2.5" stroke-dasharray="6 4"/>
        <path d="M28 30 Q40 18 52 30" stroke="#C8A96A" stroke-width="2.5" stroke-linecap="round" fill="none"/>
        <circle cx="33" cy="38" r="3" fill="#C8A96A"/>
        <circle cx="47" cy="38" r="3" fill="#C8A96A"/>
        <path d="M33 54 Q40 48 47 54" stroke="#C8A96A" stroke-width="2.5" stroke-linecap="round" fill="none"/>
      </svg>
      <p class="empty-title">Kategori bulunamadı</p>
      <p class="empty-sub">Henüz aktif kategori yok.</p>
      <button class="retry-btn" @click="load">Tekrar Dene</button>
    </div>

    <!-- Categories Grid -->
    <div v-else class="grid-container">
      <NuxtLink
        v-for="(category, index) in categories"
        :key="category.id"
        :to="`/categories/${category.id}`"
        class="category-card"
        :style="{ '--delay': `${index * 60}ms` }"
      >
        <div class="card-image-wrap">
          <img
            v-if="category.image_url"
            :src="resolveUrl(category.image_url)"
            :alt="category.name"
            class="card-image"
            loading="lazy"
          />
          <div v-else class="card-image-placeholder">
            <svg viewBox="0 0 40 40" fill="none" class="placeholder-icon">
              <path d="M8 28 Q20 12 32 28" stroke="#C8A96A" stroke-width="2" stroke-linecap="round" fill="none"/>
              <circle cx="20" cy="18" r="4" fill="#C8A96A" opacity="0.5"/>
            </svg>
          </div>
          <div class="card-overlay"></div>
        </div>
        <div class="card-body">
          <h3 class="card-title">{{ category.name }}</h3>
          <svg class="card-arrow" viewBox="0 0 20 20" fill="none">
            <path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
definePageMeta({ layout: 'client', middleware: 'auth' })

const config = useRuntimeConfig()
const BACKEND_BASE = config.public.apiBase.replace(/\/api\/?$/, '')

const resolveUrl = (url) => {
  if (!url) return null
  if (url.startsWith('http')) return url
  return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
}

const api = useApi()
const loading = ref(true)
const categories = ref([])

async function load() {
  loading.value = true
  try {
    const data = await api('/categories')
    categories.value = data ?? []
  } catch (e) {
    console.error('[categories] load error:', e)
    categories.value = []
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.categories-page {
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
}

.page-header::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 80% 50%, rgba(200,169,106,0.18) 0%, transparent 70%);
  pointer-events: none;
}

.header-inner {
  max-width: 1280px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.page-title {
  font-family: 'Georgia', serif;
  font-size: 1.85rem;
  font-weight: 700;
  color: #F5EFEA;
  margin: 0;
  letter-spacing: -0.5px;
}

/* ── Grid ── */
.grid-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

@media (min-width: 640px) {
  .grid-container { grid-template-columns: repeat(3, 1fr); }
}
@media (min-width: 1024px) {
  .grid-container { grid-template-columns: repeat(4, 1fr); gap: 1.25rem; }
}

/* ── Card ── */
.category-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 8px rgba(44,26,20,0.07);
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  animation: fadeUp 0.4s ease both;
  animation-delay: var(--delay, 0ms);
  border: 1.5px solid rgba(44,26,20,0.06);
}

.category-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(44,26,20,0.14);
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.card-image-wrap {
  position: relative;
  aspect-ratio: 4 / 3;
  background: #F5EFEA;
  overflow: hidden;
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.35s ease;
}

.category-card:hover .card-image {
  transform: scale(1.06);
}

.card-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #F5EFEA 0%, #ede3d8 100%);
}

.placeholder-icon {
  width: 48px;
  height: 48px;
  opacity: 0.6;
}

.card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(44,26,20,0.08) 0%, transparent 60%);
  pointer-events: none;
}

.badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(200,169,106,0.92);
  color: #2C1A14;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 20px;
  backdrop-filter: blur(4px);
  letter-spacing: 0.3px;
}

.card-body {
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  flex: 1;
}

.card-title {
  font-size: 0.88rem;
  font-weight: 600;
  color: #2C1A14;
  margin: 0;
  line-height: 1.3;
}

.card-arrow {
  width: 18px;
  height: 18px;
  color: #C8A96A;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}

.category-card:hover .card-arrow {
  transform: translateX(3px);
}

/* ── Skeleton ── */
.skeleton { pointer-events: none; animation: none; }

.skeleton-img {
  aspect-ratio: 4 / 3;
  background: linear-gradient(90deg, #e8ddd6 25%, #f0e8e0 50%, #e8ddd6 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

.skeleton-text {
  height: 2.5rem;
  margin: 0.75rem 1rem;
  background: linear-gradient(90deg, #e8ddd6 25%, #f0e8e0 50%, #e8ddd6 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 6px;
}

@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Empty State ── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 5rem 2rem;
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
  font-size: 1.2rem;
  color: #2C1A14;
  margin: 0;
  font-weight: 600;
}

.empty-sub {
  color: #9a7a68;
  font-size: 0.88rem;
  margin: 0;
}

.retry-btn {
  background: #2C1A14;
  color: #F5EFEA;
  border: none;
  padding: 0.6rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.88rem;
  font-weight: 600;
  margin-top: 0.5rem;
  transition: background 0.2s;
}

.retry-btn:hover {
  background: #C8A96A;
  color: #2C1A14;
}
</style>