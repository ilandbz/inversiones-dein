<script setup>
import { toRefs, onMounted, ref, onBeforeUnmount, watch, computed } from 'vue';
import usePropiedad from '@/Composables/Propiedad.js';
import useHelper from '@/Helpers';  
import useActividadNegocio from '@/Composables/ActividadNegocio.js'
import useUbigeo from '@/Composables/Ubigeo.js'
import useCliente from '@/Composables/Cliente.js'

const { hideModal, Toast, soloNumeros } = useHelper();

const props = defineProps({
  form: Object,
  currentPage: Number,
  modalTitle: String,
})

const { form, modalTitle } = toRefs(props)

// Composables
const { actividadNegocios, listaActividadNegocios, listaDetalleActividadNegocios, detalleActividadNegocios } = useActividadNegocio()
const { obtenerDepartamentos, departamentos, obtenerProvinciasPorDepartamento, provincias, obtenerDistritosPorProvincia, distritos, obtenerUbigeo, registro, buscarDistritos } = useUbigeo()
const { agregarCliente, actualizarCliente, respuesta, existeClientePorDni, existeCliente } = useCliente()

const modalEl = ref(null)
const emit = defineEmits(['onListar'])

// Ubigeo State
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

// Sync selects with form
watch(() => nac.value.dist, (ub) => { if(ub) form.value.ubigeo_nac = ub })
watch(() => dom.value.dist, (ub) => { if(ub) form.value.ubigeo_dom = ub })

// Hierarchical watch (NAC)
watch(() => nac.value.dep, async (dep) => {
  if (!dep) return; nac.value.prov = ''; nac.value.dist = '';
  loadingNac.value = true; await obtenerProvinciasPorDepartamento(dep); provinciasNac.value = provincias.value; loadingNac.value = false;
})
watch(() => nac.value.prov, async (prov) => {
  if (!prov) return; nac.value.dist = '';
  loadingNac.value = true; await obtenerDistritosPorProvincia(prov); distritosNac.value = distritos.value; loadingNac.value = false;
})

