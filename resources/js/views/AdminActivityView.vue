<template>
  <div class="admin-page admin-activity">
    <p v-if="loadError" class="admin-activity__error">{{ loadError }}</p>
    <p v-else-if="loading && items.length === 0" class="admin-activity__loading">Loading…</p>
    <p v-else-if="!loading && items.length === 0" class="admin-activity__empty">No activity yet. Product changes will appear here.</p>
    <template v-else>
      <div class="admin-table-wrap admin-activity__table">
        <table>
          <thead>
            <tr>
              <th>When</th>
              <th>Action</th>
              <th>Product</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in items" :key="row.id">
              <td class="admin-activity__time">{{ formatWhen(row.createdAt) }}</td>
              <td>
                <span class="admin-activity__badge" :class="'admin-activity__badge--' + badgeClass(row.action)">
                  {{ actionLabel(row.action) }}
                </span>
              </td>
              <td>{{ row.productName || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <ul class="admin-activity__cards" role="list">
        <li v-for="row in items" :key="'c-' + row.id" class="admin-activity-card">
          <div class="admin-activity-card__row">
            <span class="admin-activity-card__label">Action</span>
            <span class="admin-activity__badge" :class="'admin-activity__badge--' + badgeClass(row.action)">
              {{ actionLabel(row.action) }}
            </span>
          </div>
          <div class="admin-activity-card__row">
            <span class="admin-activity-card__label">Product</span>
            <span class="admin-activity-card__value">{{ row.productName || '—' }}</span>
          </div>
          <div class="admin-activity-card__meta">{{ formatWhen(row.createdAt) }}</div>
        </li>
      </ul>
      <nav
        v-if="pagination && pagination.lastPage > 1"
        class="admin-pagination"
        aria-label="Activity pages"
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
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { authHeaders } from '../admin/auth.js';

const ACTION_LABELS = {
  product_created: 'Added',
  product_updated: 'Updated',
  product_deleted: 'Deleted',
};

const items = ref([]);
const loading = ref(true);
const loadError = ref('');
const currentPage = ref(1);
const pagination = ref(null);
const PER_PAGE = 15;

const paginationSummary = computed(() => {
  const pg = pagination.value;
  if (!pg || !pg.total) return '';
  const from = pg.from != null ? pg.from : 0;
  const to = pg.to != null ? pg.to : 0;
  return `Showing ${from}–${to} of ${pg.total}`;
});

function actionLabel(action) {
  return ACTION_LABELS[action] || action || '—';
}

function badgeClass(action) {
  if (action === 'product_deleted') return 'deleted';
  if (action === 'product_created') return 'created';
  if (action === 'product_updated') return 'updated';
  return 'other';
}

function formatWhen(iso) {
  if (!iso) return '—';
  try {
    const d = new Date(iso);
    return new Intl.DateTimeFormat(undefined, {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(d);
  } catch {
    return iso;
  }
}

async function loadPage(page) {
  loading.value = true;
  loadError.value = '';
  currentPage.value = Math.max(1, page);
  try {
    const params = new URLSearchParams({
      page: String(currentPage.value),
      per_page: String(PER_PAGE),
    });
    const res = await fetch(`/api/admin/activity?${params}`, {
      credentials: 'include',
      headers: authHeaders(),
    });
    const data = await res.json().catch(() => ({}));
    if (res.status === 401) {
      loadError.value = 'You need to be signed in as admin to view activity.';
      items.value = [];
      pagination.value = null;
      return;
    }
    if (!res.ok) {
      loadError.value = data.error || data.message || 'Could not load activity.';
      items.value = [];
      pagination.value = null;
      return;
    }
    if (Array.isArray(data.data)) {
      items.value = data.data;
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
        await loadPage(currentPage.value);
      }
      return;
    }
    items.value = [];
    pagination.value = null;
  } catch {
    loadError.value = 'Network error. Please try again.';
    items.value = [];
    pagination.value = null;
  } finally {
    loading.value = false;
  }
}

function goToPage(page) {
  if (!pagination.value) return;
  if (page < 1 || page > pagination.value.lastPage) return;
  loadPage(page);
}

onMounted(() => loadPage(1));
</script>
