<script setup>
import { toRefs, onMounted, ref, onBeforeUnmount, watch, computed, nextTick } from 'vue';
import usePropiedad from '@/Composables/Propiedad.js';
import useHelper from '@/Helpers';  
import useActividadNegocio from '@/Composables/ActividadNegocio.js'
import useUbigeo from '@/Composables/Ubigeo.js'
import useCliente from '@/Composables/Cliente.js'

const { hideModal, Toast, slugify, soloNumeros } = useHelper();

const props = defineProps({
  form: Object,
  currentPage: Number,
  modalTitle: String,
})

const { form, currentPage, modalTitle } = toRefs(props)

// Composables
const {
    agregarPropiedad, respuesta: respuestaPropiedad, errors: errorsPropiedad, actualizarPropiedad
} = usePropiedad(); // Mantengo esto por compatibilidad si se usaba, aunque parece ser de Propiedad

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

const {
  agregarCliente,
  actualizarCliente,
  respuesta,
  errors,
  existeClientePorDni,
  existeCliente
} = useCliente()


const modalEl = ref(null)
const emit = defineEmits(['onListar'])

// --- Lógica de Ubigeo (Copiada y adaptada de Registro.vue) ---
const ubigeoModeNac = ref('select') 
const ubigeoModeDom = ref('select')

const nac = ref({ dep: '', prov: '', dist: '' })
const dom = ref({ dep: '', prov: '', dist: '' })

const provinciasNac = ref([])
const distritosNac = ref([])

const provinciasDom = ref([])
const distritosDom = ref([])

const buscarNac = ref('')
const buscarDom = ref('')
const resultadosNac = ref([]) 
const resultadosDom = ref([])

const loadingNac = ref(false)
const loadingDom = ref(false)

// Watchers para sincronizar selects con el form
watch(() => nac.value.dist, (ub) => { if(ub) form.value.ubigeo_nac = ub })
// watch(() => dom.value.dist, (ub) => { if(ub) form.value.ubigeo_dom = ub }) // Lo manejo manual en el change para evitar ciclos si es necesario, o igual que nac

// Al abrir el modal o cambiar el form, necesitamos cargar los datos de ubigeo si existen
watch(() => form.value.ubigeo_nac, async (newVal) => {
    if (newVal && String(newVal).length === 6) {
        // Cargar cascada si no está cargada
        await syncFromUbigeo('nac', newVal, true)
    }
}, { immediate: true })

watch(() => form.value.ubigeo_dom, async (newVal) => {
    if (newVal && String(newVal).length === 6) {
        await syncFromUbigeo('dom', newVal, true)
    }
}, { immediate: true })


// Cambios jerárquicos (NAC)
watch(() => nac.value.dep, async (dep) => {
  if (!dep) return
  nac.value.prov = ''
  nac.value.dist = ''
  provinciasNac.value = []
  distritosNac.value = []
  
  loadingNac.value = true
  try {
    await obtenerProvinciasPorDepartamento(dep)
    provinciasNac.value = provincias.value
  } finally {
    loadingNac.value = false
  }
})

watch(() => nac.value.prov, async (prov) => {
  if (!prov) return
  nac.value.dist = ''
  distritosNac.value = []
  
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
  if (!dep) return
  dom.value.prov = ''
  dom.value.dist = ''
  provinciasDom.value = []
  distritosDom.value = []
  
  loadingDom.value = true
  try {
    await obtenerProvinciasPorDepartamento(dep)
    provinciasDom.value = provincias.value
  } finally {
    loadingDom.value = false
  }
})

watch(() => dom.value.prov, async (prov) => {
  if (!prov) return
  dom.value.dist = ''
  distritosDom.value = []
  
  loadingDom.value = true
  try {
    await obtenerDistritosPorProvincia(prov)
    distritosDom.value = distritos.value
  } finally {
    loadingDom.value = false
  }
})

