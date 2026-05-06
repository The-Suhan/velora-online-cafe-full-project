<template>
    <div class="categories-page">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $t('admin.categories.title') }}</h1>
                <p class="page-sub">{{ $t('admin.categories.subtitle') }}</p>
            </div>
            <div class="header-actions">
                <div class="search-wrapper">
                    <svg class="search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="9" cy="9" r="6" />
                        <path d="M15 15l3 3" stroke-linecap="round" />
                    </svg>
                    <input v-model="search" type="text" :placeholder="$t('admin.categories.searchPlaceholder')"
                        class="search-input" @input="onSearch" />
                </div>
                <button class="btn-add" @click="openAddModal">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path
                            d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z" />
                    </svg>
                    {{ $t('admin.categories.addCategory') }}
                </button>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24"
                        height="24">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                    </svg>
                </div>
                <div class="stat-info">
                    <p class="stat-label">{{ $t('admin.categories.totalCategories') }}</p>
                    <p class="stat-value">{{ stats.total }}</p>
                    <p class="stat-growth" :class="stats.growth >= 0 ? 'positive' : 'negative'">
                        {{ stats.growth >= 0 ? '↑' : '↓' }} {{ Math.abs(stats.growth) }}% {{
                            $t('admin.dashboard.thisWeek') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-card">
            <div v-if="loading" class="loading-state">
                <div v-for="i in 6" :key="i" class="skeleton-row">
                    <div class="skeleton-img"></div>
                    <div class="skeleton-lines">
                        <div class="skeleton-line w-32"></div>
                        <div class="skeleton-line w-20 short"></div>
                    </div>
                    <div class="skeleton-line w-40 ml-auto"></div>
                    <div class="skeleton-line w-20 ml-auto"></div>
                </div>
            </div>

            <template v-else>
                <!-- Desktop Table -->
                <table class="cat-table desktop-only">
                    <thead>
                        <tr>
                            <th>{{ $t('admin.categories.categoryHeader') }}</th>
                            <th>{{ $t('admin.common.description') }}</th>
                            <th>{{ $t('admin.categories.parentCategory') }}</th>
                            <th>{{ $t('admin.common.createdAt') }}</th>
                            <th>{{ $t('admin.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cat in categories" :key="cat.id" class="cat-row">
                            <td>
                                <div class="cat-name-cell">
                                    <div class="cat-img-wrap">
                                        <img v-if="cat.image_url" :src="resolveImageUrl(cat.image_url)" :alt="cat.name"
                                            class="cat-img" />
                                        <div v-else class="cat-img-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5" width="20" height="20">
                                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="cat-name">{{ cat.name }}</p>
                                        <p class="cat-products-count">{{ cat.products_count }} {{
                                            $t('admin.dashboard.products') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="cat-desc">{{ cat.description || '—' }}</td>
                            <td>
                                <span v-if="cat.parent_name" class="parent-badge">
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"
                                        width="11" height="11">
                                        <path d="M3 3h4v4H3zM9 9h4v4H9zM5 5l6 6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    {{ cat.parent_name }}
                                </span>
                                <span v-else class="main-badge">
                                    {{ $t('admin.categories.mainCategory') }}
                                </span>
                            </td>
                            <td class="cat-date">{{ cat.created_at }}</td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-edit" :title="$t('admin.common.edit')"
                                        @click="openEditModal(cat)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="15" height="15">
                                            <path d="M13.5 3.5l3 3L7 16H4v-3L13.5 3.5z" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <div class="dropdown-wrap">
                                        <button class="btn-dots" @click.stop="toggleDropdown(cat.id, $event)">
                                            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                                <circle cx="10" cy="4" r="1.5" />
                                                <circle cx="10" cy="10" r="1.5" />
                                                <circle cx="10" cy="16" r="1.5" />
                                            </svg>
                                        </button>
                                        <div v-if="openDropdown === cat.id" class="dropdown-menu"
                                            :style="{ top: dropdownPos.top + 'px', right: dropdownPos.right + 'px' }">
                                            <button class="dropdown-item" @click="openPreviewModal(cat)">
                                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" width="14" height="14">
                                                    <path d="M2 10s3-7 8-7 8 7 8 7-3 7-8 7-8-7-8-7z" />
                                                    <circle cx="10" cy="10" r="2.5" />
                                                </svg>
                                                {{ $t('admin.common.preview') }}
                                            </button>
                                            <button class="dropdown-item danger" @click="openDeleteModal(cat)">
                                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" width="14" height="14">
                                                    <path d="M3 5h14M8 5V3h4v2M6 5l1 12h6l1-12" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                {{ $t('admin.common.delete') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Mobile Cards -->
                <div class="mobile-cards mobile-only">
                    <div v-for="cat in categories" :key="cat.id" class="mobile-card">
                        <div class="mobile-card-left">
                            <div class="cat-img-wrap">
                                <img v-if="cat.image_url" :src="resolveImageUrl(cat.image_url)" :alt="cat.name"
                                    class="cat-img" />
                                <div v-else class="cat-img-placeholder">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        width="20" height="20">
                                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="cat-name">{{ cat.name }}</p>
                                <p class="cat-products-count">{{ cat.products_count }} {{ $t('admin.dashboard.products')
                                }}</p>
                                <div class="mt-1">
                                    <span v-if="cat.parent_name" class="parent-badge small">
                                        {{ cat.parent_name }}
                                    </span>
                                    <span v-else class="main-badge small">
                                        {{ $t('admin.categories.mainCategory') }}
                                    </span>
                                </div>
                                <p class="cat-date mt-1">{{ cat.created_at }}</p>
                            </div>
                        </div>
                        <div class="mobile-card-actions">
                            <button class="btn-edit" @click="openEditModal(cat)">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="15"
                                    height="15">
                                    <path d="M13.5 3.5l3 3L7 16H4v-3L13.5 3.5z" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div class="dropdown-wrap">
                                <button class="btn-dots" @click.stop="toggleDropdown(cat.id, $event)">
                                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                        <circle cx="10" cy="4" r="1.5" />
                                        <circle cx="10" cy="10" r="1.5" />
                                        <circle cx="10" cy="16" r="1.5" />
                                    </svg>
                                </button>
                                <div v-if="openDropdown === cat.id" class="dropdown-menu"
                                    :style="{ top: dropdownPos.top + 'px', right: dropdownPos.right + 'px' }">
                                    <button class="dropdown-item" @click="openPreviewModal(cat)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M2 10s3-7 8-7 8 7 8 7-3 7-8 7-8-7-8-7z" />
                                            <circle cx="10" cy="10" r="2.5" />
                                        </svg>
                                        {{ $t('admin.common.preview') }}
                                    </button>
                                    <button class="dropdown-item danger" @click="openDeleteModal(cat)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M3 5h14M8 5V3h4v2M6 5l1 12h6l1-12" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        {{ $t('admin.common.delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="categories.length === 0" class="empty-state">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"
                        class="empty-icon">
                        <rect x="6" y="6" width="15" height="15" rx="3" />
                        <rect x="27" y="6" width="15" height="15" rx="3" />
                        <rect x="6" y="27" width="15" height="15" rx="3" />
                        <rect x="27" y="27" width="15" height="15" rx="3" />
                    </svg>
                    <p>{{ $t('admin.categories.noCategories') }}</p>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="pagination">
                    <p class="pagination-info">{{ $t('admin.common.showing') }} {{ pagination.from }} {{
                        $t('admin.common.to') }} {{ pagination.to }} {{ $t('admin.common.of') }} {{ pagination.total }}
                        {{ $t('admin.categories.categoriesTotal') }}</p>
                    <div class="pagination-btns">
                        <button class="pg-btn" :disabled="pagination.current_page === 1"
                            @click="goToPage(pagination.current_page - 1)">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
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
                            <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                <path
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else-if="categories.length > 0" class="pagination-info-only">
                    {{ $t('admin.common.showing') }} 1 {{ $t('admin.common.to') }} {{ categories.length }} {{
                        $t('admin.common.of') }}
                    {{ pagination.total }} {{ $t('admin.categories.categoriesTotal') }}
                </div>
            </template>
        </div>

        <!-- ── MODALS ── -->
        <Transition name="fade">
            <div v-if="activeModal" class="modal-overlay" @click.self="closeModal" />
        </Transition>

        <!-- ADD Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'add'" class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">{{ $t('admin.categories.addModalTitle') }}</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Category Type Selector -->
                    <div class="form-group">
                        <label class="form-label">{{ $t('admin.categories.categoryType') }}</label>
                        <div class="type-selector">
                            <button class="type-btn" :class="{ active: form.type === 'main' }"
                                @click="onTypeChange('main')">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="18"
                                    height="18">
                                    <rect x="2" y="2" width="7" height="7" rx="1.5" />
                                    <rect x="11" y="2" width="7" height="7" rx="1.5" />
                                    <rect x="2" y="11" width="7" height="7" rx="1.5" />
                                    <rect x="11" y="11" width="7" height="7" rx="1.5" />
                                </svg>
                                <span>{{ $t('admin.categories.mainCategory') }}</span>
                                <p class="type-desc">{{ $t('admin.categories.mainCategoryDesc') }}</p>
                            </button>
                            <button class="type-btn" :class="{ active: form.type === 'sub' }"
                                @click="onTypeChange('sub')">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="18"
                                    height="18">
                                    <rect x="2" y="2" width="7" height="7" rx="1.5" />
                                    <path d="M5.5 9v5.5H11" stroke-linecap="round" stroke-linejoin="round" />
                                    <rect x="11" y="11" width="7" height="7" rx="1.5" />
                                </svg>
                                <span>{{ $t('admin.categories.subCategory') }}</span>
                                <p class="type-desc">{{ $t('admin.categories.subCategoryDesc') }}</p>
                            </button>
                        </div>
                    </div>

                    <!-- Parent Category Dropdown — ADD modal uses full parentCategories (no self to exclude) -->
                    <Transition name="slide-down">
                        <div v-if="form.type === 'sub'" class="form-group">
                            <label class="form-label">{{ $t('admin.categories.parentCategoryLabel') }} <span
                                    class="required">*</span></label>
                            <div class="select-wrapper">
                                <select v-model="form.parent_id" class="form-select"
                                    :class="{ 'has-error': formErrors.parent_id }">
                                    <option value="" disabled>{{ $t('admin.categories.selectParent') }}</option>
                                    <option v-for="p in parentCategories" :key="p.id" :value="p.id">{{ p.name }}
                                    </option>
                                </select>
                                <svg class="select-arrow" viewBox="0 0 20 20" fill="currentColor" width="14"
                                    height="14">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p v-if="formErrors.parent_id" class="form-error">{{ formErrors.parent_id }}</p>
                            <p v-if="parentCategories.length === 0" class="form-hint">
                                {{ $t('admin.categories.noParentsAvailable') }}
                            </p>
                        </div>
                    </Transition>

                    <div class="form-group">
                        <label class="form-label">{{ $t('admin.categories.nameLabel') }} <span
                                class="required">*</span></label>
                        <input v-model="form.name" type="text" class="form-input"
                            :placeholder="$t('admin.categories.namePlaceholder')" />
                        <p v-if="formErrors.name" class="form-error">{{ formErrors.name }}</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ $t('admin.categories.descriptionLabel') }}</label>
                        <textarea v-model="form.description" class="form-input form-textarea"
                            :placeholder="$t('admin.categories.descriptionPlaceholder')" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ $t('admin.categories.imageLabel') }}</label>
                        <div class="image-upload-zone" :class="{ 'has-file': imagePreview, 'drag-over': isDragging }"
                            @dragover.prevent="isDragging = true" @dragleave="isDragging = false" @drop.prevent="onDrop"
                            @click="triggerFileInput">
                            <img v-if="imagePreview" :src="imagePreview" alt="Preview" class="upload-preview" />
                            <div v-else class="upload-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28"
                                    height="28">
                                    <path d="M4 16l4-4 4 4 4-6 4 6" stroke-linecap="round" stroke-linejoin="round" />
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                </svg>
                                <p>{{ $t('admin.common.dragDrop') }} <span>{{ $t('admin.common.browse') }}</span></p>
                                <p class="upload-hint">{{ $t('admin.common.imageHint') }}</p>
                            </div>
                            <input ref="fileInput" type="file" accept="image/jpg,image/jpeg,image/png,image/webp"
                                class="file-input-hidden" @change="onFileSelect" />
                        </div>
                        <button v-if="imagePreview" class="remove-image-btn" @click.stop="removeImage">{{
                            $t('admin.common.removeImage') }}</button>
                    </div>
                    <div class="form-group toggle-group">
                        <label class="form-label">{{ $t('admin.categories.activeLabel') }}</label>
                        <button class="toggle-btn" :class="{ on: form.is_active }"
                            @click="form.is_active = !form.is_active">
                            <span class="toggle-knob"></span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal">{{ $t('admin.common.cancel') }}</button>
                    <button class="btn-submit" :disabled="submitting" @click="submitAdd">
                        <span v-if="submitting" class="spinner"></span>
                        {{ submitting ? $t('admin.common.saving') : $t('admin.categories.addBtn') }}
                    </button>
                </div>
            </div>
        </Transition>

        <!-- EDIT Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'edit'" class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">{{ $t('admin.categories.editModalTitle') }}</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Category Type Selector -->
                    <div class="form-group">
                        <label class="form-label">{{ $t('admin.categories.categoryType') }}</label>
                        <div class="type-selector">
                            <button class="type-btn" :class="{ active: form.type === 'main' }"
                                @click="onTypeChange('main')">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="18"
                                    height="18">
                                    <rect x="2" y="2" width="7" height="7" rx="1.5" />
                                    <rect x="11" y="2" width="7" height="7" rx="1.5" />
                                    <rect x="2" y="11" width="7" height="7" rx="1.5" />
                                    <rect x="11" y="11" width="7" height="7" rx="1.5" />
                                </svg>
                                <span>{{ $t('admin.categories.mainCategory') }}</span>
                                <p class="type-desc">{{ $t('admin.categories.mainCategoryDesc') }}</p>
                            </button>
                            <button class="type-btn" :class="{ active: form.type === 'sub' }"
                                @click="onTypeChange('sub')">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="18"
                                    height="18">
                                    <rect x="2" y="2" width="7" height="7" rx="1.5" />
                                    <path d="M5.5 9v5.5H11" stroke-linecap="round" stroke-linejoin="round" />
                                    <rect x="11" y="11" width="7" height="7" rx="1.5" />
                                </svg>
                                <span>{{ $t('admin.categories.subCategory') }}</span>
                                <p class="type-desc">{{ $t('admin.categories.subCategoryDesc') }}</p>
                            </button>
                        </div>
                    </div>

                    <!-- Parent Category Dropdown — EDIT modal uses availableParents (self excluded) -->
                    <Transition name="slide-down">
                        <div v-if="form.type === 'sub'" class="form-group">
                            <label class="form-label">{{ $t('admin.categories.parentCategoryLabel') }} <span
                                    class="required">*</span></label>
                            <div class="select-wrapper">
                                <select v-model="form.parent_id" class="form-select"
                                    :class="{ 'has-error': formErrors.parent_id }">
                                    <option value="" disabled>{{ $t('admin.categories.selectParent') }}</option>
                                    <!-- FIX: availableParents filters out the category being edited -->
                                    <option v-for="p in availableParents" :key="p.id" :value="p.id">{{ p.name }}
                                    </option>
                                </select>
                                <svg class="select-arrow" viewBox="0 0 20 20" fill="currentColor" width="14"
                                    height="14">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p v-if="formErrors.parent_id" class="form-error">{{ formErrors.parent_id }}</p>
                            <p v-if="availableParents.length === 0" class="form-hint">
                                {{ $t('admin.categories.noParentsAvailable') }}
                            </p>
                        </div>
                    </Transition>

                    <div class="form-group">
                        <label class="form-label">{{ $t('admin.categories.nameLabel') }} <span
                                class="required">*</span></label>
                        <input v-model="form.name" type="text" class="form-input" />
                        <p v-if="formErrors.name" class="form-error">{{ formErrors.name }}</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ $t('admin.categories.descriptionLabel') }}</label>
                        <textarea v-model="form.description" class="form-input form-textarea" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ $t('admin.categories.imageLabel') }}</label>
                        <div class="image-upload-zone" :class="{ 'has-file': imagePreview, 'drag-over': isDragging }"
                            @dragover.prevent="isDragging = true" @dragleave="isDragging = false" @drop.prevent="onDrop"
                            @click="triggerFileInput">
                            <img v-if="imagePreview" :src="imagePreview" alt="Preview" class="upload-preview" />
                            <div v-else class="upload-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28"
                                    height="28">
                                    <path d="M4 16l4-4 4 4 4-6 4 6" stroke-linecap="round" stroke-linejoin="round" />
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                </svg>
                                <p>{{ $t('admin.common.dragDrop') }} <span>{{ $t('admin.common.browse') }}</span></p>
                                <p class="upload-hint">{{ $t('admin.common.imageHint') }}</p>
                            </div>
                            <input ref="fileInput" type="file" accept="image/jpg,image/jpeg,image/png,image/webp"
                                class="file-input-hidden" @change="onFileSelect" />
                        </div>
                        <button v-if="imagePreview" class="remove-image-btn" @click.stop="removeImage">{{
                            $t('admin.common.removeImage') }}</button>
                    </div>
                    <div class="form-group toggle-group">
                        <label class="form-label">{{ $t('admin.categories.activeLabel') }}</label>
                        <button class="toggle-btn" :class="{ on: form.is_active }"
                            @click="form.is_active = !form.is_active">
                            <span class="toggle-knob"></span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal">{{ $t('admin.common.cancel') }}</button>
                    <button class="btn-submit" :disabled="submitting" @click="submitEdit">
                        <span v-if="submitting" class="spinner"></span>
                        {{ submitting ? $t('admin.common.saving') : $t('admin.categories.saveBtn') }}
                    </button>
                </div>
            </div>
        </Transition>

        <!-- PREVIEW Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'preview' && selectedCategory" class="modal modal-preview">
                <div class="modal-header">
                    <h2 class="modal-title">{{ $t('admin.categories.previewModalTitle') }}</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="preview-image-wrap">
                        <img v-if="selectedCategory.image_url" :src="resolveImageUrl(selectedCategory.image_url)"
                            :alt="selectedCategory.name" class="preview-big-img" />
                        <div v-else class="preview-no-img">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" width="48"
                                height="48">
                                <rect x="6" y="6" width="15" height="15" rx="3" />
                                <rect x="27" y="6" width="15" height="15" rx="3" />
                                <rect x="6" y="27" width="15" height="15" rx="3" />
                                <rect x="27" y="27" width="15" height="15" rx="3" />
                            </svg>
                            <p>{{ $t('admin.common.noImage') }}</p>
                        </div>
                    </div>
                    <div class="preview-info">
                        <div class="preview-row">
                            <span class="preview-key">{{ $t('admin.common.name') }}</span>
                            <span class="preview-val">{{ selectedCategory.name }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">{{ $t('admin.common.description') }}</span>
                            <span class="preview-val">{{ selectedCategory.description || '—' }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">{{ $t('admin.categories.parentCategory') }}</span>
                            <span class="preview-val">
                                <span v-if="selectedCategory.parent_name" class="parent-badge">
                                    {{ selectedCategory.parent_name }}
                                </span>
                                <span v-else class="main-badge">{{ $t('admin.categories.mainCategory') }}</span>
                            </span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">{{ $t('admin.categories.products') }}</span>
                            <span class="preview-val">{{ selectedCategory.products_count }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">{{ $t('admin.categories.created') }}</span>
                            <span class="preview-val">{{ selectedCategory.created_at }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">{{ $t('admin.common.status') }}</span>
                            <span class="preview-val">
                                <span class="status-badge" :class="selectedCategory.is_active ? 'active' : 'inactive'">
                                    {{ selectedCategory.is_active ? $t('admin.common.active') :
                                        $t('admin.common.inactive') }}
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Recent Products -->
                    <div v-if="previewProducts.length" class="preview-products">
                        <p class="preview-products-title">{{ $t('admin.categories.recentProducts') }}</p>
                        <div class="preview-product-list">
                            <div v-for="p in previewProducts" :key="p.id" class="preview-product-item">
                                <img v-if="p.image_url" :src="resolveImageUrl(p.image_url)" :alt="p.name"
                                    class="pp-img" />
                                <div v-else class="pp-img-placeholder"></div>
                                <div class="pp-info">
                                    <p class="pp-name">{{ p.name }}</p>
                                    <p class="pp-price">${{ p.price }}</p>
                                </div>
                                <span class="pp-status" :class="p.is_active ? 'active' : 'inactive'">
                                    {{ p.is_active ? $t('admin.common.active') : $t('admin.common.inactive') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-submit" @click="openEditModal(selectedCategory);">{{
                        $t('admin.categories.editBtn')
                    }}</button>
                </div>
            </div>
        </Transition>

        <!-- DELETE Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'delete' && selectedCategory" class="modal modal-delete">
                <div class="modal-header">
                    <h2 class="modal-title">{{ $t('admin.categories.deleteModalTitle') }}</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="delete-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40"
                            height="40">
                            <path
                                d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <p class="delete-title">{{ $t('admin.common.areYouSure') }}</p>
                    <p class="delete-desc">
                        {{ $t('admin.categories.deleteDesc') }} <strong>{{ selectedCategory.name }}</strong>. {{
                            $t('admin.common.actionUndone') }}
                        <span v-if="selectedCategory.products_count > 0" class="delete-warning">
                            {{ $t('admin.categories.deleteWarning', { n: selectedCategory.products_count }) }}
                        </span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal">{{ $t('admin.common.cancel') }}</button>
                    <button class="btn-danger" :disabled="submitting || selectedCategory.products_count > 0"
                        @click="submitDelete">
                        <span v-if="submitting" class="spinner"></span>
                        {{ submitting ? $t('admin.common.deleting') : $t('admin.common.delete') }}
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Toast -->
        <Transition name="toast">
            <div v-if="toast.visible" class="toast" :class="toast.type">
                <svg v-if="toast.type === 'success'" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
                <svg v-else viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
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
import { ref, reactive, computed, onMounted, nextTick } from 'vue'

definePageMeta({ layout: 'admin' })

const { t } = useI18n()
const config = useRuntimeConfig()
const API = config.public.apiBase
const BACKEND_BASE = API.replace(/\/api\/?$/, '')

function resolveImageUrl(url: string | null | undefined): string | null {
    if (!url) return null
    if (url.startsWith('http://') || url.startsWith('https://')) return url
    return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
}

const { token } = useAuth()
const authHeaders = computed(() => ({ Authorization: `Bearer ${token.value}` }))

// ── State ─────────────────────────────────────────────────────
const categories = ref<any[]>([])
const parentCategories = ref<{ id: number; name: string }[]>([])
const stats = ref({ total: 0, growth: 0 })
const loading = ref(true)
const search = ref('')
const currentPage = ref(1)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

const activeModal = ref<string | null>(null)
const selectedCategory = ref<any>(null)
const previewProducts = ref<any[]>([])
const openDropdown = ref<number | null>(null)
const dropdownPos = ref({ top: 0, right: 0 })
const submitting = ref(false)
const isDragging = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref<string | null>(null)
const imageFile = ref<File | null>(null)

let searchTimeout: ReturnType<typeof setTimeout>

const form = reactive({
    name: '',
    description: '',
    is_active: true,
    type: 'main' as 'main' | 'sub',
    parent_id: '' as number | '',
})
const formErrors = reactive({ name: '', parent_id: '' })

const toast = reactive({ visible: false, message: '', type: 'success' })

// ── Pagination pages ──────────────────────────────────────────
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

// ── FIX: availableParents excludes the category currently being edited ────────
// Used only in the Edit modal so a category cannot be its own parent.
const availableParents = computed(() =>
    parentCategories.value.filter(p => p.id !== selectedCategory.value?.id)
)

// ── API calls ─────────────────────────────────────────────────
async function fetchCategories(page = 1) {
    loading.value = true
    try {
        const params = new URLSearchParams({ page: String(page), per_page: '6' })
        if (search.value) params.append('search', search.value)
        const data = await $fetch<any>(`${API}/admin/categories?${params}`, { headers: authHeaders.value })
        categories.value = data.data
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            from: data.from,
            to: data.to,
        }
    } catch (e) {
        showToast(t('admin.categories.loadFailed'), 'error')
    } finally {
        loading.value = false
    }
}

async function fetchStats() {
    try {
        const data = await $fetch<any>(`${API}/admin/categories/stats`, { headers: authHeaders.value })
        stats.value = data
    } catch { }
}

async function fetchParentCategories() {
    try {
        const data = await $fetch<any>(`${API}/admin/categories/parents`, { headers: authHeaders.value })
        parentCategories.value = data
    } catch { }
}

async function fetchCategoryDetail(id: number) {
    try {
        const data = await $fetch<any>(`${API}/admin/categories/${id}`, { headers: authHeaders.value })
        previewProducts.value = data.products || []
    } catch { }
}

// ── Lifecycle ─────────────────────────────────────────────────
onMounted(() => {
    fetchStats()
    fetchCategories()
    fetchParentCategories()
})

// ── Search ────────────────────────────────────────────────────
function onSearch() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        currentPage.value = 1
        fetchCategories(1)
    }, 400)
}

function goToPage(page: number) {
    currentPage.value = page
    fetchCategories(page)
}

// ── Type change ───────────────────────────────────────────────
function onTypeChange(type: 'main' | 'sub') {
    form.type = type
    if (type === 'main') {
        form.parent_id = ''
        formErrors.parent_id = ''
    }
}

// ── Modals ────────────────────────────────────────────────────
function openAddModal() {
    resetForm()
    activeModal.value = 'add'
}

function openEditModal(cat: any) {
    resetForm()
    selectedCategory.value = cat
    form.name = cat.name
    form.description = cat.description || ''
    form.is_active = cat.is_active
    form.type = cat.parent_id ? 'sub' : 'main'
    form.parent_id = cat.parent_id || ''
    imagePreview.value = resolveImageUrl(cat.image_url)
    activeModal.value = 'edit'
    openDropdown.value = null
}

async function openPreviewModal(cat: any) {
    selectedCategory.value = cat
    previewProducts.value = []
    activeModal.value = 'preview'
    openDropdown.value = null
    await fetchCategoryDetail(cat.id)
}

function openDeleteModal(cat: any) {
    selectedCategory.value = cat
    activeModal.value = 'delete'
    openDropdown.value = null
}

function closeModal() {
    activeModal.value = null
    selectedCategory.value = null
    previewProducts.value = []
    resetForm()
}

function resetForm() {
    form.name = ''
    form.description = ''
    form.is_active = true
    form.type = 'main'
    form.parent_id = ''
    formErrors.name = ''
    formErrors.parent_id = ''
    imagePreview.value = null
    imageFile.value = null
}

// ── Dropdown ──────────────────────────────────────────────────
function toggleDropdown(id: number, event: MouseEvent) {
    if (openDropdown.value === id) {
        openDropdown.value = null
        return
    }
    const btn = event.currentTarget as HTMLElement
    const rect = btn.getBoundingClientRect()
    dropdownPos.value = {
        top: rect.bottom + 6,
        right: window.innerWidth - rect.right,
    }
    openDropdown.value = id
    nextTick(() => {
        const handler = (e: MouseEvent) => {
            openDropdown.value = null
            document.removeEventListener('click', handler)
        }
        document.addEventListener('click', handler)
    })
}

// ── Image upload ──────────────────────────────────────────────
function triggerFileInput() { fileInput.value?.click() }

function onFileSelect(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (file) handleFile(file)
}

function onDrop(e: DragEvent) {
    isDragging.value = false
    const file = e.dataTransfer?.files[0]
    if (file) handleFile(file)
}

function handleFile(file: File) {
    if (file.size > 2 * 1024 * 1024) {
        showToast(t('admin.common.imageTooLarge'), 'error')
        return
    }
    imageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => { imagePreview.value = e.target?.result as string }
    reader.readAsDataURL(file)
}

function removeImage() {
    imagePreview.value = null
    imageFile.value = null
    if (fileInput.value) fileInput.value.value = ''
}

// ── Submit ADD ────────────────────────────────────────────────
async function submitAdd() {
    formErrors.name = ''
    formErrors.parent_id = ''
    if (!form.name.trim()) { formErrors.name = t('admin.common.nameRequired'); return }
    if (form.type === 'sub' && !form.parent_id) { formErrors.parent_id = t('admin.categories.parentRequired'); return }

    submitting.value = true
    try {
        const body = new FormData()
        body.append('name', form.name)
        if (form.description) body.append('description', form.description)
        body.append('is_active', form.is_active ? '1' : '0')
        if (form.type === 'sub' && form.parent_id) body.append('parent_id', String(form.parent_id))
        if (imageFile.value) body.append('image', imageFile.value)

        await $fetch(`${API}/admin/categories`, {
            method: 'POST',
            headers: authHeaders.value,
            body,
        })
        showToast(t('admin.categories.createdSuccess'), 'success')
        closeModal()
        fetchStats()
        fetchParentCategories()
        fetchCategories(currentPage.value)
    } catch (err: any) {
        const msg = err?.data?.errors?.name?.[0] || err?.data?.message || t('admin.categories.createFailed')
        if (err?.data?.errors?.name) formErrors.name = msg
        else showToast(msg, 'error')
    } finally {
        submitting.value = false
    }
}

// ── Submit EDIT ───────────────────────────────────────────────
async function submitEdit() {
    formErrors.name = ''
    formErrors.parent_id = ''
    if (!form.name.trim()) { formErrors.name = t('admin.common.nameRequired'); return }
    if (form.type === 'sub' && !form.parent_id) { formErrors.parent_id = t('admin.categories.parentRequired'); return }

    submitting.value = true
    try {
        const body = new FormData()
        body.append('_method', 'PUT')
        body.append('name', form.name)
        if (form.description) body.append('description', form.description)
        body.append('is_active', form.is_active ? '1' : '0')
        body.append('parent_id', form.type === 'sub' && form.parent_id ? String(form.parent_id) : '')
        if (imageFile.value) body.append('image', imageFile.value)

        await $fetch(`${API}/admin/categories/${selectedCategory.value.id}`, {
            method: 'POST',
            headers: authHeaders.value,
            body,
        })
        showToast(t('admin.categories.updatedSuccess'), 'success')
        closeModal()
        fetchParentCategories()
        fetchCategories(currentPage.value)
    } catch (err: any) {
        const msg = err?.data?.errors?.name?.[0] || err?.data?.message || t('admin.categories.updateFailed')
        if (err?.data?.errors?.name) formErrors.name = msg
        else showToast(msg, 'error')
    } finally {
        submitting.value = false
    }
}

// ── Submit DELETE ─────────────────────────────────────────────
async function submitDelete() {
    submitting.value = true
    try {
        await $fetch(`${API}/admin/categories/${selectedCategory.value.id}`, {
            method: 'DELETE',
            headers: authHeaders.value,
        })
        showToast(t('admin.categories.deletedSuccess'), 'success')
        closeModal()
        fetchStats()
        fetchParentCategories()
        if (categories.value.length === 1 && currentPage.value > 1) currentPage.value--
        fetchCategories(currentPage.value)
    } catch (err: any) {
        showToast(err?.data?.message || t('admin.categories.deleteFailed'), 'error')
    } finally {
        submitting.value = false
    }
}

// ── Toast ─────────────────────────────────────────────────────
function showToast(message: string, type: 'success' | 'error' = 'success') {
    toast.message = message
    toast.type = type
    toast.visible = true
    setTimeout(() => { toast.visible = false }, 3500)
}
</script>

<style scoped>
/* ── Root ──────────────────────────────────────────────────── */
.categories-page {
    padding: 28px 24px;
    max-width: 100%;
    margin: 0 auto;
    min-height: 100%;
}

/* ── Header ─────────────────────────────────────────────────── */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.page-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.2;
}

.page-sub {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 4px;
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
    width: 15px;
    height: 15px;
    color: #9ca3af;
}

.search-input {
    padding: 9px 14px 9px 34px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.85rem;
    width: 220px;
    background: #fff;
    transition: border-color .2s;
    outline: none;
    color: #1a1a1a;
}

.search-input:focus {
    border-color: #3d5a2e;
}

.btn-add {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #3d5a2e;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 9px 16px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s;
    white-space: nowrap;
}

.btn-add:hover {
    background: #2e4422;
}

/* ── Stats ──────────────────────────────────────────────────── */
.stats-section {
    margin-bottom: 20px;
}

.stat-card {
    display: inline-flex;
    align-items: center;
    gap: 16px;
    background: #fff;
    border: 1.5px solid #f0f0f0;
    border-radius: 14px;
    padding: 18px 24px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #3d5a2e;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
}

.stat-label {
    font-size: 0.8rem;
    color: #6b7280;
    font-weight: 500;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 800;
    color: #1a1a1a;
    line-height: 1.1;
}

.stat-growth {
    font-size: 0.78rem;
    margin-top: 2px;
    font-weight: 500;
}

.stat-growth.positive {
    color: #16a34a;
}

.stat-growth.negative {
    color: #dc2626;
}

/* ── Table Card ─────────────────────────────────────────────── */
.table-card {
    background: #fff;
    border: 1.5px solid #f0f0f0;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
    overflow: hidden;
}

.cat-table {
    width: 100%;
    border-collapse: collapse;
}

.cat-table th {
    padding: 13px 20px;
    text-align: left;
    font-size: 0.78rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1.5px solid #f3f4f6;
    background: #fafafa;
}

.cat-row td {
    padding: 14px 20px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.cat-row:last-child td {
    border-bottom: none;
}

.cat-row:hover td {
    background: #fafafa;
}

.cat-name-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cat-img-wrap {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cat-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cat-img-placeholder {
    color: #9ca3af;
}

.cat-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1a1a1a;
}

.cat-products-count {
    font-size: 0.78rem;
    color: #9ca3af;
    margin-top: 1px;
}

.cat-desc {
    font-size: 0.85rem;
    color: #6b7280;
    max-width: 220px;
}

.cat-date {
    font-size: 0.82rem;
    color: #9ca3af;
    white-space: nowrap;
}

/* ── Parent / Main badges ────────────────────────────────────── */
.parent-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #eff6ff;
    color: #2563eb;
    white-space: nowrap;
}

.parent-badge.small {
    font-size: 0.7rem;
    padding: 2px 8px;
}

.main-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f0f4ec;
    color: #3d5a2e;
    white-space: nowrap;
}

.main-badge.small {
    font-size: 0.7rem;
    padding: 2px 8px;
}

.actions-cell {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-edit {
    width: 32px;
    height: 32px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6b7280;
    transition: all .15s;
}

.btn-edit:hover {
    border-color: #3d5a2e;
    color: #3d5a2e;
    background: #f0f4ec;
}

.dropdown-wrap {
    position: relative;
}

.btn-dots {
    width: 32px;
    height: 32px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6b7280;
    transition: all .15s;
}

.btn-dots:hover {
    border-color: #3d5a2e;
    color: #3d5a2e;
}

.dropdown-menu {
    position: fixed;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
    z-index: 9000;
    min-width: 150px;
    overflow: hidden;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 10px 14px;
    font-size: 0.85rem;
    color: #374151;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: background .15s;
}

.dropdown-item:hover {
    background: #f9fafb;
}

.dropdown-item.danger {
    color: #dc2626;
}

.dropdown-item.danger:hover {
    background: #fef2f2;
}

/* ── Mobile Cards ───────────────────────────────────────────── */
.mobile-cards {
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mobile-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border: 1.5px solid #f3f4f6;
    border-radius: 12px;
}

.mobile-card-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}

.mobile-card-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.mt-1 {
    margin-top: 4px;
}

/* ── Type Selector ──────────────────────────────────────────── */
.type-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.type-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 10px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    transition: all .2s;
    text-align: center;
    color: #6b7280;
}

.type-btn:hover {
    border-color: #3d5a2e;
    background: #f8faf6;
    color: #3d5a2e;
}

.type-btn.active {
    border-color: #3d5a2e;
    background: #f0f4ec;
    color: #3d5a2e;
}

.type-btn span {
    font-size: 0.85rem;
    font-weight: 700;
    color: inherit;
}

.type-desc {
    font-size: 0.72rem;
    color: #9ca3af;
    line-height: 1.3;
    margin: 0;
}

.type-btn.active .type-desc {
    color: #6b8f52;
}

/* ── Select ─────────────────────────────────────────────────── */
.select-wrapper {
    position: relative;
}

.form-select {
    width: 100%;
    padding: 9px 36px 9px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.875rem;
    color: #1a1a1a;
    background: #fff;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    transition: border-color .2s;
    box-sizing: border-box;
}

.form-select:focus {
    border-color: #3d5a2e;
}

.form-select.has-error {
    border-color: #dc2626;
}

.select-arrow {
    position: absolute;
    right: 11px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #9ca3af;
}

.form-hint {
    font-size: 0.78rem;
    color: #f59e0b;
    margin-top: 4px;
}

/* ── Slide-down transition ──────────────────────────────────── */
.slide-down-enter-active {
    transition: all .22s ease;
    overflow: hidden;
}

.slide-down-leave-active {
    transition: all .18s ease;
    overflow: hidden;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    max-height: 0;
    margin-bottom: 0;
}

.slide-down-enter-to,
.slide-down-leave-from {
    opacity: 1;
    max-height: 120px;
}

/* ── Pagination ─────────────────────────────────────────────── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-top: 1.5px solid #f3f4f6;
    flex-wrap: wrap;
    gap: 12px;
}

.pagination-info {
    font-size: 0.82rem;
    color: #6b7280;
}

.pagination-info-only {
    padding: 14px 20px;
    font-size: 0.82rem;
    color: #6b7280;
    border-top: 1.5px solid #f3f4f6;
}

.pagination-btns {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pg-btn {
    width: 34px;
    height: 34px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    color: #374151;
    font-size: 0.83rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s;
}

.pg-btn:hover:not(:disabled) {
    border-color: #3d5a2e;
    color: #3d5a2e;
}

.pg-btn.active {
    background: #3d5a2e;
    border-color: #3d5a2e;
    color: #fff;
}

.pg-btn:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.pg-ellipsis {
    padding: 0 4px;
    color: #9ca3af;
    font-size: 0.9rem;
}

/* ── Empty ──────────────────────────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #9ca3af;
    gap: 12px;
}

.empty-icon {
    opacity: .4;
}

.empty-state p {
    font-size: 0.9rem;
}

/* ── Skeletons ──────────────────────────────────────────────── */
.loading-state {
    padding: 8px 20px;
}

.skeleton-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #f3f4f6;
}

