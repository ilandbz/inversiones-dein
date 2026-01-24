<script setup>
import { ref, onMounted, watch } from 'vue'
import Navbar from '@/Components/Navbar.vue'
import useDatosSession from '@/Composables/session'
import Footer from '@/Components/Footer.vue'
import Topbar from '@/Components/Topbar.vue'
import { useAutenticacion } from '@/Composables/autenticacion'

const { logoutUsuario } = useAutenticacion()

const isDark = ref(false)

// ✅ SOLO ESCRITORIO (COLLAPSED)
const sidebarCollapsed = ref(false)

const { usuario, menus, role } = useDatosSession()

const isDesktop = () => window.matchMedia('(min-width: 1200px)').matches

const toggleSidebarDesktop = () => {
  if (!isDesktop()) return
  sidebarCollapsed.value = !sidebarCollapsed.value

  // opcional: persistir preferencia
  localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value ? '1' : '0')
}

const logout = async () => {
  await logoutUsuario(usuario.value.id)
}

onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'light'
  isDark.value = savedTheme === 'dark'
  document.documentElement.setAttribute('data-bs-theme', savedTheme)

  // cargar preferencia collapsed
  sidebarCollapsed.value = localStorage.getItem('sidebarCollapsed') === '1'
})

watch(isDark, (value) => {
  const theme = value ? 'dark' : 'light'
  localStorage.setItem('theme', theme)
  document.documentElement.setAttribute('data-bs-theme', theme)
})
</script>

<template>

  <Navbar :menus="menus" />

  <Topbar :is-dark="isDark" :user="usuario" :role="role" @toggle-sidebar="toggleSidebarDesktop" @logout="logout" />

  <main class="nxl-container">

    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Dashboard</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Dashboard</li>
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