const syncFromUbigeo = async (which, ubigeo, isInit = false) => {
  if (!ubigeo || String(ubigeo).length !== 6) return
  try {
    await obtenerUbigeo(ubigeo)
    const r = registro.value
    if (!r) return

    const depId = r?.provincia?.departamento_id
    const provId = r?.provincia?.id
    const disUb = r?.ubigeo

    if (which === 'nac') {
      if(!isInit) ubigeoModeNac.value = 'select'
      nac.value.dep = depId
      // Esperamos a que carguen provincias
      setTimeout(async () => {
         await obtenerProvinciasPorDepartamento(depId) // Forzar carga si watcher no dispara rapido
         provinciasNac.value = provincias.value
         nac.value.prov = provId
         
         setTimeout(async () => {
             await obtenerDistritosPorProvincia(provId)
             distritosNac.value = distritos.value
             nac.value.dist = disUb
         }, 100)
      }, 100)
    } else {
      if(!isInit) ubigeoModeDom.value = 'select'
      dom.value.dep = depId
      setTimeout(async () => {
         await obtenerProvinciasPorDepartamento(depId)
         provinciasDom.value = provincias.value
         dom.value.prov = provId
         setTimeout(async () => {
             await obtenerDistritosPorProvincia(provId)
             distritosDom.value = distritos.value
             dom.value.dist = disUb
         }, 100)
      }, 100)
    }
  } catch (e) {}
}

// Búsqueda UBIGEO
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
  if (which === 'nac') {
    form.value.ubigeo_nac = item.ubigeo
    buscarNac.value = `${item.departamento} / ${item.provincia} / ${item.distrito}`
    resultadosNac.value = []
    syncFromUbigeo('nac', item.ubigeo) // Sincronizar selects tambien
  } else {
    form.value.ubigeo_dom = item.ubigeo
    buscarDom.value = `${item.departamento} / ${item.provincia} / ${item.distrito}`
    resultadosDom.value = []
    syncFromUbigeo('dom', item.ubigeo)
  }
}

// --- Lógica de Negocio ---
const esIndependiente = computed(() => form.value.origen_labor === 'INDEPENDIENTE')

const resetNegocio = () => { 
    if(form.value.negocio) {
        form.value.negocio.razonsocial = ''
        form.value.negocio.ruc = ''
        form.value.negocio.celular = ''
        form.value.negocio.tipo_actividad_id = ''
        form.value.negocio.detalle_actividad_id = ''
        form.value.negocio.inicioactividad = ''
        form.value.negocio.direccion = ''
    }
}

watch(
  () => form.value.origen_labor,
  (v) => {
    if (v !== 'INDEPENDIENTE') {
      resetNegocio()
      detalleActividadNegocios.value = []
    }
  }
)

const onChangeActividad = async () => {
  const id = form.value.negocio.actividad_negocio_id
  if (!id) {
    detalleActividadNegocios.value = []
    form.value.negocio.detalle_actividad_id = ''
    return
  }
  form.value.negocio.detalle_actividad_id = ''
  await listaDetalleActividadNegocios(id)
}

// --- Lógica de Validación / Errores ---
const hasError = (name) => {
    // Implementación simple de errores basada en lo que viene del controller o composable
    // Asumimos que form.value.errors se llena desde el padre o aqui
    return form.value.errors && form.value.errors[name]
}
const firstError = (name) => {
    return form.value.errors && form.value.errors[name] ? form.value.errors[name][0] : ''
}
const clearFieldError = (name) => { 
    if (form.value.errors && form.value.errors[name]) delete form.value.errors[name] 
}

const validarDni = async (dni) => {
    if (!dni || dni.length !== 8) return
    // Solo validar si es nuevo, si es editar podria ser el mismo
    if (form.value.estadoCrud === 'nuevo') {
        await existeClientePorDni(dni)
        if(existeCliente.value){
            form.value.dni = ''
            Toast.fire({ icon:'error', title:'El DNI ya existe' })
        }
    }
}

