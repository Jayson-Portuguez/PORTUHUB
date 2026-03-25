<template>
  <div class="admin-page">
    <div class="admin-page__toolbar admin-page__toolbar--split">
      <div class="admin-page__filters">
        <label class="admin-page__filter-label" for="admin-product-category-filter">Category</label>
        <select
          id="admin-product-category-filter"
          v-model="filterCategory"
          class="admin-page__filter-select"
        >
          <option value="">All categories</option>
          <option v-for="c in categoryOptions" :key="c" :value="c">{{ c }}</option>
        </select>
      </div>
      <button type="button" class="btn btn-primary admin-page__add" @click="openAdd">Add products</button>
    </div>
    <div class="admin-products">
      <div class="admin-table-wrap admin-products__table">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stocks</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in products" :key="'t-' + p.id">
              <td>{{ p.name }}</td>
              <td class="admin-products__category-cell">{{ p.category || '—' }}</td>
              <td>₱{{ Number(p.price).toLocaleString() }}</td>
              <td>{{ p.stock }}</td>
              <td>
                <button type="button" @click="openEdit(p)" class="admin-icon-btn admin-icon-btn-edit" title="Edit" aria-label="Edit" :disabled="deletingId !== null">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                </button>
                <button type="button" @click="deleteProduct(p.id)" class="admin-icon-btn admin-icon-btn-danger" :title="deletingId === p.id ? 'Deleting…' : 'Delete'" :aria-label="deletingId === p.id ? 'Deleting' : 'Delete'" :disabled="deletingId !== null">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <ul class="admin-products__cards" role="list">
        <li v-for="p in products" :key="'c-' + p.id" class="admin-product-card">
          <div class="admin-product-card__main">
            <h3 class="admin-product-card__name">{{ p.name }}</h3>
            <div class="admin-product-card__meta">
              <div class="admin-product-card__row">
                <span class="admin-product-card__label">Category</span>
                <span class="admin-product-card__value">{{ p.category || '—' }}</span>
              </div>
              <div class="admin-product-card__row">
                <span class="admin-product-card__label">Price</span>
                <span class="admin-product-card__value">₱{{ Number(p.price).toLocaleString() }}</span>
              </div>
              <div class="admin-product-card__row">
                <span class="admin-product-card__label">Stocks</span>
                <span class="admin-product-card__value">{{ p.stock }}</span>
              </div>
            </div>
          </div>
          <div class="admin-product-card__action-section">
            <span class="admin-product-card__action-label">Action</span>
            <div class="admin-product-card__actions" role="group" aria-label="Product actions">
              <button type="button" @click="openEdit(p)" class="admin-icon-btn admin-icon-btn-edit" title="Edit" aria-label="Edit" :disabled="deletingId !== null">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
              </button>
              <button type="button" @click="deleteProduct(p.id)" class="admin-icon-btn admin-icon-btn-danger" :title="deletingId === p.id ? 'Deleting…' : 'Delete'" :aria-label="deletingId === p.id ? 'Deleting' : 'Delete'" :disabled="deletingId !== null">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /></svg>
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>
    <nav
      v-if="pagination && pagination.lastPage > 1"
      class="admin-pagination"
      aria-label="Product list pages"
    >
      <p class="admin-pagination-summary">{{ paginationSummary }}</p>
      <div class="admin-pagination-buttons">
        <button
          type="button"
          class="btn btn-ghost admin-pagination-nav"
          :disabled="pagination.currentPage <= 1"
          @click="goToPage(pagination.currentPage - 1)"
        >
          Previous
        </button>
        <span class="admin-pagination-page">Page {{ pagination.currentPage }} of {{ pagination.lastPage }}</span>
        <button
          type="button"
          class="btn btn-ghost admin-pagination-nav"
          :disabled="pagination.currentPage >= pagination.lastPage"
          @click="goToPage(pagination.currentPage + 1)"
        >
          Next
        </button>
      </div>
    </nav>
    <div v-if="modalProduct !== null" class="admin-modal-overlay" @click.self="requestCloseModalFromOverlay">
      <div class="admin-modal">
        <div class="admin-modal-header">
          <h3 style="margin: 0; font-size: 1.1rem;">{{ isAdd ? 'Add product' : 'Edit product' }}</h3>
          <button type="button" class="admin-modal-close" :disabled="saveSubmitting" @click="requestCloseModal" aria-label="Close">×</button>
        </div>
        <form
          class="admin-modal-form"
          :class="{ 'admin-modal-form--busy': saveSubmitting }"
          :aria-busy="saveSubmitting"
          @submit.prevent="saveProduct"
        >
          <div class="admin-modal-body">
            <div class="form-group">
              <label>Name</label>
              <input v-model="form.name" @input="markDirty" required :disabled="saveSubmitting" />
            </div>
            <div class="form-group">
              <label>Category</label>
              <select v-model="form.category" @change="markDirty" required :disabled="saveSubmitting">
                <option disabled value="">Select category</option>
                <option>Electronics</option>
                <option>Fashion</option>
                <option>Home & Living</option>
                <option>Groceries</option>
                <option>Beauty & Personal Care</option>
                <option>Sports & Outdoors</option>
                <option>Others</option>
              </select>
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="form.description" @input="markDirty" rows="4" :disabled="saveSubmitting"></textarea>
            </div>
            <div class="form-row-two">
              <div class="form-group">
                <label>Price (₱)</label>
                <input v-model.number="form.price" @input="markDirty" type="number" step="0.01" min="0" required :disabled="saveSubmitting" />
              </div>
              <div class="form-group">
                <label>Stock</label>
                <input v-model.number="form.stock" @input="markDirty" type="number" min="0" class="stock-input" required :disabled="saveSubmitting" />
              </div>
            </div>
            <div class="form-group">
              <label>Images</label>
              <p class="field-help">
                Upload up to 6 clear product photos (JPG, PNG, GIF or WEBP). The first image will appear as the main product photo.
              </p>
              <div class="image-upload-shell">
                <div class="image-upload-area">
                  <div
                    v-for="(url, idx) in form.imageUrls"
                    :key="'url-' + idx"
                    class="image-preview-wrap"
                  >
                    <img :src="url" :alt="'Image ' + (idx + 1)" class="image-preview-thumb" />
                    <span v-if="idx === 0" class="image-badge">Primary</span>
                    <button
                      type="button"
                      class="image-preview-remove"
                      @click="form.imageUrls.splice(idx, 1); markDirty();"
                      :disabled="saveSubmitting"
                      aria-label="Remove image"
                    >
                      ×
                    </button>
                  </div>
                  <div
                    v-for="(file, idx) in imageFileList"
                    :key="'file-' + idx"
                    class="image-preview-wrap"
                  >
                    <img :src="filePreview(file)" :alt="'New ' + (idx + 1)" class="image-preview-thumb" />
                    <button
                      type="button"
                      class="image-preview-remove"
                      @click="imageFileList.splice(idx, 1); markDirty();"
                      :disabled="saveSubmitting"
                      aria-label="Remove"
                    >
                      ×
                    </button>
                  </div>
                  <label class="image-upload-add" :class="{ 'image-upload-add-disabled': totalImageCount >= 6 || saveSubmitting }">
                    <input
                      type="file"
                      accept="image/jpeg,image/png,image/gif,image/webp,image/jfif,.jfif"
                      multiple
                      @change="onImageSelect"
                      :disabled="totalImageCount >= 6 || saveSubmitting"
                    />
                    <div class="image-upload-add-inner">
                      <span class="image-upload-add-icon">＋</span>
                      <span class="image-upload-add-text">
                        {{ totalImageCount >= 6 ? 'Maximum images reached' : 'Click to browse images' }}
                      </span>
                    </div>
                  </label>
                </div>
                <div class="image-upload-meta">
                  <span class="image-upload-count">{{ totalImageCount }}/6 images</span>
                </div>
              </div>
            </div>
          </div>
          <div class="admin-modal-footer">
            <button type="button" @click="requestCloseModal" class="btn btn-ghost" :disabled="saveSubmitting">Cancel</button>
            <button type="submit" class="btn btn-primary admin-modal-save" :disabled="saveSubmitting">
              <span v-if="saveSubmitting">Saving…</span>
              <span v-else>Save</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, inject } from 'vue';
