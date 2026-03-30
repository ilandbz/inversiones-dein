<script setup>
import { ref, toRefs, computed, watch, nextTick, onMounted } from 'vue'
import useHelper from '@/Helpers'
import usePersona from '@/Composables/Persona.js'
import useAsesor from '@/Composables/Asesor.js'
import usePlazo from '@/Composables/Plazo.js' 
import useCredito from '@/Composables/Credito.js' 
import useOrigenFinanciamiento from '@/Composables/OrigenFinanciamiento.js'


const { asesores, listaAsesores } = useAsesor()
const { origenes, listaOrigenesFinanciamientos } = useOrigenFinanciamiento()
const { plazos, listaPlazos } = usePlazo()
const { agregarCredito, actualizarCredito, respuesta } = useCredito()

const props = defineProps({ form: Object })
const emit = defineEmits(['onListar', 'cargar'])
const { form } = toRefs(props)
const { Toast, soloNumeros, Swal, hideModal, formatoDinero } = useHelper()

/* --- AVAL LOGIC --- */
const { persona, errors: personaErrors, respuesta: personaRespuesta, obtenerPorDni, agregarPersona } = usePersona()
const aval = ref({
  dni: '', encontrado: false, loading: false,
  form: { dni: '', ape_pat: '', ape_mat: '', primernombre: '', otrosnombres: '', celular: '', email: '', genero:'M', fecha_nac:'2000-01-01', direccion: '' },
  errors: {}
})

const clearAvalErrors = () => { aval.value.errors = {} }
const hasAvalError = (k) => Array.isArray(aval.value.errors?.[k]) && aval.value.errors[k].length > 0
const firstAvalError = (k) => (aval.value.errors?.[k]?.[0] || '')

/* --- HELPERS --- */
const hasError = (name) => form.value.errors?.[name]
const firstError = (name) => form.value.errors?.[name]?.[0] || ''
const clearFieldError = (name) => { if (form.value.errors?.[name]) delete form.value.errors[name] }

const toNumber = (v) => { const n = Number(String(v ?? '').replace(',', '.')); return Number.isFinite(n) ? n : 0 }
const toMoney2 = (n) => (Math.round((Number(n) + Number.EPSILON) * 100) / 100).toFixed(2)

const frecuenciaOptions = computed(() => {
  const arr = Array.isArray(plazos.value) ? plazos.value : []
  return [...new Set(arr.map(x => x.frecuencia))].filter(Boolean)
})

const plazoOptions = computed(() => {
  const f = form.value.frecuencia
  const arr = Array.isArray(plazos.value) ? plazos.value : []
  return arr.filter(x => x.frecuencia === f).map(x => ({
      value: x.plazo, label: f === 'DIARIA' ? `${x.plazo} días` : f === 'SEMANAL' ? `${x.plazo} sem.` : `${x.plazo} cuotas`,
      tasa: x.tasainteres, mora: x.costomora,
    })).sort((a, b) => Number(a.value) - Number(b.value))
})

const selectedPlazo = computed(() => {
  const arr = Array.isArray(plazos.value) ? plazos.value : []
  return arr.find(x => x.frecuencia === form.value.frecuencia && String(x.plazo) === String(form.value.plazo)) || null
})

watch(() => form.value.frecuencia, () => { form.value.plazo = plazoOptions.value?.[0]?.value ?? '' }, { immediate: true })
watch(() => form.value.plazo, () => {
    form.value.tasainteres = toMoney2(toNumber(selectedPlazo.value?.tasainteres ?? 0))
    form.value.costomora   = toMoney2(toNumber(selectedPlazo.value?.costomora ?? 0))
})

const totalCalc = computed(() => {
  const monto = toNumber(form.value.monto)
  const tasa = toNumber(form.value.tasainteres)
  return toMoney2(monto + (monto * (tasa / 100)))
})

watch(() => [form.value.monto, form.value.tasainteres], () => { form.value.total = totalCalc.value }, { immediate: true })