// --- Listas Estáticas ---
const gradosInstruccion = [
  'SIN INSTRUCCION','INICIAL','PRIMARIA','SECUNDARIA','SUPERIOR TECNICO','SUPERIOR UNIVERSITARIO','POSTGRADO'
]
const profesionesPeru = [
  'ADMINISTRACIÓN','AGRONOMÍA','ARQUITECTURA','BIOLOGÍA','CIENCIAS DE LA COMUNICACIÓN',
  'CONTABILIDAD','DERECHO','DISEÑO GRÁFICO','ECONOMÍA','EDUCACIÓN','ENFERMERÍA',
  'INGENIERÍA CIVIL','INGENIERÍA DE SISTEMAS','INGENIERÍA INDUSTRIAL','MEDICINA HUMANA','PSICOLOGÍA','OTROS'
]
const parentescos = [
  'PADRE','MADRE','HIJO(A)','HERMANO(A)','ABUELO(A)','NIETO(A)','TÍO(A)','SOBRINO(A)','PRIMO(A)',
  'ESPOSO(A)','CONVIVIENTE','AMIGO(A)','VECINO(A)','COMPAÑERO(A) DE TRABAJO','OTRO'
]



const guardar = () => submitForm()

const submitForm = async () => {
  const fd = new FormData()

  const appendIfFilled = (key, value) => {
    if (value === null || value === undefined || value === '') return
    fd.append(key, value)
  }

  const plain = form.value

  for (const [k, v] of Object.entries(plain)) {
    if (k === 'negocio' || k === 'referente' || k === 'errors') continue
    appendIfFilled(k, v)
  }

  if (esIndependiente.value) {
    for (const [k, v] of Object.entries(plain.negocio || {})) {
      appendIfFilled(`negocio[${k}]`, v)
    }
  }

  for (const [k, v] of Object.entries(plain.referente || {})) {
    appendIfFilled(`referente[${k}]`, v)
  }

  if (plain.estadoCrud === 'nuevo') {
    await agregarCliente(fd)
  } else {
    await actualizarCliente(fd)
  }

  if (respuesta.value?.ok == 1) {
    form.value.errors = {}
    hideModal('#modalcliente')
    Toast.fire({ icon: 'success', title: rCli.value.mensaje })
    emit('onListar')
  }
}

watch(
  () => form.value.negocio?.actividad_negocio_id,
  async (id) => {
    if (!id) return
    await listaDetalleActividadNegocios(id)
  },
  { immediate: true }
)
// Eventos del modal
const onHideHandler = () => {
    if (document.activeElement && modalEl.value?.contains(document.activeElement)) {
        document.activeElement.blur()
    }
}
const onHiddenHandler = () => {
   // Limpieza si es necesaria
}

onMounted(async () => {
    await listaActividadNegocios()
    await obtenerDepartamentos()
    
    if (modalEl.value) {
        modalEl.value.addEventListener('hide.bs.modal', onHideHandler)
        modalEl.value.addEventListener('hidden.bs.modal', onHiddenHandler)
    }
})

onBeforeUnmount(() => {
    if (modalEl.value) {
        modalEl.value.removeEventListener('hide.bs.modal', onHideHandler)
        modalEl.value.removeEventListener('hidden.bs.modal', onHiddenHandler)
    }
})
</script>

