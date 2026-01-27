<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import useHelper from '@/Helpers'
import useCliente from '@/Composables/Cliente.js'
import useActividadNegocio from '@/Composables/ActividadNegocio.js'
import useUbigeo from '@/Composables/Ubigeo.js'
import Resumen from '@/Pages/Clientes/Resumen.vue'
import Prestamo from '@/Pages/Prestamos/Form.vue'

const router = useRouter()
const { Toast, soloNumeros, Swal, openModal } = useHelper()

const {
  errors, respuesta, agregarCliente,
  obtenerClienteReciente, cliente,
  obtenerClienteRecientePdf, pdfUrl,
  existeClientePorDni, existeCliente
} = useCliente()

const {
  actividadNegocios,
  listaActividadNegocios,
  listaDetalleActividadNegocios,
  detalleActividadNegocios
} = useActividadNegocio()

const {
  obtenerDepartamentos, departamentos,
  obtenerProvinciasPorDepartamento, provincias,
  obtenerDistritosPorProvincia, distritos,
  obtenerUbigeo, registro,
  buscarDistritos
} = useUbigeo()



// ----------------- UBIGEO UI (NAC / DOM) -----------------
const ubigeoModeNac = ref('select') // 'select' | 'search'
const ubigeoModeDom = ref('select')

const nac = ref({ dep: '', prov: '', dist: '' })
const dom = ref({ dep: '', prov: '', dist: '' })

const provinciasNac = ref([])
const distritosNac = ref([])

const provinciasDom = ref([])
const distritosDom = ref([])

// Para el modo búsqueda (autocomplete)
const buscarNac = ref('')
const buscarDom = ref('')
const resultadosNac = ref([]) // lista simple para UI
const resultadosDom = ref([])

const loadingNac = ref(false)
const loadingDom = ref(false)

watch(() => nac.value.dist, (ub) => { form.value.ubigeo_nac = ub || '' })
watch(() => dom.value.dist, (ub) => { form.value.ubigeo_dom = ub || '' })

// Cambios jerárquicos (NAC)
watch(() => nac.value.dep, async (dep) => {
  nac.value.prov = ''
  nac.value.dist = ''
  provinciasNac.value = []
  distritosNac.value = []
  form.value.ubigeo_nac = ''
  if (!dep) return

  loadingNac.value = true
  try {
    // usa tu endpoint
    await obtenerProvinciasPorDepartamento(dep)
    provinciasNac.value = provincias.value
  } finally {
    loadingNac.value = false
  }
})

watch(() => nac.value.prov, async (prov) => {
  nac.value.dist = ''
  distritosNac.value = []
  form.value.ubigeo_nac = ''
  if (!prov) return

  loadingNac.value = true
  try {
    await obtenerDistritosPorProvincia(prov)
    distritosNac.value = distritos.value
  } finally {
    loadingNac.value = false
  }
})

// Cambios jerárquicos (DOM)
watch(() => dom.value.dep, async (dep) => {
  dom.value.prov = ''
  dom.value.dist = ''
  provinciasDom.value = []
  distritosDom.value = []
  form.value.ubigeo_dom = ''
  if (!dep) return

  loadingDom.value = true
  try {
    await obtenerProvinciasPorDepartamento(dep)
    provinciasDom.value = provincias.value
  } finally {
    loadingDom.value = false
  }
})

watch(() => dom.value.prov, async (prov) => {
  dom.value.dist = ''
  distritosDom.value = []
  form.value.ubigeo_dom = ''
  if (!prov) return

  loadingDom.value = true
  try {
    await obtenerDistritosPorProvincia(prov)
    distritosDom.value = distritos.value
  } finally {
    loadingDom.value = false
  }
})

