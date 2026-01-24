<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
const route = useRoute()
const title = computed(() => route.meta?.title || 'En construcción')
</script>

<template>
  <div class="card">
    <div class="card-body">
      <h4 class="mb-2">{{ title }}</h4>
      <p class="mb-0 text-muted">
        Ruta: <b>{{ route.fullPath }}</b>
      </p>
    </div>
  </div>
</template>
