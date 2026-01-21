<script setup>
import { ref, onMounted, watch } from 'vue'
import Sidebar from '@/Components/Sidebar.vue'
import useDatosSession from '@/Composables/session';
import Footer from '@/Components/Footer.vue'
import Topbar from '@/Components/Topbar.vue'
import { useAutenticacion } from '@/Composables/autenticacion';
const { logoutUsuario }= useAutenticacion();
const isDark = ref(false)
const sidebarOpenMobile = ref(false)

const { usuario, roles, menus, role, cambiarRole } = useDatosSession();


const toggleSidebar = () => {
  sidebarOpenMobile.value = !sidebarOpenMobile.value
}

const logout = async () => {


  console.log('Cerrando sesión para el usuario:', usuario.value.id);

  await logoutUsuario(usuario.value.id)
}

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

    <div id="sidebar" :class="{ 'active': true, 'sidebar-open': sidebarOpenMobile }">
      <Sidebar v-model:isDark="isDark" :menus="menus" />
    </div>

    <div id="main">
      <Topbar
        :isDark="isDark"
        :user="usuario"
        :role="role"
        @toggleSidebar="toggleSidebar"
        @logout="logout"
      />

      <slot />

      <Footer />
    </div>
  </div>



</template>