const buscarAval = async () => {
  if (String(aval.value.dni).length !== 8) return Swal.fire('Aviso', 'Ingrese 8 dígitos de DNI', 'warning')
  aval.value.loading = true; aval.value.encontrado = false;
  try {
    await obtenerPorDni(aval.value.dni)
    if (persona.value?.id) {
      aval.value.encontrado = true; form.value.aval_id = persona.value.id;
      Object.assign(aval.value.form, persona.value); Toast.fire({ icon: 'success', title: 'Aval encontrado' })
    } else {
      aval.value.form.dni = aval.value.dni; Swal.fire('Info', 'DNI no registrado. Complete los datos para crear el aval.', 'info')
    }
  } catch (e) { aval.value.encontrado = false } finally { aval.value.loading = false }
}

const registrarAval = async () => {
  try {
    await agregarPersona({ ...aval.value.form })
    if (personaRespuesta.value?.ok == 1) {
      await obtenerPorDni(aval.value.form.dni)
      if (persona.value?.id) {
          aval.value.encontrado = true; form.value.aval_id = persona.value.id;
          Swal.fire('Éxito', 'Aval registrado y asignado.', 'success')
      }
    } else { aval.value.errors = personaErrors.value }
  } catch (e) { console.error(e) }
}

const submitForm = async () => {
  const isEdit = form.value.estadoCrud === 'editar'
  try {
    if (isEdit) await actualizarCredito(form.value)
    else await agregarCredito(form.value)
    
    if (respuesta.value?.ok == 1) {
      Swal.fire('Éxito', respuesta.value.mensaje, 'success')
      hideModal('#prestamomodal'); emit('onListar')
    }
  } catch (e) { Toast.fire({ icon: 'error', title: 'Error al procesar solicitud' }) }
}

onMounted(() => { listaAsesores(); listaOrigenesFinanciamientos(); listaPlazos() })
</script>

