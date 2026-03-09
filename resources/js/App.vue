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
        </div>
      </div>
    </nav>
    <main class="main-wrap">
      <router-view />
    </main>
    <footer class="site-footer footer-simple">
      <div class="container footer-inner-simple">
        <!-- <div class="footer-socials-circle">
          <a href="#" class="footer-social-circle" aria-label="Facebook">F</a>
          <a href="#" class="footer-social-circle" aria-label="Instagram">I</a>
          <a href="#" class="footer-social-circle" aria-label="Twitter">T</a>
        </div> -->
        <div class="footer-links-row">
          <a href="#" class="footer-link">Info</a>
          <span>·</span>
          <a href="#" class="footer-link">Support</a>
          <span>·</span>
          <a href="#" class="footer-link">Marketing</a>
        </div>
        <div class="footer-links-row">
          <a href="#" class="footer-link">Terms of Use</a>
          <span>·</span>
          <a href="#" class="footer-link">Privacy Policy</a>
        </div>
        <p class="footer-copy-muted">© 2026 PortuHub</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
</script>
