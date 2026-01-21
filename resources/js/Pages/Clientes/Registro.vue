<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import useHelper from '@/Helpers'
import useCliente from '@/Composables/Cliente.js'

const router = useRouter()
const { Toast, soloNumeros } = useHelper()

const { errors, respuesta, agregarCliente } = useCliente()

onMounted(() => {
  document.title = 'Registro de Clientes'
})

const form = ref({
  dni: '',
  ruc: '',
  celular: '',
  email: '',

  ape_pat: '',
  ape_mat: '',
  primernombre: '',
  otrosnombres: '',

  fecha_nac: '',
  genero: 'M',
  estado_civil: 'SOLTERO',
  ubigeo: '',

  profesion: '',
  grado_instr: '',
  origen_labor: 'INDEPENDIENTE',
  ocupacion: '',
  institucion_lab: '',

  direccion: '',

  estado: 'ACTIVO',
  fecha_reg: '',
  hora_reg: '',

  errors: {}
})

const toArr = (v) => {
  if (!v) return []
  if (Array.isArray(v)) return v.map(String)
  return [String(v)]
}

const normalizeErrors = (payload) => {
  const out = {}
  const src = payload?.errors && typeof payload.errors === 'object' ? payload.errors : payload

  if (src && typeof src === 'object') {
    for (const k of Object.keys(src)) out[k] = toArr(src[k])
  }

  if (out.ubigeo && !out.ubigeo) out.ubigeo = out.ubigeo

  return out
}

const clearErrors = () => {
  form.value.errors = {}
}

const setErrorsFromComposable = () => {
  form.value.errors = normalizeErrors(errors.value)
}

const hasError = (name) => toArr(form.value?.errors?.[name]).length > 0
const firstError = (name) => toArr(form.value?.errors?.[name])[0] || ''

const clearFieldError = (name) => {
  if (form.value?.errors?.[name]) delete form.value.errors[name]
}

const topErrors = computed(() => {
  const e = form.value?.errors || {}
  const set = new Set()

  for (const key of Object.keys(e)) {
    for (const msg of toArr(e[key])) {
      const m = String(msg).trim()
      if (m === '* Dato Obligatorio' || m.toLowerCase().includes('dato obligatorio')) continue
      set.add(m)
    }
  }
  return [...set]
})

const showTopAlert = computed(() => {
  return Object.keys(form.value?.errors || {}).length > 0
})

const guardar = async () => {
  clearErrors()

  const formData = new FormData()

  for (const key in form.value) {
    if (key === 'errors') continue

    const v = form.value[key]
    if (v === null || v === undefined || v === '') continue

    formData.append(key, v)
  }

  if (photoFile.value instanceof File) {
    formData.append('foto', photoFile.value)
  }

  await agregarCliente(formData)

  if (errors.value) {
    setErrorsFromComposable()
    return
  }

  if (respuesta.value?.ok == 1) {
    Toast.fire({ icon: 'success', title: respuesta.value.mensaje })
    clearErrors()
    resetForm()
  }
}

const cancelar = () => {
  router.push({ name: 'Principal' })
}

// FOTO
const photoFile = ref(null)
const photoPreview = ref(null)
let previewUrl = null

const handlePhotoChange = (e) => {
  const file = e.target.files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    alert('Selecciona una imagen válida.')
    e.target.value = ''
    return
  }

  const maxMB = 4
  if (file.size > maxMB * 1024 * 1024) {
    alert(`La imagen no debe superar ${maxMB}MB.`)
    e.target.value = ''
    return
  }

  photoFile.value = file

  if (previewUrl) URL.revokeObjectURL(previewUrl)
  previewUrl = URL.createObjectURL(file)
  photoPreview.value = previewUrl
}

const removePhoto = () => {
  photoFile.value = null
  if (previewUrl) URL.revokeObjectURL(previewUrl)
  previewUrl = null
  photoPreview.value = null
}

onBeforeUnmount(() => {
  if (previewUrl) URL.revokeObjectURL(previewUrl)
})
</script>