.skeleton-img {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: #f0f0f0;
    flex-shrink: 0;
    animation: shimmer 1.2s infinite;
}

.skeleton-lines {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.skeleton-line {
    height: 12px;
    border-radius: 6px;
    background: #f0f0f0;
    animation: shimmer 1.2s infinite;
}

.w-32 {
    width: 128px;
}

.w-40 {
    width: 160px;
}

.w-20 {
    width: 80px;
}

.ml-auto {
    margin-left: auto;
}

.short {
    height: 10px;
    width: 64px !important;
}

@keyframes shimmer {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: .5;
    }
}

/* ── Modal Overlay ──────────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .45);
    backdrop-filter: blur(2px);
    z-index: 200;
}

/* ── Modal ──────────────────────────────────────────────────── */
.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 480px;
    max-height: 90vh;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, .18);
    z-index: 300;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.modal-preview {
    max-width: 520px;
}

.modal-delete {
    max-width: 420px;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1.5px solid #f3f4f6;
    flex-shrink: 0;
}

.modal-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a1a1a;
}

.modal-close {
    width: 30px;
    height: 30px;
    border: none;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6b7280;
    transition: background .15s;
}

.modal-close:hover {
    background: #e5e7eb;
}

.modal-body {
    padding: 20px 24px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1.5px solid #f3f4f6;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-shrink: 0;
}

