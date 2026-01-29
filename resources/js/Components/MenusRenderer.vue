<!-- src/Components/MenusRenderer.vue -->
<script setup>
import { RouterLink } from 'vue-router'

const props = defineProps({
  menuUI: { type: Array, default: () => [] },
  openMenu: { type: String, default: null },
  isActive: { type: Function, required: true },
  toggleMenu: { type: Function, required: true },
  submenuRefs: { type: Object, default: () => ({}) }
})
</script>

<template>
  <ul class="nxl-navbar">
    <template v-for="item in menuUI" :key="item.key">
      <li v-if="item.isTitle" class="nxl-item nxl-caption">
        <label>{{ item.title }}</label>
      </li>

      <li
        v-else-if="!item.submenu"
        class="nxl-item"
        :class="{ active: isActive(item.url) }"
      >
        <RouterLink
          v-if="item.url && item.url !== '#'"
          :to="item.url"
          class="nxl-link"
        >
          <span class="nxl-micon"><i :class="`feather-${item.icon}`"></i></span>
          <span class="nxl-mtext">{{ item.name }}</span>
        </RouterLink>

        <a v-else href="javascript:void(0);" class="nxl-link" @click.prevent>
          <span class="nxl-micon"><i :class="`feather-${item.icon}`"></i></span>
          <span class="nxl-mtext">{{ item.name }}</span>
        </a>
      </li>

      <li
        v-else
        class="nxl-item nxl-hasmenu"
        :class="{ active: openMenu === item.key }"
      >
        <a href="javascript:void(0);" class="nxl-link" @click.prevent="toggleMenu(item.key)">
          <span class="nxl-micon"><i :class="`feather-${item.icon}`"></i></span>
          <span class="nxl-mtext">{{ item.name }}</span>
          <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
        </a>

        <ul
          class="nxl-submenu"
          :class="{ active: openMenu === item.key }"
          :ref="el => { if (el) submenuRefs[item.key] = el }"
          style="max-height:0; overflow:hidden; transition:max-height .25s ease;"
        >
          <li
            v-for="sub in item.submenu"
            :key="sub.key"
            class="nxl-item"
            :class="{ active: isActive(sub.url) }"
          >
            <RouterLink :to="sub.url" class="nxl-link">{{ sub.name }}</RouterLink>
          </li>
        </ul>
      </li>
    </template>
  </ul>
</template>