<template>
  <div class="page-heading">
    <h3>Clientes</h3>
    <p class="text-subtitle text-muted">Registro de clientes</p>
  </div>

  <div class="page-content">
    <section class="row">
      <div class="col-12 col-lg-8">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Formulario de Registro</h4>
          </div>

          <div class="card-body">

              <div class="row g-3">

                <div class="col-12 col-md-3">
                  <label class="form-label">DNI</label>
                  <input
                    v-model="form.dni"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('dni') }"
                    maxlength="8"
                    @keypress="soloNumeros"
                    placeholder="Ej: 12345678"
                    @input="clearFieldError('dni')"
                  />
                  <div class="invalid-feedback" v-if="hasError('dni')">
                    {{ firstError('dni') }}
                  </div>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">RUC (opcional)</label>
                  <input
                    v-model="form.ruc"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('ruc') }"
                    maxlength="11"
                    placeholder="Ej: 10456789123"
                    @keypress="soloNumeros"
                    @input="clearFieldError('ruc')"
                  />
                  <div class="invalid-feedback" v-if="hasError('ruc')">
                    {{ firstError('ruc') }}
                  </div>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Celular</label>
                  <input
                    v-model="form.celular"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('celular') }"
                    maxlength="9"
                    @keypress="soloNumeros"
                    placeholder="Ej: 999999999"
                    @input="clearFieldError('celular')"
                  />
                  <div class="invalid-feedback" v-if="hasError('celular')">
                    {{ firstError('celular') }}
                  </div>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Email (opcional)</label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('email') }"
                    placeholder="correo@dominio.com"
                    @input="clearFieldError('email')"
                  />
                  <div class="invalid-feedback" v-if="hasError('email')">
                    {{ firstError('email') }}
                  </div>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Apellido paterno</label>
                  <input
                    v-model="form.ape_pat"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('ape_pat') }"
                    placeholder="Ej: Pérez"
                    @input="clearFieldError('ape_pat')"
                  />
                  <div class="invalid-feedback" v-if="hasError('ape_pat')">
                    {{ firstError('ape_pat') }}
                  </div>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Apellido materno</label>
                  <input
                    v-model="form.ape_mat"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('ape_mat') }"
                    placeholder="Ej: Gómez"
                    @input="clearFieldError('ape_mat')"
                  />
                  <div class="invalid-feedback" v-if="hasError('ape_mat')">
                    {{ firstError('ape_mat') }}
                  </div>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Primer nombre</label>
                  <input
                    v-model="form.primernombre"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('primernombre') }"
                    placeholder="Ej: Juan"
                    @input="clearFieldError('primernombre')"
                  />
                  <div class="invalid-feedback" v-if="hasError('primernombre')">
                    {{ firstError('primernombre') }}
                  </div>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Otros nombres</label>
                  <input v-model="form.otrosnombres" class="form-control" placeholder="Ej: Carlos Alberto" />
                  <div class="invalid-feedback" v-if="hasError('otrosnombres')">
                    {{ firstError('otrosnombres') }}
                  </div>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Fecha de nacimiento</label>
                  <input
                    v-model="form.fecha_nac"
                    type="date"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('fecha_nac') }"
                    @input="clearFieldError('fecha_nac')"
                  />
                  <div class="invalid-feedback" v-if="hasError('fecha_nac')">
                    {{ firstError('fecha_nac') }}
                  </div>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Género</label>
                  <select v-model="form.genero" class="form-select">
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                  </select>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Estado civil</label>
                  <select v-model="form.estado_civil" class="form-select">
                    <option>SOLTERO</option>
                    <option>CASADO</option>
                    <option>CONVIVIENTE</option>
                    <option>DIVORCIADO</option>
                    <option>VIUDO</option>
                  </select>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Ubigeo Nacimiento (opcional)</label>
                  <input
                    v-model="form.ubigeo"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('ubigeo') }"
                    maxlength="6"
                    @keypress="soloNumeros"
                    placeholder="Ej: 090101"
                    @input="clearFieldError('ubigeo')"
                  />
                  <div class="invalid-feedback" v-if="hasError('ubigeo')">
                    {{ firstError('ubigeo') }}
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Profesión</label>
                  <input
                    v-model="form.profesion"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('profesion') }"
                    placeholder="Ej: Comerciante"
                    @input="clearFieldError('profesion')"
                  />
                  <div class="invalid-feedback" v-if="hasError('profesion')">
                    {{ firstError('profesion') }}
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Grado de instrucción</label>
                  <input
                    v-model="form.grado_instr"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('grado_instr') }"
                    placeholder="Ej: Secundaria / Superior"
                    @input="clearFieldError('grado_instr')"
                  />
                  <div class="invalid-feedback" v-if="hasError('grado_instr')">
                    {{ firstError('grado_instr') }}
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Origen laboral</label>
                  <select
                    v-model="form.origen_labor"
                    class="form-select"
                    :class="{ 'is-invalid': hasError('origen_labor') }"
                    @change="clearFieldError('origen_labor')"
                  >
                    <option>INDEPENDIENTE</option>
                    <option>DEPENDIENTE</option>
                  </select>
                  <div class="invalid-feedback" v-if="hasError('origen_labor')">
                    {{ firstError('origen_labor') }}
                  </div>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label">Ocupación</label>
                  <input
                    v-model="form.ocupacion"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('ocupacion') }"
                    placeholder="Ej: Vendedor"
                    @input="clearFieldError('ocupacion')"
                  />
                  <div class="invalid-feedback" v-if="hasError('ocupacion')">
                    {{ firstError('ocupacion') }}
                  </div>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label">Institución laboral</label>
                  <input
                    v-model="form.institucion_lab"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('institucion_lab') }"
                    placeholder="Ej: NINGUNO / Empresa"
                    @input="clearFieldError('institucion_lab')"
                  />
                  <div class="invalid-feedback" v-if="hasError('institucion_lab')">
                    {{ firstError('institucion_lab') }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Dirección</label>
                  <textarea
                    v-model="form.direccion"
                    class="form-control"
                    rows="2"
                    :class="{ 'is-invalid': hasError('direccion') }"
                    placeholder="Av / Jr / Mz / Lt..."
                    @input="clearFieldError('direccion')"
                  ></textarea>
                  <div class="invalid-feedback" v-if="hasError('direccion')">
                    {{ firstError('direccion') }}
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Estado (cliente)</label>
                  <input v-model="form.estado" class="form-control" disabled />
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Fecha registro</label>
                  <input v-model="form.fecha_reg" type="date" class="form-control" disabled />
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Hora registro</label>
                  <input v-model="form.hora_reg" type="time" class="form-control" disabled />
                </div>
              </div>

              <div class="d-flex gap-2 mt-4">
                <button type="button" @click="guardar()" class="btn btn-primary">
                  <i class="bi bi-save me-1"></i> Guardar
                </button>

                <button type="button" class="btn btn-light" @click="cancelar">
                  Cancelar
                </button>
              </div>
          </div>
        </div>
      </div>

      <!-- DERECHA: FOTO -->
      <div class="col-12 col-lg-4">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Foto del cliente</h4>
          </div>

          <div class="card-body">
            <div class="d-flex flex-column align-items-center gap-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center border"
                   style="width: 160px; height: 160px; overflow: hidden;">
                <img v-if="photoPreview" :src="photoPreview" alt="Foto"
                     style="width: 100%; height: 100%; object-fit: cover;" />
                <div v-else class="text-muted text-center px-3">
                  <i class="bi bi-person-circle" style="font-size: 3rem;"></i>
                  <div class="small mt-1">Sin foto</div>
                </div>
              </div>

              <div class="w-100">
                <label class="form-label">Subir imagen</label>
                <input type="file" accept="image/*" class="form-control" @change="handlePhotoChange" />
                <div class="form-text">JPG/PNG. Recomendado: 400x400.</div>
              </div>

              <div class="d-flex gap-2 w-100">
                <button type="button" class="btn btn-light w-100"
                        @click="removePhoto"
                        :disabled="!photoFile && !photoPreview">
                  <i class="bi bi-trash me-1"></i> Quitar
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>

    </section>
  </div>
</template>
