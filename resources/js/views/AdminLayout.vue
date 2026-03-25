<template>
  <div class="admin-app">
    <template v-if="admin === null">
      <div class="admin-app__checking">Checking…</div>
    </template>
    <template v-else-if="!admin">
      <div class="admin-login-page">
        <div class="admin-login-body">
          <div class="login-box admin-login-box">
            <RouterLink to="/" class="admin-login-logo-link" aria-label="PortuHub home">
              <img src="/logo.png" alt="" class="admin-login-logo" />
            </RouterLink>
            <h1>Admin login</h1>
            <form
              class="admin-login-form"
              :class="{ 'admin-login-form--busy': loginSubmitting }"
              :aria-busy="loginSubmitting"
              @submit.prevent="handleLogin"
            >
              <div class="form-group">
                <label>Username</label>
                <input v-model="username" type="text" required :disabled="loginSubmitting" autocomplete="username" />
              </div>
              <div class="form-group">
                <label>Password</label>
                <input v-model="password" type="password" required :disabled="loginSubmitting" autocomplete="current-password" />
              </div>
              <button type="submit" class="btn btn-primary admin-login-submit" style="width: 100%;" :disabled="loginSubmitting">
                <span v-if="loginSubmitting" class="admin-login-submit__loading">Signing in…</span>
                <span v-else>Log in</span>
              </button>
            </form>
            <p class="admin-login-home-link-wrap">
              <RouterLink to="/" class="admin-login-home-link">Go home</RouterLink>
            </p>
          </div>
        </div>
      </div>
    </template>
    <template v-else>
      <div class="admin-shell">
        <div
          v-if="navOpen"
          class="admin-sidenav-backdrop"
          aria-hidden="true"
          @click="navOpen = false"
        />
        <aside
          class="admin-sidenav"
          :class="{ 'admin-sidenav--open': navOpen }"
          aria-label="Admin navigation"
        >
          <div class="admin-sidenav__inner">
            <div class="admin-sidenav__brand">
              <span class="admin-sidenav__brand-title">PortuHub</span>
              <span class="admin-sidenav__brand-badge">Admin</span>
            </div>
            <nav class="admin-sidenav__links">
              <RouterLink
                to="/admin/products"
                class="admin-sidenav__link"
                active-class="admin-sidenav__link--active"
                @click="closeNavIfMobile"
              >
                Products
              </RouterLink>
              <RouterLink
                to="/admin/activity"
                class="admin-sidenav__link"
                active-class="admin-sidenav__link--active"
                @click="closeNavIfMobile"
              >
                Activity history
              </RouterLink>
              <RouterLink
                to="/admin/landing"
                class="admin-sidenav__link"
                active-class="admin-sidenav__link--active"
                @click="closeNavIfMobile"
              >
                Landing page
              </RouterLink>
            </nav>
            <div class="admin-sidenav__footer">
              <button
                type="button"
                class="btn btn-ghost admin-sidenav__logout"
                :disabled="logoutSubmitting"
                @click="handleLogout"
              >
                {{ logoutSubmitting ? 'Signing out…' : 'Log out' }}
              </button>
            </div>
          </div>
        </aside>
        <div class="admin-shell__main">
          <header class="admin-shell__topbar">
            <button
              type="button"
              class="admin-shell__menu-btn"
              aria-label="Open menu"
              :aria-expanded="navOpen"
              @click="navOpen = !navOpen"
            >
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
            <h1 class="admin-shell__title">{{ pageTitle }}</h1>
          </header>
          <div class="admin-shell__body">
            <RouterView />
          </div>
        </div>
      </div>
    </template>
    <Teleport to="body">
      <div class="admin-toast-host" aria-live="polite" aria-relevant="additions text">
        <TransitionGroup name="admin-toast" tag="div" class="admin-toast-stack">
          <div
            v-for="t in toasts"
            :key="t.id"
            :class="['admin-toast', 'admin-toast--' + t.tone]"
            role="status"
          >
            <p class="admin-toast__title">{{ t.title }}</p>
            <p v-if="t.text" class="admin-toast__text">{{ t.text }}</p>
          </div>
        </TransitionGroup>
      </div>
    </Teleport>
    <Teleport to="body">
      <div
        v-if="confirmDialog.open"
        class="admin-confirm-overlay"
        role="dialog"
        aria-modal="true"
        aria-labelledby="admin-confirm-title"
        @click.self="onConfirmSecondary"
      >
        <div class="admin-confirm-card" @click.stop>
          <h2 id="admin-confirm-title" class="admin-confirm-title">{{ confirmDialog.title }}</h2>
          <p v-if="confirmDialog.text" class="admin-confirm-text">{{ confirmDialog.text }}</p>
          <div class="admin-confirm-actions">
            <button type="button" class="btn btn-ghost admin-confirm-btn" @click="onConfirmSecondary">
              {{ confirmDialog.secondaryLabel }}
            </button>
            <button
              type="button"
              class="btn btn-primary admin-confirm-btn"
              :class="{ 'admin-confirm-btn--danger': confirmDialog.primaryDanger }"
              @click="onConfirmPrimary"
            >
              {{ confirmDialog.primaryLabel }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, provide, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ADMIN_TOKEN_KEY, authHeaders } from '../admin/auth.js';

