<script setup>
import { ref, computed, inject } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useHelper from '@/Helpers'
import useUsuario from '@/Composables/Usuario.js'

const Swal = inject('Swal', null)
const { respuesta, cambiarClave } = useUsuario()

const loading = ref(false)
const form = ref({
  clave_actual: '',
  clave_nueva: '',
  clave_nueva_confirmacion: ''
})

const show = ref({
  actual: false,
  nueva: false,
  confirm: false
})

const errors = ref({})

const clearErrors = () => {
  errors.value = {}
}

const minOk = computed(() => (form.value.clave_nueva || '').length >= 8)
const matchOk = computed(() => form.value.clave_nueva === form.value.clave_nueva_confirmacion && form.value.clave_nueva !== '')

const validateClient = () => {
  const e = {}
  if (!form.value.clave_actual) e.clave_actual = ['Ingresa tu clave actual.']
  if (!form.value.clave_nueva) e.clave_nueva = ['Ingresa la nueva clave.']
  else if (!minOk.value) e.clave_nueva = ['Mínimo 8 caracteres.']
  if (!form.value.clave_nueva_confirmacion) e.clave_nueva_confirmacion = ['Confirma la nueva clave.']
  else if (!matchOk.value) e.clave_nueva_confirmacion = ['Las claves no coinciden.']
  errors.value = e
  return Object.keys(e).length === 0
}

