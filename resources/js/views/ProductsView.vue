<template>
  <div class="container" style="padding-bottom: 3rem;">
    <h1 class="section-title">Products</h1>
    <div v-if="loading" style="color: #737373;">Loading...</div>
    <div v-else class="card-grid">
      <div
        v-for="p in products"
        :key="p.id"
        class="product-card product-card-clickable"
        @click="openZoom(p)"
      >
        <img
          :src="(p.imageUrls && p.imageUrls[0]) || '/placeholder.svg'"
          :alt="p.name"
        />
        <div class="product-card-body">
          <h3>{{ p.name }}</h3>
          <p class="desc">{{ p.description || '—' }}</p>
          <p class="price">₱{{ Number(p.price).toLocaleString() }}</p>
          <p class="stock">Stock: {{ p.stock }}</p>
        </div>
      </div>
    </div>
    <div v-if="zoomProduct" class="product-zoom-overlay" @click.self="zoomProduct = null">
      <div class="product-zoom-modal">
        <button type="button" class="product-zoom-close" @click="zoomProduct = null" aria-label="Close">×</button>
        <div class="product-zoom-content">
          <div class="product-zoom-images">
            <template v-if="zoomImages.length > 1">
              <button type="button" class="product-carousel-btn product-carousel-prev" @click="zoomIndex = (zoomIndex - 1 + zoomImages.length) % zoomImages.length" aria-label="Previous">‹</button>
              <img :src="zoomImages[zoomIndex]" :alt="zoomProduct.name" class="product-zoom-img" />
              <button type="button" class="product-carousel-btn product-carousel-next" @click="zoomIndex = (zoomIndex + 1) % zoomImages.length" aria-label="Next">›</button>
              <div class="product-carousel-dots">
                <button
                  v-for="(_, i) in zoomImages"
                  :key="i"
                  type="button"
                  class="product-carousel-dot"
                  :class="{ active: i === zoomIndex }"
                  :aria-label="'Image ' + (i + 1)"
                  @click="zoomIndex = i"
                />
              </div>
            </template>
            <img v-else :src="zoomImages[0] || '/placeholder.svg'" :alt="zoomProduct.name" class="product-zoom-img" />
          </div>
          <div class="product-zoom-info">
            <h2 class="product-zoom-name">{{ zoomProduct.name }}</h2>
            <p class="product-zoom-price">₱{{ Number(zoomProduct.price).toLocaleString() }}</p>
            <p class="product-zoom-stock">Stock: {{ zoomProduct.stock }}</p>
            <p class="product-zoom-desc">{{ zoomProduct.description || 'No description.' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const products = ref([]);
const loading = ref(true);
const zoomProduct = ref(null);
const zoomIndex = ref(0);

const zoomImages = computed(() => {
  if (!zoomProduct.value || !zoomProduct.value.imageUrls || !zoomProduct.value.imageUrls.length) {
    return ['/placeholder.svg'];
  }
  return zoomProduct.value.imageUrls;
});

function openZoom(p) {
  zoomProduct.value = p;
  zoomIndex.value = 0;
}

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
  if (route.hash) {
    const id = route.hash.slice(1);
    const p = products.value.find((x) => x.id === id);
    if (p) openZoom(p);
  }
});

watch(() => route.hash, (h) => {
  if (!h) return;
  const id = h.slice(1);
  const p = products.value.find((x) => x.id === id);
  if (p) openZoom(p);
});
</script>
