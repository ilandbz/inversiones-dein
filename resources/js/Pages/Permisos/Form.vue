<script setup>
import { computed, ref, toRefs, watch } from 'vue'
import useMenuRole from '@/Composables/menu-role.js'
import useHelper from '@/Helpers'

const props = defineProps({
  role_menu: Object,
  menus: Object
})
const { role_menu, menus } = toRefs(props)

const { agregarMenuRole, mrRespuesta } = useMenuRole()
const { Toast } = useHelper()

const q = ref('')
const guardando = ref(false)

// Filtrado por búsqueda
const menusFiltrados = computed(() => {
  const term = (q.value || '').trim().toLowerCase()
  if (!term) return menus.value || []
  return (menus.value || []).filter(m =>
    String(m.nombre || '').toLowerCase().includes(term) ||
    String(m.slug || '').toLowerCase().includes(term)
  )
})

const totalSeleccionados = computed(() => (role_menu.value.menu_id || []).length)
const totalVisible = computed(() => menusFiltrados.value.length)

const estaMarcado = (id) => (role_menu.value.menu_id || []).includes(id)

// Selecciona todo lo que está visible (filtrado)
const seleccionarTodoVisible = () => {
  const visibles = menusFiltrados.value.map(m => m.id)
  const set = new Set(role_menu.value.menu_id || [])
  visibles.forEach(id => set.add(id))
  role_menu.value.menu_id = Array.from(set)
}

const limpiarSeleccion = () => {
  role_menu.value.menu_id = []
}

const toggleMenu = (id) => {
  const current = new Set(role_menu.value.menu_id || [])
  if (current.has(id)) current.delete(id)
  else current.add(id)
  role_menu.value.menu_id = Array.from(current)
}

const guardar = async () => {
  guardando.value = true
  try {
    await agregarMenuRole(role_menu.value)
    if (mrRespuesta.value?.ok == 1) {
      Toast.fire({ icon: 'success', title: mrRespuesta.value.mensaje })
    } else {
      Toast.fire({ icon: 'warning', title: 'No se pudo guardar' })
    }
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <div class="card border border-info">
    <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
      <div>
        <div class="fw-bold">Menús para: {{ role_menu.role_nombre }}</div>
        <small class="opacity-75">Seleccionados: {{ totalSeleccionados }}</small>
      </div>

      <button class="btn btn-light btn-sm" @click="guardar" :disabled="guardando">
        <span v-if="guardando" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        <i class="fas fa-save me-1"></i> {{ guardando ? 'Guardando…' : 'Guardar' }}
      </button>
    </div>

    <div class="card-body">
      <!-- Toolbar -->
      <div class="row g-2 align-items-center mb-3">
        <div class="col-12 col-md-6">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input v-model="q" type="text" class="form-control" placeholder="Buscar menú por nombre o slug..." />
          </div>
        </div>

        <div class="col-12 col-md-6 d-flex gap-2 justify-content-md-end">
          <button class="btn btn-outline-secondary btn-sm" type="button" @click="seleccionarTodoVisible" :disabled="totalVisible === 0">
            <i class="fas fa-check-double me-1"></i> Seleccionar visibles
          </button>
          <button class="btn btn-outline-danger btn-sm" type="button" @click="limpiarSeleccion" :disabled="totalSeleccionados === 0">
            <i class="fas fa-eraser me-1"></i> Limpiar
          </button>
        </div>

        <div class="col-12">
          <small class="text-muted">
            Mostrando {{ totalVisible }} menú(s) {{ q ? 'filtrado(s)' : '' }}.
          </small>
        </div>
      </div>

      <!-- Lista con scroll -->
      <div class="border rounded p-2" style="max-height: 420px; overflow:auto;">
        <div v-if="menusFiltrados.length === 0" class="text-center text-muted py-4">
          <i class="fas fa-inbox mb-2"></i>
          <div>No hay menús para mostrar.</div>
        </div>

        <div v-else class="row g-2">
          <div class="col-12 col-md-6" v-for="menu in menusFiltrados" :key="menu.id">
            <div class="border rounded p-2 d-flex align-items-start gap-2">
              <div class="form-check mt-1">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :id="`m_${menu.id}`"
                  :checked="estaMarcado(menu.id)"
                  @change="toggleMenu(menu.id)"
                />
              </div>

              <label class="flex-grow-1" :for="`m_${menu.id}`" style="cursor:pointer;">
                <div class="fw-semibold">{{ menu.nombre }}</div>
                <small class="text-muted" v-if="menu.slug">
                  <i class="fas fa-link me-1"></i> {{ menu.slug }}
                </small>
              </label>

              <span class="badge bg-light text-dark">#{{ menu.id }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer acciones -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
          Seleccionados: <b>{{ totalSeleccionados }}</b>
        </small>

        <button class="btn btn-success" @click="guardar" :disabled="guardando">
          <span v-if="guardando" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          <i class="fas fa-save me-1"></i> Guardar cambios
        </button>
      </div>
    </div>
  </div>
</template>
