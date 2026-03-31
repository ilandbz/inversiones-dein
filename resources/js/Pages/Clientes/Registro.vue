<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
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

const formPrestamo = ref({
  id: '',
  cliente_id: '',
  cliente_apenom : '',
  asesor_id: '',
  aval_id: null,
  tipo: 'NUEVO',
  monto: '',
  origen_financiamiento_id: '',
  frecuencia: 'MENSUAL',
  plazo: '',

  fuenterecursos: '',

  tasainteres: '0.00',
  costomora: '0.00',
  total: '0.00',

  estadoCrud: 'nuevo',
  errors: {}
})

const abrirPrestamo = (cliente) => {
  formPrestamo.value.cliente_id = cliente.id
  formPrestamo.value.cliente_apenom = cliente.persona.apenom
  form.value.estadoCrud = 'nuevo'
  document.getElementById("prestamomodalLabel").innerHTML = 'Solicitar Prestamo';
  openModal('#prestamomodal')
}

const ubigeoTextoNac = ref('')
const ubigeoTextoDom = ref('')

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

const syncFromUbigeo = async (which, ubigeo) => {
  if (!ubigeo || String(ubigeo).length !== 6) {
    if (which === 'nac') {
      ubigeoTextoNac.value = ''
    } else {
      ubigeoTextoDom.value = ''
    }
    return
  }

  try {
    await obtenerUbigeo(ubigeo)
    const r = registro.value
    if (!r) return

    const depId = r?.provincia?.departamento_id
    const provId = r?.provincia?.id
    const disUb = r?.ubigeo

    const texto = `${r?.provincia?.departamento?.nombre || ''} / ${r?.provincia?.nombre || ''} / ${r?.nombre || ''}`

    if (which === 'nac') {
      ubigeoModeNac.value = 'select'
      nac.value.dep = depId || ''
      form.value.ubigeo_nac = disUb || ''

      await obtenerProvinciasPorDepartamento(depId)
      provinciasNac.value = provincias.value || []
      nac.value.prov = provId || ''

      await obtenerDistritosPorProvincia(provId)
      distritosNac.value = distritos.value || []
      nac.value.dist = disUb || ''

      ubigeoTextoNac.value = texto
      buscarNac.value = texto
    } else {
      ubigeoModeDom.value = 'select'
      dom.value.dep = depId || ''
      form.value.ubigeo_dom = disUb || ''

      await obtenerProvinciasPorDepartamento(depId)
      provinciasDom.value = provincias.value || []
      dom.value.prov = provId || ''

      await obtenerDistritosPorProvincia(provId)
      distritosDom.value = distritos.value || []
      dom.value.dist = disUb || ''

      ubigeoTextoDom.value = texto
      buscarDom.value = texto
    }
  } catch (e) {
    if (which === 'nac') ubigeoTextoNac.value = ''
    else ubigeoTextoDom.value = ''
  }
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
  const texto = `${item.departamento} / ${item.provincia} / ${item.distrito}`

  if (which === 'nac') {
    form.value.ubigeo_nac = item.ubigeo
    buscarNac.value = texto
    ubigeoTextoNac.value = texto
    resultadosNac.value = []
  } else {
    form.value.ubigeo_dom = item.ubigeo
    buscarDom.value = texto
    ubigeoTextoDom.value = texto
    resultadosDom.value = []
  }
}

const getClienteReciente = async () => {
  await obtenerClienteReciente()
}

