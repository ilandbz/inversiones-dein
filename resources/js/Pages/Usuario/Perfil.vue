<script setup>
import { computed, ref, watch } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useDatosSession from '@/Composables/session'

const { usuario, roles, role, cambiarRole } = useDatosSession()

const boolToEstado = (v) => (Number(v) === 1 ? 'ACTIVO' : 'INACTIVO')

const apenom = computed(() => {
  const p = usuario.value?.persona
  if (!p) return '-'
  return p.apenom || `${p.ape_pat ?? ''} ${p.ape_mat ?? ''} ${p.primernombre ?? ''} ${p.otrosnombres ?? ''}`.replace(/\s+/g, ' ').trim()
})

const rolActualName = computed(() => usuario.value?.role?.nombre || role.value?.nombre || '-')
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
  await cambiarRole(selectedRoleId.value)
}
</script>

<template>
  <AppLayoutDefault title="Mi Perfil">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Perfil de Usuario</h3>
                <p class="text-muted small mb-0">Gestión de datos personales y preferencias de sesión</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sidebar Perfil -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-primary py-5 text-center position-relative">
                        <div class="avatar avatar-xl shadow-lg border border-4 border-white mx-auto mb-3">
                            <img :src="`/storage/fotos/usuarios/${usuario?.name}.webp`" @error="(e) => e.target.src = '/storage/fotos/usuarios/default.png'">
                        </div>
                        <h5 class="text-white fw-bold mb-0 text-uppercase">{{ usuario?.name }}</h5>
                        <p class="text-white-50 small mb-0">{{ rolActualName }}</p>
                        <span class="badge rounded-pill position-absolute top-0 end-0 m-3 px-3" :class="Number(usuario?.es_activo) === 1 ? 'bg-success border border-white' : 'bg-danger border border-white'">
                            {{ boolToEstado(usuario?.es_activo) }}
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">DNI / Identificación</label>
                            <div class="fs-5 fw-bold text-dark">{{ usuario?.dni || '-' }}</div>
                        </div>

                        <div v-if="(roles?.length ?? 0) > 1" class="p-3 bg-light rounded-4">
                            <label class="form-label fw-bold text-muted small text-uppercase mb-2">Alternar Perfil</label>
                            <div class="d-flex gap-2">
                                <select v-model="selectedRoleId" class="form-select border-0 shadow-none rounded-pill px-3">
                                    <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.nombre }}</option>
                                </select>
                                <button class="btn btn-primary rounded-circle p-2 shadow-sm" @click="onChangeRole">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div class="small text-muted mt-2 lh-sm px-1">Actualice su rol para cambiar sus permisos actuales.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos Detallados -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box bg-primary-subtle text-primary rounded-circle me-3">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">Información Personal</h5>
                    </div>

                    <div class="row g-4 pt-2">
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-4">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Apellidos y Nombres</label>
                                <div class="fw-bold fs-5 text-dark">{{ apenom }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Fecha Nac.</label>
                            <div class="fw-bold text-dark"><i class="fas fa-calendar-day me-2 text-primary opacity-50"></i> {{ usuario?.persona?.fecha_nac || '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Género</label>
                            <div class="fw-bold text-dark"><i class="fas fa-venus-mars me-2 text-primary opacity-50"></i> {{ usuario?.persona?.genero || '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Estado Civil</label>
                            <div class="fw-bold text-dark"><i class="fas fa-heart me-2 text-primary opacity-50"></i> {{ usuario?.persona?.estado_civil || '-' }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Celular Principal</label>
                            <div class="fw-bold text-dark"><i class="fas fa-phone-alt me-2 text-primary opacity-50"></i> {{ usuario?.persona?.celular || '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Correo Electrónico</label>
                            <div class="fw-bold text-dark"><i class="fas fa-envelope me-2 text-primary opacity-50"></i> {{ usuario?.persona?.email || '-' }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Profesión</label>
                            <div class="fw-bold text-dark"><i class="fas fa-user-graduate me-2 text-primary opacity-50"></i> {{ usuario?.persona?.profesion || '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Ocupación</label>
                            <div class="fw-bold text-dark"><i class="fas fa-tools me-2 text-primary opacity-50"></i> {{ usuario?.persona?.ocupacion || '-' }}</div>
                        </div>

                        <div class="col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Dirección de Domicilio</label>
                            <div class="fw-bold text-dark"><i class="fas fa-map-marker-alt me-2 text-primary opacity-50"></i> {{ usuario?.persona?.direccion || '-' }}</div>
                        </div>
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
.avatar-xl { width: 100px; height: 100px; overflow: hidden; border-radius: 50%; }
.avatar-xl img { width: 100%; height: 100%; object-fit: cover; }
.icon-box { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; }
</style>
