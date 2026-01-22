<script setup>
import { computed, ref, watch } from 'vue'
import useDatosSession from '@/Composables/session'

const { usuario, roles, role, cambiarRole } = useDatosSession()

// helpers
const boolToEstado = (v) => (Number(v) === 1 ? 'ACTIVO' : 'INACTIVO')

const apenom = computed(() => {
  const p = usuario.value?.persona
  if (!p) return '-'
  return p.apenom || `${p.ape_pat ?? ''} ${p.ape_mat ?? ''} ${p.primernombre ?? ''} ${p.otrosnombres ?? ''}`.replace(/\s+/g, ' ').trim()
})

const rolActual = computed(() => usuario.value?.role?.nombre || role.value?.nombre || '-')

// selector role
const selectedRoleId = ref(usuario.value?.role_id || role.value?.id || null)

watch(
  () => usuario.value,
  (u) => {
    selectedRoleId.value = u?.role_id || u?.role?.id || role.value?.id || null
  },
  { immediate: true, deep: true }
)

const onChangeRole = async () => {
  if (!selectedRoleId.value) return
  await cambiarRole(selectedRoleId.value) // <-- si tu cambiarRole espera objeto, abajo te digo el cambio
}
</script>

<template>
  <div class="page-heading">
    <div class="page-title mb-2">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h6 class="mb-1">Perfil de Usuario</h6>
          <p class="text-muted mb-0" style="font-size:.85rem">
            Información de sesión y datos personales.
          </p>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <!-- Resumen -->
      <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">Resumen</h6>
            <span
              class="badge"
              :class="Number(usuario?.es_activo) === 1 ? 'bg-success' : 'bg-danger'"
            >
              {{ boolToEstado(usuario?.es_activo) }}
            </span>
          </div>

          <div class="card-body">
            <div class="mb-2">
              <div class="text-muted" style="font-size:.75rem">Usuario</div>
              <div class="fw-semibold">{{ usuario?.name ?? '-' }}</div>
            </div>

            <div class="mb-2">
              <div class="text-muted" style="font-size:.75rem">DNI</div>
              <div class="fw-semibold">{{ usuario?.dni ?? usuario?.persona?.dni ?? '-' }}</div>
            </div>

            <div class="mb-3">
              <div class="text-muted" style="font-size:.75rem">Rol actual</div>
              <div class="fw-semibold">{{ rolActual }}</div>
            </div>

            <div v-if="(roles?.length ?? 0) > 1">
              <label class="form-label mb-1" style="font-size:.8rem">Cambiar rol</label>
              <div class="d-flex gap-2">
                <select v-model="selectedRoleId" class="form-select">
                  <option
                    v-for="r in roles"
                    :key="r.id"
                    :value="r.id"
                  >
                    {{ r.nombre }}
                  </option>
                </select>

                <button class="btn btn-primary" type="button" @click="onChangeRole">
                  <i class="bi bi-arrow-repeat me-1"></i> Aplicar
                </button>
              </div>
              <div class="text-muted mt-2" style="font-size:.75rem">
                Esto actualizará permisos/menú según el rol.
              </div>
            </div>

            <div v-else class="text-muted" style="font-size:.8rem">
              Solo tienes un rol asignado.
            </div>
          </div>
        </div>
      </div>

      <!-- Datos Personales -->
      <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header">
            <h6 class="card-title mb-0">Datos personales</h6>
          </div>

          <div class="card-body">
            <div class="row g-2">
              <div class="col-12">
                <div class="text-muted" style="font-size:.75rem">Apellidos y nombres</div>
                <div class="fw-semibold">{{ apenom }}</div>
              </div>

              <div class="col-12 col-md-4">
                <div class="text-muted" style="font-size:.75rem">Fecha nac.</div>
                <div class="fw-semibold">{{ usuario?.persona?.fecha_nac ?? '-' }}</div>
              </div>

              <div class="col-6 col-md-4">
                <div class="text-muted" style="font-size:.75rem">Género</div>
                <div class="fw-semibold">{{ usuario?.persona?.genero ?? '-' }}</div>
              </div>

              <div class="col-6 col-md-4">
                <div class="text-muted" style="font-size:.75rem">Edad</div>
                <div class="fw-semibold">{{ usuario?.persona?.edad ?? '-' }}</div>
              </div>

              <div class="col-12 col-md-6">
                <div class="text-muted" style="font-size:.75rem">Celular</div>
                <div class="fw-semibold">{{ usuario?.persona?.celular ?? '-' }}</div>
              </div>

              <div class="col-12 col-md-6">
                <div class="text-muted" style="font-size:.75rem">Celular 2</div>
                <div class="fw-semibold">{{ usuario?.persona?.celular2 ?? '-' }}</div>
              </div>

              <div class="col-12 col-md-6">
                <div class="text-muted" style="font-size:.75rem">Email</div>
                <div class="fw-semibold">{{ usuario?.persona?.email ?? '-' }}</div>
              </div>

              <div class="col-12 col-md-6">
                <div class="text-muted" style="font-size:.75rem">Estado civil</div>
                <div class="fw-semibold">{{ usuario?.persona?.estado_civil ?? '-' }}</div>
              </div>

              <div class="col-12 col-md-6">
                <div class="text-muted" style="font-size:.75rem">Profesión</div>
                <div class="fw-semibold">{{ usuario?.persona?.profesion ?? '-' }}</div>
              </div>

              <div class="col-12 col-md-6">
                <div class="text-muted" style="font-size:.75rem">Grado instrucción</div>
                <div class="fw-semibold">{{ usuario?.persona?.grado_instr ?? '-' }}</div>
              </div>

              <div class="col-12 col-md-6">
                <div class="text-muted" style="font-size:.75rem">Origen laboral</div>
                <div class="fw-semibold">{{ usuario?.persona?.origen_labor ?? '-' }}</div>
              </div>

              <div class="col-12 col-md-6">
                <div class="text-muted" style="font-size:.75rem">Ocupación</div>
                <div class="fw-semibold">{{ usuario?.persona?.ocupacion ?? '-' }}</div>
              </div>

              <div class="col-12">
                <div class="text-muted" style="font-size:.75rem">Dirección</div>
                <div class="fw-semibold">{{ usuario?.persona?.direccion ?? '-' }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Debug (opcional) -->
        <!--
        <div class="card mt-3">
          <div class="card-header"><h6 class="card-title mb-0">Debug</h6></div>
          <div class="card-body">
            <pre style="font-size:.75rem">{{ usuario }}</pre>
          </div>
        </div>
        -->
      </div>
    </div>
  </div>
</template>
