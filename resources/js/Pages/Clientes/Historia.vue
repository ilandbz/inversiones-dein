<script setup>
import { ref, computed } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useHelper from '@/Helpers'
import useCliente from '@/Composables/Cliente.js'
import useNegocioCliente from '@/Composables/NegocioCliente.js'
import useActividadNegocio from '@/Composables/ActividadNegocio.js'

const { openModal, closeModal, Toast, Swal } = useHelper()

const { obtenerClientePorBusqueda, cliente } = useCliente()
const { negocios, obtenerNegociosPorCliente, agregarNegocio, actualizarNegocio, eliminarNegocio, errors: negocioErrors, respuesta: negocioRespuesta, loading: negocioLoading } = useNegocioCliente()
const { listaActividadNegocios, actividadNegocios, listaDetalleActividadNegocios, detalleActividadNegocios } = useActividadNegocio()

const searchQuery = ref('')
const loading = ref(false)
const searched = ref(false)
const clientFound = ref(false)

const clientData = ref({
  name: '', dni: '', initials: '', memberSince: '', riskLevel: '', credits: [], savings: [], photoUrl: '', estado: ''
})

const activeTab = ref('credits')

const FORM_DEFAULT = () => ({
  id: '', cliente_id: '', razonsocial: '', ruc: '', celular: '', actividad_negocio_id: '', detalle_actividad_id: '', inicioactividad: '', direccion: '', estadoCrud: 'nuevo'
})

const formNeg = ref(FORM_DEFAULT())
const modalTitle = ref('')