// Auto-set si escriben ubigeo manual: usa tu obtenerPorUbigeo
const syncFromUbigeo = async (which, ubigeo) => {
  if (!ubigeo || String(ubigeo).length !== 6) return
  try {
    await obtenerUbigeo(ubigeo)
    const r = registro.value
    if (!r) return

    const depId = r?.provincia?.departamento_id
    const provId = r?.provincia?.id
    const disUb = r?.ubigeo

    if (which === 'nac') {
      ubigeoModeNac.value = 'select'
      nac.value.dep = depId
      // provincias se cargarán por watch(dep). Esperamos microtick y seteamos
      setTimeout(() => {
        nac.value.prov = provId
        setTimeout(() => (nac.value.dist = disUb), 0)
      }, 0)
    } else {
      ubigeoModeDom.value = 'select'
      dom.value.dep = depId
      setTimeout(() => {
        dom.value.prov = provId
        setTimeout(() => (dom.value.dist = disUb), 0)
      }, 0)
    }
  } catch (e) {}
}

// búsqueda (autocomplete)
let tNac = null
let tDom = null

watch(buscarNac, (q) => {
  clearTimeout(tNac)
  resultadosNac.value = []
  if (!q || q.trim().length < 3) return
  tNac = setTimeout(async () => {
    await buscarDistritos({ buscar: q, paginacion: 8 })
    resultadosNac.value = distritos.value?.data || []
  }, 300)
})

watch(buscarDom, (q) => {
  clearTimeout(tDom)
  resultadosDom.value = []
  if (!q || q.trim().length < 3) return
  tDom = setTimeout(async () => {
    await buscarDistritos({ buscar: q, paginacion: 8 })
    resultadosDom.value = distritos.value?.data || []
  }, 300)
})

const selectFromSearch = (which, item) => {
  // item: {ubigeo, distrito, provincia, departamento}
  if (which === 'nac') {
    form.value.ubigeo_nac = item.ubigeo
    buscarNac.value = `${item.departamento} / ${item.provincia} / ${item.distrito}`
    resultadosNac.value = []
  } else {
    form.value.ubigeo_dom = item.ubigeo
    buscarDom.value = `${item.departamento} / ${item.provincia} / ${item.distrito}`
    resultadosDom.value = []
  }
}

onMounted(async () => {
  document.title = 'Registro de Clientes'
  await listaActividadNegocios()
  await obtenerClienteReciente()
  await obtenerDepartamentos()
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


/* ----------------- UI State ----------------- */
const isSaving = ref(false)

/* ----------------- GEO ----------------- */
const geoLoading = ref(false)
const geoMsg = ref('')

const setLatLng = (lat, lng) => {
  const latF = Number(lat).toFixed(6)
  const lngF = Number(lng).toFixed(6)
  form.value.latitud_longitud = `${latF},${lngF}`
  clearFieldError('latitud_longitud')
}

const parseLatLng = (s) => {
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
      if (err.code === 1) geoMsg.value = 'Permiso denegado para acceder a la ubicación.'
      else if (err.code === 2) geoMsg.value = 'No se pudo determinar la ubicación (señal/GPS).'
      else geoMsg.value = 'Error al obtener la ubicación.'
    },
    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
  )
}

watch(
  () => form.value.latitud_longitud,
  (v) => {
    if (!v) return
    geoMsg.value = parseLatLng(v) ? '' : 'Formato sugerido: -9.930123,-76.242991'
  }
)

/* ----------------- LISTAS ----------------- */
const parentescos = [
  'PADRE','MADRE','HIJO(A)','HERMANO(A)','ABUELO(A)','NIETO(A)','TÍO(A)','SOBRINO(A)','PRIMO(A)',
  'CUÑADO(A)','SUEGRO(A)','YERNO','NUERA','ESPOSO(A)','CONVIVIENTE','PAREJA','FAMILIAR','AMIGO(A)',
  'VECINO(A)','COMPAÑERO(A) DE TRABAJO','JEFE(A)','OTRO'
]

const gradosInstruccion = [
  'SIN INSTRUCCION','INICIAL','PRIMARIA','SECUNDARIA','SUPERIOR TECNICO','SUPERIOR UNIVERSITARIO','POSTGRADO'
]

