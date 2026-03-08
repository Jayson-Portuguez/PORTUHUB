<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Products</h1>
    <div v-if="loading" class="text-gray-500">Loading...</div>
    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="p in products"
        :key="p.id"
        class="border border-gray-200 rounded-lg overflow-hidden bg-white hover:shadow-md cursor-pointer"
        :class="{ 'ring-2 ring-green-500': selectedId === p.id }"
        @click="selectedId = p.id"
      >
        <img
          :src="(p.imageUrls && p.imageUrls[0]) || '/placeholder.png'"
          :alt="p.name"
          class="w-full h-48 object-cover"
        />
        <div class="p-3">
          <h3 class="font-medium text-gray-900">{{ p.name }}</h3>
          <p class="text-green-600 font-semibold">₱{{ Number(p.price).toLocaleString() }}</p>
        </div>
      </div>
    </div>
    <div v-if="selectedProduct" class="mt-8 p-6 bg-white border border-gray-200 rounded-lg">
      <h2 class="text-xl font-semibold mb-2">{{ selectedProduct.name }}</h2>
      <p class="text-gray-600 mb-4">{{ selectedProduct.description }}</p>
      <p class="text-green-600 font-bold text-lg">₱{{ Number(selectedProduct.price).toLocaleString() }}</p>
      <p class="text-sm text-gray-500">Stock: {{ selectedProduct.stock }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const products = ref([]);
const loading = ref(true);
const selectedId = ref('');

onMounted(async () => {
  try {
    const res = await fetch('/api/products', { credentials: 'include' });
    const data = await res.json();
    products.value = Array.isArray(data) ? data : [];
  } catch {
    products.value = [];
  } finally {
    loading.value = false;
  }
  if (route.hash) selectedId.value = route.hash.slice(1);
});

watch(() => route.hash, (h) => { if (h) selectedId.value = h.slice(1); });

const selectedProduct = computed(() =>
  products.value.find((p) => p.id === selectedId.value) || null
);
</script>
