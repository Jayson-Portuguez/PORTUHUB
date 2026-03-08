<template>
  <div>
    <section class="mb-12">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome to PortuHub</h1>
      <p class="text-gray-600 mb-6">Discover the latest products. Simple shopping, fast delivery.</p>
      <router-link
        to="/products"
        class="inline-block px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700"
      >
        View all products
      </router-link>
    </section>
    <section>
      <h2 class="text-xl font-semibold mb-4">New arrivals</h2>
      <div v-if="loading" class="text-gray-500">Loading...</div>
      <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="p in newItems"
          :key="p.id"
          class="border border-gray-200 rounded-lg overflow-hidden bg-white hover:shadow-md"
        >
          <router-link :to="`/products#${p.id}`">
            <img
              :src="(p.imageUrls && p.imageUrls[0]) || '/placeholder.png'"
              :alt="p.name"
              class="w-full h-40 object-cover"
            />
            <div class="p-3">
              <h3 class="font-medium text-gray-900 truncate">{{ p.name }}</h3>
              <p class="text-green-600 font-semibold">₱{{ Number(p.price).toLocaleString() }}</p>
            </div>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const newItems = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const res = await fetch('/api/products/new', { credentials: 'include' });
    const data = await res.json();
    newItems.value = Array.isArray(data) ? data : [];
  } catch {
    newItems.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
