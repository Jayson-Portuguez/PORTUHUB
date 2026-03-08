<template>
  <div class="container" style="padding-bottom: 3rem;">
    <section class="hero">
      <h1>Welcome to PortuHub</h1>
      <p>Discover the latest products. Simple shopping, fast delivery.</p>
      <router-link to="/products" class="btn btn-primary">View all products</router-link>
    </section>
    <section class="new-arrivals-section">
      <h2 class="section-title">New arrivals</h2>
      <div v-if="loading" style="color: #737373;">Loading...</div>
      <div v-else-if="newItems.length === 0" style="color: #525252; font-size: 0.95rem;">No products yet. Add some in Admin.</div>
      <div v-else class="carousel">
        <div class="carousel-track">
          <router-link
            v-for="p in newItems"
            :key="p.id"
            :to="`/products#${p.id}`"
            class="carousel-item"
          >
            <img
              :src="(p.imageUrls && p.imageUrls[0]) || '/placeholder.svg'"
              :alt="p.name"
            />
            <div class="carousel-item-content">
              <h3>{{ p.name }}</h3>
              <p class="price">₱{{ Number(p.price).toLocaleString() }}</p>
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
