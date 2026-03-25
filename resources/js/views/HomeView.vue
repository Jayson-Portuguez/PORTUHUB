<template>
  <div class="container home-page">
    <section class="home-hero">
      <h1 class="home-hero-title">{{ landing.heroHeadline }}</h1>
      <div class="home-hero-meta">
        <span class="home-accent-bar" aria-hidden="true" />
        <p class="home-hero-body">{{ landing.heroBody }}</p>
      </div>
      <div class="home-hero-image-wrap">
        <img
          :src="landing.heroImageUrl || '/placeholder.svg'"
          alt=""
          class="home-hero-image"
          width="1200"
          height="800"
          loading="eager"
        />
      </div>
    </section>

    <section class="home-feature" id="about">
      <div class="home-feature-inner">
        <div class="home-feature-copy">
          <p class="home-feature-kicker">
            <span class="home-accent-dot" aria-hidden="true" />
            {{ landing.featureKicker }}
          </p>
          <p class="home-feature-title">{{ landing.featureTitle }}</p>
          <a
            v-if="ctaIsExternal"
            :href="landing.featureCtaHref"
            class="btn btn-primary"
            target="_blank"
            rel="noopener noreferrer"
          >
            {{ landing.featureCtaLabel || 'View all products' }}
          </a>
          <RouterLink v-else :to="landing.featureCtaHref || '/products'" class="btn btn-primary">
            {{ landing.featureCtaLabel || 'View all products' }}
          </RouterLink>
        </div>
        <div class="home-feature-visual">
          <p v-if="landing.featureCaptionRight" class="home-feature-caption">{{ landing.featureCaptionRight }}</p>
          <div class="home-feature-img-wrap">
            <img
              :src="featureSecondarySrc"
              alt=""
              class="home-feature-image"
              width="800"
              height="900"
              loading="lazy"
            />
          </div>
        </div>
      </div>
    </section>

    <section class="new-arrivals-section home-arrivals">
      <h2 class="section-title">New arrivals</h2>
      <p class="home-arrivals-hint">Latest additions — open a card to view details on the products page.</p>
      <div v-if="loading" style="color: #737373;">Loading...</div>
      <div v-else-if="newItems.length === 0" style="color: #525252; font-size: 0.95rem;">No products yet. Add some in Admin.</div>
      <div v-else class="carousel">
        <div class="carousel-track">
          <router-link
            v-for="p in newItems"
            :key="p.id"
            :to="{ path: '/products', hash: '#' + p.id }"
            class="carousel-item"
          >
            <img
              :src="(p.imageUrls && p.imageUrls[0]) || '/placeholder.svg'"
              :alt="p.name"
            />
            <div class="carousel-item-content">
              <h3>{{ p.name }}</h3>
              <p v-if="p.category" style="font-size: 0.8rem; color: #737373; margin-top: 0.15rem;">
                {{ p.category }}
              </p>
              <p class="price">₱{{ Number(p.price).toLocaleString() }}</p>
            </div>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const defaults = {
  heroHeadline: 'Welcome to PortuHub — knives & tools you can trust',
  heroBody: '',
  heroImageUrl: null,
  featureKicker: '',
  featureTitle: '',
  featureCtaLabel: 'View all products',
  featureCtaHref: '/products',
  featureImageUrl: null,
  featureCaptionRight: '',
};

const landing = ref({ ...defaults });
const newItems = ref([]);
const loading = ref(true);

const featureSecondarySrc = computed(() => {
  const u = landing.value.featureImageUrl;
  if (u) return u;
  if (landing.value.heroImageUrl) return landing.value.heroImageUrl;
  return '/placeholder.svg';
});

const ctaIsExternal = computed(() => /^https?:\/\//i.test(String(landing.value.featureCtaHref || '')));

onMounted(async () => {
  try {
    const [lr, pr] = await Promise.all([
      fetch('/api/landing', { credentials: 'include' }).then((r) => r.json()),
      fetch('/api/products/new', { credentials: 'include' }).then((r) => r.json()),
    ]);
    if (lr && typeof lr === 'object') {
      landing.value = {
        heroHeadline: lr.heroHeadline ?? defaults.heroHeadline,
        heroBody: lr.heroBody ?? '',
        heroImageUrl: lr.heroImageUrl ?? null,
        featureKicker: lr.featureKicker ?? '',
        featureTitle: lr.featureTitle ?? '',
        featureCtaLabel: lr.featureCtaLabel ?? 'View all products',
        featureCtaHref: lr.featureCtaHref || '/products',
        featureImageUrl: lr.featureImageUrl ?? null,
        featureCaptionRight: lr.featureCaptionRight ?? '',
      };
    }
    newItems.value = Array.isArray(pr) ? pr : [];
  } catch {
    newItems.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