<template>
    <teleport to="body">
        <form @submit.prevent="guardar">
            <div ref="modalEl" class="modal fade" id="modalcliente" tabindex="-1" aria-labelledby="modalclienteLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-xl"> <!-- Usamos modal-xl para que quepa todo -->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="modalclienteLabel">{{ modalTitle }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <!-- DATOS PERSONALES -->
                            <div class="card mb-3 shadow-sm border-0">
                                <div class="card-header bg-white border-bottom-0 pt-3">
                                    <h6 class="text-primary mb-0"><i class="fas fa-user me-2"></i>Datos Personales</h6>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">DNI</label>
                                            <input v-model="form.dni" class="form-control" :class="{ 'is-invalid': hasError('dni') }" maxlength="8" @keypress="soloNumeros" @change="validarDni(form.dni)" />
                                            <div class="invalid-feedback">{{ firstError('dni') }}</div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Apellido Paterno</label>
                                            <input v-model.trim="form.ape_pat" class="form-control" :class="{ 'is-invalid': hasError('ape_pat') }" />
                                            <div class="invalid-feedback">{{ firstError('ape_pat') }}</div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Apellido Materno</label>
                                            <input v-model.trim="form.ape_mat" class="form-control" :class="{ 'is-invalid': hasError('ape_mat') }" />
                                            <div class="invalid-feedback">{{ firstError('ape_mat') }}</div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Nombres</label>
                                            <input v-model.trim="form.primernombre" class="form-control" placeholder="Primer Nombre" :class="{ 'is-invalid': hasError('primernombre') }" />
                                            <div class="invalid-feedback">{{ firstError('primernombre') }}</div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Otros Nombres</label>
                                            <input v-model.trim="form.otrosnombres" class="form-control" />
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Fecha Nacimiento</label>
                                            <input type="date" v-model="form.fecha_nac" class="form-control" :class="{ 'is-invalid': hasError('fecha_nac') }" />
                                            <div class="invalid-feedback">{{ firstError('fecha_nac') }}</div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Género</label>
                                            <select v-model="form.genero" class="form-select">
                                                <option value="M">MASCULINO</option>
                                                <option value="F">FEMENINO</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Estado Civil</label>
                                            <select v-model="form.estado_civil" class="form-select">
                                                <option value="SOLTERO">SOLTERO</option>
                                                <option value="CASADO">CASADO</option>
                                                <option value="CONVIVIENTE">CONVIVIENTE</option>
                                                <option value="VIUDO">VIUDO</option>
                                                <option value="DIVORCIADO">DIVORCIADO</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Celular</label>
                                            <input v-model="form.celular" class="form-control" :class="{ 'is-invalid': hasError('celular') }" maxlength="9" @keypress="soloNumeros" />
                                             <div class="invalid-feedback">{{ firstError('celular') }}</div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Email</label>
                                            <input type="email" v-model="form.email" class="form-control" />
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Grado Instr.</label>
                                            <select v-model="form.grado_instr" class="form-select">
                                                <option value="">Seleccione</option>
                                                <option v-for="g in gradosInstruccion" :key="g" :value="g">{{ g }}</option>
                                            </select>
                                        </div>
                                        
                                        <!-- UBIGEO NACIMIENTO -->
                                        <div class="col-12 col-md-9 pt-2 border-top mt-3">
                                             <label class="form-label small fw-bold d-block mb-1 text-muted">Lugar de Nacimiento</label>
                                             <div class="row g-2 align-items-center">
                                                 <div class="col-auto">
                                                     <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn" :class="ubigeoModeNac==='select'?'btn-primary':'btn-outline-secondary'" @click="ubigeoModeNac='select'">Select</button>
                                                        <button type="button" class="btn" :class="ubigeoModeNac==='search'?'btn-primary':'btn-outline-secondary'" @click="ubigeoModeNac='search'">Buscar</button>
                                                     </div>
                                                 </div>
                                                 
                                                 <div class="col">
                                                     <div v-if="ubigeoModeNac==='select'" class="row g-1">
                                                         <div class="col-4">
                                                             <select v-model="nac.dep" class="form-select form-select-sm">
                                                                 <option value="">Dep...</option>
                                                                 <option v-for="d in departamentos.data || departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                                                             </select>
                                                         </div>
                                                         <div class="col-4">
                                                             <select v-model="nac.prov" class="form-select form-select-sm">
                                                                 <option value="">Prov...</option>
                                                                 <option v-for="p in provinciasNac" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                                                             </select>
                                                         </div>
                                                         <div class="col-4">
                                                              <select v-model="nac.dist" class="form-select form-select-sm">
                                                                 <option value="">Dist...</option>
                                                                 <option v-for="d in distritosNac" :key="d.ubigeo" :value="d.ubigeo">{{ d.nombre }}</option>
                                                             </select>
                                                         </div>
                                                     </div>
                                                     <div v-else class="position-relative">
                                                         <input v-model="buscarNac" class="form-control form-control-sm" placeholder="Buscar distrito..." />
                                                         <ul v-if="resultadosNac.length" class="list-group position-absolute w-100 shadow" style="z-index:100; max-height:200px; overflow:auto;">
                                                             <li v-for="r in resultadosNac" :key="r.ubigeo" class="list-group-item list-group-item-action cursor-pointer" @click="selectFromSearch('nac', r)">
                                                                 {{ r.distrito }} - {{ r.provincia }}
                                                             </li>
                                                         </ul>
                                                     </div>
                                                 </div>
                                             </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- UBICACION DOMICILIO -->
                            <div class="card mb-3 shadow-sm border-0">
                                <div class="card-header bg-white border-bottom-0 pt-3">
                                    <h6 class="text-primary mb-0"><i class="fas fa-map-marker-alt me-2"></i>Domicilio Actual</h6>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-5">
                                             <label class="form-label small fw-bold">Ubigeo Domicilio</label>
                                             <div class="row g-2">
                                                 <div class="col-12">
                                                     <div class="input-group input-group-sm mb-1">
                                                        <button type="button" class="btn" :class="ubigeoModeDom==='select'?'btn-primary':'btn-outline-secondary'" @click="ubigeoModeDom='select'">Select</button>
                                                        <button type="button" class="btn" :class="ubigeoModeDom==='search'?'btn-primary':'btn-outline-secondary'" @click="ubigeoModeDom='search'">Buscar</button>
                                                     </div>
                                                 </div>
                                                 <div class="col-12">
                                                     <div v-if="ubigeoModeDom==='select'" class="row g-1">
                                                         <div class="col-4">
                                                             <select v-model="dom.dep" class="form-select form-select-sm">
                                                                 <option value="">Dep...</option>
                                                                 <option v-for="d in departamentos.data || departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                                                             </select>
                                                         </div>
                                                         <div class="col-4">
                                                             <select v-model="dom.prov" class="form-select form-select-sm">
                                                                 <option value="">Prov...</option>
                                                                 <option v-for="p in provinciasDom" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                                                             </select>
                                                         </div>
                                                         <div class="col-4">
                                                              <select v-model="dom.dist" class="form-select form-select-sm">
                                                                 <option value="">Dist...</option>
                                                                 <option v-for="d in distritosDom" :key="d.ubigeo" :value="d.ubigeo">{{ d.nombre }}</option>
                                                             </select>
                                                         </div>
                                                     </div>
                                                     <div v-else class="position-relative">
                                                         <input v-model="buscarDom" class="form-control form-control-sm" placeholder="Buscar distrito..." />
                                                         <ul v-if="resultadosDom.length" class="list-group position-absolute w-100 shadow" style="z-index:100; max-height:200px; overflow:auto;">
                                                             <li v-for="r in resultadosDom" :key="r.ubigeo" class="list-group-item list-group-item-action cursor-pointer" @click="selectFromSearch('dom', r)">
                                                                 {{ r.distrito }} - {{ r.provincia }}
                                                             </li>
                                                         </ul>
                                                     </div>
                                                 </div>
                                             </div>
                                        </div>
                                        <div class="col-12 col-md-7">
                                            <label class="form-label small fw-bold">Dirección</label>
                                            <input v-model="form.direccion" class="form-control" :class="{ 'is-invalid': hasError('direccion') }" />
                                            <div class="invalid-feedback">{{ firstError('direccion') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- LABORAL -->
                            <div class="card mb-3 shadow-sm border-0">
                                <div class="card-header bg-white border-bottom-0 pt-3">
                                    <h6 class="text-primary mb-0"><i class="fas fa-briefcase me-2"></i>Datos Laborales</h6>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small fw-bold">Origen Laboral</label>
                                            <select v-model="form.origen_labor" class="form-select">
                                                <option value="INDEPENDIENTE">INDEPENDIENTE</option>
                                                <option value="DEPENDIENTE">DEPENDIENTE</option>
                                            </select>
                                        </div>
                                        
                                        <div v-if="esIndependiente && form.negocio" class="col-12">
                                            <div class="card card-body bg-light border-0 p-3">
                                                <div class="row g-2">
                                                    <div class="col-12 text-muted small mb-2 text-uppercase fw-bold">Detalles del Negocio</div>
                                                    <div class="col-md-4">
                                                        <label class="small">Razón Social / Nombre</label>
                                                        <input v-model="form.negocio.razonsocial" class="form-control form-control-sm" :class="{ 'is-invalid': hasError('negocio.razonsocial') }" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">RUC</label>
                                                        <input v-model="form.negocio.ruc" class="form-control form-control-sm" maxlength="11" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">Actividad</label>
                                                        <select v-model="form.negocio.actividad_negocio_id" class="form-select form-select-sm" @change="onChangeActividad">
                                                            <option value="">Seleccione</option>
                                                            <option v-for="a in actividadNegocios" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                                                        </select>
                                                    </div>
                                                     <div class="col-md-3">
                                                        <label class="small">Detalle Actividad</label>
                                                        {{ form.negocio.detalle_actividad_id }}
                                                        <select v-model="form.negocio.detalle_actividad_id" class="form-select form-select-sm">
                                                            <option value="">Seleccione</option>
                                                            <option v-for="d in detalleActividadNegocios" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="small">Dirección Negocio</label>
                                                        <input v-model="form.negocio.direccion" class="form-control form-control-sm" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">Inicio Actividad</label>
                                                        <input type="date" v-model="form.negocio.inicioactividad" class="form-control form-control-sm" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div v-else class="col-12 col-md-8">
                                            <!-- Campos Dependiente -->
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Institución Laboral</label>
                                                    <input v-model="form.institucion_lab" class="form-control" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Ocupación / Cargo</label>
                                                    <input v-model="form.ocupacion" class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- REFERENTE -->
                            <div class="card mb-3 shadow-sm border-0">
                                <div class="card-header bg-white border-bottom-0 pt-3">
                                    <h6 class="text-primary mb-0"><i class="fas fa-users me-2"></i>Referente Familiar / Aval</h6>
                                </div>
                                <div class="card-body pt-0">
                                    <div v-if="form.referente" class="row g-2">
                                         <div class="col-md-2">
                                            <label class="small fw-bold">DNI</label>
                                            <input v-model="form.referente.dni" class="form-control form-control-sm" maxlength="8" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small fw-bold">Apellidos</label>
                                            <div class="input-group input-group-sm">
                                                <input v-model="form.referente.ape_pat" class="form-control" placeholder="Pat" />
                                                <input v-model="form.referente.ape_mat" class="form-control" placeholder="Mat" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small fw-bold">Nombres</label>
                                            <input v-model="form.referente.primernombre" class="form-control form-control-sm" placeholder="Primer Nombre" />
                                        </div>
                                         <div class="col-md-2">
                                            <label class="small fw-bold">Celular</label>
                                            <input v-model="form.referente.celular" class="form-control form-control-sm" maxlength="9" />
                                        </div>
                                         <div class="col-md-2">
                                            <label class="small fw-bold">Parentesco</label>
                                            <select v-model="form.referente.parentesco" class="form-select form-select-sm">
                                                <option value="">Seleccione</option>
                                                <option v-for="p in parentescos" :key="p" :value="p">{{ p }}</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                             <label class="small fw-bold">Dirección Referente</label>
                                             <input v-model="form.referente.direccion" class="form-control form-control-sm" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                              
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">{{ (form.estadoCrud=='nuevo') ? 'Guardar' : 'Actualizar' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </teleport>
</template>

<style scoped>
.modal-body {
    max-height: 80vh;
    overflow-y: auto;
}
</style>