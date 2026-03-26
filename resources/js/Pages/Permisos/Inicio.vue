<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useRol from '@/Composables/Rol.js'
import useMenuRole from '@/Composables/menu-role'
import useHelper from '@/Helpers'
import MenuRoleForm from './Form.vue'

const { Toast } = useHelper()
const { roles, listaRoles } = useRol()
const { listarRoleMenus, roleMenus, role, listarMenus, menus } = useMenuRole()

const role_menu = ref({
  role_id: '',
  role_nombre: '',
  menu_id: []
})

const errors = ref({})
const loading = ref(false)

onMounted(async () => {
  await listaRoles()
})

const rolSeleccionado = computed(() => roles.value?.find(r => r.id === role_menu.value.role_id))

const mostrarRoleMenus = async () => {
  if (!role_menu.value.role_id) return
  loading.value = true
  try {
    await listarRoleMenus(role_menu.value)
    await listarMenus()

    role_menu.value.menu_id = []
    role_menu.value.role_nombre = role.value?.nombre ?? (rolSeleccionado.value?.nombre ?? '')

    roleMenus.value.forEach(m => role_menu.value.menu_id.push(m.id))

    Toast.fire({ icon: 'info', title: 'Permisos cargados correctamente' })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AppLayoutDefault title="Permisos por Rol">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Accesos y Permisos</h3>
                <p class="text-muted small mb-0">Configure la visibilidad de los menús para cada rol del sistema</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Selector de Rol -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box bg-primary-subtle text-primary rounded-circle me-3">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">Configurar Rol</h5>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase mb-2">Seleccione un Perfil</label>
                        <select v-model="role_menu.role_id" class="form-select border-0 bg-light rounded-pill px-4 py-2" :disabled="loading">
                            <option value="" disabled>-- Elegir Rol --</option>
                            <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
                        </select>
                        <div v-if="errors.role_id" class="text-danger small mt-2 px-3">{{ errors.role_id[0] }}</div>
                    </div>

                    <div class="d-grid mb-4">
                        <button class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm" @click="mostrarRoleMenus" :disabled="!role_menu.role_id || loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ loading ? 'Sincronizando...' : 'CARGAR ACCESOS' }}
                        </button>
                    </div>

                    <div class="alert bg-light border-0 rounded-4 p-3 mb-0">
                        <div v-if="!role_menu.role_nombre" class="text-center py-2 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-2 opacity-25 d-block"></i>
                            <span class="small">Elija un rol para comenzar la gestión de permisos.</span>
                        </div>
                        <div v-else class="text-center py-2">
                            <div class="small text-muted fw-bold mb-1">ROL SELECCIONADO</div>
                            <div class="text-primary fw-bold text-uppercase fs-5">{{ role_menu.role_nombre }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Editor de Permisos -->
            <div class="col-12 col-lg-8">
                <div v-if="role_menu.role_nombre" class="h-100">
                    <MenuRoleForm :role_menu="role_menu" :menus="menus" />
                </div>
                <div v-else class="card border-0 shadow-sm rounded-4 h-100 d-flex align-items-center justify-content-center text-center p-5 bg-white border-dashed border-primary-subtle border-2">
                    <div class="p-5">
                        <i class="fas fa-lock fa-4x text-primary-subtle mb-4"></i>
                        <h4 class="fw-bold text-dark">Acceso Restringido</h4>
                        <p class="text-muted mx-auto" style="max-width: 300px;">
                            Por favor seleccione un rol en el panel de la izquierda para administrar sus permisos de acceso.
                        </p>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayoutDefault>
</template>

<style scoped>
.bg-primary-subtle { background-color: #e9f2ff !important; }
.icon-box { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; }
.border-dashed { border-style: dashed !important; }
.form-select:focus { box-shadow: none; background-color: #ffffff; border: 1px solid #0d6efd !important; }
</style>