import { authHeaders } from '../admin/auth.js';

const { showAlert, showConfirm } = inject('adminNotify');

function firstValidationMessage(data) {
  if (data && data.errors && typeof data.errors === 'object') {
    const vals = Object.values(data.errors).flat();
    if (vals.length) return String(vals[0]);
  }
  return null;
}

async function resizeImageFileForUpload(file, maxEdge = 1280, quality = 0.78) {
  if (!(file instanceof File) || !file.type.startsWith('image/')) return file;
  try {
    const bitmap = await createImageBitmap(file);
    let w = bitmap.width;
    let h = bitmap.height;
    if (w <= maxEdge && h <= maxEdge) {
      bitmap.close();
      return file;
    }
    const scale = Math.min(maxEdge / w, maxEdge / h);
    w = Math.round(w * scale);
    h = Math.round(h * scale);
    const canvas = document.createElement('canvas');
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(bitmap, 0, 0, w, h);
    bitmap.close();
    const blob = await new Promise((resolve) => {
      canvas.toBlob(resolve, 'image/jpeg', quality);
    });
    if (!blob) return file;
    const base = file.name.replace(/\.[^/.]+$/, '') || 'image';
    return new File([blob], `${base}.jpg`, { type: 'image/jpeg' });
  } catch {
    return file;
  }
}

