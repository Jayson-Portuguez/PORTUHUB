<template>
  <div class="layout-wrap">
    <nav class="nav">
      <div class="container nav-inner">
        <router-link to="/" class="logo">
          <img src="/logo.png" alt="PortuHub" class="logo-img" />
        </router-link>
        <div class="nav-links">
          <router-link to="/">Home</router-link>
          <router-link to="/products">Products</router-link>
          <router-link to="/admin">Admin</router-link>
        </div>
      </div>
    </nav>
    <main class="main-wrap">
      <router-view />
    </main>
    <footer class="site-footer">
      <div class="container footer-inner">
        <div class="footer-brand">
          <router-link to="/" class="footer-logo">
            <img src="/logo.png" alt="PortuHub" class="footer-logo-img" />
          </router-link>
          <p class="footer-tagline">Simple shopping, fast delivery</p>
          <p class="footer-copy">© 2026 PortuHub. All rights reserved.</p>
        </div>
        <div class="footer-links">
          <router-link to="/">Home</router-link>
          <router-link to="/products">Products</router-link>
          <router-link to="/admin">Admin</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
</script>
