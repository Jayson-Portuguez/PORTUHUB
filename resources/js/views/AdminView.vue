<template>
  <div class="admin-layout">
    <template v-if="admin === null">
      <div style="color: #737373;">Checking...</div>
    </template>
    <template v-else-if="!admin">
      <div class="login-box">
        <h1>Admin login</h1>
        <div v-if="loginError" class="alert alert-error">{{ loginError }}</div>
        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label>Username</label>
            <input v-model="username" type="text" required />
          </div>
          <div class="form-group">
            <label>Password</label>
            <input v-model="password" type="password" required />
          </div>
          <button type="submit" class="btn btn-primary" style="width: 100%;">Log in</button>
        </form>
      </div>
    </template>
    <template v-else>
      <div class="admin-top-header">
        <div class="admin-top-header-inner">
          <h1>Admin</h1>
          <div class="admin-top-right">
            <button type="button" @click="handleLogout" class="btn btn-ghost">Logout</button>
          </div>
        </div>
        <div class="admin-top-right">
        <button type="button" @click="openAdd" class="btn btn-primary">Add products</button>
        </div>
      </div>
      <div class="admin-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Price</th>
              <th>Stocdasdask</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in products" :key="p.id">
              <td>{{ p.name }}</td>
              <td>₱{{ Number(p.price).toLocaleString() }}</td>
              <td>{{ p.stock }}</td>
              <td>
                <button type="button" @click="openEdit(p)" class="admin-icon-btn admin-icon-btn-edit" title="Edit" aria-label="Edit">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                </button>
                <button type="button" @click="deleteProduct(p.id)" class="admin-icon-btn admin-icon-btn-danger" title="Delete" aria-label="Delete">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="modalProduct !== null" class="admin-modal-overlay" @click.self="requestCloseModal">
        <div class="admin-modal">
          <div class="admin-modal-header">
            <h3 style="margin: 0; font-size: 1.1rem;">{{ isAdd ? 'Add product' : 'Edit product' }}</h3>
            <button type="button" class="admin-modal-close" @click="requestCloseModal" aria-label="Close">×</button>
          </div>
          <form @submit.prevent="saveProduct">
            <div class="admin-modal-body">
              <div class="form-group">
                <label>Name</label>
                <input v-model="form.name" @input="markDirty" required />
              </div>
              <div class="form-group">
                <label>Category</label>
                <select v-model="form.category" @change="markDirty" required>
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
                <textarea v-model="form.description" @input="markDirty" rows="4"></textarea>
              </div>
              <div class="form-row-two">
                <div class="form-group">
                  <label>Price (₱)</label>
                  <input v-model.number="form.price" @input="markDirty" type="number" step="0.01" min="0" required />
                </div>
                <div class="form-group">
                  <label>Stock</label>
                  <input v-model.number="form.stock" @input="markDirty" type="number" min="0" class="stock-input" required />
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
                        aria-label="Remove"
                      >
                        ×
                      </button>
                    </div>
                    <label class="image-upload-add" :class="{ 'image-upload-add-disabled': totalImageCount >= 6 }">
                      <input
                        type="file"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        multiple
                        @change="onImageSelect"
                        :disabled="totalImageCount >= 6"
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
              <button type="button" @click="requestCloseModal" class="btn btn-ghost">Cancel</button>
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const admin = ref(null);
const username = ref('');
const password = ref('');
const loginError = ref('');
const products = ref([]);
const modalProduct = ref(null);
const form = ref({ name: '', category: '', description: '', price: 0, stock: 0, imageUrls: [] });
const imageFileList = ref([]);
const isDirty = ref(false);

const ADMIN_TOKEN_KEY = 'admin_session_token';

function authHeaders() {
  const headers = { };
  const token = sessionStorage.getItem(ADMIN_TOKEN_KEY);
  if (token) headers['Authorization'] = `Bearer ${token}`;
  return headers;
}

const isAdd = computed(() => modalProduct.value === 'add');
const totalImageCount = computed(() => (form.value.imageUrls?.length || 0) + imageFileList.value.length);

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

