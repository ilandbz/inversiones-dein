<script setup>
import { ref, onMounted, watch } from 'vue'
import Sidebar from '@/Components/Sidebar.vue'

const isDark = ref(false)

onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'light'
  isDark.value = savedTheme === 'dark'
  document.documentElement.setAttribute('data-bs-theme', savedTheme)
})

watch(isDark, (value) => {
  const theme = value ? 'dark' : 'light'
  localStorage.setItem('theme', theme)
  document.documentElement.setAttribute('data-bs-theme', theme)
})
</script>

<template>
  <div id="app">
    <div id="sidebar">
      <Sidebar v-model:isDark="isDark" />
    </div>

    <div id="main">
      <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
          <i class="bi bi-justify fs-3"></i>
        </a>
      </header>

      <slot />
    </div>
  </div>
</template>
