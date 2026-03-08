<template>
  <div class="min-h-screen">
    <nav class="bg-white shadow border-b border-gray-200">
      <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-6">
          <router-link to="/" class="text-lg font-semibold text-gray-800 hover:text-gray-600">PortuHub</router-link>
          <router-link to="/products" class="text-gray-600 hover:text-gray-900">Products</router-link>
          <router-link to="/admin" class="text-gray-600 hover:text-gray-900">Admin</router-link>
        </div>
      </div>
    </nav>
    <main class="max-w-6xl mx-auto px-4 py-8">
      <router-view />
    </main>
  </div>
</template>

<script setup>
</script>
