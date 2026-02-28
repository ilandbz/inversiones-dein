<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import Navbar from '@/Components/Navbar.vue'
import useDatosSession from '@/Composables/session'
import Footer from '@/Components/Footer.vue'
import Topbar from '@/Components/Topbar.vue'
import { useAutenticacion } from '@/Composables/autenticacion'
import NetflixSplash from '@/Components/NetflixSplash.vue'
const route = useRoute()
const { logoutUsuario } = useAutenticacion()
// const showWelcome = ref(false)
const showWelcome = ref(true)
const isDark = ref(false)
const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false) 
const { usuario, menus, role } = useDatosSession()

const pageTitle = computed(() => route.meta?.title || String(route.name || ''))

const breadcrumbs = computed(() => {
  const items = [
    { text: 'Home', to: '/' }
  ]

  // si estás en Home, no repitas
  if (route.path !== '/') {
    items.push({
      text: pageTitle.value || 'Página',
      to: route.fullPath
    })
  }

  return items
})
const applyLayoutClasses = () => {
  const html = document.documentElement
  html.classList.toggle('minimenu', sidebarCollapsed.value)
  html.classList.toggle('nav-open', mobileSidebarOpen.value) // la usaremos en CSS
}

const isDesktop = () => window.matchMedia('(min-width: 1200px)').matches

const toggleSidebarDesktop = () => {
  // solo desktop
  if (!window.matchMedia('(min-width: 1200px)').matches) return
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value ? '1' : '0')
}
const toggleSidebarMobile = () => {
  // solo mobile/tablet
  if (window.matchMedia('(min-width: 1200px)').matches) return
  mobileSidebarOpen.value = !mobileSidebarOpen.value
}
const logout = async () => {
  await logoutUsuario(usuario.value.id)
}
watch([sidebarCollapsed, mobileSidebarOpen], applyLayoutClasses)
watch(isDark, (value) => {
  const theme = value ? 'dark' : 'light'
  localStorage.setItem('theme', theme)
  document.documentElement.setAttribute('data-bs-theme', theme)
})
onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'light'
  isDark.value = savedTheme === 'dark'
  document.documentElement.setAttribute('data-bs-theme', savedTheme)
  const seen = sessionStorage.getItem('welcomeSeen')
  if (!seen) {
    showWelcome.value = true
    //sessionStorage.setItem('welcomeSeen', '1')
  }
  sidebarCollapsed.value = localStorage.getItem('sidebarCollapsed') === '1'
  applyLayoutClasses()
})

</script>

<template>
<NetflixSplash
  v-if="showWelcome"
  v-model="showWelcome"
  logo="/imagenes/logo_redondo.png"
  subtitle="Bienvenido al Dashboard"
  :duration="1800"
/>
  <Navbar :menus="menus" />

  <Topbar :is-dark="isDark"
  :user="usuario"
  :role="role"
  :menus="menus"
  @toggle-sidebar="toggleSidebarDesktop"
  @toggle-mobile="toggleSidebarMobile"
  @logout="logout" />

  <main class="nxl-container">

    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{ pageTitle }}</h5>
                </div>
                <ul class="breadcrumb">
                  <li
                    v-for="(bc, idx) in breadcrumbs"
                    :key="idx"
                    class="breadcrumb-item"
                    :class="{ active: idx === breadcrumbs.length - 1 }"
                    aria-current="page"
                  >
                    <!-- si es el último, texto normal -->
                    <span v-if="idx === breadcrumbs.length - 1">
                      {{ bc.text }}
                    </span>

                    <!-- si no es el último, clickeable -->
                    <RouterLink v-else :to="bc.to">
                      {{ bc.text }}
                    </RouterLink>
                  </li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="javascript:void(0)" class="page-header-right-close-toggle">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <div id="reportrange" class="reportrange-picker d-flex align-items-center">
                            <span class="reportrange-picker-field"></span>
                        </div>

                    </div>
                </div>
                <div class="d-md-none d-flex align-items-center">
                    <a href="javascript:void(0)" class="page-header-right-open-toggle">
                        <i class="feather-align-right fs-20"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="main-content">
          <slot />
        </div>
    </div>
    <Footer />
  </main>

</template>
<style scoped>
.netflix-splash.hide{
  animation: splashOut .45s ease-in both;
  pointer-events: none;   /* <-- CLAVE: ya no bloquea clicks */
}

.netflix-splash { pointer-events: auto; }
.netflix-splash.hide { pointer-events: none; }
</style>

