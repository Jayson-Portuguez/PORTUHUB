<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Admin</h1>
    <template v-if="admin === null">
      <div class="text-gray-500">Checking...</div>
    </template>
    <template v-else-if="!admin">
      <div class="max-w-md bg-white border border-gray-200 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Admin login</h2>
        <div v-if="loginError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
          {{ loginError }}
        </div>
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input
              v-model="username"
              type="text"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
              required
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
              v-model="password"
              type="password"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
              required
            />
          </div>
          <button
            type="submit"
            class="w-full py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700"
          >
            Log in
          </button>
        </form>
      </div>
    </template>
    <template v-else>
      <div class="flex justify-end mb-4">
        <button
          @click="handleLogout"
          class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          Logout
        </button>
      </div>
      <div class="mb-4">
        <button
          @click="openAdd"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
        >
          Add product
        </button>
      </div>
      <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left p-3 font-medium">Name</th>
              <th class="text-left p-3 font-medium">Price</th>
              <th class="text-left p-3 font-medium">Stock</th>
              <th class="p-3"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="p in products"
              :key="p.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="p-3">{{ p.name }}</td>
              <td class="p-3">₱{{ Number(p.price).toLocaleString() }}</td>
              <td class="p-3">{{ p.stock }}</td>
              <td class="p-3 flex gap-2">
                <button
                  @click="openEdit(p)"
                  class="text-blue-600 hover:underline"
                >
                  Edit
                </button>
                <button
                  @click="deleteProduct(p.id)"
                  class="text-red-600 hover:underline"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div
        v-if="modalProduct !== null"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-10"
        @click.self="modalProduct = null"
      >
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 p-6">
          <h3 class="text-lg font-semibold mb-4">{{ isAdd ? 'Add product' : 'Edit product' }}</h3>
          <form @submit.prevent="saveProduct" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Name</label>
              <input v-model="form.name" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Description</label>
              <textarea v-model="form.description" class="w-full border rounded px-3 py-2" rows="3"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Price (₱)</label>
              <input v-model.number="form.price" type="number" step="0.01" min="0" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Stock</label>
              <input v-model.number="form.stock" type="number" min="0" class="w-full border rounded px-3 py-2" required />
            </div>
            <div class="flex gap-2 justify-end pt-4">
              <button type="button" @click="modalProduct = null" class="px-4 py-2 border rounded-lg">Cancel</button>
              <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Save</button>
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
const form = ref({ name: '', description: '', price: 0, stock: 0 });

const isAdd = computed(() => modalProduct.value === 'add');

async function checkAdmin() {
  try {
    const res = await fetch('/api/auth/me', { credentials: 'include' });
    const data = await res.json().catch(() => ({}));
    admin.value = data.admin === true;
  } catch {
    admin.value = false;
  }
}

async function loadProducts() {
  try {
    const res = await fetch('/api/products', { credentials: 'include' });
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
    admin.value = true;
  } catch {
    loginError.value = 'Network error. Is the server running?';
  }
}

async function handleLogout() {
  try {
    await fetch('/api/auth/logout', { method: 'POST', credentials: 'include' });
  } catch {}
  admin.value = false;
  username.value = '';
  password.value = '';
}

function openAdd() {
  modalProduct.value = 'add';
  form.value = { name: '', description: '', price: 0, stock: 0 };
}

function openEdit(p) {
  modalProduct.value = p;
  form.value = {
    name: p.name,
    description: p.description || '',
    price: p.price,
    stock: p.stock,
  };
}

async function saveProduct() {
  const isAddMode = modalProduct.value === 'add';
  const url = isAddMode ? '/api/products' : `/api/products/${modalProduct.value.id}`;
  const method = isAddMode ? 'POST' : 'PATCH';
  try {
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({
        name: form.value.name,
        description: form.value.description,
        price: form.value.price,
        stock: form.value.stock,
        imageUrls: ['/placeholder.png'],
      }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      alert(data.error || 'Failed to save');
      return;
    }
    modalProduct.value = null;
    await loadProducts();
  } catch {
    alert('Network error');
  }
}

async function deleteProduct(id) {
  if (!confirm('Delete this product?')) return;
  try {
    const res = await fetch(`/api/products/${id}`, { method: 'DELETE', credentials: 'include' });
    if (res.ok) products.value = products.value.filter((p) => p.id !== id);
  } catch {}
}
</script>