const profesionesPeru = [
  'ADMINISTRACIÓN','ADMINISTRACIÓN DE EMPRESAS','AGRONOMÍA','ARQUITECTURA','BIOLOGÍA','CIENCIAS DE LA COMUNICACIÓN',
  'CIENCIAS CONTABLES','CONTABILIDAD','DERECHO','DISEÑO GRÁFICO','ECONOMÍA','EDUCACIÓN INICIAL','EDUCACIÓN PRIMARIA',
  'EDUCACIÓN SECUNDARIA','ENFERMERÍA','ESTADÍSTICA','FARMACIA Y BIOQUÍMICA','GASTRONOMÍA','INGENIERÍA AGRÍCOLA',
  'INGENIERÍA AGROINDUSTRIAL','INGENIERÍA AMBIENTAL','INGENIERÍA CIVIL','INGENIERÍA DE MINAS','INGENIERÍA DE SISTEMAS',
  'INGENIERÍA ELECTRÓNICA','INGENIERÍA INDUSTRIAL','INGENIERÍA MECÁNICA','INGENIERÍA PESQUERA','INGENIERÍA QUÍMICA',
  'MARKETING','MEDICINA HUMANA','MEDICINA VETERINARIA','NUTRICIÓN','OBSTETRICIA','ODONTOLOGÍA','PSICOLOGÍA','SOCIOLOGÍA',
  'TRABAJO SOCIAL','TURISMO Y HOTELERÍA'
]

const esSuperior = computed(() => {
  const g = String(form.value.grado_instr || '').toUpperCase()
  return g.includes('SUPERIOR') || g.includes('POSTGRADO')
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

const validarDni = async (dni) => {
  if (!dni) return
  await existeClientePorDni(dni)
  if(existeCliente.value){
    form.value.dni = ''
    resetForm()
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'El DNI ya existe',
    })
  }
}

/* ----------------- ERRORS ----------------- */
const toArr = (v) => (Array.isArray(v) ? v.map(String) : v ? [String(v)] : [])

const normalizeErrors = (payload) => {
  const out = {}
  const src = payload?.errors && typeof payload.errors === 'object' ? payload.errors : payload

  if (src && typeof src === 'object') {
    for (const k of Object.keys(src)) out[k] = toArr(src[k])
  }

  for (const k of Object.keys(out)) {
    const m = k.match(/^negocio\[(.+)\]$/)
    if (m?.[1]) out[`negocio.${m[1]}`] ||= out[k]
    const r = k.match(/^referente\[(.+)\]$/)
    if (r?.[1]) out[`referente.${r[1]}`] ||= out[k]
  }
  return out
}

const setErrorsFromComposable = () => { form.value.errors = normalizeErrors(errors.value) }
const clearErrors = () => { form.value.errors = {} }
const hasError = (name) => toArr(form.value.errors?.[name]).length > 0
const firstError = (name) => toArr(form.value.errors?.[name])[0] || ''
const clearFieldError = (name) => { if (form.value.errors?.[name]) delete form.value.errors[name] }

