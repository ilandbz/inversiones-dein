<script setup>
import { toRef } from 'vue'
import MenusRenderer from '@/Components/MenusRenderer.vue'
import useMenuUI from '@/Composables/useMenuUI'

const props = defineProps({ menus: { type: Array, default: () => [] } })
const menusRef = toRef(props, 'menus')

const { menuUI, openMenu, submenuRefs, toggleMenu, isActive } = useMenuUI(menusRef)
</script>

<template>
  <nav class="nxl-navigation">
    <div class="navbar-wrapper">
      <!-- Header -->
      <div class="m-header">
        <RouterLink to="/" class="b-brand">
          <img src="/imagenes/logo_navbar.jpeg" alt="Logo" class="logo logo-lg" />
          <img src="/imagenes/logo_navbar.jpeg" alt="Logo" class="logo logo-sm" />
        </RouterLink>
      </div>

      <div class="navbar-content">
        <MenusRenderer
          :menuUI="menuUI"
          :openMenu="openMenu"
          :submenuRefs="submenuRefs"
          :toggleMenu="toggleMenu"
          :isActive="isActive"
        />
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* Mantén tu estilo de mayúsculas si lo deseas */
.nxl-caption label {
  text-transform: uppercase;
  letter-spacing: .6px;
}

.nxl-link .nxl-mtext {
  text-transform: uppercase;
  letter-spacing: .3px;
}

/* Submenu */
.nxl-submenu .nxl-link {
  text-transform: uppercase;
  letter-spacing: .25px;
}

/* si el template no resalta active, esto ayuda */
.nxl-item.active > .nxl-link {
  font-weight: 600;
}
</style>