const route = useRoute();
const router = useRouter();

const admin = ref(null);
const username = ref('');
const password = ref('');
const loginSubmitting = ref(false);
const logoutSubmitting = ref(false);
const navOpen = ref(false);

const pageTitle = computed(() => (route.meta.title ? String(route.meta.title) : 'Admin'));

const toasts = ref([]);
let toastSeq = 0;

function dismissToast(id) {
  toasts.value = toasts.value.filter((x) => x.id !== id);
}

/** Auto-dismissing message (no buttons). */
function showAlert({ tone = 'success', title, text = '' }) {
  const id = ++toastSeq;
  const extra = text ? Math.min(3500, Math.floor(text.length / 8) * 200) : 0;
  const duration = tone === 'error' ? 5200 + extra : 3600 + extra;
  toasts.value = [...toasts.value, { id, tone, title, text }];
  window.setTimeout(() => dismissToast(id), duration);
  return Promise.resolve();
}

const confirmDialog = ref({
  open: false,
  tone: 'warning',
  title: '',
  text: '',
  primaryLabel: 'OK',
  secondaryLabel: 'Cancel',
  primaryDanger: false,
});

let pendingConfirmResolve = null;

function showConfirm({
  tone = 'warning',
  title,
  text = '',
  primaryLabel = 'OK',
  secondaryLabel = 'Cancel',
  primaryDanger = false,
}) {
  return new Promise((resolve) => {
    pendingConfirmResolve = resolve;
    confirmDialog.value = {
      open: true,
      tone,
      title,
      text,
      primaryLabel,
      secondaryLabel,
      primaryDanger,
    };
  });
}

function onConfirmPrimary() {
  const r = pendingConfirmResolve;
  pendingConfirmResolve = null;
  confirmDialog.value.open = false;
  r?.(true);
}

function onConfirmSecondary() {
  const r = pendingConfirmResolve;
  pendingConfirmResolve = null;
  confirmDialog.value.open = false;
  r?.(false);
}

provide('adminNotify', { showAlert, showConfirm });

function closeNavIfMobile() {
  if (typeof window !== 'undefined' && window.innerWidth < 1024) {
    navOpen.value = false;
  }
}

watch(() => route.path, () => {
  closeNavIfMobile();
});

async function checkAdmin() {
  try {
    const res = await fetch('/api/auth/me', { credentials: 'include', headers: authHeaders() });
    const data = await res.json().catch(() => ({}));
    admin.value = data.admin === true;
  } catch {
    admin.value = false;
  }
}

onMounted(() => checkAdmin());

async function handleLogin() {
  if (loginSubmitting.value) return;
  loginSubmitting.value = true;
  try {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ username: username.value, password: password.value }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      await showAlert({
        tone: 'error',
        title: 'Login failed',
        text: data.error || 'Invalid username or password.',
      });
      return;
    }
    if (data.token) sessionStorage.setItem(ADMIN_TOKEN_KEY, data.token);
    admin.value = true;
    if (route.path === '/admin') {
      await router.replace('/admin/products');
    }
    const who = username.value?.trim() || 'there';
    await showAlert({
      tone: 'success',
      title: 'Welcome',
      text: `Hello, ${who}. You're signed in to the admin panel.`,
    });
  } catch {
    await showAlert({
      tone: 'error',
      title: 'Network error',
      text: 'Could not reach the server. Is it running?',
    });
  } finally {
    loginSubmitting.value = false;
  }
}

async function handleLogout() {
  if (logoutSubmitting.value) return;
  const confirmed = await showConfirm({
    tone: 'info',
    title: 'Log out?',
    text: 'You will need to sign in again to manage products.',
    primaryLabel: 'Yes, log out',
    secondaryLabel: 'Cancel',
    primaryDanger: false,
  });
  if (!confirmed) return;

  logoutSubmitting.value = true;
  try {
    try {
      await fetch('/api/auth/logout', { method: 'POST', credentials: 'include', headers: authHeaders() });
    } catch {}
    sessionStorage.removeItem(ADMIN_TOKEN_KEY);
    admin.value = false;
    username.value = '';
    password.value = '';
    navOpen.value = false;
    await router.replace('/admin');
  } finally {
    logoutSubmitting.value = false;
  }
}
</script>