<template>
    <teleport to="body">
        <div class="modal fade" id="prestamomodal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-primary text-white p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-white-20 rounded-circle p-2 me-3"><i class="fas fa-hand-holding-usd fs-4"></i></div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">Configuración de Crédito</h5>
                                <div class="small opacity-75">Cliente: {{ form.cliente_apenom }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body bg-light p-4">
                        <div class="row g-4">
                            <!-- Datos del Crédito -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold text-primary text-uppercase small mb-4"><i class="fas fa-file-invoice-dollar me-2"></i>Condiciones del Préstamo</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Asesor Responsable</label>
                                                <select v-model="form.asesor_id" class="form-select border-0 bg-light rounded-3 shadow-none">
                                                    <option value="">Seleccione asesor...</option>
                                                    <option v-for="a in asesores" :key="a.id" :value="a.id">{{ a.user.name }}</option>
                                                </select>
                                                <div v-if="hasError('asesor_id')" class="text-danger extra-small mt-1">{{ firstError('asesor_id') }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Tipo</label>
                                                <select v-model="form.tipo" class="form-select border-0 bg-light rounded-3 shadow-none">
                                                    <option value="">Seleccione tipo...</option>
                                                    <option value="NUEVO">NUEVO</option>
                                                    <option value="RENOVACIÓN">RENOVACIÓN</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Origen</label>
                                                <select v-model="form.origen_financiamiento_id" class="form-select border-0 bg-light rounded-3 shadow-none">
                                                    <option value="">Seleccione origen...</option>
                                                    <option v-for="o in origenes" :key="o.id" :value="o.id">{{ o.nombre }}</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Frecuencia</label>
                                                <select v-model="form.frecuencia" class="form-select border-0 bg-light rounded-3 shadow-none">
                                                    <option v-for="f in frecuenciaOptions" :key="f" :value="f">{{ f }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Plazo</label>
                                                <select v-model="form.plazo" class="form-select border-0 bg-light rounded-3 shadow-none">
                                                    <option v-for="p in plazoOptions" :key="p.value" :value="p.value">{{ p.label }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold text-primary text-uppercase">Monto Solicitado (S/)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text border-0 bg-primary text-white rounded-start-3 shadow-none">S/</span>
                                                    <input v-model="form.monto" type="number" step="0.01" class="form-control border-0 bg-light rounded-end-3 shadow-none fw-bold" placeholder="0.00" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Aval Section -->
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h6 class="fw-bold text-primary text-uppercase small mb-0"><i class="fas fa-shield-alt me-2"></i>Garantía / Aval</h6>
                                            <span v-if="form.aval_id" class="badge bg-success shadow-none rounded-pill px-3">ASIGNADO</span>
                                        </div>

                                        <div class="row g-3 align-items-end" v-if="!form.aval_id">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted">DNI del Aval</label>
                                                <div class="input-group">
                                                    <input v-model="aval.dni" class="form-control border-0 bg-light rounded-start-3 shadow-none" maxlength="8" placeholder="Ingrese DNI..." />
                                                    <button class="btn btn-primary rounded-end-3 px-4 fw-bold" @click="buscarAval" :disabled="aval.loading">
                                                        <i v-if="!aval.loading" class="fas fa-search"></i>
                                                        <span v-else class="spinner-border spinner-border-sm"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="p-3 rounded-4 bg-light d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold text-dark">{{ aval.form.primernombre }} {{ aval.form.ape_pat }}</div>
                                                <div class="small text-muted">DNI: {{ aval.form.dni }} · Cel: {{ aval.form.celular }}</div>
                                            </div>
                                            <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" @click="form.aval_id = null; aval.dni = ''; aval.encontrado = false"><i class="fas fa-times"></i></button>
                                        </div>

                                        <!-- Nuevo Aval Form -->
                                        <div v-if="!aval.encontrado && aval.form.dni && !form.aval_id" class="mt-4 p-3 border border-warning-subtle bg-warning-subtle rounded-4">
                                            <p class="small text-warning-emphasis fw-bold mb-3"><i class="fas fa-info-circle me-1"></i>DNI NO ENCONTRADO. REGISTRE LOS DATOS:</p>
                                            <div class="row g-2">
                                                <div class="col-md-6"><input v-model="aval.form.ape_pat" class="form-control form-control-sm border-0 shadow-none" placeholder="Ape. Paterno" /></div>
                                                <div class="col-md-6"><input v-model="aval.form.ape_mat" class="form-control form-control-sm border-0 shadow-none" placeholder="Ape. Materno" /></div>
                                                <div class="col-md-12"><input v-model="aval.form.primernombre" class="form-control form-control-sm border-0 shadow-none" placeholder="Nombres Completos" /></div>
                                                <div class="col-md-12"><input v-model="aval.form.direccion" class="form-control form-control-sm border-0 shadow-none" placeholder="Dirección de Domicilio" /></div>
                                                <div class="col-12"><button class="btn btn-warning w-100 btn-sm fw-bold border-0 shadow-none" @click="registrarAval">GUARDAR Y ASIGNAR AVAL</button></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen del Crédito -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                                    <div class="bg-primary text-white p-4">
                                        <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Total a Devolver</h6>
                                        <h2 class="fw-bold mb-0">S/ {{ totalCalc }}</h2>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Monto Base:</span>
                                                <span class="fw-bold">S/ {{ form.monto || '0.00' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Interés ({{ form.tasainteres }}%):</span>
                                                <span class="fw-bold text-success">+ S/ {{ toMoney2(toNumber(form.monto) * (toNumber(form.tasainteres)/100)) }}</span>
                                            </div>
                                            <hr class="my-3 opacity-25">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted small">Mora por Atraso:</span>
                                                <span class="text-danger fw-bold">S/ {{ form.costomora }}</span>
                                            </div>
                                            <div class="text-muted extra-small italic">(*) La mora se aplica por cada día de retraso.</div>
                                        </div>

                                        <div class="bg-light p-3 rounded-4 mb-4 text-center">
                                            <div class="text-uppercase small fw-bold text-muted mb-1">Estimación de Cuotas</div>
                                            <div class="fs-4 fw-bold text-dark">S/ {{ toMoney2(toNumber(totalCalc) / (toNumber(form.plazo) || 1)) }}</div>
                                            <div class="small text-muted">Aprox. por cuota</div>
                                        </div>

                                        <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow mt-2" @click="submitForm">
                                            <i class="fas fa-check-circle me-2"></i>PROCESAR CRÉDITO
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
.bg-white-20 { background: rgba(255,255,255,0.2); }
.extra-small { font-size: 0.75rem; }
.bg-primary-subtle { background-color: #e9f2ff !important; }
.border-primary-subtle { border-color: #cfe2ff !important; }
.bg-warning-subtle { background-color: #fff9e6 !important; }
.border-warning-subtle { border-color: #ffecb5 !important; }
.modal-body { max-height: 80vh; overflow-y: auto; }
</style>
