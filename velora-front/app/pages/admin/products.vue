<template>
    <div class="products-page">

        <!-- ── Page Header ──────────────────────────────────────── -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Products</h1>
                <p class="page-sub">Manage and view all products.</p>
            </div>
            <div class="header-actions">
                <div class="search-wrapper">
                    <svg class="search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="9" cy="9" r="6" />
                        <path d="M15 15l3 3" stroke-linecap="round" />
                    </svg>
                    <input v-model="search" type="text" placeholder="Search products..." class="search-input"
                        @input="onSearch" />
                </div>
                <!-- Filter -->
                <div class="filter-wrap" @click.stop>
                    <button class="btn-filter" @click="showFilter = !showFilter">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="15"
                            height="15">
                            <polygon points="20 2 2 2 9 10.46 9 17 11 19 11 10.46 20 2" />
                        </svg>
                        Filter
                    </button>
                    <Transition name="dropdown">
                        <div v-if="showFilter" class="filter-dropdown">
                            <p class="filter-label">Category</p>
                            <select v-model="filterCat" class="filter-select" @change="applyFilter">
                                <option value="">All Categories</option>
                                <option v-for="c in allCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p class="filter-label mt">Status</p>
                            <div class="filter-radio">
                                <label><input v-model="filterActive" type="radio" value="" @change="applyFilter" />
                                    All</label>
                                <label><input v-model="filterActive" type="radio" value="true" @change="applyFilter" />
                                    Active</label>
                                <label><input v-model="filterActive" type="radio" value="false" @change="applyFilter" />
                                    Inactive</label>
                            </div>
                            <button class="filter-reset" @click="resetFilter">Reset</button>
                        </div>
                    </Transition>
                </div>
                <button class="btn-add" @click="openAddModal">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path
                            d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z" />
                    </svg>
                    Add Product
                </button>
            </div>
        </div>

        <!-- ── Stat Card ─────────────────────────────────────────── -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24"
                        height="24">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <path d="M16 10a4 4 0 01-8 0" />
                    </svg>
                </div>
                <div class="stat-info">
                    <p class="stat-label">Total Products</p>
                    <p class="stat-value">{{ stats.total ?? 0 }}</p>
                    <p class="stat-growth" :class="(stats.growth ?? 0) >= 0 ? 'positive' : 'negative'">
                        {{ (stats.growth ?? 0) >= 0 ? '↑' : '↓' }} {{ Math.abs(stats.growth ?? 0) }}% this week
                    </p>
                </div>
            </div>
        </div>

        <!-- ── Table Card ─────────────────────────────────────────── -->
        <div class="table-card">

            <!-- Loading -->
            <div v-if="loading" class="loading-state">
                <div v-for="i in 8" :key="i" class="skeleton-row">
                    <div class="skeleton-img" />
                    <div class="skeleton-lines">
                        <div class="skeleton-line w-40" />
                        <div class="skeleton-line w-24 short" />
                    </div>
                    <div class="skeleton-line w-20 ml-auto" />
                    <div class="skeleton-line w-16 ml-auto" />
                    <div class="skeleton-line w-20 ml-auto" />
                </div>
            </div>

            <template v-else>
                <!-- Desktop Table -->
                <table class="prod-table desktop-only">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in products" :key="p.id" class="prod-row">
                            <!-- Product -->
                            <td>
                                <div class="prod-name-cell">
                                    <div class="prod-img-wrap">
                                        <img v-if="p.image_url" :src="resolveUrl(p.image_url)" :alt="p.name"
                                            class="prod-img" />
                                        <div v-else class="prod-img-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5" width="18" height="18">
                                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                                <line x1="3" y1="6" x2="21" y2="6" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="prod-name">{{ p.name }}</p>
                                        <p class="prod-desc">{{ p.description?.slice(0, 40) }}{{ p.description?.length >
                                            40 ? '…' : '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Category -->
                            <td>
                                <span v-if="p.category" class="cat-badge">{{ p.category.name }}</span>
                                <span v-else class="text-muted">—</span>
                            </td>
                            <!-- Price -->
                            <td class="price-td">${{ p.price.toFixed(2) }}</td>
                            <!-- Rating -->
                            <td>
                                <div class="stars-row">
                                    <span v-for="s in 5" :key="s" class="star"
                                        :class="s <= Math.round(p.avg_rating) ? 'on' : 'off'">★</span>
                                    <span class="rating-num">{{ p.avg_rating.toFixed(1) }}</span>
                                </div>
                            </td>
                            <!-- Status -->
                            <td>
                                <span class="status-badge" :class="p.is_active ? 'active' : 'inactive'">
                                    {{ p.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <!-- Date -->
                            <td class="date-td">{{ p.created_at }}</td>
                            <!-- Actions -->
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-edit" title="Edit" @click="openEditModal(p)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="15" height="15">
                                            <path d="M13.5 3.5l3 3L7 16H4v-3L13.5 3.5z" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <div class="dropdown-wrap">
                                        <button class="btn-dots" @click.stop="toggleDropdown(p.id, $event)">
                                            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                                <circle cx="10" cy="4" r="1.5" />
                                                <circle cx="10" cy="10" r="1.5" />
                                                <circle cx="10" cy="16" r="1.5" />
                                            </svg>
                                        </button>
                                        <div v-if="openDrop === p.id" class="dropdown-menu"
                                            :style="{ top: dropPos.top + 'px', right: dropPos.right + 'px' }">
                                            <button class="dropdown-item" @click="openPreviewModal(p)">
                                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" width="14" height="14">
                                                    <path d="M2 10s3-7 8-7 8 7 8 7-3 7-8 7-8-7-8-7z" />
                                                    <circle cx="10" cy="10" r="2.5" />
                                                </svg>
                                                Preview
                                            </button>
                                            <button class="dropdown-item danger" @click="openDeleteModal(p)">
                                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" width="14" height="14">
                                                    <path d="M3 5h14M8 5V3h4v2M6 5l1 12h6l1-12" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                Delete
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
                    <div v-for="p in products" :key="p.id" class="mobile-card">
                        <div class="mobile-card-left">
                            <div class="prod-img-wrap">
                                <img v-if="p.image_url" :src="resolveUrl(p.image_url)" :alt="p.name" class="prod-img" />
                                <div v-else class="prod-img-placeholder">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        width="16" height="16">
                                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                        <line x1="3" y1="6" x2="21" y2="6" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="prod-name">{{ p.name }}</p>
                                <p class="prod-desc">{{ p.category?.name ?? '—' }} · ${{ p.price.toFixed(2) }}</p>
                                <span class="status-badge mt-1" :class="p.is_active ? 'active' : 'inactive'">
                                    {{ p.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="mobile-card-actions">
                            <button class="btn-edit" @click="openEditModal(p)">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="15"
                                    height="15">
                                    <path d="M13.5 3.5l3 3L7 16H4v-3L13.5 3.5z" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div class="dropdown-wrap">
                                <button class="btn-dots" @click.stop="toggleDropdown(p.id, $event)">
                                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                        <circle cx="10" cy="4" r="1.5" />
                                        <circle cx="10" cy="10" r="1.5" />
                                        <circle cx="10" cy="16" r="1.5" />
                                    </svg>
                                </button>
                                <div v-if="openDrop === p.id" class="dropdown-menu"
                                    :style="{ top: dropPos.top + 'px', right: dropPos.right + 'px' }">
                                    <button class="dropdown-item" @click="openPreviewModal(p)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M2 10s3-7 8-7 8 7 8 7-3 7-8 7-8-7-8-7z" />
                                            <circle cx="10" cy="10" r="2.5" />
                                        </svg>
                                        Preview
                                    </button>
                                    <button class="dropdown-item danger" @click="openDeleteModal(p)">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                                            width="14" height="14">
                                            <path d="M3 5h14M8 5V3h4v2M6 5l1 12h6l1-12" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-if="products.length === 0" class="empty-state">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"
                        class="empty-icon">
                        <path d="M12 4L6 12v28a4 4 0 004 4h28a4 4 0 004-4V12l-6-8z" />
                        <line x1="6" y1="12" x2="42" y2="12" />
                    </svg>
                    <p>No products found</p>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="pagination">
                    <p class="pagination-info">Showing {{ pagination.from }} to {{ pagination.to }} of {{
                        pagination.total }} products</p>
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
                <div v-else-if="products.length > 0" class="pagination-info-only">
                    Showing 1 to {{ products.length }} of {{ pagination.total }} products
                </div>
            </template>
        </div>

        <!-- ══════════ MODALS ══════════ -->
        <Transition name="fade">
            <div v-if="activeModal" class="modal-overlay" @click.self="closeModal" />
        </Transition>

        <!-- ADD Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'add'" class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Add Product</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Image upload -->
                    <div class="form-group">
                        <label class="form-label">Image</label>
                        <div class="image-upload-zone" :class="{ 'has-file': imagePreview, 'drag-over': isDragging }"
                            @dragover.prevent="isDragging = true" @dragleave="isDragging = false" @drop.prevent="onDrop"
                            @click="triggerFileInput">
                            <img v-if="imagePreview" :src="imagePreview" class="upload-preview" />
                            <div v-else class="upload-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28"
                                    height="28">
                                    <path d="M4 16l4-4 4 4 4-6 4 6" stroke-linecap="round" stroke-linejoin="round" />
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                </svg>
                                <p>Drag & drop or <span>browse</span></p>
                                <p class="upload-hint">JPG, PNG, WEBP · Max 2MB</p>
                            </div>
                            <input ref="fileInputRef" type="file" accept="image/jpeg,image/png,image/webp"
                                class="file-input-hidden" @change="onFileSelect" />
                        </div>
                        <button v-if="imagePreview" class="remove-image-btn" @click.stop="removeImage">Remove
                            image</button>
                    </div>
                    <!-- Name + Price row -->
                    <div class="form-row">
                        <div class="form-group fg-flex2">
                            <label class="form-label">Name <span class="required">*</span></label>
                            <input v-model="form.name" type="text" class="form-input" placeholder="e.g. Latte" />
                            <p v-if="formErrors.name" class="form-error">{{ formErrors.name }}</p>
                        </div>
                        <div class="form-group fg-flex1">
                            <label class="form-label">Price <span class="required">*</span></label>
                            <input v-model="form.price" type="number" step="0.01" min="0" class="form-input"
                                placeholder="0.00" />
                            <p v-if="formErrors.price" class="form-error">{{ formErrors.price }}</p>
                        </div>
                    </div>
                    <!-- Category -->
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select v-model="form.category_id" class="form-input form-select">
                            <option value="">Select category</option>
                            <option v-for="c in allCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <p v-if="formErrors.category_id" class="form-error">{{ formErrors.category_id }}</p>
                    </div>
                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" class="form-input form-textarea" rows="3"
                            placeholder="Short description..." />
                    </div>
                    <!-- Active toggle -->
                    <div class="form-group toggle-group">
                        <label class="form-label">Active</label>
                        <button class="toggle-btn" :class="{ on: form.is_active }"
                            @click="form.is_active = !form.is_active">
                            <span class="toggle-knob" />
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal">Cancel</button>
                    <button class="btn-submit" :disabled="submitting" @click="submitAdd">
                        <span v-if="submitting" class="spinner" />
                        {{ submitting ? 'Saving…' : 'Add Product' }}
                    </button>
                </div>
            </div>
        </Transition>

        <!-- EDIT Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'edit'" class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Product</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Image</label>
                        <div class="image-upload-zone" :class="{ 'has-file': imagePreview, 'drag-over': isDragging }"
                            @dragover.prevent="isDragging = true" @dragleave="isDragging = false" @drop.prevent="onDrop"
                            @click="triggerFileInput">
                            <img v-if="imagePreview" :src="imagePreview" class="upload-preview" />
                            <div v-else class="upload-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28"
                                    height="28">
                                    <path d="M4 16l4-4 4 4 4-6 4 6" stroke-linecap="round" stroke-linejoin="round" />
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                </svg>
                                <p>Click to change image</p>
                            </div>
                            <input ref="fileInputRef" type="file" accept="image/jpeg,image/png,image/webp"
                                class="file-input-hidden" @change="onFileSelect" />
                        </div>
                        <button v-if="imagePreview" class="remove-image-btn" @click.stop="removeImage">Remove
                            image</button>
                    </div>
                    <div class="form-row">
                        <div class="form-group fg-flex2">
                            <label class="form-label">Name <span class="required">*</span></label>
                            <input v-model="form.name" type="text" class="form-input" />
                            <p v-if="formErrors.name" class="form-error">{{ formErrors.name }}</p>
                        </div>
                        <div class="form-group fg-flex1">
                            <label class="form-label">Price <span class="required">*</span></label>
                            <input v-model="form.price" type="number" step="0.01" min="0" class="form-input" />
                            <p v-if="formErrors.price" class="form-error">{{ formErrors.price }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select v-model="form.category_id" class="form-input form-select">
                            <option value="">Select category</option>
                            <option v-for="c in allCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <p v-if="formErrors.category_id" class="form-error">{{ formErrors.category_id }}</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" class="form-input form-textarea" rows="3" />
                    </div>
                    <div class="form-group toggle-group">
                        <label class="form-label">Active</label>
                        <button class="toggle-btn" :class="{ on: form.is_active }"
                            @click="form.is_active = !form.is_active">
                            <span class="toggle-knob" />
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal">Cancel</button>
                    <button class="btn-submit" :disabled="submitting" @click="submitEdit">
                        <span v-if="submitting" class="spinner" />
                        {{ submitting ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </Transition>

        <!-- PREVIEW Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'preview' && selectedProd" class="modal modal-preview">
                <div class="modal-header">
                    <h2 class="modal-title">Product Preview</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="preview-image-wrap">
                        <img v-if="selectedProd.image_url" :src="resolveUrl(selectedProd.image_url)"
                            :alt="selectedProd.name" class="preview-big-img" />
                        <div v-else class="preview-no-img">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" width="48"
                                height="48">
                                <path d="M12 4L6 12v28a4 4 0 004 4h28a4 4 0 004-4V12l-6-8z" />
                                <line x1="6" y1="12" x2="42" y2="12" />
                            </svg>
                            <p>No image</p>
                        </div>
                    </div>
                    <div class="preview-info">
                        <div class="preview-row">
                            <span class="preview-key">Name</span>
                            <span class="preview-val">{{ selectedProd.name }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Category</span>
                            <span class="preview-val">
                                <span v-if="selectedProd.category" class="cat-badge">{{ selectedProd.category.name
                                    }}</span>
                                <span v-else>—</span>
                            </span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Price</span>
                            <span class="preview-val price-green">${{ selectedProd.price?.toFixed(2) }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Rating</span>
                            <span class="preview-val">
                                <span class="stars-row">
                                    <span v-for="s in 5" :key="s" class="star"
                                        :class="s <= Math.round(selectedProd.avg_rating) ? 'on' : 'off'">★</span>
                                    <span class="rating-num">{{ selectedProd.avg_rating?.toFixed(1) }}</span>
                                </span>
                            </span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Status</span>
                            <span class="status-badge" :class="selectedProd.is_active ? 'active' : 'inactive'">
                                {{ selectedProd.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div v-if="selectedProd.order_count !== undefined" class="preview-row">
                            <span class="preview-key">Sold</span>
                            <span class="preview-val">{{ selectedProd.order_count }} units</span>
                        </div>
                        <div v-if="selectedProd.ratings_count !== undefined" class="preview-row">
                            <span class="preview-key">Reviews</span>
                            <span class="preview-val">{{ selectedProd.ratings_count }}</span>
                        </div>
                        <div v-if="selectedProd.description" class="preview-row">
                            <span class="preview-key">Desc</span>
                            <span class="preview-val">{{ selectedProd.description }}</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-key">Created</span>
                            <span class="preview-val">{{ selectedProd.created_at }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-submit" @click="openEditModal(selectedProd)">Edit</button>
                </div>
            </div>
        </Transition>

        <!-- DELETE Modal -->
        <Transition name="modal-slide">
            <div v-if="activeModal === 'delete' && selectedProd" class="modal modal-delete">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Product</h2>
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
                    <p class="delete-title">Are you sure?</p>
                    <p class="delete-desc">
                        You're about to delete <strong>{{ selectedProd.name }}</strong>. This action cannot be undone.
                        <span class="delete-warning">Products used in orders cannot be deleted.</span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="closeModal">Cancel</button>
                    <button class="btn-danger" :disabled="submitting" @click="submitDelete">
                        <span v-if="submitting" class="spinner" />
                        {{ submitting ? 'Deleting…' : 'Delete' }}
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
import { nextTick } from 'vue'

definePageMeta({ layout: 'admin' as any, middleware: 'admin' })

const config = useRuntimeConfig()
const API = config.public.apiBase
const BACKEND_BASE = API.replace(/\/api\/?$/, '')

const resolveUrl = (url: string | null | undefined): string | null => {
    if (!url) return null
    if (url.startsWith('http')) return url
    return `${BACKEND_BASE}${url.startsWith('/') ? '' : '/'}${url}`
}
import { useProducts } from '~/composables/useProducts'
const { fetchProducts, fetchProductStats, fetchProduct, createProduct, updateProduct, deleteProduct } = useProducts()
const { fetchCategories } = useAdmin()

// ── State ──────────────────────────────────────────────────
const products = ref<any[]>([])
const allCategories = ref<any[]>([])
const stats = ref<any>({ total: 0, growth: 0 })
const loading = ref(true)
const search = ref('')
const filterCat = ref<string | number>('')
const filterActive = ref('')
const showFilter = ref(false)
const currentPage = ref(1)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })

const activeModal = ref<string | null>(null)
const selectedProd = ref<any>(null)
const openDrop = ref<number | null>(null)
const dropPos = ref({ top: 0, right: 0 })
const submitting = ref(false)
const isDragging = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)
const imagePreview = ref<string | null>(null)
const imageFile = ref<File | null>(null)
let searchTimeout: ReturnType<typeof setTimeout>

const form = reactive({
    name: '', description: '', price: '' as string | number,
    category_id: '' as string | number, is_active: true,
})
const formErrors = reactive({ name: '', price: '', category_id: '' })
const toast = reactive({ visible: false, message: '', type: 'success' })

// ── Computed ───────────────────────────────────────────────
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

// ── Load ───────────────────────────────────────────────────
onMounted(async () => {
    await Promise.all([loadProducts(), loadStats(), loadCategories()])
    document.addEventListener('click', () => { openDrop.value = null; showFilter.value = false })
})
onUnmounted(() => document.removeEventListener('click', () => { }))

async function loadProducts(page = 1) {
    loading.value = true
    try {
        const params: any = { page, per_page: 8, search: search.value }
        if (filterCat.value) params.category_id = filterCat.value
        if (filterActive.value !== '') params.is_active = filterActive.value
        const data = await fetchProducts(params) as any
        products.value = data.data
        pagination.value = { current_page: data.current_page, last_page: data.last_page, total: data.total, from: data.from ?? 0, to: data.to ?? 0 }
    } catch { showToast('Failed to load products', 'error') }
    finally { loading.value = false }
}

async function loadStats() {
    try { stats.value = await fetchProductStats() as any } catch { }
}

async function loadCategories() {
    try {
        const res = await fetchCategories({ per_page: 100 }) as any
        allCategories.value = res.data
    } catch { }
}

// ── Search / Filter ────────────────────────────────────────
function onSearch() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => { currentPage.value = 1; loadProducts(1) }, 400)
}

function applyFilter() { showFilter.value = false; currentPage.value = 1; loadProducts(1) }
function resetFilter() { filterCat.value = ''; filterActive.value = ''; showFilter.value = false; loadProducts(1) }

function goToPage(page: number) { currentPage.value = page; loadProducts(page) }

// ── Dropdown ───────────────────────────────────────────────
function toggleDropdown(id: number, event: MouseEvent) {
    if (openDrop.value === id) { openDrop.value = null; return }
    const rect = (event.currentTarget as HTMLElement).getBoundingClientRect()
    dropPos.value = { top: rect.bottom + 6, right: window.innerWidth - rect.right }
    openDrop.value = id
    nextTick(() => {
        const h = (e: MouseEvent) => { openDrop.value = null; document.removeEventListener('click', h) }
        document.addEventListener('click', h)
    })
}

// ── Image ──────────────────────────────────────────────────
function triggerFileInput() { fileInputRef.value?.click() }
function onFileSelect(e: Event) { const f = (e.target as HTMLInputElement).files?.[0]; if (f) handleFile(f) }
function onDrop(e: DragEvent) { isDragging.value = false; const f = e.dataTransfer?.files[0]; if (f) handleFile(f) }
function handleFile(file: File) {
    if (file.size > 2 * 1024 * 1024) { showToast('Image must be under 2MB', 'error'); return }
    imageFile.value = file
    const r = new FileReader()
    r.onload = (e) => { imagePreview.value = e.target?.result as string }
    r.readAsDataURL(file)
}
function removeImage() { imagePreview.value = null; imageFile.value = null; if (fileInputRef.value) fileInputRef.value.value = '' }

// ── Form helpers ───────────────────────────────────────────
function resetForm() {
    form.name = ''; form.description = ''; form.price = ''; form.category_id = ''; form.is_active = true
    formErrors.name = ''; formErrors.price = ''; formErrors.category_id = ''
    imagePreview.value = null; imageFile.value = null
}

function buildFD() {
    const fd = new FormData()
    fd.append('name', form.name)
    fd.append('price', String(form.price))
    fd.append('category_id', String(form.category_id))
    fd.append('is_active', form.is_active ? '1' : '0')
    if (form.description) fd.append('description', form.description)
    if (imageFile.value) fd.append('image', imageFile.value)
    return fd
}

function validate() {
    formErrors.name = ''; formErrors.price = ''; formErrors.category_id = ''
    let ok = true
    if (!form.name.trim()) { formErrors.name = 'Name is required'; ok = false }
    if (!form.price) { formErrors.price = 'Price is required'; ok = false }
    if (!form.category_id) { formErrors.category_id = 'Category is required'; ok = false }
    return ok
}

// ── Modal openers ──────────────────────────────────────────
function openAddModal() { resetForm(); activeModal.value = 'add' }

function openEditModal(p: any) {
    resetForm()
    selectedProd.value = p
    form.name = p.name
    form.description = p.description ?? ''
    form.price = p.price
    form.category_id = p.category?.id ?? ''
    form.is_active = p.is_active
    imagePreview.value = resolveUrl(p.image_url)
    activeModal.value = 'edit'
    openDrop.value = null
}

async function openPreviewModal(p: any) {
    selectedProd.value = p
    activeModal.value = 'preview'
    openDrop.value = null
    try { selectedProd.value = await fetchProduct(p.id) } catch { }
}

function openDeleteModal(p: any) { selectedProd.value = p; activeModal.value = 'delete'; openDrop.value = null }

function closeModal() { activeModal.value = null; selectedProd.value = null; resetForm() }

// ── Submits ────────────────────────────────────────────────
async function submitAdd() {
    if (!validate()) return
    submitting.value = true
    try {
        await createProduct(buildFD())
        showToast('Product created successfully', 'success')
        closeModal(); await loadProducts(currentPage.value); await loadStats()
    } catch (err: any) {
        const errs = err?.data?.errors
        if (errs?.name) formErrors.name = errs.name[0]
        else if (errs?.price) formErrors.price = errs.price[0]
        else showToast(err?.data?.message || 'Failed to create', 'error')
    } finally { submitting.value = false }
}

async function submitEdit() {
    if (!validate()) return
    submitting.value = true
    try {
        await updateProduct(selectedProd.value.id, buildFD())
        showToast('Product updated successfully', 'success')
        closeModal(); await loadProducts(currentPage.value)
    } catch (err: any) {
        const errs = err?.data?.errors
        if (errs?.name) formErrors.name = errs.name[0]
        else showToast(err?.data?.message || 'Failed to update', 'error')
    } finally { submitting.value = false }
}

async function submitDelete() {
    submitting.value = true
    try {
        await deleteProduct(selectedProd.value.id)
        showToast('Product deleted successfully', 'success')
        closeModal()
        if (products.value.length === 1 && currentPage.value > 1) currentPage.value--
        await loadProducts(currentPage.value); await loadStats()
    } catch (err: any) {
        showToast(err?.data?.message || 'Failed to delete', 'error')
    } finally { submitting.value = false }
}

// ── Toast ──────────────────────────────────────────────────
function showToast(message: string, type: 'success' | 'error' = 'success') {
    toast.message = message; toast.type = type; toast.visible = true
    setTimeout(() => { toast.visible = false }, 3500)
}
</script>

<style scoped>
.products-page {
    padding: 28px 24px;
    max-width: 100%;
    margin: 0 auto;
    min-height: 100%;
}

/* Header */
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
    font-size: 0.85rem;
    color: #374151;
    cursor: pointer;
    transition: border-color .2s;
}

.btn-filter:hover {
    border-color: #3d5a2e;
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
    font-size: 0.72rem;
    font-weight: 600;
    color: #6b7280;
    margin: 0 0 6px;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.filter-label.mt {
    margin-top: 12px;
}

.filter-select {
    width: 100%;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 0.84rem;
    color: #1a1a1a;
    outline: none;
    margin-bottom: 4px;
}

.filter-radio {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}

.filter-radio label {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 0.83rem;
    color: #1a1a1a;
    cursor: pointer;
}

.filter-reset {
    width: 100%;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    padding: 7px;
    font-size: 0.82rem;
    color: #6b7280;
    cursor: pointer;
}

.filter-reset:hover {
    background: #e5e7eb;
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

/* Stat */
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
    background: #2C1810;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #C8A96E;
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

/* Table card */
.table-card {
    background: #fff;
    border: 1.5px solid #f0f0f0;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
    overflow: hidden;
}

.prod-table {
    width: 100%;
    border-collapse: collapse;
}

.prod-table th {
    padding: 13px 16px;
    text-align: left;
    font-size: 0.78rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1.5px solid #f3f4f6;
    background: #fafafa;
}

.prod-row td {
    padding: 12px 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.prod-row:last-child td {
    border-bottom: none;
}

.prod-row:hover td {
    background: #fafafa;
}

.prod-name-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.prod-img-wrap {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.prod-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.prod-img-placeholder {
    color: #9ca3af;
}

.prod-name {
    font-weight: 600;
    font-size: 0.88rem;
    color: #1a1a1a;
}

.prod-desc {
    font-size: 0.76rem;
    color: #9ca3af;
    margin-top: 2px;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cat-badge {
    display: inline-block;
    background: #e8f0e4;
    color: #3d5a2e;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 99px;
    white-space: nowrap;
}

.price-td {
    font-size: 0.88rem;
    font-weight: 600;
    color: #1a1a1a;
    white-space: nowrap;
}

.price-green {
    color: #16a34a;
    font-weight: 600;
}

.text-muted {
    color: #9ca3af;
    font-size: 0.82rem;
}

.stars-row {
    display: flex;
    align-items: center;
    gap: 1px;
}

.star {
    font-size: 12px;
}

.star.on {
    color: #C8A96E;
}

.star.off {
    color: #e5e7eb;
}

.rating-num {
    font-size: 0.75rem;
    color: #6b7280;
    margin-left: 3px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
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

.mt-1 {
    display: inline-flex;
    margin-top: 4px;
}

.date-td {
    font-size: 0.8rem;
    color: #9ca3af;
    white-space: nowrap;
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

/* Skeletons */
.loading-state {
    padding: 8px 16px;
}

.skeleton-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 0;
    border-bottom: 1px solid #f3f4f6;
}

.skeleton-img {
    width: 50px;
    height: 50px;
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

.w-40 {
    width: 160px;
}

.w-24 {
    width: 96px;
}

.w-20 {
    width: 80px;
}

.w-16 {
    width: 64px;
}

.short {
    height: 10px;
    width: 64px !important;
}

.ml-auto {
    margin-left: auto;
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

/* Mobile cards */
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

/* Empty */
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

/* Pagination */
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

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .45);
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

/* Form */
.form-group {
    margin-bottom: 16px;
}

.form-row {
    display: flex;
    gap: 12px;
}

.fg-flex2 {
    flex: 2;
}

.fg-flex1 {
    flex: 1;
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
    font-family: inherit;
}

.form-input:focus {
    border-color: #3d5a2e;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-select {
    appearance: auto;
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

/* Preview */
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
}

.preview-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
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

/* Delete */
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

/* Toast */
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

/* Transitions */
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
    display: table;
}

.mobile-only {
    display: none;
}

@media (max-width: 768px) {
    .products-page {
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

    .form-row {
        flex-direction: column;
        gap: 0;
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
</style>