/* ── Form ───────────────────────────────────────────────────── */
.form-group {
    margin-bottom: 18px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: block;
    font-size: 0.83rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

.required {
    color: #dc2626;
}

.form-input {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.875rem;
    color: #1a1a1a;
    background: #fff;
    outline: none;
    transition: border-color .2s;
    box-sizing: border-box;
}

.form-input:focus {
    border-color: #3d5a2e;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
}

.form-error {
    font-size: 0.78rem;
    color: #dc2626;
    margin-top: 4px;
}

.image-upload-zone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    cursor: pointer;
    overflow: hidden;
    transition: border-color .2s, background .2s;
    background: #fafafa;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.image-upload-zone:hover,
.image-upload-zone.drag-over {
    border-color: #3d5a2e;
    background: #f0f4ec;
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 24px;
    color: #6b7280;
    text-align: center;
}

.upload-placeholder p {
    font-size: 0.85rem;
    margin: 0;
}

.upload-placeholder span {
    color: #3d5a2e;
    font-weight: 600;
    text-decoration: underline;
}

.upload-hint {
    font-size: 0.75rem !important;
    color: #9ca3af !important;
}

.upload-preview {
    width: 100%;
    height: 160px;
    object-fit: cover;
}

.file-input-hidden {
    display: none;
}

.remove-image-btn {
    font-size: 0.78rem;
    color: #dc2626;
    background: none;
    border: none;
    cursor: pointer;
    margin-top: 6px;
    text-decoration: underline;
}

.toggle-group {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.toggle-btn {
    width: 44px;
    height: 24px;
    border-radius: 12px;
    background: #d1d5db;
    border: none;
    cursor: pointer;
    position: relative;
    transition: background .25s;
}

.toggle-btn.on {
    background: #3d5a2e;
}

.toggle-knob {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
    transition: transform .25s;
}

.toggle-btn.on .toggle-knob {
    transform: translateX(20px);
}

.btn-cancel {
    padding: 9px 18px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    transition: all .15s;
}

.btn-cancel:hover {
    background: #f9fafb;
}

.btn-submit {
    padding: 9px 18px;
    border: none;
    border-radius: 10px;
    background: #3d5a2e;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-submit:hover {
    background: #2e4422;
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
    transition: background .15s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-danger:hover:not(:disabled) {
    background: #b91c1c;
}

.btn-danger:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.spinner {
    width: 14px;
    height: 14px;
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

/* ── Preview Modal ──────────────────────────────────────────── */
.preview-image-wrap {
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 20px;
    background: #f3f4f6;
}

.preview-big-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

.preview-no-img {
    height: 140px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #9ca3af;
}

.preview-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-bottom: 20px;
}

.preview-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid #f3f4f6;
    gap: 16px;
}

.preview-row:last-child {
    border-bottom: none;
}

.preview-key {
    font-size: 0.82rem;
    color: #9ca3af;
    font-weight: 500;
    flex-shrink: 0;
}

.preview-val {
    font-size: 0.875rem;
    color: #1a1a1a;
    font-weight: 500;
    text-align: right;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
}

.status-badge::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.status-badge.active {
    background: #dcfce7;
    color: #16a34a;
}

.status-badge.active::before {
    background: #16a34a;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #dc2626;
}

.status-badge.inactive::before {
    background: #dc2626;
}

.preview-products-title {
    font-size: 0.83rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 10px;
}

.preview-product-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.preview-product-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border: 1.5px solid #f3f4f6;
    border-radius: 10px;
}

.pp-img {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.pp-img-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #f3f4f6;
    flex-shrink: 0;
}

.pp-info {
    flex: 1;
    min-width: 0;
}

.pp-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1a1a1a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pp-price {
    font-size: 0.78rem;
    color: #6b7280;
    margin-top: 1px;
}

.pp-status {
    font-size: 0.72rem;
    font-weight: 600;
    flex-shrink: 0;
}

.pp-status.active {
    color: #16a34a;
}

.pp-status.inactive {
    color: #dc2626;
}

/* ── Delete Modal ───────────────────────────────────────────── */
.delete-icon-wrap {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #fef2f2;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    color: #dc2626;
}

.delete-title {
    text-align: center;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.delete-desc {
    text-align: center;
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.5;
}

.delete-warning {
    display: block;
    margin-top: 10px;
    color: #dc2626;
    font-weight: 500;
}

/* ── Toast ──────────────────────────────────────────────────── */
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
    box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
    z-index: 9999;
}

.toast.success {
    background: #1a1a1a;
    color: #fff;
}

.toast.error {
    background: #dc2626;
    color: #fff;
}

/* ── Transitions ────────────────────────────────────────────── */
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

/* ── Responsive ─────────────────────────────────────────────── */
.desktop-only {
    display: table;
}

.mobile-only {
    display: none;
}

@media (max-width: 680px) {
    .categories-page {
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

    .btn-add {
        flex: 1;
        justify-content: center;
    }

    .desktop-only {
        display: none;
    }

    .mobile-only {
        display: flex;
    }

    .modal {
        width: 95%;
        max-height: 95vh;
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

    .type-selector {
        grid-template-columns: 1fr 1fr;
    }
}
</style>