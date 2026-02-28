<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Navbar from '@/Components/Navbar.vue'
import Topbar from '@/Components/Topbar.vue'
import Footer from '@/Components/Footer.vue'
import { useRoute } from 'vue-router'
import useDatosSession from '@/Composables/session'
import { useAutenticacion } from '@/Composables/autenticacion'

const route = useRoute()
const { usuario, menus, role } = useDatosSession()
const { logoutUsuario } = useAutenticacion()

const sidebarCollapsed = ref(false)     // html.minimenu
const mobileSidebarOpen = ref(false)    // html.nav-open

const isDesktop = () => window.matchMedia('(min-width: 1200px)').matches

const applyHtmlClasses = () => {
  const html = document.documentElement
  html.classList.toggle('minimenu', sidebarCollapsed.value)
  html.classList.toggle('nav-open', mobileSidebarOpen.value)
}

// desktop minimenu
const toggleSidebarDesktop = () => {
  if (!isDesktop()) return
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value ? '1' : '0')
}

// mobile overlay
const toggleSidebarMobile = () => {
  if (isDesktop()) return
  mobileSidebarOpen.value = !mobileSidebarOpen.value
}

// cerrar mobile sidebar al cambiar ruta
watch(() => route.fullPath, () => {
  mobileSidebarOpen.value = false
})

onMounted(() => {
  sidebarCollapsed.value = localStorage.getItem('sidebarCollapsed') === '1'
  applyHtmlClasses()
})

watch([sidebarCollapsed, mobileSidebarOpen], applyHtmlClasses)

const logout = async () => {
  await logoutUsuario(usuario.value.id)
}
</script>

<template>
  <Navbar :menus="menus" />

  <Topbar
    :user="usuario"
    :role="role"
    :menus="menus"
    @toggle-sidebar="toggleSidebarDesktop"
    @toggle-mobile="toggleSidebarMobile"
    @logout="logout"
  />

  <main class="nxl-container" @click="mobileSidebarOpen = false">
    <div class="nxl-content" @click.stop>
      <slot />
    </div>
    <Footer />
  </main>
</template>