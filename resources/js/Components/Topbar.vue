<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { RouterLink } from 'vue-router'
import useHelper from '@/Helpers'

const { Swal } = useHelper()

const props = defineProps({
  isDark: { type: Boolean, default: false },
  user: { type: Object, default: () => ({}) },
  role: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['toggleSidebar', 'logout'])

const avatarSrc = computed(() => props.user?.avatar || '/assets/static/images/faces/1.jpg')

// --- Dropdown con Vue ---
const open = ref(false)

const toggleDropdown = () => {
  open.value = !open.value
}

const closeDropdown = () => {
  open.value = false
}

const onDocClick = (e) => {
  // Si el click fue fuera del contenedor del dropdown, cerrar
  const inside = e.target.closest?.('[data-dd="profile"]')
  if (!inside) closeDropdown()
}

const onKeyDown = (e) => {
  if (e.key === 'Escape') closeDropdown()
}

document.addEventListener('click', onDocClick)
document.addEventListener('keydown', onKeyDown)

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onKeyDown)
})

const cerrarSesion = async () => {
  Swal.fire({
    title: '¿Está seguro de Cerrar Sesión?',
    text: 'INVERSIONES DEIN',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
      closeDropdown()
      emit('logout')
    }
  })
}
</script>

<template>
  <header class="topbar">
    <nav class="navbar navbar-expand navbar-light px-2 py-1">
      <div class="container-fluid px-0">
        <!-- Burger (móvil) -->
        <button
          type="button"
          class="btn btn-link p-0 d-block d-xl-none burger-btn"
          @click="emit('toggleSidebar')"
          aria-label="Abrir menú"
        >
          <i class="bi bi-justify fs-3"></i>
        </button>

        <div class="flex-grow-1"></div>

        <!-- PERFIL -->
        <div class="dropdown" data-dd="profile">
          <!-- Botón -->
          <button
            type="button"
            class="btn btn-link nav-link d-flex align-items-center gap-2 p-0"
            @click="toggleDropdown"
            :aria-expanded="open ? 'true' : 'false'"
          >
            <div class="avatar avatar-sm">
              <img :src="avatarSrc" alt="Avatar" />
            </div>

            <div class="d-none d-lg-block text-start lh-1">
              <div class="fw-semibold text-body-emphasis small text-truncate" style="max-width: 160px;">
                {{ props.user?.name }}
              </div>
              <div class="text-secondary" style="font-size: 11px;">
                {{ props.role?.nombre }}
              </div>
            </div>

            <i class="bi bi-chevron-down small"></i>
          </button>

          <!-- Menú -->
          <ul
            class="dropdown-menu dropdown-menu-end"
            :class="{ show: open }"
            :style="open ? 'display:block;' : 'display:none;'"
          >
            <li class="dropdown-header">Cuenta</li>

            <li>
              <RouterLink class="dropdown-item" to="/perfil" @click="closeDropdown">
                <i class="bi bi-person me-2"></i> Ver perfil
              </RouterLink>
            </li>

            <li>
              <RouterLink class="dropdown-item" to="/cambiar-clave" @click="closeDropdown">
                <i class="bi bi-shield-lock me-2"></i> Cambiar clave
              </RouterLink>
            </li>

            <li><hr class="dropdown-divider" /></li>

            <li>
              <a class="dropdown-item text-danger" href="#" @click.prevent="cerrarSesion">
                <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
</template>

<style scoped>
.topbar {
  position: sticky;
  top: 0;
  z-index: 10;
}

.navbar {
  min-height: 44px;
}

/* avatar compacto */
.avatar.avatar-sm {
  width: 34px;
  height: 34px;
}
.avatar.avatar-sm img {
  width: 34px;
  height: 34px;
  object-fit: cover;
  border-radius: 999px;
}
</style>