const submit = async () => {
  clearErrors()
  if (!validateClient()) return

  loading.value = true
  try {
    const res = await cambiarClave(form.value)

    form.value = { clave_actual: '', clave_nueva: '', clave_nueva_confirmacion: '' }

    if (Swal) {
      Swal.fire({
        icon: 'success',
        title: 'Clave Actualizada',
        text: 'Su contraseña ha sido modificada con éxito.',
        confirmButtonText: 'ENTENDIDO',
        customClass: { confirmButton: 'btn btn-primary rounded-pill px-4' },
        buttonsStyling: false
      })
    }
  } catch (err) {
    const data = err?.response?.data
    if (data?.errors) {
      errors.value = data.errors
    } else {
      errors.value = { general: 'Verifique sus datos e intente nuevamente.' }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AppLayoutDefault title="Cambio de Contraseña">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Seguridad</h3>
                <p class="text-muted small mb-0">Actualice sus credenciales para mantener su cuenta protegida</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Formulario -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box bg-warning-subtle text-warning rounded-circle me-3">
                            <i class="fas fa-key"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">Modificar Contraseña</h5>
                    </div>

                    <div v-if="errors.general" class="alert bg-danger-subtle text-danger border-0 rounded-4 px-4 py-3 mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i> {{ errors.general }}
                    </div>

                    <form @submit.prevent="submit" class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small fw-bold text-uppercase px-1">Clave Actual</label>
                            <div class="input-group bg-light rounded-pill px-3 overflow-hidden border" :class="{ 'border-danger': errors.clave_actual }">
                                <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-lock"></i></span>
                                <input v-model="form.clave_actual" :type="show.actual ? 'text' : 'password'" class="form-control bg-transparent border-0 shadow-none py-2" placeholder="Digite su clave vigente">
                                <button class="btn btn-transparent border-0 text-muted" type="button" @click="show.actual = !show.actual">
                                    <i class="fas" :class="show.actual ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                            <div v-if="errors.clave_actual" class="text-danger small px-4 mt-1">{{ errors.clave_actual[0] }}</div>
                        </div>

                        <div class="col-md-6 mt-4">
                            <label class="form-label text-muted small fw-bold text-uppercase px-1">Nueva Clave</label>
                            <div class="input-group bg-light rounded-pill px-3 overflow-hidden border" :class="{ 'border-danger': errors.clave_nueva, 'border-success': minOk }">
                                <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-shield-alt"></i></span>
                                <input v-model="form.clave_nueva" :type="show.nueva ? 'text' : 'password'" class="form-control bg-transparent border-0 shadow-none py-2" placeholder="Mínimo 8 caracteres">
                                <button class="btn btn-transparent border-0 text-muted" type="button" @click="show.nueva = !show.nueva">
                                    <i class="fas" :class="show.nueva ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                            <div class="px-4 mt-2">
                                <span class="small" :class="minOk ? 'text-success fw-bold' : 'text-muted'">
                                    <i class="fas me-1" :class="minOk ? 'fa-check-circle' : 'fa-circle opacity-25'"></i> Longitud segura
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6 mt-4">
                            <label class="form-label text-muted small fw-bold text-uppercase px-1">Confirmar Clave</label>
                            <div class="input-group bg-light rounded-pill px-3 overflow-hidden border" :class="{ 'border-danger': errors.clave_nueva_confirmacion, 'border-success': matchOk && minOk }">
                                <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-check-double"></i></span>
                                <input v-model="form.clave_nueva_confirmacion" :type="show.confirm ? 'text' : 'password'" class="form-control bg-transparent border-0 shadow-none py-2" placeholder="Repita la nueva clave">
                                <button class="btn btn-transparent border-0 text-muted" type="button" @click="show.confirm = !show.confirm">
                                    <i class="fas" :class="show.confirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                            <div class="px-4 mt-2">
                                <span class="small" :class="matchOk ? 'text-success fw-bold' : 'text-muted'">
                                    <i class="fas me-1" :class="matchOk ? 'fa-check-circle' : 'fa-circle opacity-25'"></i> Coinciden
                                </span>
                            </div>
                        </div>

                        <div class="col-12 text-end mt-5 pt-3 border-top">
                            <button type="button" class="btn btn-light rounded-pill px-4 text-muted border me-2" @click="form = { clave_actual: '', clave_nueva: '', clave_nueva_confirmacion: '' }; clearErrors()">
                                <i class="fas fa-eraser me-1"></i> LIMPIAR
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="fas fa-save me-1"></i> ACTUALIZAR CLAVE
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tips de Seguridad -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4 h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-4 opacity-10">
                        <i class="fas fa-user-shield fa-9x"></i>
                    </div>
                    <div class="position-relative">
                        <h5 class="fw-bold mb-4 d-flex align-items-center text-primary-subtle">
                            <i class="fas fa-info-circle me-2"></i> Consejos de Seguridad
                        </h5>
                        <ul class="list-unstyled d-grid gap-4">
                            <li class="d-flex align-items-start">
                                <span class="bg-primary bg-opacity-25 rounded-circle p-2 me-3 lh-1"><i class="fas fa-check text-primary"></i></span>
                                <div>
                                    <div class="fw-bold">Variedad de Caracteres</div>
                                    <div class="small text-white-50">Incluya números, letras mayúsculas y símbolos para mayor robustez.</div>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <span class="bg-primary bg-opacity-25 rounded-circle p-2 me-3 lh-1"><i class="fas fa-check text-primary"></i></span>
                                <div>
                                    <div class="fw-bold">Rotación Periódica</div>
                                    <div class="small text-white-50">Se recomienda cambiar su contraseña cada 90 días por seguridad.</div>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <span class="bg-primary bg-opacity-25 rounded-circle p-2 me-3 lh-1"><i class="fas fa-check text-primary"></i></span>
                                <div>
                                    <div class="fw-bold">Clave Única</div>
                                    <div class="small text-white-50">Evite usar la misma clave que utiliza en sus correos o redes sociales.</div>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <span class="bg-primary bg-opacity-25 rounded-circle p-2 me-3 lh-1"><i class="fas fa-check text-primary"></i></span>
                                <div>
                                    <div class="fw-bold">Privacidad Absoluta</div>
                                    <div class="small text-white-50">Nunca comparta sus credenciales con otros colaboradores del sistema.</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayoutDefault>
</template>

<style scoped>
.bg-warning-subtle { background-color: #fffdec !important; }
.bg-danger-subtle { background-color: #fff5f5 !important; }
.icon-box { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; }
.form-control:focus { background: transparent !important; }
</style>
