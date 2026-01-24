<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import useHelper from '@/Helpers'
import useCliente from '@/Composables/Cliente.js'
import useActividadNegocio from '@/Composables/ActividadNegocio.js'

const router = useRouter()
const { Toast, soloNumeros } = useHelper()
const { errors, respuesta, agregarCliente } = useCliente()
const {
  actividadNegocios,
  listaActividadNegocios,
  listaDetalleActividadNegocios,
  detalleActividadNegocios
} = useActividadNegocio()

onMounted(() => {
  document.title = 'Registro de Clientes'
  listaActividadNegocios()
})

/* ----------------- FORM (base) ----------------- */
const NEGOCIO_DEFAULT = () => ({
  razonsocial: '',
  ruc: '',
  celular: '',
  tipo_actividad_id: '',
  detalle_actividad_id: '',
  inicioactividad: '',
  direccion: ''
})

const REFERENTE_DEFAULT = () => ({
  primernombre: '',
  otrosnombres: '',
  ape_pat: '',
  ape_mat: '',
  dni: '',
  celular: '',
  parentesco: '',
  email: '',
  direccion: ''
})

const form = ref({
  dni: '',
  ruc: '',
  celular: '',
  celular2: '',
  email: '',

  ape_pat: '',
  ape_mat: '',
  primernombre: '',
  otrosnombres: '',

  fecha_nac: '',
  genero: 'M',
  estado_civil: 'SOLTERO',
  ubigeo_nac: '',
  ubigeo_dom: '',

  profesion: '',
  grado_instr: '',
  origen_labor: 'INDEPENDIENTE',
  ocupacion: '',
  institucion_lab: '',

  direccion: '',

  latitud_longitud: '',

  negocio: NEGOCIO_DEFAULT(),
  referente: REFERENTE_DEFAULT(),

  estado: 'ACTIVO',
  fecha_reg: '',
  hora_reg: '',

  errors: {}
})


const geoLoading = ref(false)
const geoMsg = ref('')

const setLatLng = (lat, lng) => {
  // 6 decimales aprox. (buen equilibrio)
  const latF = Number(lat).toFixed(6)
  const lngF = Number(lng).toFixed(6)
  form.value.latitud_longitud = `${latF},${lngF}`
  clearFieldError('latitud_longitud')
}

const parseLatLng = (s) => {
  // acepta "lat,lng" con espacios
  const parts = String(s || '').split(',').map(x => x.trim())
  if (parts.length !== 2) return null
  const lat = Number(parts[0])
  const lng = Number(parts[1])
  if (Number.isNaN(lat) || Number.isNaN(lng)) return null
  if (lat < -90 || lat > 90) return null
  if (lng < -180 || lng > 180) return null
  return { lat, lng }
}

const obtenerUbicacion = () => {
  geoMsg.value = ''
  if (!('geolocation' in navigator)) {
    geoMsg.value = 'Tu navegador no soporta geolocalización.'
    return
  }

  geoLoading.value = true

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const { latitude, longitude } = pos.coords
      setLatLng(latitude, longitude)
      geoLoading.value = false
    },
    (err) => {
      geoLoading.value = false
      // mensajes amigables
      if (err.code === 1) geoMsg.value = 'Permiso denegado para acceder a la ubicación.'
      else if (err.code === 2) geoMsg.value = 'No se pudo determinar la ubicación (señal/GPS).'
      else geoMsg.value = 'Error al obtener la ubicación.'
    },
    {
      enableHighAccuracy: true,
      timeout: 12000,
      maximumAge: 0
    }
  )
}

// opcional: valida cuando escriben manualmente "lat,lng"
watch(
  () => form.value.latitud_longitud,
  (v) => {
    if (!v) return
    const ok = parseLatLng(v)
    if (!ok) {
      // No bloquea, solo aviso (Laravel validará si quieres)
      geoMsg.value = 'Formato sugerido: -9.930123,-76.242991'
    } else {
      geoMsg.value = ''
    }
  }
)


