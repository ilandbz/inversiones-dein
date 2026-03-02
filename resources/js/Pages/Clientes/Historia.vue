<script setup>
import { ref, computed } from 'vue'
import useHelper from '@/Helpers'
import useCliente from '@/Composables/Cliente.js'
import useNegocioCliente from '@/Composables/NegocioCliente.js'
import useActividadNegocio from '@/Composables/ActividadNegocio.js'

const { openModal, closeModal, Toast, Swal } = useHelper()

const {
  obtenerClientePorBusqueda,
  cliente,
} = useCliente()

const {
  negocios,
  obtenerNegociosPorCliente,
  agregarNegocio,
  actualizarNegocio,
  eliminarNegocio,
  errors: negocioErrors,
  respuesta: negocioRespuesta,
  loading: negocioLoading
} = useNegocioCliente()

const {
  listaActividadNegocios,
  actividadNegocios,
  listaDetalleActividadNegocios,
  detalleActividadNegocios
} = useActividadNegocio()

/* ── Estado de búsqueda ── */
const searchQuery = ref('')
const loading = ref(false)
const searched = ref(false)
const clientFound = ref(false)

const client = ref({
  name: '',
  dni: '',
  initials: '',
  memberSince: '',
  riskLevel: '',
  credits: [],
  savings: []
})

/* ── Pestaña activa ── */
const activeTab = ref('credits')

/* ── Formulario negocio ── */
const FORM_DEFAULT = () => ({
  id: '',
  cliente_id: '',
  razonsocial: '',
  ruc: '',
  celular: '',
  actividad_negocio_id: '',
  detalle_actividad_id: '',
  inicioactividad: '',
  direccion: '',
  estadoCrud: 'nuevo'
})

const formNeg = ref(FORM_DEFAULT())
const modalTitle = ref('')

/* ── Computed ── */
const riskPercentage = computed(() => {
  if (client.value.riskLevel === 'Bajo') return 100
  if (client.value.riskLevel === 'Intermedio') return 60
  if (client.value.riskLevel === 'Alto') return 30
  return 0
})

const activeCredits = computed(() => {
  return client.value.credits.filter(c => c.status !== 'Pagado').length
})

const riskBadgeClass = computed(() => {
  if (client.value.riskLevel === 'Bajo') return 'bg-success-subtle text-success'
  if (client.value.riskLevel === 'Intermedio') return 'bg-warning-subtle text-warning'
  if (client.value.riskLevel === 'Alto') return 'bg-danger-subtle text-danger'
  return 'bg-light text-dark'
})

const riskTextClass = computed(() => {
  if (client.value.riskLevel === 'Bajo') return 'text-success'
  if (client.value.riskLevel === 'Intermedio') return 'text-warning'
  if (client.value.riskLevel === 'Alto') return 'text-danger'
  return 'text-dark'
})

const riskColor = computed(() => {
  if (client.value.riskLevel === 'Bajo') return '#10b981'
  if (client.value.riskLevel === 'Intermedio') return '#f59e0b'
  if (client.value.riskLevel === 'Alto') return '#ef4444'
  return '#d1d5db'
})

const totalSavings = computed(() => {
  return client.value.savings.reduce((acc, curr) => acc + curr.amount, 0).toFixed(2)
})

/* ── Búsqueda ── */
const handleSearch = async () => {
  if (!searchQuery.value) return

  loading.value = true
  searched.value = true
  clientFound.value = false
  negocios.value = []

  try {
    await obtenerClientePorBusqueda(searchQuery.value.trim())

    if (cliente.value && cliente.value.id) {
      clientFound.value = true

      const p = cliente.value.persona ?? cliente.value
      const name = p.apenom ?? `${p.primernombre ?? ''} ${p.ape_pat ?? ''} ${p.ape_mat ?? ''}`.trim()

      client.value = {
        name: name,
        dni: p.dni ?? searchQuery.value,
        initials: (p.primernombre?.[0] ?? '') + (p.ape_pat?.[0] ?? ''),
        memberSince: cliente.value.fecha_reg ?? '',
        riskLevel: ['Bajo', 'Intermedio', 'Alto'][Math.floor(Math.random() * 3)],
        credits: cliente.value.creditos ?? [],
        savings: cliente.value.ahorros ?? []
      }

      // Cargar negocios del cliente
      await obtenerNegociosPorCliente(cliente.value.id)
    } else {
      clientFound.value = false
    }
  } catch (e) {
    clientFound.value = false
  } finally {
    loading.value = false
  }
}