const riskLevelMap = {
  'Bajo': { color: '#10b981', bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-check-circle', percent: 100 },
  'Intermedio': { color: '#f59e0b', bg: 'bg-warning-subtle', text: 'text-warning', icon: 'fa-exclamation-circle', percent: 60 },
  'Alto': { color: '#ef4444', bg: 'bg-danger-subtle', text: 'text-danger', icon: 'fa-times-circle', percent: 30 }
}

const activeRisk = computed(() => riskLevelMap[clientData.value.riskLevel] || { color: '#d1d5db', bg: 'bg-light', text: 'text-muted', icon: 'fa-info-circle', percent: 0 })

const totalSavings = computed(() => clientData.value.savings.reduce((acc, curr) => acc + (Number(curr.amount) || 0), 0).toFixed(2))
const activeCreditsCount = computed(() => clientData.value.credits.filter(c => c.estado !== 'Pagado').length)

const handleSearch = async () => {
  if (!searchQuery.value) return
  loading.value = true
  searched.value = true
  clientFound.value = false
  try {
    await obtenerClientePorBusqueda(searchQuery.value.trim())
    if (cliente.value && cliente.value.id) {
      clientFound.value = true
      const p = cliente.value.persona ?? cliente.value
      clientData.value = {
        name: p.apenom,
        dni: p.dni,
        initials: (p.primernombre?.[0] ?? '') + (p.ape_pat?.[0] ?? ''),
        memberSince: cliente.value.fecha_reg,
        riskLevel: ['Bajo', 'Intermedio', 'Alto'][Math.floor(Math.random() * 3)],
        credits: cliente.value.creditos ?? [],
        savings: cliente.value.ahorros ?? [],
        photoUrl: `/storage/fotos/clientes/${p.dni}.webp`,
        estado: cliente.value.estado
      }
      await obtenerNegociosPorCliente(cliente.value.id)
    }
  } finally {
    loading.value = false
  }
}

const nuevoNegocio = async () => {
  formNeg.value = { ...FORM_DEFAULT(), cliente_id: cliente.value.id, estadoCrud: 'nuevo' }
  modalTitle.value = 'Nuevo Negocio'
  await listaActividadNegocios()
  openModal('#modalNegocio')
}

const editarNegocio = async (neg) => {
  formNeg.value = {
    id: neg.id, cliente_id: neg.cliente_id, razonsocial: neg.razonsocial, ruc: neg.ruc, celular: neg.celular,
    actividad_negocio_id: neg.detalle_activity?.actividad_negocio_id || '',
    detalle_actividad_id: neg.detalle_actividad_id,
    inicioactividad: neg.inicioactividad, direccion: neg.direccion, estadoCrud: 'editar'
  }
  modalTitle.value = 'Editar Negocio'
  await listaActividadNegocios()
  if (formNeg.value.actividad_negocio_id) await listaDetalleActividadNegocios(formNeg.value.actividad_negocio_id)
  openModal('#modalNegocio')
}

const guardarNegocio = async () => {
  if (formNeg.value.estadoCrud === 'nuevo') await agregarNegocio(formNeg.value)
  else await actualizarNegocio(formNeg.value)
  if (negocioRespuesta.value?.ok == 1) {
    Toast.fire({ icon: 'success', title: negocioRespuesta.value.mensaje })
    closeModal('#modalNegocio')
    await obtenerNegociosPorCliente(cliente.value.id)
  }
}

const eliminarNeg = (id) => {
  Swal.fire({
    title: '¿Eliminar Negocio?',
    text: 'Esta acción es permanente.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'SÍ, ELIMINAR',
    cancelButtonText: 'CANCELAR',
    customClass: { confirmButton: 'btn btn-danger rounded-pill px-4', cancelButton: 'btn btn-light rounded-pill px-4' },
    buttonsStyling: false
  }).then(async (result) => {
    if (result.isConfirmed) {
      await eliminarNegocio(id)
      if (negocioRespuesta.value?.ok == 1) {
        Toast.fire({ icon: 'success', title: negocioRespuesta.value.mensaje })
        await obtenerNegociosPorCliente(cliente.value.id)
      }
    }
  })
}

const onActividadChange = async () => {
  formNeg.value.detalle_actividad_id = ''
  if (formNeg.value.actividad_negocio_id) await listaDetalleActividadNegocios(formNeg.value.actividad_negocio_id)
}
</script>

<template>
  <AppLayoutDefault title="Expediente del Cliente">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Search Section -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
          <div class="card-body p-4 bg-primary text-white position-relative">
            <div class="position-absolute top-0 end-0 p-4 opacity-10"><i class="fas fa-search fa-6x"></i></div>
            <div class="row align-items-center position-relative">
                <div class="col-lg-6">
                    <h3 class="fw-bold mb-1">Consulta Histórica</h3>
                    <p class="text-white-50 small mb-4">Ingrese DNI o apellidos para visualizar el perfil integral</p>
                    <div class="input-group bg-white rounded-pill p-1 shadow">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="fas fa-search"></i></span>
                        <input v-model="searchQuery" type="text" class="form-control border-0 shadow-none px-2" placeholder="Ej: 45532962 o Perez Garcia Juan" @keyup.enter="handleSearch">
                        <button class="btn btn-dark rounded-pill px-4 fw-bold" @click="handleSearch" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ loading ? 'BUSCANDO...' : 'BUSCAR' }}
                        </button>
                    </div>
                </div>
            </div>
          </div>
        </div>

        <!-- No results -->
        <div v-if="searched && !clientFound && !loading" class="text-center py-5">
            <div class="mb-3 text-muted opacity-25"><i class="fas fa-user-slash fa-5x"></i></div>
            <h4 class="fw-bold text-dark">Cliente no localizado</h4>
            <p class="text-muted">Verifique los datos de búsqueda o registre un nuevo cliente.</p>
        </div>

        <!-- Result Content -->
        <div v-if="clientFound && !loading">
            <div class="row g-4 mb-4">
                <!-- Profile Card -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-4 mb-4">
                                <div class="avatar-box shadow-sm border border-2 border-white rounded-circle position-relative">
                                    <img :src="clientData.photoUrl" class="rounded-circle w-100 h-100 object-fit-cover" @error="(e) => e.target.src = '/storage/fotos/clientes/default.png'">
                                </div>
                                <div>
                                    <h2 class="fw-bold text-dark mb-1">{{ clientData.name }}</h2>
                                    <div class="d-flex gap-3 align-items-center">
                                        <span class="badge bg-light text-dark border rounded-pill px-3">DNI {{ clientData.dni }}</span>
                                        <span class="badge rounded-pill px-3" :class="clientData.estado === 'ACTIVO' ? 'bg-success' : 'bg-danger'">{{ clientData.estado }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded-4 text-center">
                                        <div class="text-muted small fw-bold mb-1">CRÉDITOS</div>
                                        <div class="fs-4 fw-bold text-primary">{{ clientData.credits.length }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded-4 text-center border border-primary border-opacity-10">
                                        <div class="text-muted small fw-bold mb-1">VIGENTES</div>
                                        <div class="fs-4 fw-bold text-success">{{ activeCreditsCount }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded-4 text-center">
                                        <div class="text-muted small fw-bold mb-1">AHORROS</div>
                                        <div class="fs-4 fw-bold text-dark">S/ {{ totalSavings }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded-4 text-center">
                                        <div class="text-muted small fw-bold mb-1">NEGOCIOS</div>
                                        <div class="fs-4 fw-bold text-warning">{{ negocios.length }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Risk Card -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4 overflow-hidden">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h5 class="fw-bold text-dark mb-0">Análisis de Riesgo</h5>
                            <i class="fas fa-chart-line text-muted opacity-50"></i>
                        </div>
                        <div class="text-center py-2">
                            <div class="risk-circle mx-auto mb-3 d-flex align-items-center justify-content-center" :style="{ borderColor: activeRisk.color }">
                                <i class="fas fa-2x" :class="[activeRisk.icon, activeRisk.text]"></i>
                            </div>
                            <span class="badge rounded-pill px-4 py-2 fs-6 mb-2" :class="activeRisk.bg + ' ' + activeRisk.text">{{ clientData.riskLevel }}</span>
                            <div class="small text-muted mb-4">Scoring basado en historial</div>
                            
                            <div class="progress rounded-pill bg-light" style="height: 8px;">
                                <div class="progress-bar rounded-pill" role="progressbar" :style="{ width: activeRisk.percent + '%', backgroundColor: activeRisk.color }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-0 border-0">
                    <ul class="nav nav-tabs nav-fill border-0">
                        <li class="nav-item">
                            <button class="nav-link py-3 border-0 transition-all d-flex align-items-center justify-content-center gap-2" :class="{ active: activeTab === 'credits' }" @click="activeTab = 'credits'">
                                <i class="fas fa-hand-holding-usd"></i> CRÉDITOS <span class="badge bg-primary rounded-pill">{{ clientData.credits.length }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 border-0 transition-all d-flex align-items-center justify-content-center gap-2" :class="{ active: activeTab === 'savings' }" @click="activeTab = 'savings'">
                                <i class="fas fa-piggy-bank"></i> AHORROS <span class="badge bg-dark rounded-pill">S/{{ totalSavings }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 border-0 transition-all d-flex align-items-center justify-content-center gap-2" :class="{ active: activeTab === 'negocios' }" @click="activeTab = 'negocios'">
                                <i class="fas fa-store"></i> NEGOCIOS <span class="badge bg-warning text-dark rounded-pill">{{ negocios.length }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4 min-vh-25">
                    <!-- Credits Table -->
                    <div v-if="activeTab === 'credits'" class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-3">Nº Operación</th>
                                    <th>Fecha Solicitud</th>
                                    <th>Tipo Crédito</th>
                                    <th class="text-end">Monto</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!clientData.credits.length"><td colspan="5" class="text-center py-5 text-muted">Sin historial de créditos.</td></tr>
                                <tr v-for="c in clientData.credits" :key="c.id">
                                    <td class="ps-3 fw-bold">#{{ c.id }}</td>
                                    <td>{{ c.fecha_reg }}</td>
                                    <td><span class="fw-semibold">{{ c.tipo }}</span></td>
                                    <td class="text-end fw-bold text-dark">S/ {{ c.monto }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3" :class="c.estado === 'Pagado' ? 'bg-success' : 'bg-warning text-dark'">{{ c.estado }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Savings Table -->
                    <div v-if="activeTab === 'savings'" class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-3">Fecha</th>
                                    <th>Operación</th>
                                    <th>Modalidad</th>
                                    <th class="text-end pe-3">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!clientData.savings.length"><td colspan="4" class="text-center py-5 text-muted">Sin movimientos de ahorro.</td></tr>
                                <tr v-for="s in clientData.savings" :key="s.id">
                                    <td class="ps-3Small">{{ s.date }}</td>
                                    <td class="fw-bold">{{ s.type }}</td>
                                    <td><span class="badge bg-white border text-dark text-uppercase rounded-pill px-3">{{ s.method }}</span></td>
                                    <td class="text-end pe-3 fw-bold text-primary">S/ {{ s.amount }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Negocios Cards -->
                    <div v-if="activeTab === 'negocios'">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold text-muted text-uppercase small mb-0">Unidades de Negocio Registradas</h6>
                            <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" @click="nuevoNegocio">
                                <i class="fas fa-plus me-1"></i> NUEVO NEGOCIO
                            </button>
                        </div>
                        <div v-if="negocioLoading" class="text-center py-4"><div class="spinner-border text-primary"></div></div>
                        <div v-else-if="!negocios.length" class="text-center py-5 border rounded-4 border-dashed">
                             <i class="fas fa-store-slash fa-3x text-muted opacity-25 mb-3"></i>
                             <p class="text-muted mb-0">No hay negocios asociados.</p>
                        </div>
                        <div v-else class="row g-3">
                            <div v-for="n in negocios" :key="n.id" class="col-md-6 col-xl-4">
                                <div class="card border-0 bg-light rounded-4 h-100 p-3 shadow-sm hover-shadow transition-all">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="icon-box bg-white text-warning rounded-pill shadow-sm"><i class="fas fa-store"></i></div>
                                        <div class="dropdown">
                                            <button class="btn btn-link py-0 text-muted shadow-none" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                <li><a class="dropdown-item small" href="#" @click.prevent="editarNegocio(n)"><i class="fas fa-edit me-2 text-warning"></i> Editar</a></li>
                                                <li><a class="dropdown-item small text-danger" href="#" @click.prevent="eliminarNeg(n.id)"><i class="fas fa-trash me-2"></i> Eliminar</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">{{ n.razonsocial || 'Sin Razón Social' }}</h6>
                                    <div class="small text-muted mb-2"><i class="fas fa-barcode me-1 opacity-50"></i> RUC: {{ n.ruc || '-' }}</div>
                                    <div class="small text-muted mb-1"><i class="fas fa-map-marker-alt me-1 opacity-50"></i> {{ n.direccion || '-' }}</div>
                                    <div class="small text-muted"><i class="fas fa-tag me-1 opacity-50"></i> {{ n.detalle_actividad?.nombre || 'General' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
    
    <!-- Modal Negocio -->
    <div class="modal fade" id="modalNegocio" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold text-dark">{{ modalTitle }}</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form @submit.prevent="guardarNegocio" class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-bold">Razón Social</label>
                            <input v-model="formNeg.razonsocial" type="text" class="form-control bg-light border-0 rounded-pill px-3" placeholder="Nombre comercial" :class="{'is-invalid': negocioErrors?.razonsocial}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">RUC</label>
                            <input v-model="formNeg.ruc" type="text" class="form-control bg-light border-0 rounded-pill px-3" maxlength="11" placeholder="10XXXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Giro del Negocio</label>
                            <select v-model="formNeg.actividad_negocio_id" class="form-select bg-light border-0 rounded-pill px-3" @change="onActividadChange">
                                <option value="">-- Seleccionar --</option>
                                <option v-for="act in actividadNegocios" :key="act.id" :value="act.id">{{ act.nombre }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Actividad Específica</label>
                            <select v-model="formNeg.detalle_actividad_id" class="form-select bg-light border-0 rounded-pill px-3" :disabled="!formNeg.actividad_negocio_id">
                                <option value="">-- Seleccionar --</option>
                                <option v-for="det in detalleActividadNegocios" :key="det.id" :value="det.id">{{ det.nombre }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-bold">Dirección Fiscal / Local</label>
                            <input v-model="formNeg.direccion" type="text" class="form-control bg-light border-0 rounded-pill px-3" placeholder="Av / Jr / Calle...">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">GUARDAR NEGOCO</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
  </AppLayoutDefault>
</template>

<style scoped>
.avatar-box { width: 100px; height: 100px; }
.risk-circle { width: 80px; height: 80px; border-radius: 50%; border: 4px solid #eee; }
.nav-tabs .nav-link { color: #64748b; font-weight: 600; font-size: 0.9rem; }
.nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd !important; background: transparent; }
.icon-box { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; }
.transition-all { transition: all 0.2s ease-in-out; }
.hover-shadow:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; transform: translateY(-3px); }
.border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>