const parentescos = [
  'PADRE',
  'MADRE',
  'HIJO(A)',
  'HERMANO(A)',
  'ABUELO(A)',
  'NIETO(A)',
  'TÍO(A)',
  'SOBRINO(A)',
  'PRIMO(A)',
  'CUÑADO(A)',
  'SUEGRO(A)',
  'YERNO',
  'NUERA',
  'ESPOSO(A)',
  'CONVIVIENTE',
  'PAREJA',
  'FAMILIAR',
  'AMIGO(A)',
  'VECINO(A)',
  'COMPAÑERO(A) DE TRABAJO',
  'JEFE(A)',
  'OTRO'
]

const gradosInstruccion = [
  'SIN INSTRUCCION',
  'INICIAL',
  'PRIMARIA',
  'SECUNDARIA',
  'SUPERIOR TECNICO',
  'SUPERIOR UNIVERSITARIO',
  'POSTGRADO'
]

const profesionesPeru = [
  'ADMINISTRACIÓN',
  'ADMINISTRACIÓN DE EMPRESAS',
  'AGRONOMÍA',
  'ARQUITECTURA',
  'BIOLOGÍA',
  'CIENCIAS DE LA COMUNICACIÓN',
  'CIENCIAS CONTABLES',
  'CONTABILIDAD',
  'DERECHO',
  'DISEÑO GRÁFICO',
  'ECONOMÍA',
  'EDUCACIÓN INICIAL',
  'EDUCACIÓN PRIMARIA',
  'EDUCACIÓN SECUNDARIA',
  'ENFERMERÍA',
  'ESTADÍSTICA',
  'FARMACIA Y BIOQUÍMICA',
  'GASTRONOMÍA',
  'INGENIERÍA AGRÍCOLA',
  'INGENIERÍA AGROINDUSTRIAL',
  'INGENIERÍA AMBIENTAL',
  'INGENIERÍA CIVIL',
  'INGENIERÍA DE MINAS',
  'INGENIERÍA DE SISTEMAS',
  'INGENIERÍA ELECTRÓNICA',
  'INGENIERÍA INDUSTRIAL',
  'INGENIERÍA MECÁNICA',
  'INGENIERÍA PESQUERA',
  'INGENIERÍA QUÍMICA',
  'MARKETING',
  'MEDICINA HUMANA',
  'MEDICINA VETERINARIA',
  'NUTRICIÓN',
  'OBSTETRICIA',
  'ODONTOLOGÍA',
  'PSICOLOGÍA',
  'SOCIOLOGÍA',
  'TRABAJO SOCIAL',
  'TURISMO Y HOTELERÍA'
]


const esSuperior = computed(() => {
  const g = String(form.value.grado_instr || '').toUpperCase()
  return g.includes('SUPERIOR') || g.includes('POSTGRADO')
})

const toArr = (v) => (Array.isArray(v) ? v.map(String) : v ? [String(v)] : [])

const normalizeErrors = (payload) => {
  const out = {}
  const src = payload?.errors && typeof payload.errors === 'object' ? payload.errors : payload

  if (src && typeof src === 'object') {
    for (const k of Object.keys(src)) out[k] = toArr(src[k])
  }

  // soporte negocio[xxx] -> negocio.xxx
  for (const k of Object.keys(out)) {
    const m = k.match(/^negocio\[(.+)\]$/)
    if (m?.[1]) out[`negocio.${m[1]}`] ||= out[k]
  }

  return out
}

const setErrorsFromComposable = () => {
  form.value.errors = normalizeErrors(errors.value)
}
const clearErrors = () => {
  form.value.errors = {}
}
const hasError = (name) => toArr(form.value.errors?.[name]).length > 0
const firstError = (name) => toArr(form.value.errors?.[name])[0] || ''
const clearFieldError = (name) => {
  if (form.value.errors?.[name]) delete form.value.errors[name]
}

/* ----------------- INDEPENDIENTE / NEGOCIO ----------------- */
const esIndependiente = computed(() => form.value.origen_labor === 'INDEPENDIENTE')

const resetNegocio = () => {
  form.value.negocio = NEGOCIO_DEFAULT()
}

