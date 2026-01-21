<template>
  <div id="app">
    <div id="sidebar">
      <Sidebar />
    </div>

    <div id="main">
      <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
          <i class="bi bi-justify fs-3"></i>
        </a>
      </header>

      <slot />

      <Footer />
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/Components/Sidebar.vue'
import Footer from '@/Components/Footer.vue'
</script>