// Hierarchical watch (DOM)
watch(() => dom.value.dep, async (dep) => {
  if (!dep) return; dom.value.prov = ''; dom.value.dist = '';
  loadingDom.value = true; await obtenerProvinciasPorDepartamento(dep); provinciasDom.value = provincias.value; loadingDom.value = false;
})
watch(() => dom.value.prov, async (prov) => {
  if (!prov) return; dom.value.dist = '';
  loadingDom.value = true; await obtenerDistritosPorProvincia(prov); distritosDom.value = distritos.value; loadingDom.value = false;
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
      setTimeout(async () => {
         await obtenerProvinciasPorDepartamento(depId)
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

watch(() => form.value.ubigeo_nac, (val) => syncFromUbigeo('nac', val, true), { immediate: true })
watch(() => form.value.ubigeo_dom, (val) => syncFromUbigeo('dom', val, true), { immediate: true })

// Search Logic
let tNac = null; let tDom = null;
watch(buscarNac, (q) => {
  clearTimeout(tNac); resultadosNac.value = []; if (!q || q.trim().length < 3) return
  tNac = setTimeout(async () => { await buscarDistritos({ buscar: q, paginacion: 8 }); resultadosNac.value = distritos.value?.data || [] }, 300)
})
watch(buscarDom, (q) => {
  clearTimeout(tDom); resultadosDom.value = []; if (!q || q.trim().length < 3) return
  tDom = setTimeout(async () => { await buscarDistritos({ buscar: q, paginacion: 8 }); resultadosDom.value = distritos.value?.data || [] }, 300)
})

const selectFromSearch = (which, item) => {
  if (which === 'nac') {
    form.value.ubigeo_nac = item.ubigeo; buscarNac.value = `${item.departamento} / ${item.provincia} / ${item.distrito}`; resultadosNac.value = []; syncFromUbigeo('nac', item.ubigeo)
  } else {
    form.value.ubigeo_dom = item.ubigeo; buscarDom.value = `${item.departamento} / ${item.provincia} / ${item.distrito}`; resultadosDom.value = []; syncFromUbigeo('dom', item.ubigeo)
  }
}

// Business Logic
const esIndependiente = computed(() => form.value.origen_labor === 'INDEPENDIENTE')
const onChangeActividad = async () => {
  const id = form.value.negocio?.actividad_negocio_id
  if (!id) { if(form.value.negocio) form.value.negocio.detalle_actividad_id = ''; return }
  await listaDetalleActividadNegocios(id)
}

const hasError = (name) => form.value.errors && form.value.errors[name]
const firstError = (name) => form.value.errors && form.value.errors[name] ? form.value.errors[name][0] : ''

const validarDni = async (dni) => {
    if (!dni || dni.length !== 8) return
    if (form.value.estadoCrud === 'nuevo') {
        await existeClientePorDni(dni)
        if(existeCliente.value){
            form.value.dni = ''; Toast.fire({ icon:'error', title:'El DNI ya existe' })
        }
    }
}

const gradosInstruccion = ['SIN INSTRUCCION','INICIAL','PRIMARIA','SECUNDARIA','SUPERIOR TECNICO','SUPERIOR UNIVERSITARIO','POSTGRADO']
const parentescos = ['PADRE','MADRE','HIJO(A)','HERMANO(A)','ABUELO(A)','NIETO(A)','TÍO(A)','SOBRINO(A)','PRIMO(A)','ESPOSO(A)','CONVIVIENTE','AMIGO(A)','VECINO(A)','COMPAÑERO(A) DE TRABAJO','OTRO']

const submitForm = async () => {
  const fd = new FormData()
  const appendIfFilled = (key, value) => { if (value !== null && value !== undefined && value !== '') fd.append(key, value) }
  const plain = form.value
  for (const [k, v] of Object.entries(plain)) { if (!['negocio', 'referente', 'errors'].includes(k)) appendIfFilled(k, v) }
  if (esIndependiente.value) { for (const [k, v] of Object.entries(plain.negocio || {})) { appendIfFilled(`negocio[${k}]`, v) } }
  for (const [k, v] of Object.entries(plain.referente || {})) { appendIfFilled(`referente[${k}]`, v) }

  if (plain.estadoCrud === 'nuevo') await agregarCliente(fd)
  else await actualizarCliente(fd)

  if (respuesta.value?.ok == 1) {
    form.value.errors = {}; hideModal('#modalcliente');
    Toast.fire({ icon: 'success', title: respuesta.value.mensaje }); emit('onListar')
  }
}

onMounted(async () => {
    await listaActividadNegocios(); await obtenerDepartamentos()
})
</script>

<template>
    <teleport to="body">
        <div ref="modalEl" class="modal fade" id="modalcliente" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-primary text-white p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-white-20 rounded-circle p-2 me-3">
                                <i class="fas fa-user-edit fs-4"></i>
                            </div>
                            <h5 class="modal-title fw-bold mb-0">{{ modalTitle }}</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body bg-light p-4">
                        <form @submit.prevent="submitForm" id="formCliente">
                            <div class="row g-4">
                                <!-- Columna 1: Datos Personales y Ubicación -->
                                <div class="col-lg-7">
                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-primary text-uppercase small mb-4">
                                                <i class="fas fa-id-card me-2"></i>Información Personal
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">DNI</label>
                                                    <input v-model="form.dni" class="form-control form-control-sm border-0 bg-light" :class="{ 'is-invalid': hasError('dni') }" maxlength="8" @keypress="soloNumeros" @change="validarDni(form.dni)" />
                                                    <div class="invalid-feedback">{{ firstError('dni') }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">Apellido Paterno</label>
                                                    <input v-model.trim="form.ape_pat" class="form-control form-control-sm border-0 bg-light" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">Apellido Materno</label>
                                                    <input v-model.trim="form.ape_mat" class="form-control form-control-sm border-0 bg-light" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Nombres</label>
                                                    <input v-model.trim="form.primernombre" class="form-control form-control-sm border-0 bg-light" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Otros Nombres</label>
                                                    <input v-model.trim="form.otrosnombres" class="form-control form-control-sm border-0 bg-light" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">Fecha Nac.</label>
                                                    <input type="date" v-model="form.fecha_nac" class="form-control form-control-sm border-0 bg-light" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">Género</label>
                                                    <select v-model="form.genero" class="form-select form-select-sm border-0 bg-light">
                                                        <option value="M">MASCULINO</option>
                                                        <option value="F">FEMENINO</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">Estado Civil</label>
                                                    <select v-model="form.estado_civil" class="form-select form-select-sm border-0 bg-light">
                                                        <option value="SOLTERO">SOLTERO</option>
                                                        <option value="CASADO">CASADO</option>
                                                        <option value="CONVIVIENTE">CONVIVIENTE</option>
                                                        <option value="DIVORCIADO">DIVORCIADO</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-primary text-uppercase small mb-4">
                                                <i class="fas fa-map-marked-alt me-2"></i>Residencia y Ubicación
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted d-block">Ubigeo Domicilio</label>
                                                    <div class="input-group input-group-sm mb-2">
                                                        <button type="button" class="btn" :class="ubigeoModeDom==='select'?'btn-primary':'btn-outline-primary'" @click="ubigeoModeDom='select'">Manual</button>
                                                        <button type="button" class="btn" :class="ubigeoModeDom==='search'?'btn-primary':'btn-outline-primary'" @click="ubigeoModeDom='search'">Buscador</button>
                                                    </div>
                                                    <div v-if="ubigeoModeDom==='select'" class="row g-2">
                                                        <div class="col-md-4">
                                                            <select v-model="dom.dep" class="form-select form-select-sm border-0 bg-light">
                                                                <option value="">Dep...</option>
                                                                <option v-for="d in departamentos.data || departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <select v-model="dom.prov" class="form-select form-select-sm border-0 bg-light">
                                                                <option value="">Prov...</option>
                                                                <option v-for="p in provinciasDom" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <select v-model="dom.dist" class="form-select form-select-sm border-0 bg-light">
                                                                <option value="">Dist...</option>
                                                                <option v-for="d in distritosDom" :key="d.ubigeo" :value="d.ubigeo">{{ d.nombre }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div v-else class="position-relative">
                                                        <input v-model="buscarDom" class="form-control form-control-sm border-0 bg-light" placeholder="Escriba distrito / provincia..." />
                                                        <ul v-if="resultadosDom.length" class="list-group position-absolute w-100 shadow-lg top-100 mt-1" style="z-index:1060;">
                                                            <li v-for="r in resultadosDom" :key="r.ubigeo" class="list-group-item list-group-item-action small py-2" @click="selectFromSearch('dom', r)">
                                                                {{ r.distrito }} - {{ r.provincia }} ({{ r.departamento }})
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted">Dirección Detallada</label>
                                                    <input v-model="form.direccion" class="form-control form-control-sm border-0 bg-light" placeholder="Av. Ejemplos #123, Referencia..." />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna 2: Laboral y Referente -->
                                <div class="col-lg-5">
                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-primary text-uppercase small mb-4">
                                                <i class="fas fa-briefcase me-2"></i>Perfil Económico
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Origen Laboral</label>
                                                    <select v-model="form.origen_labor" class="form-select form-select-sm border-0 bg-light">
                                                        <option value="INDEPENDIENTE">INDEPENDIENTE</option>
                                                        <option value="DEPENDIENTE">DEPENDIENTE</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Instrucción</label>
                                                    <select v-model="form.grado_instr" class="form-select form-select-sm border-0 bg-light">
                                                        <option v-for="g in gradosInstruccion" :key="g" :value="g">{{ g }}</option>
                                                    </select>
                                                </div>
                                                
                                                <div v-if="esIndependiente && form.negocio" class="col-12">
                                                    <div class="p-3 rounded-4 bg-primary-subtle border border-primary-subtle mt-2">
                                                        <div class="row g-2">
                                                            <div class="col-md-12 mb-1"><span class="badge bg-primary px-3">Datos del Negocio</span></div>
                                                            <div class="col-12">
                                                                <input v-model="form.negocio.razonsocial" class="form-control form-control-sm border-0" placeholder="Nombre Comercial / Razón Social" />
                                                            </div>
                                                            <div class="col-md-6">
                                                                <select v-model="form.negocio.actividad_negocio_id" class="form-select form-select-sm border-0" @change="onChangeActividad">
                                                                    <option value="">Giro...</option>
                                                                    <option v-for="a in actividadNegocios" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <select v-model="form.negocio.detalle_actividad_id" class="form-select form-select-sm border-0">
                                                                    <option v-for="d in detalleActividadNegocios" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div v-else class="col-12">
                                                    <div class="row g-2">
                                                        <div class="col-12">
                                                            <label class="form-label small fw-bold text-muted">Institución</label>
                                                            <input v-model="form.institucion_lab" class="form-control form-control-sm border-0 bg-light" />
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-bold text-muted">Ocupación</label>
                                                            <input v-model="form.ocupacion" class="form-control form-control-sm border-0 bg-light" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-primary text-uppercase small mb-4">
                                                <i class="fas fa-users-cog me-2"></i>Referencia Familiar
                                            </h6>
                                            <div v-if="form.referente" class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label small fw-bold text-muted">DNI Referente</label>
                                                    <input v-model="form.referente.dni" class="form-control form-control-sm border-0 bg-light" maxlength="8" />
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label small fw-bold text-muted">Apellidos y Nombres</label>
                                                    <input v-model="form.referente.apenom_full" class="form-control form-control-sm border-0 bg-light" placeholder="Ape. Paterno, Materno y Nombres" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Celular</label>
                                                    <input v-model="form.referente.celular" class="form-control form-control-sm border-0 bg-light" maxlength="9" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Parentesco</label>
                                                    <select v-model="form.referente.parentesco" class="form-select form-select-sm border-0 bg-light">
                                                        <option v-for="p in parentescos" :key="p" :value="p">{{ p }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer bg-white p-4 border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                        <button type="submit" form="formCliente" class="btn btn-primary rounded-pill px-5 fw-bold shadow">
                            <i class="fas fa-save me-2"></i>{{ (form.estadoCrud=='nuevo') ? 'GUARDAR CLIENTE' : 'ACTUALIZAR DATOS' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
.bg-white-20 { background: rgba(255,255,255,0.2); }
.cursor-pointer { cursor: pointer; }
.modal-body { max-height: 75vh; overflow-y: auto; scrollbar-width: thin; }
.form-control:focus, .form-select:focus { background-color: #fff !important; box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.1); border-color: var(--bs-primary); }
</style>