async function checkAdmin() {
  try {
    const res = await fetch('/api/auth/me', { credentials: 'include', headers: authHeaders() });
    const data = await res.json().catch(() => ({}));
    admin.value = data.admin === true;
  } catch {
    admin.value = false;
  }
}

async function loadProducts() {
  try {
    const res = await fetch('/api/products', { credentials: 'include', headers: authHeaders() });
    const data = await res.json();
    products.value = Array.isArray(data) ? data : [];
  } catch {
    products.value = [];
  }
}

onMounted(() => checkAdmin());
watch(admin, (a) => { if (a) loadProducts(); });

async function handleLogin() {
  loginError.value = '';
  try {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ username: username.value, password: password.value }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      loginError.value = data.error || 'Login failed';
      return;
    }
    if (data.token) sessionStorage.setItem(ADMIN_TOKEN_KEY, data.token);
    admin.value = true;
  } catch {
    loginError.value = 'Network error. Is the server running?';
  }
}

async function handleLogout() {
  try {
    await fetch('/api/auth/logout', { method: 'POST', credentials: 'include', headers: authHeaders() });
  } catch {}
  sessionStorage.removeItem(ADMIN_TOKEN_KEY);
  admin.value = false;
  username.value = '';
  password.value = '';
}

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
  const isAddMode = modalProduct.value === 'add';
  let imageUrls = [...form.value.imageUrls];
  if (imageFileList.value.length > 0) {
    const fd = new FormData();
    imageFileList.value.forEach((file) => fd.append('images[]', file));
    try {
      const up = await fetch('/api/upload', { method: 'POST', credentials: 'include', headers: authHeaders(), body: fd });
      const upData = await up.json().catch(() => ({}));
      if (up.ok && Array.isArray(upData.urls)) imageUrls = imageUrls.concat(upData.urls);
    } catch {
      if (window.Swal) {
        await window.Swal.fire({
          icon: 'error',
          title: 'Upload failed',
          text: 'Image upload failed. Please try again.',
          confirmButtonColor: '#16a34a',
        });
      } else {
        alert('Image upload failed.');
      }
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
      const message = data.error || 'Failed to save product.';
      if (window.Swal) {
        await window.Swal.fire({
          icon: 'error',
          title: 'Save failed',
          text: message,
          confirmButtonColor: '#16a34a',
        });
      } else {
        alert(message);
      }
      return;
    }
    modalProduct.value = null;
    isDirty.value = false;
    await loadProducts();
  } catch {
    if (window.Swal) {
      await window.Swal.fire({
        icon: 'error',
        title: 'Network error',
        text: 'Something went wrong. Please check your connection.',
        confirmButtonColor: '#16a34a',
      });
    } else {
      alert('Network error');
    }
  }
}

async function deleteProduct(id) {
  if (window.Swal) {
    const result = await window.Swal.fire({
      icon: 'warning',
      title: 'Delete product?',
      text: 'This action cannot be undone.',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
    });
    if (!result.isConfirmed) return;
  } else if (!confirm('Delete this product? This cannot be undone.')) {
    return;
  }
  try {
    const res = await fetch(`/api/products/${id}`, { method: 'DELETE', credentials: 'include', headers: authHeaders() });
    if (res.ok) products.value = products.value.filter((p) => p.id !== id);
  } catch {}
}

function markDirty() {
  isDirty.value = true;
}

async function requestCloseModal() {
  if (window.Swal) {
    const result = await window.Swal.fire({
      icon: 'question',
      title: 'Close without saving?',
      text: 'Any unsaved changes to this product will be lost.',
      showCancelButton: true,
      confirmButtonText: 'Close anyway',
      cancelButtonText: 'Continue editing',
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
    });
    if (!result.isConfirmed) return;
  } else if (!confirm('Close without saving this product?')) {
    return;
  }
  modalProduct.value = null;
  isDirty.value = false;
}
</script>
