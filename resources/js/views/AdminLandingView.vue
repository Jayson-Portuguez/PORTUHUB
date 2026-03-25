<template>
  <div class="admin-page admin-landing-edit">
    <p v-if="loadError" class="admin-landing-edit__error">{{ loadError }}</p>
    <form v-else class="admin-landing-form" @submit.prevent="save">
      <div class="admin-table-wrap" style="padding: 1.25rem;">
        <h2 class="admin-landing-form__h">Hero</h2>
        <div class="form-group">
          <label>Headline</label>
          <input v-model="form.heroHeadline" required maxlength="500" />
        </div>
        <div class="form-group">
          <label>Inspiration / body (beside red dot)</label>
          <textarea v-model="form.heroBody" rows="3" maxlength="5000" />
        </div>
        <div class="form-group">
          <label>Hero image</label>
          <p class="field-help">Upload a photo — it is stored in the database with the landing content (no server folder).</p>
          <div v-if="form.heroImageUrl" class="admin-landing-preview">
            <img :src="form.heroImageUrl" alt="" />
          </div>
          <input type="file" accept="image/*" class="admin-landing-file" @change="onHeroFile" />
        </div>

        <h2 class="admin-landing-form__h">Feature band</h2>
        <div class="form-group">
          <label>Kicker (e.g. CRAFTED FOR…)</label>
          <input v-model="form.featureKicker" maxlength="500" />
        </div>
        <div class="form-group">
          <label>Title / description</label>
          <textarea v-model="form.featureTitle" rows="3" maxlength="5000" />
        </div>
        <div class="form-row-two">
          <div class="form-group">
            <label>CTA label</label>
            <input v-model="form.featureCtaLabel" maxlength="200" />
          </div>
          <div class="form-group">
            <label>CTA link</label>
            <input v-model="form.featureCtaHref" maxlength="500" placeholder="/products" />
          </div>
        </div>
        <div class="form-group">
          <label>Caption above secondary image (right)</label>
          <input v-model="form.featureCaptionRight" maxlength="500" />
        </div>
        <div class="form-group">
          <label>Secondary image (optional)</label>
          <p class="field-help">Optional second photo, stored in the database. If empty, the hero image is reused.</p>
          <div v-if="form.featureImageUrl" class="admin-landing-preview">
            <img :src="form.featureImageUrl" alt="" />
          </div>
          <input type="file" accept="image/*" class="admin-landing-file" @change="onFeatureFile" />
        </div>

        <div class="admin-landing-form__actions">
          <button type="submit" class="btn btn-primary" :disabled="saving">
            {{ saving ? 'Saving…' : 'Save landing page' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import { authHeaders } from '../admin/auth.js';

const { showAlert, showConfirm } = inject('adminNotify');

const form = ref({
  heroHeadline: '',
  heroBody: '',
  heroImageUrl: '',
  featureKicker: '',
  featureTitle: '',
  featureCtaLabel: 'View all products',
  featureCtaHref: '/products',
  featureImageUrl: '',
  featureCaptionRight: '',
});

const loadError = ref('');
const saving = ref(false);

function formatApiError(data, res, verb = 'Request failed') {
  if (data?.errors && typeof data.errors === 'object') {
    const msgs = Object.values(data.errors)
      .flat()
      .filter(Boolean);
    if (msgs.length) return msgs.join(' ');
  }
  if (res.status === 401) {
    return data?.message || data?.error || 'Session expired. Open the admin menu and sign in again.';
  }
  return data?.error || data?.message || `${verb} (${res.status}).`;
}

async function load() {
  loadError.value = '';
  try {
    const res = await fetch('/api/landing', { credentials: 'include' });
    const d = await res.json().catch(() => ({}));
    if (!res.ok) {
      loadError.value = 'Could not load landing settings.';
      return;
    }
    form.value = {
      heroHeadline: d.heroHeadline ?? '',
      heroBody: d.heroBody ?? '',
      heroImageUrl: d.heroImageUrl ?? '',
      featureKicker: d.featureKicker ?? '',
      featureTitle: d.featureTitle ?? '',
      featureCtaLabel: d.featureCtaLabel ?? 'View all products',
      featureCtaHref: d.featureCtaHref || '/products',
      featureImageUrl: d.featureImageUrl ?? '',
      featureCaptionRight: d.featureCaptionRight ?? '',
    };
  } catch {
    loadError.value = 'Network error loading landing page.';
  }
}

async function uploadFile(file) {
  const fd = new FormData();
  fd.append('images[]', file);
  const res = await fetch('/api/upload', {
    method: 'POST',
    credentials: 'include',
    headers: authHeaders(),
    body: fd,
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok || !Array.isArray(data.urls) || !data.urls[0]) {
    throw new Error(data.error || data.message || 'Upload failed');
  }
  return data.urls[0];
}

async function onHeroFile(e) {
  const f = e.target.files?.[0];
  e.target.value = '';
  if (!f) return;
  try {
    form.value.heroImageUrl = await uploadFile(f);
    await showAlert({ tone: 'success', title: 'Hero image updated', text: 'Save the form to publish.' });
  } catch (err) {
    await showAlert({ tone: 'error', title: 'Upload failed', text: String(err.message || err) });
  }
}

async function onFeatureFile(e) {
  const f = e.target.files?.[0];
  e.target.value = '';
  if (!f) return;
  try {
    form.value.featureImageUrl = await uploadFile(f);
    await showAlert({ tone: 'success', title: 'Feature image updated', text: 'Save the form to publish.' });
  } catch (err) {
    await showAlert({ tone: 'error', title: 'Upload failed', text: String(err.message || err) });
  }
}

async function save() {
  if (saving.value) return;
  const confirmed = await showConfirm({
    tone: 'info',
    title: 'Save landing page?',
    text: 'This will update the storefront home page for all visitors.',
    primaryLabel: 'Yes, save',
    secondaryLabel: 'Cancel',
    primaryDanger: false,
  });
  if (!confirmed) return;

  saving.value = true;
  try {
    const res = await fetch('/api/admin/landing', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...authHeaders(),
      },
      credentials: 'include',
      body: JSON.stringify({
        heroHeadline: form.value.heroHeadline,
        heroBody: form.value.heroBody,
        heroImageUrl: form.value.heroImageUrl || null,
        featureKicker: form.value.featureKicker,
        featureTitle: form.value.featureTitle,
        featureCtaLabel: form.value.featureCtaLabel,
        featureCtaHref: form.value.featureCtaHref,
        featureImageUrl: form.value.featureImageUrl || null,
        featureCaptionRight: form.value.featureCaptionRight,
      }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      await showAlert({
        tone: 'error',
        title: 'Save failed',
        text: formatApiError(data, res, 'Could not save'),
      });
      return;
    }
    form.value.heroImageUrl = data.heroImageUrl || '';
    form.value.featureImageUrl = data.featureImageUrl || '';
    await showAlert({ tone: 'success', title: 'Saved', text: 'The storefront home page was updated.' });
  } catch {
    await showAlert({ tone: 'error', title: 'Network error', text: 'Try again.' });
  } finally {
    saving.value = false;
  }
}

onMounted(() => load());
</script>

<style scoped>
.admin-landing-form__h {
  margin: 1.5rem 0 0.75rem;
  font-size: 1rem;
  font-weight: 700;
  color: #0a0a0a;
}
.admin-landing-form__h:first-child {
  margin-top: 0;
}
.admin-landing-preview {
  max-width: 320px;
  margin-bottom: 0.5rem;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #e5e5e5;
}
.admin-landing-preview img {
  width: 100%;
  display: block;
  aspect-ratio: 4/3;
  object-fit: cover;
}
.admin-landing-file {
  font-size: 0.875rem;
}
.admin-landing-form__actions {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e5e5;
}
.admin-landing-edit__error {
  color: #b91c1c;
}
</style>