const scrollToFirstInvalid = async () => {
  await nextTick()
  const el = document.querySelector('.is-invalid')
  if (el?.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

/* ----------------- INDEPENDIENTE / NEGOCIO ----------------- */
const esIndependiente = computed(() => form.value.origen_labor === 'INDEPENDIENTE')

const resetNegocio = () => { form.value.negocio = NEGOCIO_DEFAULT() }

watch(
  () => form.value.origen_labor,
  (v) => {
    if (v !== 'INDEPENDIENTE') {
      resetNegocio()
      for (const k of Object.keys(form.value.errors || {})) {
        if (k.startsWith('negocio.') || k.startsWith('negocio[')) delete form.value.errors[k]
      }
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
    Swal.fire({ icon: 'warning', title: 'Archivo inválido', text: 'Selecciona una imagen válida.' })
    e.target.value = ''
    return
  }

  const maxMB = 4
  if (file.size > maxMB * 1024 * 1024) {
    Swal.fire({ icon: 'warning', title: 'Imagen muy grande', text: `La imagen no debe superar ${maxMB}MB.` })
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

/* ----------------- HELPERS ----------------- */
const appendIfFilled = (fd, key, value) => {
  if (value === null || value === undefined || value === '') return
  fd.append(key, value)
}

const fullName = computed(() => {
  const p = form.value
  return [p.ape_pat, p.ape_mat, p.primernombre, p.otrosnombres]
    .filter(Boolean).join(' ').replace(/\s+/g, ' ').trim()
})

/* ----------------- PDF (modal) ----------------- */
const openPdf = async (cliente_id) => {
  if (!cliente_id) return
  await obtenerClienteRecientePdf(cliente_id)
  const t = document.getElementById('impresionresumenLabel')
  if (t) t.innerHTML = 'Resumen Cliente'
  openModal('#impresionresumen')
}

/* ----------------- GUARDAR ----------------- */
const guardar = async () => {
  if (isSaving.value) return
  clearErrors()
  isSaving.value = true

  try {
    const fd = new FormData()
    const { negocio, referente, errors: _errors, ...plain } = form.value // eslint-disable-line no-unused-vars

    for (const [k, v] of Object.entries(plain)) appendIfFilled(fd, k, v)

    if (esIndependiente.value) {
      for (const [k, v] of Object.entries(negocio || {})) appendIfFilled(fd, `negocio[${k}]`, v)
    }

    for (const [k, v] of Object.entries(referente || {})) appendIfFilled(fd, `referente[${k}]`, v)
    if (photoFile.value instanceof File) fd.append('foto', photoFile.value)

    await agregarCliente(fd)

    if (errors.value) {
      setErrorsFromComposable()
      await scrollToFirstInvalid()
      return
    }

    if (respuesta.value?.ok == 1) {
      const cliente = respuesta.value?.cliente
      const html = `
        <div class="text-start">
          <div class="mb-2"><b>Cliente:</b> ${fullName.value || '-'}</div>
          <div class="mb-2"><b>Codigo:</b> ${cliente.id || '-'}</div>
          <div class="mb-2"><b>DNI:</b> ${form.value.dni || '-'}</div>
          <div class="mb-2"><b>Celular:</b> ${form.value.celular || '-'}</div>
          <div class="mb-2"><b>Origen:</b> ${form.value.origen_labor || '-'}</div>
        </div>
      `

      const result = await Swal.fire({
        title: respuesta.value?.mensaje || 'Registro exitoso',
        icon: 'success',
        html,
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: 'Nuevo registro',
        denyButtonText: 'Ver PDF',
        cancelButtonText: 'Cerrar',
        reverseButtons: true
      })

      // Actualiza "cliente reciente" (para tener id seguro)
      await obtenerClienteReciente()

      if (result.isDenied) {
        await openPdf(cliente.value?.id)
      }

      if (result.isConfirmed) {
        resetForm()
      }
    }
  } catch (e) {
    Toast?.error ? Toast.error('Ocurrió un error al guardar.') : console.error(e)
  } finally {
    isSaving.value = false
  }
}

const resetForm = () => {
  const keep = {
    genero: 'M',
    estado_civil: 'SOLTERO',
    origen_labor: 'INDEPENDIENTE',
    estado: 'ACTIVO'
  }

  for (const k of Object.keys(form.value)) {
    if (k === 'errors' || k === 'negocio' || k === 'referente') continue
    form.value[k] = keep[k] ?? ''
  }

  resetNegocio()
  form.value.referente = REFERENTE_DEFAULT()
  clearErrors()
  removePhoto()
  detalleActividadNegocios.value = []
}

const cancelar = () => router.push({ name: 'Principal' })
</script>


<template>
  <div class="page-content">
    <section class="row">


      <!-- IZQUIERDA -->
      <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div>
              <h3 class="mb-0">Cliente Nuevo</h3>
              <div class="text-muted small">Complete los datos principales y del referente.</div>
            </div>
            <span class="badge bg-light text-dark border">Registro</span>
          </div>

          <div class="card-body">
            <!-- Sección: Datos del Cliente -->
            <div class="section-title mb-2">
              <i class="bi bi-person-lines-fill me-2"></i> Datos del Cliente
            </div>

            <div class="row g-3">
              <div class="col-12 col-md-3">
                <label class="form-label">Primer nombre</label>
                <input
                  v-model.trim="form.primernombre"
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
                <input
                  v-model.trim="form.otrosnombres"
                  class="form-control"
                  :class="{ 'is-invalid': hasError('otrosnombres') }"
                  placeholder="Ej: Carlos Alberto"
                  @input="clearFieldError('otrosnombres')"
                />
                <div class="invalid-feedback" v-if="hasError('otrosnombres')">
                  {{ firstError('otrosnombres') }}
                </div>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label">Apellido paterno</label>
                <input
                  v-model.trim="form.ape_pat"
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
                  v-model.trim="form.ape_mat"
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
                  @change="validarDni(form.dni)"
                  placeholder="Ej: 12345678"
                  @input="clearFieldError('dni')"
                />
                <div class="invalid-feedback" v-if="hasError('dni')">
                  {{ firstError('dni') }}
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between">
                  <label class="form-label mb-0">Ubigeo Nacimiento</label>
                  <div class="btn-group btn-group-sm">
                    <button type="button"
                      class="btn"
                      :class="ubigeoModeNac==='select' ? 'btn-primary' : 'btn-outline-primary'"
                      @click="ubigeoModeNac='select'">
                      Seleccionar
                    </button>
                    <button type="button"
                      class="btn"
                      :class="ubigeoModeNac==='search' ? 'btn-primary' : 'btn-outline-primary'"
                      @click="ubigeoModeNac='search'">
                      Buscar
                    </button>
                  </div>
                </div>

                <!-- MODO SELECT -->
                <div v-if="ubigeoModeNac==='select'" class="row g-2 mt-1">
                  <div class="col-12 col-md-4">
                    <select v-model="nac.dep" class="form-select">
                      <option value="">Departamento</option>
                      <option v-for="d in departamentos.data || departamentos" :key="d.id" :value="d.id">
                        {{ d.nombre }}
                      </option>
                    </select>
                  </div>

                  <div class="col-12 col-md-4">
                    <select v-model="nac.prov" class="form-select" :disabled="!nac.dep">
                      <option value="">Provincia</option>
                      <option v-for="p in provinciasNac" :key="p.id" :value="p.id">
                        {{ p.nombre }}
                      </option>
                    </select>
                  </div>

                  <div class="col-12 col-md-4">
                    <select v-model="nac.dist" class="form-select" :disabled="!nac.prov">
                      <option value="">Distrito</option>
                      <option v-for="di in distritosNac" :key="di.ubigeo" :value="di.ubigeo">
                        {{ di.nombre }} — {{ di.ubigeo }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- MODO SEARCH -->
                <div v-else class="mt-2 position-relative">
                  <input
                    v-model="buscarNac"
                    class="form-control"
                    placeholder="Escribe: distrito / provincia / departamento (min 3 letras)"
                  />

                  <div v-if="resultadosNac.length" class="list-group position-absolute w-100 shadow" style="z-index: 20;">
                    <button
                      v-for="it in resultadosNac"
                      :key="it.ubigeo"
                      type="button"
                      class="list-group-item list-group-item-action"
                      @click="selectFromSearch('nac', it)">
                      <div class="fw-semibold">{{ it.distrito }} — {{ it.ubigeo }}</div>
                      <div class="small text-muted">{{ it.departamento }} / {{ it.provincia }}</div>
                    </button>
                  </div>
                </div>

                <!-- Ubigeo final -->
                <input
                  v-model.trim="form.ubigeo_nac"
                  class="form-control mt-2"
                  :class="{ 'is-invalid': hasError('ubigeo_nac') }"
                  maxlength="6"
                  placeholder="Ubigeo (6 dígitos) - auto"
                  @input="syncFromUbigeo('nac', form.ubigeo_nac)"
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
                  v-model.trim="form.email"
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

              <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between">
                  <label class="form-label mb-0">Ubigeo Domicilio</label>
                  <div class="btn-group btn-group-sm">
                    <button type="button"
                      class="btn"
                      :class="ubigeoModeDom==='select' ? 'btn-primary' : 'btn-outline-primary'"
                      @click="ubigeoModeDom='select'">
                      Seleccionar
                    </button>
                    <button type="button"
                      class="btn"
                      :class="ubigeoModeDom==='search' ? 'btn-primary' : 'btn-outline-primary'"
                      @click="ubigeoModeDom='search'">
                      Buscar
                    </button>
                  </div>
                </div>

                <!-- MODO SELECT -->
                <div v-if="ubigeoModeDom==='select'" class="row g-2 mt-1">
                  <div class="col-12 col-md-4">
                    <select v-model="dom.dep" class="form-select">
                      <option value="">Departamento</option>
                      <option v-for="d in departamentos.data || departamentos" :key="d.id" :value="d.id">
                        {{ d.nombre }}
                      </option>
                    </select>
                  </div>

                  <div class="col-12 col-md-4">
                    <select v-model="dom.prov" class="form-select" :disabled="!dom.dep">
                      <option value="">Provincia</option>
                      <option v-for="p in provinciasDom" :key="p.id" :value="p.id">
                        {{ p.nombre }}
                      </option>
                    </select>
                  </div>

                  <div class="col-12 col-md-4">
                    <select v-model="dom.dist" class="form-select" :disabled="!dom.prov">
                      <option value="">Distrito</option>
                      <option v-for="di in distritosDom" :key="di.ubigeo" :value="di.ubigeo">
                        {{ di.nombre }} — {{ di.ubigeo }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- MODO SEARCH -->
                <div v-else class="mt-2 position-relative">
                  <input
                    v-model="buscarDom"
                    class="form-control"
                    placeholder="Escribe: distrito / provincia / departamento (min 3 letras)"
                  />

                  <div v-if="resultadosDom.length" class="list-group position-absolute w-100 shadow" style="z-index: 20;">
                    <button
                      v-for="it in resultadosDom"
                      :key="it.ubigeo"
                      type="button"
                      class="list-group-item list-group-item-action"
                      @click="selectFromSearch('dom', it)">
                      <div class="fw-semibold">{{ it.distrito }} — {{ it.ubigeo }}</div>
                      <div class="small text-muted">{{ it.departamento }} / {{ it.provincia }}</div>
                    </button>
                  </div>
                </div>

                <!-- Ubigeo final -->
                <input
                  v-model.trim="form.ubigeo_dom"
                  class="form-control mt-2"
                  :class="{ 'is-invalid': hasError('ubigeo_dom') }"
                  maxlength="6"
                  placeholder="Ubigeo (6 dígitos) - auto"
                  @input="syncFromUbigeo('dom', form.ubigeo_dom)"
                />
                <div class="invalid-feedback" v-if="hasError('ubigeo_dom')">
                  {{ firstError('ubigeo_dom') }}
                </div>
              </div>


              <div class="col-12 col-md-6">
                <label class="form-label">Dirección</label>
                <textarea
                  v-model.trim="form.direccion"
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

              <div class="col-12 col-md-6">
                <label class="form-label">Ubicación (Latitud, Longitud)</label>

                <div class="input-group">
                  <input
                    v-model.trim="form.latitud_longitud"
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
                <div class="border rounded-3 p-3 section-box">
                  <div class="section-title mb-2">
                    <i class="bi bi-shop me-2"></i> Datos del Negocio
                  </div>

                  <div class="row g-3 mb-1">
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
                      <label class="form-label">Celular / Teléfono</label>
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
                        v-model.trim="form.negocio.razonsocial"
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
                        v-model.trim="form.negocio.direccion"
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
              <div v-else class="mt-3">
                <div class="border rounded-3 p-3 section-box">
                  <div class="section-title mb-2">
                    <i class="bi bi-briefcase me-2"></i> Datos Laborales (Dependiente)
                  </div>

                  <div class="row g-3">
                    <div class="col-12 col-md-6">
                      <label class="form-label">Ocupación</label>
                      <input
                        v-model.trim="form.ocupacion"
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
                        v-model.trim="form.institucion_lab"
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
                </div>
              </div>

              <!-- REFERENTE -->
              <div class="mt-3">
                <div class="border rounded-3 p-3 section-box">
                  <div class="section-title mb-2">
                    <i class="bi bi-people me-2"></i> Datos del Referente
                  </div>

                  <div class="row g-3">
                    <div class="col-12 col-md-3">
                      <label class="form-label">Primer nombre</label>
                      <input
                        v-model.trim="form.referente.primernombre"
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
                        v-model.trim="form.referente.otrosnombres"
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
                        v-model.trim="form.referente.ape_pat"
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
                        v-model.trim="form.referente.ape_mat"
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
                        v-model.trim="form.referente.email"
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
                        v-model.trim="form.referente.direccion"
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


            </div>

            <!-- BOTONES -->
            <div class="d-flex gap-2 mt-4">
              <button type="button" @click="guardar()" class="btn btn-primary" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                <i v-else class="bi bi-save me-1"></i>
                {{ isSaving ? 'Guardando...' : 'Guardar' }}
              </button>

              <button type="button" class="btn btn-light" @click="cancelar" :disabled="isSaving">
                Cancelar
              </button>

              <button type="button" class="btn btn-outline-secondary ms-auto" @click="resetForm" :disabled="isSaving">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- DERECHA: FOTO -->
      <div class="col-12 col-lg-4">
        <div class="card shadow-sm sticky-lg-top" style="top: 1rem;">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title mb-0">Foto del cliente</h4>
            <span class="badge bg-light text-secondary">Vista previa</span>
          </div>

          <div class="card-body">
            <div class="d-flex flex-column align-items-center gap-3">

              <div class="avatar-box rounded-circle d-flex align-items-center justify-content-center border">
                <img v-if="photoPreview" :src="photoPreview" alt="Foto" class="avatar-img" />
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

              <!-- Cliente reciente -->
              <div class="w-100" v-if="cliente?.id">
                <div class="border rounded p-3 bg-light">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Cliente reciente</h6>
                    <span class="badge bg-success-subtle text-success">OK</span>
                  </div>

                  <div class="small">
                    <div class="mb-1">
                      <b>Codigo : </b> {{ cliente.id }}<br>
                      <b>Nombre:</b>
                      {{ cliente.persona?.ape_pat }} {{ cliente.persona?.ape_mat }}
                      {{ cliente.persona?.primernombre }} {{ cliente.persona?.otrosnombres }}
                    </div>
                    <div><b>DNI:</b> {{ cliente.persona?.dni }}</div>
                  </div>

                  <div class="pt-3">
                    <div class="row">
                      <div class="col-6">
                        <button
                          type="button"
                          class="btn btn-outline-danger w-100"
                          @click="openPdf(cliente.id)"
                        >
                          <i class="bi bi-filetype-pdf me-1"></i> Ver resumen PDF
                        </button>
                      </div>
                      <div class="col-6">
                        <button
                        type="button"
                        class="btn btn-outline-warning w-100"
                        @click="openModal('#prestamomodal')"
                        >
                        Solicitar Prestamo
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </section>
  </div>
  <Resumen :url="pdfUrl" />
  <Prestamo :cliente="cliente" />
</template>

<style scoped>
.section-title{
  font-weight: 700;
  font-size: .95rem;
  color: rgba(0,0,0,.7);
  padding: .35rem .5rem;
  border-left: 4px solid rgba(13,110,253,.35);
  background: rgba(13,110,253,.05);
  border-radius: .35rem;
}
.section-box{
  background: rgba(0,0,0,.015);
}
.avatar-box{
  width: 160px;
  height: 160px;
  overflow: hidden;
  background: rgba(0,0,0,.02);
}
.avatar-img{
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