watch(
  () => form.value.origen_labor,
  (v) => {
    if (v !== 'INDEPENDIENTE') {
      resetNegocio()
      // limpia errores de negocio
      for (const k of Object.keys(form.value.errors || {})) {
        if (k.startsWith('negocio.') || k.startsWith('negocio[')) delete form.value.errors[k]
      }
      // opcional: limpia combos
      detalleActividadNegocios.value = []
    }
  }
)

const onChangeTipoActividad = async () => {
  clearFieldError('negocio.tipo_actividad_id')

  const id = form.value.negocio.tipo_actividad_id
  if (!id) {
    detalleActividadNegocios.value = []
    form.value.negocio.detalle_actividad_id = ''
    return
  }

  form.value.negocio.detalle_actividad_id = ''
  await listaDetalleActividadNegocios(id)
}

/* ----------------- FOTO ----------------- */
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


watch(
  () => form.value.grado_instr,
  () => {
    if (!esSuperior.value) {
      form.value.profesion = ''
      clearFieldError('profesion')
    }
  }
)


const appendIfFilled = (fd, key, value) => {
  if (value === null || value === undefined || value === '') return
  fd.append(key, value)
}

const guardar = async () => {
  clearErrors()

  const fd = new FormData()

  // campos simples (excluye objetos y errors)
  const {
    negocio, referente, errors: _errors, // eslint-disable-line no-unused-vars
    ...plain
  } = form.value

  for (const [k, v] of Object.entries(plain)) appendIfFilled(fd, k, v)

  // negocio solo si es independiente
  if (esIndependiente.value) {
    for (const [k, v] of Object.entries(negocio || {})) {
      appendIfFilled(fd, `negocio[${k}]`, v)
    }
  }

  // referente (siempre)
  for (const [k, v] of Object.entries(referente || {})) {
    appendIfFilled(fd, `referente[${k}]`, v)
  }

  // foto
  if (photoFile.value instanceof File) fd.append('foto', photoFile.value)

  await agregarCliente(fd)

  if (errors.value) {
    setErrorsFromComposable()
    return
  }

  if (respuesta.value?.ok == 1) {
    Toast.fire({ icon: 'success', title: respuesta.value.mensaje })
    resetForm()
  }
}

const resetForm = () => {
  const keep = {
    genero: 'M',
    estado_civil: 'SOLTERO',
    origen_labor: 'INDEPENDIENTE',
    estado: 'ACTIVO'
  }

  // resetea campos planos
  for (const k of Object.keys(form.value)) {
    if (k === 'errors' || k === 'negocio' || k === 'referente') continue
    form.value[k] = keep[k] ?? ''
  }

  resetNegocio()
  form.value.referente = REFERENTE_DEFAULT()
  clearErrors()
  removePhoto()

  // opcional: limpia combos
  detalleActividadNegocios.value = []
}

const cancelar = () => router.push({ name: 'Principal' })
</script>

