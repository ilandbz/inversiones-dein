<script setup>
import { ref, computed, inject } from 'vue'
import axios from 'axios'
import useHelper from '@/Helpers'
import useUsuario from '@/Composables/Usuario.js'

const Swal = inject('Swal', null)
const { getConfigHeader } = useHelper() // si no existe en tu helper, abajo te doy alternativa

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

const hasError = (k) => !!errors.value?.[k]
const firstError = (k) => (errors.value?.[k]?.[0] ? errors.value[k][0] : errors.value?.[k] || '')

const minOk = computed(() => (form.value.clave_nueva || '').length >= 8)
const matchOk = computed(() => form.value.clave_nueva === form.value.clave_nueva_confirmacion)

const validateClient = () => {
  const e = {}
  if (!form.value.clave_actual) e.clave_actual = 'Ingresa tu clave actual.'
  if (!form.value.clave_nueva) e.clave_nueva = 'Ingresa la nueva clave.'
  else if (!minOk.value) e.clave_nueva = 'La nueva clave debe tener mínimo 8 caracteres.'
  if (!form.value.clave_nueva_confirmacion) e.clave_nueva_confirmacion = 'Confirma la nueva clave.'
  else if (!matchOk.value) e.clave_nueva_confirmacion = 'La confirmación no coincide.'
  errors.value = e
  return Object.keys(e).length === 0
}

const submit = async () => {
  clearErrors()
  if (!validateClient()) return

  loading.value = true
  try {
    await cambiarClave(form.value)

    // limpiar
    form.value.clave_actual = ''
    form.value.clave_nueva = ''
    form.value.clave_nueva_confirmacion = ''

    if (Swal) {
      Swal.fire({
        icon: 'success',
        title: 'Listo',
        text: 'Tu clave fue actualizada correctamente.'
      })
    }
  } catch (err) {
    const data = err?.response?.data
    if (data?.errors) {
      errors.value = data.errors
    } else if (data?.message) {
      errors.value = { general: data.message }
    } else {
      errors.value = { general: 'Ocurrió un error al cambiar la clave.' }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page-heading">
    <div class="page-title mb-2">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h6 class="mb-1">Cambiar clave</h6>
          <p class="text-muted mb-0" style="font-size:.85rem">
            Actualiza tu contraseña para mantener tu cuenta segura.
          </p>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header">
            <h6 class="card-title mb-0">Formulario</h6>
          </div>

          <div class="card-body">
            <div v-if="errors.general" class="alert alert-danger py-2" style="font-size:.85rem">
              {{ errors.general }}
            </div>

            <form @submit.prevent="submit" class="row g-2">
              <!-- Clave actual -->
              <div class="col-12">
                <label class="form-label mb-1" style="font-size:.8rem">Clave actual</label>
                <div class="input-group">
                  <input
                    v-model="form.clave_actual"
                    :type="show.actual ? 'text' : 'password'"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('clave_actual') }"
                    placeholder="Ingresa tu clave actual"
                    autocomplete="current-password"
                  />
                  <button class="btn btn-outline-secondary" type="button" @click="show.actual = !show.actual">
                    <i class="bi" :class="show.actual ? 'bi-eye-slash' : 'bi-eye'"></i>
                  </button>
                  <div v-if="hasError('clave_actual')" class="invalid-feedback">
                    {{ firstError('clave_actual') }}
                  </div>
                </div>
              </div>

              <!-- Nueva clave -->
              <div class="col-12">
                <label class="form-label mb-1" style="font-size:.8rem">Nueva clave</label>
                <div class="input-group">
                  <input
                    v-model="form.clave_nueva"
                    :type="show.nueva ? 'text' : 'password'"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('clave_nueva') }"
                    placeholder="Mínimo 8 caracteres"
                    autocomplete="new-password"
                  />
                  <button class="btn btn-outline-secondary" type="button" @click="show.nueva = !show.nueva">
                    <i class="bi" :class="show.nueva ? 'bi-eye-slash' : 'bi-eye'"></i>
                  </button>
                  <div v-if="hasError('clave_nueva')" class="invalid-feedback">
                    {{ firstError('clave_nueva') }}
                  </div>
                </div>

                <div class="mt-1 text-muted" style="font-size:.75rem">
                  <i class="bi" :class="minOk ? 'bi-check-circle' : 'bi-x-circle'"></i>
                  8+ caracteres
                </div>
              </div>

              <!-- Confirmación -->
              <div class="col-12">
                <label class="form-label mb-1" style="font-size:.8rem">Confirmar nueva clave</label>
                <div class="input-group">
                  <input
                    v-model="form.clave_nueva_confirmacion"
                    :type="show.confirm ? 'text' : 'password'"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('clave_nueva_confirmacion') }"
                    placeholder="Repite la nueva clave"
                    autocomplete="new-password"
                  />
                  <button class="btn btn-outline-secondary" type="button" @click="show.confirm = !show.confirm">
                    <i class="bi" :class="show.confirm ? 'bi-eye-slash' : 'bi-eye'"></i>
                  </button>
                  <div v-if="hasError('clave_nueva_confirmacion')" class="invalid-feedback">
                    {{ firstError('clave_nueva_confirmacion') }}
                  </div>
                </div>

                <div class="mt-1 text-muted" style="font-size:.75rem">
                  <i class="bi" :class="matchOk ? 'bi-check-circle' : 'bi-x-circle'"></i>
                  Coinciden
                </div>
              </div>

              <!-- Acciones -->
              <div class="col-12 d-flex gap-2 mt-2">
                <button class="btn btn-primary" type="submit" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-1" role="status"></span>
                  <i v-else class="bi bi-shield-lock me-1"></i>
                  Guardar cambios
                </button>

                <button
                  class="btn btn-light"
                  type="button"
                  :disabled="loading"
                  @click="
                    form.clave_actual='';
                    form.clave_nueva='';
                    form.clave_nueva_confirmacion='';
                    clearErrors();
                  "
                >
                  <i class="bi bi-arrow-counterclockwise me-1"></i>
                  Limpiar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Tips -->
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header">
            <h6 class="card-title mb-0">Recomendaciones</h6>
          </div>
          <div class="card-body" style="font-size:.85rem">
            <ul class="mb-0">
              <li>Usa una clave de mínimo 8 caracteres (mejor 12+).</li>
              <li>Combina letras, números y símbolos.</li>
              <li>No reutilices claves de otros sistemas.</li>
              <li>Si sospechas accesos, cambia la clave inmediatamente.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