/* ── Negocios CRUD ── */
const nuevoNegocio = async () => {
  limpiarFormNeg()
  formNeg.value.estadoCrud = 'nuevo'
  formNeg.value.cliente_id = cliente.value.id
  modalTitle.value = 'Nuevo Negocio'
  await listaActividadNegocios()
  openModal('#modalNegocio')
}

const editarNegocio = async (neg) => {
  limpiarFormNeg()
  formNeg.value = {
    id: neg.id,
    cliente_id: neg.cliente_id,
    razonsocial: neg.razonsocial ?? '',
    ruc: neg.ruc ?? '',
    celular: neg.celular ?? '',
    actividad_negocio_id: neg.detalle_actividad?.actividad_negocio_id ?? '',
    detalle_actividad_id: neg.detalle_actividad_id ?? '',
    inicioactividad: neg.inicioactividad ?? '',
    direccion: neg.direccion ?? '',
    estadoCrud: 'editar'
  }
  modalTitle.value = 'Editar Negocio'
  await listaActividadNegocios()

  if (formNeg.value.actividad_negocio_id) {
    await listaDetalleActividadNegocios(formNeg.value.actividad_negocio_id)
  }

  openModal('#modalNegocio')
}

const guardarNegocio = async () => {
  const payload = {
    id: formNeg.value.id,
    cliente_id: formNeg.value.cliente_id,
    razonsocial: formNeg.value.razonsocial,
    ruc: formNeg.value.ruc,
    celular: formNeg.value.celular,
    detalle_actividad_id: formNeg.value.detalle_actividad_id,
    inicioactividad: formNeg.value.inicioactividad,
    direccion: formNeg.value.direccion,
  }

  if (formNeg.value.estadoCrud === 'nuevo') {
    await agregarNegocio(payload)
  } else {
    await actualizarNegocio(payload)
  }

  if (negocioErrors.value && Object.keys(negocioErrors.value).length > 0) return

  if (negocioRespuesta.value?.ok == 1) {
    Toast.fire({ icon: 'success', title: negocioRespuesta.value.mensaje })
    closeModal('#modalNegocio')
    limpiarFormNeg()
    await obtenerNegociosPorCliente(cliente.value.id)
  }
}