<template>
  <div class="page-heading">
    <h3 class="card-header">Formulario de Registro</h3>
  </div>

  <div class="page-content">
    <section class="row">
      <div class="col-12 col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3>Cliente Nuevo</h3>

          </div>

          <div class="card-body">
            <div class="row g-3">
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
                <label class="form-label">Segundo Nombre</label>
                <input v-model="form.otrosnombres" class="form-control" placeholder="Ej: Carlos Alberto" />
                <div class="invalid-feedback" v-if="hasError('otrosnombres')">
                  {{ firstError('otrosnombres') }}
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
                <label class="form-label">Ubigeo Nacimiento</label>
                <input
                  v-model="form.ubigeo_nac"
                  class="form-control"
                  :class="{ 'is-invalid': hasError('ubigeo_nac') }"
                  maxlength="6"
                  @keypress="soloNumeros"
                  placeholder="Ej: 090101"
                  @input="clearFieldError('ubigeo_nac')"
                />
                <div class="invalid-feedback" v-if="hasError('ubigeo_nac')">
                  {{ firstError('ubigeo_nac') }}
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

              <!-- GRADO INSTRUCCIÓN (select) -->
              <div class="col-12 col-md-3">
                <label class="form-label">Grado de instrucción</label>
                <select
                  v-model="form.grado_instr"
                  class="form-select"
                  :class="{ 'is-invalid': hasError('grado_instr') }"
                  @change="clearFieldError('grado_instr')"
                >
                  <option value="">-- Seleccione --</option>
                  <option v-for="g in gradosInstruccion" :key="g" :value="g">
                    {{ g }}
                  </option>
                </select>
                <div class="invalid-feedback" v-if="hasError('grado_instr')">
                  {{ firstError('grado_instr') }}
                </div>
              </div>

              <!-- PROFESIÓN (solo si es superior) -->
              <div class="col-12 col-md-3" v-if="esSuperior">
                <label class="form-label">Profesión</label>
                <select
                  v-model="form.profesion"
                  class="form-select"
                  :class="{ 'is-invalid': hasError('profesion') }"
                  @change="clearFieldError('profesion')"
                >
                  <option value="">-- Seleccione profesión --</option>
                  <option v-for="p in profesionesPeru" :key="p" :value="p">
                    {{ p }}
                  </option>
                </select>

                <div class="invalid-feedback" v-if="hasError('profesion')">
                  {{ firstError('profesion') }}
                </div>
              </div>


              <div class="col-12 col-md-3">
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
                <label class="form-label">Celular 2</label>
                <input
                  v-model="form.celular2"
                  class="form-control"
                  :class="{ 'is-invalid': hasError('celular2') }"
                  maxlength="9"
                  @keypress="soloNumeros"
                  placeholder="Ej: 999999999"
                  @input="clearFieldError('celular2')"
                />
                <div class="invalid-feedback" v-if="hasError('celular2')">
                  {{ firstError('celular2') }}
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
                <label class="form-label">Ubigeo Domicilio</label>
                <input
                  v-model="form.ubigeo_dom"
                  class="form-control"
                  :class="{ 'is-invalid': hasError('ubigeo_dom') }"
                  maxlength="6"
                  @keypress="soloNumeros"
                  placeholder="Ej: 090101"
                  @input="clearFieldError('ubigeo_dom')"
                />
                <div class="invalid-feedback" v-if="hasError('ubigeo_dom')">
                  {{ firstError('ubigeo_dom') }}
                </div>
              </div>

              <div class="col-6">
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

              <div class="col-6">
                <label class="form-label">Ubicación (Latitud, Longitud)</label>

                <div class="input-group">
                  <input
                    v-model="form.latitud_longitud"
                    class="form-control"
                    :class="{ 'is-invalid': hasError('latitud_longitud') }"
                    placeholder="Ej: -9.930123,-76.242991"
                    @input="clearFieldError('latitud_longitud')"
                  />
                  <button
                    class="btn btn-outline-primary"
                    type="button"
                    @click="obtenerUbicacion"
                    :disabled="geoLoading"
                  >
                    <span v-if="geoLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Obtener mi ubicación
                  </button>
                </div>

                <div class="invalid-feedback" v-if="hasError('latitud_longitud')">
                  {{ firstError('latitud_longitud') }}
                </div>

                <div class="form-text" v-if="!hasError('latitud_longitud')">
                  Guarda en formato: <b>lat,lng</b>. Ej: -9.930123,-76.242991
                </div>

                <div class="text-danger small mt-1" v-if="geoMsg">
                  {{ geoMsg }}
                </div>
              </div>

              <!-- NEGOCIO -->
              <div v-if="esIndependiente" class="mt-3">
                <div class="border rounded p-3">
                  <h6 class="mb-3">Datos del Negocio</h6>

                  <div class="row mb-3">
                    <div class="col-12 col-md-3">
                      <label class="form-label">Tipo actividad (opcional)</label>
                      <select
                        v-model="form.negocio.tipo_actividad_id"
                        class="form-select"
                        :class="{ 'is-invalid': hasError('negocio.tipo_actividad_id') }"
                        @change="onChangeTipoActividad"
                      >
                        <option value="">-- Seleccione --</option>
                        <option v-for="value in actividadNegocios" :key="value.id" :value="value.id">
                          {{ value.nombre }}
                        </option>
                      </select>

                      <div class="invalid-feedback" v-if="hasError('negocio.tipo_actividad_id')">
                        {{ firstError('negocio.tipo_actividad_id') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-6">
                      <label class="form-label">Detalle Actividad</label>
                      <select
                        v-model="form.negocio.detalle_actividad_id"
                        class="form-select"
                        :class="{ 'is-invalid': hasError('negocio.detalle_actividad_id') }"
                        @change="clearFieldError('negocio.detalle_actividad_id')"
                      >
                        <option value="">-- Seleccione --</option>
                        <option v-for="value in detalleActividadNegocios" :key="value.id" :value="value.id">
                          {{ value.nombre }}
                        </option>
                      </select>

                      <div class="invalid-feedback" v-if="hasError('negocio.detalle_actividad_id')">
                        {{ firstError('negocio.detalle_actividad_id') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Celular / Telefono</label>
                      <input
                        v-model="form.negocio.celular"
                        class="form-control"
                        maxlength="9"
                        @keypress="soloNumeros"
                        :class="{ 'is-invalid': hasError('negocio.celular') }"
                        @input="clearFieldError('negocio.celular')"
                        placeholder="Ej: 999999999"
                      />
                      <div class="invalid-feedback" v-if="hasError('negocio.celular')">
                        {{ firstError('negocio.celular') }}
                      </div>
                    </div>
                  </div>

                  <div class="row g-3">
                    <div class="col-12 col-md-6">
                      <label class="form-label">Razón social</label>
                      <input
                        v-model="form.negocio.razonsocial"
                        class="form-control"
                        :class="{ 'is-invalid': hasError('negocio.razonsocial') }"
                        @input="clearFieldError('negocio.razonsocial')"
                        placeholder="Ej: Bodega San Martín"
                      />
                      <div class="invalid-feedback" v-if="hasError('negocio.razonsocial')">
                        {{ firstError('negocio.razonsocial') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">RUC (opcional)</label>
                      <input
                        v-model="form.negocio.ruc"
                        class="form-control"
                        maxlength="11"
                        @keypress="soloNumeros"
                        :class="{ 'is-invalid': hasError('negocio.ruc') }"
                        @input="clearFieldError('negocio.ruc')"
                        placeholder="Ej: 10456789123"
                      />
                      <div class="invalid-feedback" v-if="hasError('negocio.ruc')">
                        {{ firstError('negocio.ruc') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Inicio actividad (opcional)</label>
                      <input
                        v-model="form.negocio.inicioactividad"
                        type="date"
                        class="form-control"
                        :class="{ 'is-invalid': hasError('negocio.inicioactividad') }"
                        @input="clearFieldError('negocio.inicioactividad')"
                      />
                      <div class="invalid-feedback" v-if="hasError('negocio.inicioactividad')">
                        {{ firstError('negocio.inicioactividad') }}
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label">Dirección del negocio (opcional)</label>
                      <textarea
                        v-model="form.negocio.direccion"
                        class="form-control"
                        rows="2"
                        :class="{ 'is-invalid': hasError('negocio.direccion') }"
                        @input="clearFieldError('negocio.direccion')"
                        placeholder="Av / Jr / Mz / Lt..."
                      ></textarea>
                      <div class="invalid-feedback" v-if="hasError('negocio.direccion')">
                        {{ firstError('negocio.direccion') }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- DEPENDIENTE -->
              <div v-else class="row g-3">
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
              </div>



              <!-- REFERENTE -->
              <div class="mt-3">
                <div class="border rounded p-3">
                  <h6 class="mb-3">Datos del Referente</h6>

                  <div class="row g-3">
                    <div class="col-12 col-md-3">
                      <label class="form-label">Primer nombre</label>
                      <input
                        v-model="form.referente.primernombre"
                        class="form-control"
                        :class="{ 'is-invalid': hasError('referente.primernombre') }"
                        placeholder="Ej: Juan"
                        @input="clearFieldError('referente.primernombre')"
                      />
                      <div class="invalid-feedback" v-if="hasError('referente.primernombre')">
                        {{ firstError('referente.primernombre') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Segundo nombre</label>
                      <input
                        v-model="form.referente.otrosnombres"
                        class="form-control"
                        :class="{ 'is-invalid': hasError('referente.otrosnombres') }"
                        placeholder="Ej: Carlos Alberto"
                        @input="clearFieldError('referente.otrosnombres')"
                      />
                      <div class="invalid-feedback" v-if="hasError('referente.otrosnombres')">
                        {{ firstError('referente.otrosnombres') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Apellido paterno</label>
                      <input
                        v-model="form.referente.ape_pat"
                        class="form-control"
                        :class="{ 'is-invalid': hasError('referente.ape_pat') }"
                        placeholder="Ej: Pérez"
                        @input="clearFieldError('referente.ape_pat')"
                      />
                      <div class="invalid-feedback" v-if="hasError('referente.ape_pat')">
                        {{ firstError('referente.ape_pat') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Apellido materno</label>
                      <input
                        v-model="form.referente.ape_mat"
                        class="form-control"
                        :class="{ 'is-invalid': hasError('referente.ape_mat') }"
                        placeholder="Ej: Gómez"
                        @input="clearFieldError('referente.ape_mat')"
                      />
                      <div class="invalid-feedback" v-if="hasError('referente.ape_mat')">
                        {{ firstError('referente.ape_mat') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">DNI</label>
                      <input
                        v-model="form.referente.dni"
                        class="form-control"
                        :class="{ 'is-invalid': hasError('referente.dni') }"
                        maxlength="8"
                        @keypress="soloNumeros"
                        placeholder="Ej: 12345678"
                        @input="clearFieldError('referente.dni')"
                      />
                      <div class="invalid-feedback" v-if="hasError('referente.dni')">
                        {{ firstError('referente.dni') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Celular</label>
                      <input
                        v-model="form.referente.celular"
                        class="form-control"
                        :class="{ 'is-invalid': hasError('referente.celular') }"
                        maxlength="9"
                        @keypress="soloNumeros"
                        placeholder="Ej: 999999999"
                        @input="clearFieldError('referente.celular')"
                      />
                      <div class="invalid-feedback" v-if="hasError('referente.celular')">
                        {{ firstError('referente.celular') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Parentesco / Relación</label>

                      <select
                        v-model="form.referente.parentesco"
                        class="form-select"
                        :class="{ 'is-invalid': hasError('referente.parentesco') }"
                        @change="clearFieldError('referente.parentesco')"
                      >
                        <option value="">-- Seleccione --</option>
                        <option v-for="p in parentescos" :key="p" :value="p">
                          {{ p }}
                        </option>
                      </select>

                      <div class="invalid-feedback" v-if="hasError('referente.parentesco')">
                        {{ firstError('referente.parentesco') }}
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Email (opcional)</label>
                      <input
                        v-model="form.referente.email"
                        type="email"
                        class="form-control"
                        :class="{ 'is-invalid': hasError('referente.email') }"
                        placeholder="correo@dominio.com"
                        @input="clearFieldError('referente.email')"
                      />
                      <div class="invalid-feedback" v-if="hasError('referente.email')">
                        {{ firstError('referente.email') }}
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label">Dirección (opcional)</label>
                      <textarea
                        v-model="form.referente.direccion"
                        class="form-control"
                        rows="2"
                        :class="{ 'is-invalid': hasError('referente.direccion') }"
                        placeholder="Av / Jr / Mz / Lt..."
                        @input="clearFieldError('referente.direccion')"
                      ></textarea>
                      <div class="invalid-feedback" v-if="hasError('referente.direccion')">
                        {{ firstError('referente.direccion') }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- INFO -->
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
              <div
                class="rounded-circle d-flex align-items-center justify-content-center border"
                style="width: 160px; height: 160px; overflow: hidden;"
              >
                <img
                  v-if="photoPreview"
                  :src="photoPreview"
                  alt="Foto"
                  style="width: 100%; height: 100%; object-fit: cover;"
                />
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
                <button
                  type="button"
                  class="btn btn-light w-100"
                  @click="removePhoto"
                  :disabled="!photoFile && !photoPreview"
                >
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