const products = ref([]);
const categoryOptions = ref([]);
const filterCategory = ref('');
const ADMIN_PRODUCTS_PER_PAGE = 10;
const currentPage = ref(1);
const pagination = ref(null);
const modalProduct = ref(null);
const form = ref({ name: '', category: '', description: '', price: 0, stock: 0, imageUrls: [] });
const imageFileList = ref([]);
const isDirty = ref(false);
const saveSubmitting = ref(false);
const deletingId = ref(null);

const isAdd = computed(() => modalProduct.value === 'add');
const totalImageCount = computed(() => (form.value.imageUrls?.length || 0) + imageFileList.value.length);

const paginationSummary = computed(() => {
  const pg = pagination.value;
  if (!pg || !pg.total) return '';
  const from = pg.from != null ? pg.from : 0;
  const to = pg.to != null ? pg.to : 0;
  return `Showing ${from}–${to} of ${pg.total}`;
});

function filePreview(file) {
  return file && file instanceof File ? URL.createObjectURL(file) : '';
}

function onImageSelect(e) {
  const files = e.target.files;
  if (files && files.length) {
    const remaining = 6 - totalImageCount.value;
    if (remaining <= 0) {
      e.target.value = '';
      return;
    }
    const toAdd = Array.from(files).slice(0, remaining);
    if (toAdd.length) {
      imageFileList.value.push(...toAdd);
      isDirty.value = true;
    }
  }
  e.target.value = '';
}

async function loadCategoryOptions() {
  try {
    const res = await fetch('/api/products/categories', { credentials: 'include' });
    const data = await res.json().catch(() => []);
    categoryOptions.value = Array.isArray(data) ? data.filter((x) => typeof x === 'string' && x !== '') : [];
  } catch {
    categoryOptions.value = [];
  }
}

async function loadProducts(page) {
  const target = page != null ? page : currentPage.value;
  currentPage.value = Math.max(1, target);
  try {
    const params = new URLSearchParams({
      page: String(currentPage.value),
      per_page: String(ADMIN_PRODUCTS_PER_PAGE),
    });
    if (filterCategory.value) {
      params.set('category', filterCategory.value);
    }
    const res = await fetch(`/api/products?${params}`, { credentials: 'include', headers: authHeaders() });
    const data = await res.json();
    if (Array.isArray(data)) {
      products.value = data;
      pagination.value = null;
      return;
    }
    if (data && Array.isArray(data.data)) {
      products.value = data.data;
      pagination.value = {
        currentPage: data.current_page,
        lastPage: data.last_page,
        perPage: data.per_page,
        total: data.total,
        from: data.from,
        to: data.to,
      };
      if (pagination.value.lastPage >= 1 && currentPage.value > pagination.value.lastPage) {
        currentPage.value = pagination.value.lastPage;
        await loadProducts(currentPage.value);
      }
      return;
    }
    products.value = [];
    pagination.value = null;
  } catch {
    products.value = [];
    pagination.value = null;
  }
}

function goToPage(page) {
  if (!pagination.value) return;
  if (page < 1 || page > pagination.value.lastPage) return;
  loadProducts(page);
}

watch(filterCategory, () => {
  loadProducts(1);
});

onMounted(async () => {
  await loadCategoryOptions();
  await loadProducts(1);
});

function openAdd() {
  modalProduct.value = 'add';
  form.value = { name: '', category: '', description: '', price: 0, stock: 0, imageUrls: [] };
  imageFileList.value = [];
  isDirty.value = false;
}

function openEdit(p) {
  modalProduct.value = p;
  form.value = {
    name: p.name,
    category: p.category || '',
    description: p.description || '',
    price: p.price,
    stock: p.stock,
    imageUrls: [...(p.imageUrls || [])],
  };
  imageFileList.value = [];
  isDirty.value = false;
}

