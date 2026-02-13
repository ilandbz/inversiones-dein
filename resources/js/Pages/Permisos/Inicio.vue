<script setup>
import { ref, onMounted, computed } from 'vue'
import useRol from '@/Composables/Rol.js'
import { defineTitle } from '@/Helpers'
import useMenuRole from '@/Composables/menu-role'
import useHelper from '@/Helpers'
import MenuRoleForm from './Form.vue'

const { Toast, Swal } = useHelper()
const { roles, listaRoles } = useRol()
const { listarRoleMenus, roleMenus, role, listarMenus, menus } = useMenuRole()

const titleHeader = ref({
  titulo: "Menu Role",
  subTitulo: "Inicio",
  icon: "",
  vista: ""
})

const role_menu = ref({
  role_id: '',
  role_nombre: '',
  menu_id: []
})

const errors = ref({})
const loading = ref(false)

onMounted(async () => {
  defineTitle(titleHeader.value.titulo)
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

    Toast.fire({ icon: 'info', title: 'Menús cargados' })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="container-fluid">
      <div class="card card-primary card-outline mt-2">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h6 class="card-title mb-0">Menús por Rol</h6>
            <small class="text-muted">Selecciona un rol y administra accesos</small>
          </div>
        </div>

        <div class="card-body">
          <div class="row g-3">
            <!-- Panel izquierdo -->
            <div class="col-12 col-lg-4">
              <div class="card h-100">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                  <span>Rol</span>
                  <span v-if="rolSeleccionado" class="badge bg-light text-primary">
                    ID: {{ rolSeleccionado.id }}
                  </span>
                </div>

                <div class="card-body">
                  <label class="form-label form-label-sm mb-1">Selecciona un rol</label>
                  <select
                    class="form-control form-control-sm"
                    v-model="role_menu.role_id"
                    :class="{ 'is-invalid': errors.role_id }"
                  >
                    <option value="" disabled>-Seleccionar-</option>
                    <option v-for="rol in roles" :key="rol.id" :value="rol.id" :title="rol.nombre">
                      {{ rol.nombre }}
                    </option>
                  </select>
                  <small class="text-danger" v-for="error in (errors.role_id || [])" :key="error">
                    {{ error }}
                  </small>

                  <div class="mt-3 d-grid">
                    <button
                      class="btn btn-primary"
                      @click="mostrarRoleMenus"
                      :disabled="!role_menu.role_id || loading"
                    >
                      <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                      {{ loading ? 'Cargando…' : 'Cargar menús' }}
                      <i class="fas fa-arrow-alt-circle-right ms-2"></i>
                    </button>
                  </div>

                  <div class="mt-3">
                    <div class="alert alert-light border mb-0" v-if="!role_menu.role_nombre">
                      <i class="fas fa-info-circle me-2"></i>
                      Elige un rol y presiona <b>Cargar menús</b>.
                    </div>
                    <div class="alert alert-success border mb-0" v-else>
                      <b>Rol:</b> {{ role_menu.role_nombre }} <br />
                      <small class="text-muted">Puedes buscar y marcar/desmarcar menús.</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Panel derecho -->
            <div class="col-12 col-lg-8">
              <MenuRoleForm
                v-if="role_menu.role_nombre"
                :role_menu="role_menu"
                :menus="menus"
              />
              <div v-else class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-center text-center text-muted">
                  <div>
                    <i class="fas fa-lock fa-2x mb-2"></i>
                    <div>Selecciona un rol para ver los menús</div>
                  </div>
                </div>
              </div>
            </div>

          </div><!-- row -->
        </div>
      </div>
  </div>
</template>