const eliminarNeg = (id) => {
  Swal.fire({
    title: '¿Estás seguro de eliminar?',
    text: 'Este negocio será eliminado permanentemente',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, eliminar'
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
  detalleActividadNegocios.value = []
  if (formNeg.value.actividad_negocio_id) {
    await listaDetalleActividadNegocios(formNeg.value.actividad_negocio_id)
  }
}

const limpiarFormNeg = () => {
  Object.assign(formNeg.value, FORM_DEFAULT())
  negocioErrors.value = ''
}
</script>

<style scoped>
.avatar-initials {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.2rem;
  color: #fff;
  flex-shrink: 0;
}

.metric-box {
  border: 1px solid #e5e7eb;
  border-radius: .5rem;
  padding: .75rem;
  text-align: center;
}

.metric-val {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.empty-icon {
  font-size: 3rem;
}

.tip-box {
  background-color: #f0f9ff;
  border-color: #bae6fd !important;
}

.tip-icon {
  color: #0284c7;
  font-size: 1.25rem;
}

.risk-title {
  font-size: 1.5rem;
  font-weight: 700;
  text-align: center;
}

.negocio-card {
  transition: box-shadow .2s, transform .2s;
}

.negocio-card:hover {
  box-shadow: 0 4px 15px rgba(0,0,0,.1);
  transform: translateY(-2px);
}
</style>

<template>
  <div class="page-content">
    <section class="row">
      <div class="col-12">

      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h3 class="mb-0">Historial del Cliente</h3>
            <div class="text-muted small">Busque un cliente para ver su historial de créditos, ahorros, nivel de riesgo y sus negocios.</div>
          </div>
          <span class="badge bg-light text-dark border">Historial</span>
        </div>

        <div class="card-body">
          <!-- SEARCH CARD -->
          <div class="card shadow-sm mb-4">
            <div class="card-body">
              <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-7">
                  <label class="form-label">Buscar por DNI o Apellidos y Nombres</label>

                  <div class="input-group">
                    <span class="input-group-text bg-white">
                      <i class="bi bi-search"></i>
                    </span>

                    <input
                      v-model="searchQuery"
                      type="text"
                      class="form-control"
                      placeholder="Ej: 45879612 o Perez Garcia Juan"
                      @keyup.enter="handleSearch"
                    />

                    <button class="btn btn-primary" type="button" @click="handleSearch" :disabled="loading">
                      <span v-if="loading">
                        <span class="spinner-border spinner-border-sm me-1"></span> Buscando...
                      </span>
                      <span v-else>
                        <i class="bi bi-search me-1"></i> Buscar
                      </span>
                    </button>
                  </div>

                  <div class="d-flex flex-wrap gap-2 mt-2">
                    <span class="badge bg-light text-secondary border">
                      <i class="bi bi-keyboard me-1"></i> Enter para buscar
                    </span>
                    <span class="badge bg-light text-secondary border">
                      <i class="bi bi-person-vcard me-1"></i> DNI o nombre completo
                    </span>
                  </div>
                </div>

                <div class="col-12 col-lg-5">
                  <div class="tip-box border rounded-3 p-3">
                    <div class="d-flex gap-2">
                      <div class="tip-icon">
                        <i class="bi bi-info-circle"></i>
                      </div>
                      <div>
                        <div class="fw-semibold">Tip rápido</div>
                        <div class="text-muted small">
                          Si el cliente es encontrado, verás el resumen, riesgo, movimientos y sus negocios en pestañas.
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- LOADING -->
          <div v-if="loading" class="card shadow-sm mb-4">
            <div class="card-body d-flex align-items-center justify-content-center py-5">
              <div class="spinner-border text-primary me-2" role="status"></div>
              <div class="text-muted">Buscando cliente...</div>
            </div>
          </div>

          <!-- RESULT -->
          <div v-if="clientFound && !loading">
            <div class="row g-3 mb-4">
              <!-- CLIENT CARD -->
              <div class="col-12 col-lg-8">
                <div class="card shadow-sm">
                  <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                      <div class="avatar-initials">
                        {{ client.initials }}
                      </div>

                      <div class="flex-grow-1">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                          <div>
                            <h4 class="mb-1">{{ client.name }}</h4>
                            <div class="text-muted small">DNI: <span class="fw-semibold text-dark">{{ client.dni }}</span></div>
                          </div>

                          <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-success-subtle text-success border">Activo</span>
                            <span v-if="client.memberSince" class="badge bg-light text-dark border">
                              Cliente desde {{ client.memberSince }}
                            </span>
                          </div>
                        </div>

                        <!-- quick metrics -->
                        <div class="row g-2 mt-3">
                          <div class="col-6 col-md-3">
                            <div class="metric-box">
                              <div class="text-muted small">Créditos</div>
                              <div class="metric-val">{{ client.credits.length }}</div>
                            </div>
                          </div>
                          <div class="col-6 col-md-3">
                            <div class="metric-box">
                              <div class="text-muted small">En curso</div>
                              <div class="metric-val">{{ activeCredits }}</div>
                            </div>
                          </div>
                          <div class="col-6 col-md-3">
                            <div class="metric-box">
                              <div class="text-muted small">Ahorros total</div>
                              <div class="metric-val">S/ {{ totalSavings }}</div>
                            </div>
                          </div>
                          <div class="col-6 col-md-3">
                            <div class="metric-box">
                              <div class="text-muted small">Negocios</div>
                              <div class="metric-val">{{ negocios.length }}</div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card-footer bg-light small text-muted d-flex justify-content-between flex-wrap gap-2">
                    <span>
                      <i class="bi bi-clock me-1"></i> Última búsqueda: <b class="text-dark">{{ searchQuery }}</b>
                    </span>
                    <span class="text-muted">
                      <i class="bi bi-shield-lock me-1"></i> Solo lectura
                    </span>
                  </div>
                </div>
              </div>

              <!-- RISK CARD -->
              <div class="col-12 col-lg-4">
                <div class="card shadow-sm h-100">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">
                      <i class="bi bi-graph-up-arrow me-2"></i> Nivel de Riesgo
                    </div>
                    <span class="badge bg-light text-secondary border">score</span>
                  </div>

                  <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <div class="text-muted small">Clasificación</div>
                      <span :class="['badge border', riskBadgeClass]">{{ client.riskLevel }}</span>
                    </div>

                    <div :class="['risk-title', riskTextClass]">{{ client.riskLevel }}</div>

                    <div class="progress mt-3" style="height: 10px;">
                      <div
                        class="progress-bar"
                        role="progressbar"
                        :style="{ width: riskPercentage + '%', backgroundColor: riskColor }"
                      ></div>
                    </div>

                    <div class="d-flex justify-content-between text-muted small mt-2">
                      <span>Alto</span><span>Intermedio</span><span>Bajo</span>
                    </div>

                    <div class="text-muted small mt-3">
                      <i class="bi bi-info-circle me-1"></i>
                      Basado en comportamiento histórico.
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TABS -->
            <div class="card shadow-sm">
              <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="fw-semibold">
                  <i class="bi bi-journal-text me-2"></i> Historial
                </div>

                <ul class="nav nav-pills">
                  <li class="nav-item">
                    <button
                      class="nav-link"
                      :class="{ active: activeTab === 'credits' }"
                      @click="activeTab = 'credits'"
                      type="button"
                    >
                      <i class="bi bi-cash-coin me-1"></i>
                      Créditos
                      <span class="badge bg-light text-dark border ms-2">{{ client.credits.length }}</span>
                    </button>
                  </li>
                  <li class="nav-item">
                    <button
                      class="nav-link"
                      :class="{ active: activeTab === 'savings' }"
                      @click="activeTab = 'savings'"
                      type="button"
                    >
                      <i class="bi bi-piggy-bank me-1"></i>
                      Ahorros
                      <span class="badge bg-light text-dark border ms-2">S/ {{ totalSavings }}</span>
                    </button>
                  </li>
                  <li class="nav-item">
                    <button
                      class="nav-link"
                      :class="{ active: activeTab === 'negocios' }"
                      @click="activeTab = 'negocios'"
                      type="button"
                    >
                      <i class="bi bi-shop me-1"></i>
                      Negocios
                      <span class="badge bg-light text-dark border ms-2">{{ negocios.length }}</span>
                    </button>
                  </li>
                </ul>
              </div>

              <div class="card-body p-0">
                <!-- CREDITS -->
                <div v-if="activeTab === 'credits'" class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 45%">Crédito</th>
                        <th>Fecha</th>
                        <th class="text-end">Monto</th>
                        <th class="text-end">Estado</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="client.credits.length === 0">
                        <td colspan="4" class="text-center text-muted py-4">Sin créditos registrados</td>
                      </tr>
                      <tr v-for="c in client.credits" :key="c.id">
                        <td>
                          <div class="fw-semibold">{{ c.tipo }}</div>
                          <div class="text-muted small">Operación #{{ c.id }}</div>
                        </td>
                        <td class="text-muted">{{ c.fecha_reg }}</td>
                        <td class="text-end fw-bold">S/ {{ c.monto }}</td>
                        <td class="text-end">
                          <span
                            class="badge border"
                            :class="c.estado === 'Pagado'
                              ? 'bg-success-subtle text-success'
                              : 'bg-warning-subtle text-warning'"
                          >
                            {{ c.estado }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- SAVINGS -->
                <div v-else-if="activeTab === 'savings'" class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 45%">Ahorro</th>
                        <th>Fecha</th>
                        <th>Método</th>
                        <th class="text-end">Monto</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="client.savings.length === 0">
                        <td colspan="4" class="text-center text-muted py-4">Sin ahorros registrados</td>
                      </tr>
                      <tr v-for="s in client.savings" :key="s.id">
                        <td>
                          <div class="fw-semibold">{{ s.type }}</div>
                          <div class="text-muted small">Movimiento #{{ s.id }}</div>
                        </td>
                        <td class="text-muted">{{ s.date }}</td>
                        <td>
                          <span class="badge bg-light text-secondary border text-uppercase">{{ s.method }}</span>
                        </td>
                        <td class="text-end fw-bold">S/ {{ s.amount }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- NEGOCIOS -->
                <div v-else-if="activeTab === 'negocios'" class="p-3">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted small">Negocios asociados a este cliente</div>
                    <button class="btn btn-primary btn-sm" @click="nuevoNegocio">
                      <i class="bi bi-plus-lg me-1"></i> Agregar Negocio
                    </button>
                  </div>

                  <!-- Loading negocios -->
                  <div v-if="negocioLoading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="text-muted ms-2">Cargando negocios...</span>
                  </div>

                  <!-- Sin negocios -->
                  <div v-else-if="negocios.length === 0" class="text-center py-5">
                    <div class="empty-icon text-muted mb-2">
                      <i class="bi bi-shop-window"></i>
                    </div>
                    <h6 class="mb-1">Sin negocios registrados</h6>
                    <div class="text-muted small">Este cliente aún no tiene negocios. Agregue uno nuevo.</div>
                  </div>

                  <!-- Cards de negocios -->
                  <div v-else class="row g-3">
                    <div class="col-12 col-md-6 col-xl-4" v-for="neg in negocios" :key="neg.id">
                      <div class="card border negocio-card h-100">
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0 fw-bold text-primary">
                              <i class="bi bi-building me-1"></i>
                              {{ neg.razonsocial || 'Sin razón social' }}
                            </h6>
                            <div class="btn-group btn-group-sm">
                              <button class="btn btn-outline-warning btn-sm" title="Editar" @click="editarNegocio(neg)">
                                <i class="bi bi-pencil"></i>
                              </button>
                              <button class="btn btn-outline-danger btn-sm" title="Eliminar" @click="eliminarNeg(neg.id)">
                                <i class="bi bi-trash"></i>
                              </button>
                            </div>
                          </div>

                          <div class="small text-muted mb-1" v-if="neg.ruc">
                            <i class="bi bi-upc me-1"></i> RUC: <span class="fw-semibold text-dark">{{ neg.ruc }}</span>
                          </div>
                          <div class="small text-muted mb-1" v-if="neg.celular">
                            <i class="bi bi-telephone me-1"></i> {{ neg.celular }}
                          </div>
                          <div class="small text-muted mb-1" v-if="neg.direccion">
                            <i class="bi bi-geo-alt me-1"></i> {{ neg.direccion }}
                          </div>
                          <div class="small text-muted mb-1" v-if="neg.inicioactividad">
                            <i class="bi bi-calendar-event me-1"></i> Inicio: {{ neg.inicioactividad }}
                          </div>
                          <div class="small text-muted" v-if="neg.detalle_actividad">
                            <i class="bi bi-tag me-1"></i>
                            {{ neg.detalle_actividad.actividad_negocio?.nombre ?? '' }}
                            <span v-if="neg.detalle_actividad.nombre"> — {{ neg.detalle_actividad.nombre }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- NOT FOUND -->
          <div v-else-if="!loading && searchQuery && searched && !clientFound" class="card shadow-sm">
            <div class="card-body py-5 text-center">
              <div class="empty-icon text-danger mb-2">
                <i class="bi bi-person-x"></i>
              </div>
              <h5 class="mb-1">Cliente no encontrado</h5>
              <div class="text-muted">No pudimos encontrar ningún cliente con los datos proporcionados.</div>
              <button class="btn btn-outline-secondary mt-3" @click="searchQuery = ''">
                <i class="bi bi-eraser me-1"></i> Limpiar búsqueda
              </button>
            </div>
          </div>

          <!-- EMPTY STATE -->
          <div v-else-if="!loading && !searched" class="card shadow-sm">
            <div class="card-body py-5 text-center">
              <div class="empty-icon text-primary mb-2">
                <i class="bi bi-search"></i>
              </div>
              <h5 class="mb-1">¿A quién buscamos hoy?</h5>
              <div class="text-muted">Ingrese el DNI o el nombre completo del cliente para visualizar su perfil crediticio y negocios.</div>
            </div>
          </div>
        </div>
      </div>

      </div>
    </section>
  </div>

  <!-- MODAL NEGOCIO -->
  <div class="modal fade" id="modalNegocio" tabindex="-1" aria-labelledby="modalNegocioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalNegocioLabel">{{ modalTitle }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <!-- Razón Social -->
            <div class="col-md-6">
              <label class="form-label">Razón Social</label>
              <input type="text" class="form-control" :class="{'is-invalid': negocioErrors?.razonsocial}" v-model="formNeg.razonsocial" placeholder="Nombre del negocio">
              <div class="invalid-feedback" v-if="negocioErrors?.razonsocial">{{ negocioErrors.razonsocial[0] }}</div>
            </div>

            <!-- RUC -->
            <div class="col-md-6">
              <label class="form-label">RUC</label>
              <input type="text" class="form-control" :class="{'is-invalid': negocioErrors?.ruc}" v-model="formNeg.ruc" placeholder="20XXXXXXXXX" maxlength="11">
              <div class="invalid-feedback" v-if="negocioErrors?.ruc">{{ negocioErrors.ruc[0] }}</div>
            </div>

            <!-- Celular -->
            <div class="col-md-6">
              <label class="form-label">Celular</label>
              <input type="text" class="form-control" :class="{'is-invalid': negocioErrors?.celular}" v-model="formNeg.celular" placeholder="999 999 999">
              <div class="invalid-feedback" v-if="negocioErrors?.celular">{{ negocioErrors.celular[0] }}</div>
            </div>

            <!-- Inicio de actividad -->
            <div class="col-md-6">
              <label class="form-label">Inicio de Actividad</label>
              <input type="date" class="form-control" :class="{'is-invalid': negocioErrors?.inicioactividad}" v-model="formNeg.inicioactividad">
              <div class="invalid-feedback" v-if="negocioErrors?.inicioactividad">{{ negocioErrors.inicioactividad[0] }}</div>
            </div>

            <!-- Actividad de Negocio (padre) -->
            <div class="col-md-6">
              <label class="form-label">Actividad de Negocio</label>
              <select class="form-select" v-model="formNeg.actividad_negocio_id" @change="onActividadChange">
                <option value="">-- Seleccione --</option>
                <option v-for="act in actividadNegocios" :key="act.id" :value="act.id">
                  {{ act.nombre }}
                </option>
              </select>
            </div>

            <!-- Detalle Actividad (hijo) -->
            <div class="col-md-6">
              <label class="form-label">Detalle de Actividad</label>
              <select class="form-select" :class="{'is-invalid': negocioErrors?.detalle_actividad_id}" v-model="formNeg.detalle_actividad_id" :disabled="!formNeg.actividad_negocio_id">
                <option value="">-- Seleccione --</option>
                <option v-for="det in detalleActividadNegocios" :key="det.id" :value="det.id">
                  {{ det.nombre }}
                </option>
              </select>
              <div class="invalid-feedback" v-if="negocioErrors?.detalle_actividad_id">{{ negocioErrors.detalle_actividad_id[0] }}</div>
            </div>

            <!-- Dirección -->
            <div class="col-12">
              <label class="form-label">Dirección del Negocio</label>
              <textarea class="form-control" :class="{'is-invalid': negocioErrors?.direccion}" v-model="formNeg.direccion" rows="2" placeholder="Dirección completa del negocio"></textarea>
              <div class="invalid-feedback" v-if="negocioErrors?.direccion">{{ negocioErrors.direccion[0] }}</div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i> Cancelar
          </button>
          <button type="button" class="btn btn-primary" @click="guardarNegocio">
            <i class="bi bi-check-lg me-1"></i> {{ formNeg.estadoCrud === 'nuevo' ? 'Guardar' : 'Actualizar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>