async function saveProduct() {
  if (saveSubmitting.value) return;
  const isAddMode = modalProduct.value === 'add';
  const confirmed = await showConfirm({
    tone: 'info',
    title: isAddMode ? 'Add this product?' : 'Save changes?',
    text: isAddMode
      ? 'This will add the product to your catalog.'
      : 'Your updates will be saved to this product.',
    primaryLabel: isAddMode ? 'Yes, add' : 'Yes, save',
    secondaryLabel: 'Cancel',
    primaryDanger: false,
  });
  if (!confirmed) return;

  saveSubmitting.value = true;
  try {
    let imageUrls = [...form.value.imageUrls];
    if (imageFileList.value.length > 0) {
      const fd = new FormData();
      const resized = await Promise.all(
        imageFileList.value.map((f) => resizeImageFileForUpload(f)),
      );
      resized.forEach((file) => fd.append('images[]', file));
      try {
        const up = await fetch('/api/upload', { method: 'POST', credentials: 'include', headers: authHeaders(), body: fd });
        const upData = await up.json().catch(() => ({}));
        if (!up.ok) {
          const message =
            firstValidationMessage(upData) ||
            upData.error ||
            upData.message ||
            'Image upload failed. Try fewer or smaller images.';
          await showAlert({
            tone: 'error',
            title: 'Upload failed',
            text: message,
          });
          return;
        }
        if (!Array.isArray(upData.urls)) {
          await showAlert({
            tone: 'error',
            title: 'Upload failed',
            text: 'The server did not return image data. Please try again.',
          });
          return;
        }
        imageUrls = imageUrls.concat(upData.urls);
      } catch {
        await showAlert({
          tone: 'error',
          title: 'Upload failed',
          text: 'Image upload failed. Please check your connection and try again.',
        });
        return;
      }
    }
    if (imageUrls.length === 0) imageUrls = ['/placeholder.svg'];
    const url = isAddMode ? '/api/products' : `/api/products/${modalProduct.value.id}`;
    const method = isAddMode ? 'POST' : 'PATCH';
    try {
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', ...authHeaders() },
        credentials: 'include',
        body: JSON.stringify({
          name: form.value.name,
          category: form.value.category,
          description: form.value.description,
          price: form.value.price,
          stock: form.value.stock,
          imageUrls,
        }),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const message =
          firstValidationMessage(data) ||
          data.error ||
          data.message ||
          'Failed to save product. If you added many photos, try again with smaller files.';
        await showAlert({
          tone: 'error',
          title: 'Save failed',
          text: message,
        });
        return;
      }
      modalProduct.value = null;
      isDirty.value = false;
      await loadCategoryOptions();
      if (filterCategory.value && !categoryOptions.value.includes(filterCategory.value)) {
        filterCategory.value = '';
      } else {
        if (isAddMode) {
          currentPage.value = 1;
        }
        await loadProducts(isAddMode ? 1 : currentPage.value);
      }
      await showAlert({
        tone: 'success',
        title: isAddMode ? 'Product added' : 'Saved successfully',
        text: isAddMode
          ? 'The new product is now in your catalog.'
          : 'Your product changes have been saved.',
      });
    } catch {
      await showAlert({
        tone: 'error',
        title: 'Network error',
        text: 'Something went wrong. Please check your connection.',
      });
    }
  } finally {
    saveSubmitting.value = false;
  }
}

async function deleteProduct(id) {
  if (deletingId.value !== null) return;
  const confirmed = await showConfirm({
    tone: 'warning',
    title: 'Delete product?',
    text: 'This action cannot be undone.',
    primaryLabel: 'Yes, delete',
    secondaryLabel: 'Cancel',
    primaryDanger: true,
  });
  if (!confirmed) return;

  deletingId.value = id;
  try {
    const res = await fetch(`/api/products/${id}`, { method: 'DELETE', credentials: 'include', headers: authHeaders() });
    if (res.ok) {
      const wasOnlyOnPage = products.value.length === 1;
      const pg = pagination.value;
      let nextPage = currentPage.value;
      if (wasOnlyOnPage && pg && currentPage.value > 1) {
        nextPage = currentPage.value - 1;
      }
      await loadCategoryOptions();
      if (filterCategory.value && !categoryOptions.value.includes(filterCategory.value)) {
        filterCategory.value = '';
      } else {
        currentPage.value = nextPage;
        await loadProducts(nextPage);
      }
      await showAlert({
        tone: 'success',
        title: 'Deleted successfully',
        text: 'The product has been removed from your catalog.',
      });
    } else {
      const err = await res.json().catch(() => ({}));
      await showAlert({
        tone: 'error',
        title: 'Delete failed',
        text: err.error || 'Could not delete this product.',
      });
    }
  } catch {
    await showAlert({
      tone: 'error',
      title: 'Network error',
      text: 'Could not delete this product. Check your connection.',
    });
  } finally {
    deletingId.value = null;
  }
}

function markDirty() {
  isDirty.value = true;
}

function requestCloseModal() {
  if (saveSubmitting.value) return;
  modalProduct.value = null;
  isDirty.value = false;
}

function requestCloseModalFromOverlay() {
  if (saveSubmitting.value) return;
  requestCloseModal();
}
</script>