onMounted(async () => {
  document.title = 'Registro de Clientes'
  await listaActividadNegocios()
  await getClienteReciente()
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
  id : '',
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
  estadoCrud: '',     
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
const hasError = (name) => {
  const errs = form.value.errors?.[name]
  return Array.isArray(errs) ? errs.length > 0 : !!errs
}
const firstError = (name) => {
  const errs = toArr(form.value.errors?.[name])
  return errs[0] || ''
}
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
  await nextTick()
  const t = document.getElementById('impresionresumenLabel')
  if (t) t.innerHTML = 'Resumen Cliente'
  setTimeout(() => { openModal('#impresionresumen') }, 150)
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
          <div class="mb-2 small"><b>Cliente:</b> ${fullName.value || '-'}</div>
          <div class="mb-2 small"><b>DNI:</b> ${form.value.dni || '-'}</div>
          <div class="mb-2 small"><b>Tipo:</b> ${form.value.origen_labor || '-'}</div>
        </div>
      `

      const result = await Swal.fire({
        title: '¡Registro Exitoso!',
        icon: 'success',
        html,
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: 'Nuevo registro',
        denyButtonText: 'Ver PDF',
        cancelButtonText: 'Cerrar',
        customClass: {
            confirmButton: 'btn btn-primary rounded-pill px-4 ms-2',
            denyButton: 'btn btn-outline-dark rounded-pill px-4 ms-2',
            cancelButton: 'btn btn-light rounded-pill px-4 ms-2'
        },
        buttonsStyling: false,
        reverseButtons: true
      })

      await obtenerClienteReciente()
      if (result.isDenied) await openPdf(cliente.id)
      resetForm()
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

ubigeoTextoNac.value = ''
ubigeoTextoDom.value = ''
buscarNac.value = ''
buscarDom.value = ''
nac.value = { dep: '', prov: '', dist: '' }
dom.value = { dep: '', prov: '', dist: '' }
provinciasNac.value = []
distritosNac.value = []
provinciasDom.value = []
distritosDom.value = []

}

const cancelar = () => router.push({ name: 'Principal' })
</script>

<template>
  <AppLayoutDefault title="Registro de Clientes">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Nuevo Socio Receptivo</h3>
                <p class="text-muted small mb-0">Complete el expediente digital del nuevo cliente para evaluación crediticia</p>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <button class="btn btn-light rounded-pill px-4 shadow-sm fw-bold small text-uppercase" @click="cancelar">
                        <i class="fas fa-times me-2 opacity-50"></i> Cancelar
                    </button>
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold small text-uppercase" @click="guardar" :disabled="isSaving">
                        <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="fas fa-save me-2 opacity-50"></i> Guardar Expediente
                    </button>
                </div>
            </div>
        </div>

        <div class="row g-4">
          <!-- LEFT FORM COLUMN -->
          <div class="col-lg-8">
            <!-- 1. Información Personal -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center">
                    <div class="icon-box bg-primary text-white rounded-3 me-3"><i class="fas fa-user"></i></div>
                    <h5 class="fw-bold text-dark mb-0">Información de Identidad</h5>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">DNI / DOCUMENTO</label>
                            <input v-model="form.dni" type="text" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" maxlength="8" @keypress="soloNumeros" @change="validarDni(form.dni)" :class="{'is-invalid': hasError('dni')}" placeholder="12345678">
                            <div class="invalid-feedback" v-if="hasError('dni')">{{ firstError('dni') }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">PRIMER NOMBRE</label>
                            <input v-model.trim="form.primernombre" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" :class="{'is-invalid': hasError('primernombre')}" placeholder="Ej: Juan">
                            <div class="invalid-feedback" v-if="hasError('primernombre')">{{ firstError('primernombre') }}</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-bold">OTROS NOMBRES</label>
                            <input v-model.trim="form.otrosnombres" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" placeholder="Ej: Carlos Alberto">
                            <div class="invalid-feedback" v-if="hasError('otrosnombres')">{{ firstError('otrosnombres') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">APELLIDO PATERNO</label>
                            <input v-model.trim="form.ape_pat" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" :class="{'is-invalid': hasError('ape_pat')}" placeholder="Ej: Pérez">
                            <div class="invalid-feedback" v-if="hasError('ape_pat')">{{ firstError('ape_pat') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">APELLIDO MATERNO</label>
                            <input v-model.trim="form.ape_mat" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" :class="{'is-invalid': hasError('ape_mat')}" placeholder="Ej: Gómez">
                            <div class="invalid-feedback" v-if="hasError('ape_mat')">{{ firstError('ape_mat') }}</div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">FECHA NACIMIENTO</label>
                            <input v-model="form.fecha_nac" type="date" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" :class="{'is-invalid': hasError('fecha_nac')}">
                            <div class="invalid-feedback" v-if="hasError('fecha_nac')">{{ firstError('fecha_nac') }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">GÉNERO</label>
                            <select v-model="form.genero" class="form-select rounded-pill bg-light border-0 px-3 shadow-none">
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">ESTADO CIVIL</label>
                            <select v-model="form.estado_civil" class="form-select rounded-pill bg-light border-0 px-3 shadow-none">
                                <option>SOLTERO</option><option>CASADO</option><option>CONVIVIENTE</option><option>DIVORCIADO</option><option>VIUDO</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label text-muted small fw-bold">LUGAR DE NACIMIENTO / UBIGEO</label>
                          <div class="d-flex gap-2 mb-2">
                            <div class="btn-group btn-group-sm rounded-pill overflow-hidden border">
                              <button type="button" class="btn btn-light border-0 px-3" :class="{'bg-primary text-white': ubigeoModeNac==='manual'}" @click="ubigeoModeNac='manual'">Manual</button>
                              <button type="button" class="btn btn-light border-0 px-3" :class="{'bg-primary text-white': ubigeoModeNac==='select'}" @click="ubigeoModeNac='select'">Selección</button>
                            </div>
                          </div>

                          <div v-if="ubigeoModeNac==='manual'">
                            <input
                              v-model.trim="form.ubigeo_nac"
                              class="form-control rounded-pill bg-light border-0 px-3 shadow-none"
                              maxlength="6"
                              @input="syncFromUbigeo('nac', form.ubigeo_nac)"
                              placeholder="Código de 6 dígitos"
                            >
                          </div>
                          
                          <div v-else class="row g-2">
                            <div class="col-md-4">
                              <select v-model="nac.dep" class="form-select form-select-sm rounded-pill bg-light border-0 px-2 shadow-none small">
                                <option value="">Departamento</option>
                                <option v-for="d in (departamentos.data || departamentos)" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                              </select>
                            </div>
                            <div class="col-md-4">
                              <select v-model="nac.prov" class="form-select form-select-sm rounded-pill bg-light border-0 px-2 shadow-none small" :disabled="!nac.dep || loadingNac">
                                <option value="">Provincia</option>
                                <option v-for="p in provinciasNac" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                              </select>
                            </div>
                            <div class="col-md-4">
                              <select v-model="nac.dist" class="form-select form-select-sm rounded-pill bg-light border-0 px-2 shadow-none small" :disabled="!nac.prov || loadingNac">
                                <option value="">Distrito</option>
                                <option v-for="d in distritosNac" :key="d.ubigeo" :value="d.ubigeo">{{ d.nombre }}</option>
                              </select>
                            </div>
                          </div>

                          <small v-if="ubigeoTextoNac" class="text-primary fw-bold d-block mt-1 extra-small">
                            <i class="fas fa-map-marker-alt me-1"></i> {{ ubigeoTextoNac }} ({{ form.ubigeo_nac }})
                          </small>
                        </div>

                        <!-- Grado de Instrucción y Profesión -->
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">GRADO DE INSTRUCCIÓN</label>
                            <select v-model="form.grado_instr" class="form-select rounded-pill bg-light border-0 px-3 shadow-none" :class="{'is-invalid': hasError('grado_instr')}">
                                <option value="">-- Seleccionar --</option>
                                <option v-for="g in gradosInstruccion" :key="g" :value="g">{{ g }}</option>
                            </select>
                            <div class="invalid-feedback" v-if="hasError('grado_instr')">{{ firstError('grado_instr') }}</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-bold">PROFESIÓN</label>
                            <select v-model="form.profesion" class="form-select rounded-pill bg-light border-0 px-3 shadow-none" :disabled="!esSuperior" :class="{'is-invalid': hasError('profesion')}">
                                <option value="">-- No Aplica / Otros --</option>
                                <option v-for="p in profesionesPeru" :key="p" :value="p">{{ p }}</option>
                            </select>
                            <div class="invalid-feedback" v-if="hasError('profesion')">{{ firstError('profesion') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Contacto y Ubicación -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center">
                    <div class="icon-box bg-success text-white rounded-3 me-3"><i class="fas fa-map-marker-alt"></i></div>
                    <h5 class="fw-bold text-dark mb-0">Contacto y Residencia</h5>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">CELULAR PRINCIPAL</label>
                            <input v-model="form.celular" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" maxlength="9" @keypress="soloNumeros" placeholder="999 999 999">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">CELULAR SECUNDARIO</label>
                            <input v-model="form.celular2" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" maxlength="9" @keypress="soloNumeros" placeholder="Opcional">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">CORREO ELECTRÓNICO</label>
                            <input v-model.trim="form.email" type="email" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" placeholder="socio@ejemplo.com">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label text-muted small fw-bold">DIRECCIÓN DOMICILIARIA ACTUAL</label>
                            <textarea v-model.trim="form.direccion" class="form-control rounded-4 bg-light border-0 px-3 shadow-none" rows="2" placeholder="Av / Jr / Calle / Psje / Mz / Lt..." :class="{'is-invalid': hasError('direccion')}"></textarea>
                            <div class="invalid-feedback" v-if="hasError('direccion')">{{ firstError('direccion') }}</div>
                        </div>

                        <div class="col-md-12 mt-2">
                          <label class="form-label text-muted small fw-bold">UBIGEO DOMICILIO (RESIDENCIA)</label>
                          <div class="d-flex gap-2 mb-2">
                            <div class="btn-group btn-group-sm rounded-pill overflow-hidden border">
                              <button type="button" class="btn btn-light border-0 px-3" :class="{'bg-primary text-white': ubigeoModeDom==='manual'}" @click="ubigeoModeDom='manual'">Manual</button>
                              <button type="button" class="btn btn-light border-0 px-3" :class="{'bg-primary text-white': ubigeoModeDom==='select'}" @click="ubigeoModeDom='select'">Selección</button>
                            </div>
                          </div>

                          <div v-if="ubigeoModeDom==='manual'">
                            <input
                              v-model.trim="form.ubigeo_dom"
                              class="form-control rounded-pill bg-light border-0 px-3 shadow-none"
                              maxlength="6"
                              @input="syncFromUbigeo('dom', form.ubigeo_dom)"
                              placeholder="Código de 6 dígitos"
                            >
                          </div>
                          
                          <div v-else class="row g-2">
                            <div class="col-md-4">
                              <select v-model="dom.dep" class="form-select form-select-sm rounded-pill bg-light border-0 px-2 shadow-none small">
                                <option value="">Departamento</option>
                                <option v-for="d in (departamentos.data || departamentos)" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                              </select>
                            </div>
                            <div class="col-md-4">
                              <select v-model="dom.prov" class="form-select form-select-sm rounded-pill bg-light border-0 px-2 shadow-none small" :disabled="!dom.dep || loadingDom">
                                <option value="">Provincia</option>
                                <option v-for="p in provinciasDom" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                              </select>
                            </div>
                            <div class="col-md-4">
                              <select v-model="dom.dist" class="form-select form-select-sm rounded-pill bg-light border-0 px-2 shadow-none small" :disabled="!dom.prov || loadingDom">
                                <option value="">Distrito</option>
                                <option v-for="d in distritosDom" :key="d.ubigeo" :value="d.ubigeo">{{ d.nombre }}</option>
                              </select>
                            </div>
                          </div>

                          <small v-if="ubigeoTextoDom" class="text-primary fw-bold d-block mt-1 extra-small">
                            <i class="fas fa-map-marker-alt me-1"></i> {{ ubigeoTextoDom }} ({{ form.ubigeo_dom }})
                          </small>
                        </div>
                        <div class="invalid-feedback" v-if="hasError('ubigeo_dom')">{{ firstError('ubigeo_dom') }}</div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">GEOLOCALIZACIÓN (COORDENADAS)</label>
                            <div class="input-group bg-light rounded-pill px-1 shadow-none border-0">
                                <input v-model.trim="form.latitud_longitud" class="form-control bg-transparent border-0 shadow-none px-3" placeholder="Lat, Lng">
                                <button class="btn btn-dark rounded-pill px-3 small fw-bold" @click="obtenerUbicacion" :disabled="geoLoading">
                                    <i class="fas fa-crosshairs me-2"></i> GPS
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Perfil Económico / Negocio -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center">
                    <div class="icon-box bg-warning text-dark rounded-3 me-3"><i class="fas fa-briefcase"></i></div>
                    <h5 class="fw-bold text-dark mb-0">Situación Laboral / Económica</h5>
                    <div class="ms-auto">
                        <select v-model="form.origen_labor" class="form-select form-select-sm rounded-pill px-3 bg-light border-0 shadow-none fw-bold">
                            <option>INDEPENDIENTE</option><option>DEPENDIENTE</option>
                        </select>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div v-if="esIndependiente" class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">RAZÓN SOCIAL / NOMBRE COMERCIAL</label>
                            <input v-model.trim="form.negocio.razonsocial" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" placeholder="Nombre del negocio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">RUC NEGOCIO (SI TIENE)</label>
                            <input v-model="form.negocio.ruc" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" maxlength="11" placeholder="20XXXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">GIRO / ACTIVIDAD</label>
                            <select v-model="form.negocio.tipo_actividad_id" class="form-select rounded-pill bg-light border-0 px-3 shadow-none" @change="onChangeTipoActividad">
                                <option value="">-- Seleccionar --</option>
                                <option v-for="a in actividadNegocios" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                            </select>
                            <div class="invalid-feedback" v-if="hasError('negocio.tipo_actividad_id')">{{ firstError('negocio.tipo_actividad_id') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">DETALLE ESPECÍFICO</label>
                            <select v-model="form.negocio.detalle_actividad_id" class="form-select rounded-pill bg-light border-0 px-3 shadow-none" :disabled="!form.negocio.tipo_actividad_id">
                                <option value="">-- Seleccionar --</option>
                                <option v-for="d in detalleActividadNegocios" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                            </select>
                            <div class="invalid-feedback" v-if="hasError('negocio.detalle_actividad_id')">{{ firstError('negocio.detalle_actividad_id') }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-muted small fw-bold">DIRECCIÓN DEL NEGOCIO</label>
                            <input v-model.trim="form.negocio.direccion" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" :class="{'is-invalid': hasError('negocio.direccion')}" placeholder="Calle / Mz / Lt del local comercial">
                            <div class="invalid-feedback" v-if="hasError('negocio.direccion')">{{ firstError('negocio.direccion') }}</div>
                        </div>
                    </div>
                    <div v-else class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">CENTRO DE TRABAJO</label>
                            <input v-model.trim="form.institucion_lab" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" placeholder="Nombre de empresa">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">OCUPACIÓN / CARGO</label>
                            <input v-model.trim="form.ocupacion" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" placeholder="Ej: Administrador">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Referente -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center">
                    <div class="icon-box bg-dark text-white rounded-3 me-3"><i class="fas fa-users"></i></div>
                    <h5 class="fw-bold text-dark mb-0">Referente de Confianza</h5>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">DNI REFERENTE</label>
                            <input v-model="form.referente.dni" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" maxlength="8" :class="{'is-invalid': hasError('referente.dni')}">
                            <div class="invalid-feedback" v-if="hasError('referente.dni')">{{ firstError('referente.dni') }}</div>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label text-muted small fw-bold">NOMBRE COMPLETO</label>
                            <div class="input-group">
                                <input v-model.trim="form.referente.primernombre" class="form-control rounded-start-pill bg-light border-0 px-3 shadow-none" placeholder="Nombres" :class="{'is-invalid': hasError('referente.primernombre')}">
                                <input v-model.trim="form.referente.ape_pat" class="form-control bg-light border-0 px-3 shadow-none" placeholder="Ape. Paterno" :class="{'is-invalid': hasError('referente.ape_pat')}">
                                <input v-model.trim="form.referente.ape_mat" class="form-control rounded-end-pill bg-light border-0 px-3 shadow-none" placeholder="Ape. Materno" :class="{'is-invalid': hasError('referente.ape_mat')}">
                            </div>
                            <!-- Error display for names (multiple possible) -->
                            <div v-if="hasError('referente.primernombre') || hasError('referente.ape_pat') || hasError('referente.ape_mat')" class="text-danger extra-small mt-1 px-3">
                                {{ firstError('referente.primernombre') || firstError('referente.ape_pat') || firstError('referente.ape_mat') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">VÍNCULO / PARENTESCO</label>
                            <select v-model="form.referente.parentesco" class="form-select rounded-pill bg-light border-0 px-3 shadow-none">
                                <option v-for="p in parentescos" :key="p" :value="p">{{ p }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">CELULAR REFERENTE</label>
                            <input v-model="form.referente.celular" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" maxlength="9" :class="{'is-invalid': hasError('referente.celular')}">
                            <div class="invalid-feedback" v-if="hasError('referente.celular')">{{ firstError('referente.celular') }}</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-bold">DIRECCIÓN (REFERENCIA)</label>
                            <input v-model.trim="form.referente.direccion" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" placeholder="Opcional">
                        </div>
                    </div>
                </div>
            </div>
          </div>

          <!-- RIGHT SIDEBAR (SUMMARY & PHOTO) -->
          <div class="col-lg-4">
            <div class="sticky-top" style="top: 2rem; z-index: 10;">
                <!-- Photo Upload -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-dark text-white border-0 py-3 px-4 fw-bold small text-center">FOTOGRAFÍA DEL SOCIO</div>
                    <div class="card-body p-4 text-center">
                      <input
                        type="file"
                        ref="photoInput"
                        class="d-none"
                        @change="handlePhotoChange"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                      />

                      <div
                        class="photo-preview-box mx-auto mb-3 shadow-sm border border-2 border-dashed rounded-4 position-relative"
                        :class="{ 'border-danger': hasError('foto') }"
                        style="width: 200px; height: 200px; cursor: pointer"
                        @click="$refs.photoInput.click()"
                      >
                        <img v-if="photoPreview" :src="photoPreview" class="w-100 h-100 object-fit-cover rounded-4" />
                        <div v-else class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                          <i class="fas fa-camera fa-3x mb-2 opacity-25"></i>
                          <span class="small fw-bold">SUBIR FOTO</span>
                        </div>
                      </div>

                      <div v-if="photoPreview" class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" @click="removePhoto">
                          <i class="fas fa-trash me-1"></i> Quitar
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3" @click="$refs.photoInput.click()">
                          Cambiar
                        </button>
                      </div>

                      <p class="small text-muted mt-2 mb-0">Formato JPG/PNG/WEBP. Máx 4MB.</p>

                      <div v-if="hasError('foto')" class="text-danger small mt-2">
                        {{ firstError('foto') }}
                      </div>
                    </div>
                </div>

                <!-- Recent Summary -->
                <Resumen />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODALS -->
    <Prestamo :form="formPrestamo" @onListar="getClienteReciente" />
    <teleport to="body">
        <div class="modal fade" id="impresionresumen" tabindex="-1" data-bs-focus="false">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header bg-dark text-white border-0">
                        <h5 class="fw-bold mb-0" id="impresionresumenLabel">Expediente Digital</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe v-if="pdfUrl" :src="pdfUrl" class="w-100" style="height: 80vh;" border="0"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
  </AppLayoutDefault>
</template>

<style scoped>
.icon-box { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; }
.photo-preview-box:hover { background: #f8f9fa; border-color: #0d6efd !important; }
.sticky-top { transition: top 0.3s ease; }
.extra-small { font-size: 0.75rem; }
/* Estilos para que el modal de prestamo no rompa la estetica si es necesario */
:deep(.modal-content) { border-radius: 1rem !important; border: none !important; box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; }
</style>
