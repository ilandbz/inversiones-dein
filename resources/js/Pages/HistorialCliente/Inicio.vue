

<script setup>
import { ref, computed } from 'vue'

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

const riskPercentage = computed(() => {
  if (client.value.riskLevel === 'Bajo') return 100
  if (client.value.riskLevel === 'Intermedio') return 60
  if (client.value.riskLevel === 'Alto') return 30
  return 0
})


const activeTab = ref('credits')

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
  if (client.value.riskLevel === 'Bajo') return '#10b981' // emerald-500
  if (client.value.riskLevel === 'Intermedio') return '#f59e0b' // amber-500
  if (client.value.riskLevel === 'Alto') return '#ef4444' // red-500
  return '#d1d5db'
})

const riskClass = computed(() => {
  if (client.value.riskLevel === 'Bajo') return 'text-emerald-600'
  if (client.value.riskLevel === 'Intermedio') return 'text-amber-600'
  if (client.value.riskLevel === 'Alto') return 'text-red-600'
  return 'text-gray-600'
})

const totalSavings = computed(() => {
  return client.value.savings.reduce((acc, curr) => acc + curr.amount, 0).toFixed(2)
})


const tab = ref('credits')




const handleSearch = () => {
  if (!searchQuery.value) return

  loading.value = true
  searched.value = true

  // Simulate API call
  setTimeout(() => {
    loading.value = false

    // Mock logic: if search length > 3, we "find" a client
    if (searchQuery.value.length >= 3) {
      clientFound.value = true
      client.value = {
        name: searchQuery.value.match(/^\d+$/) ? 'Juan Perez Garcia' : searchQuery.value,
        dni: searchQuery.value.match(/^\d+$/) ? searchQuery.value : '45879612',
        initials: 'JP',
        memberSince: 'enero 2023',
        riskLevel: ['Bajo', 'Intermedio', 'Alto'][Math.floor(Math.random() * 3)],
        credits: [
          { id: 1, type: 'Préstamo Personal', amount: 5000, date: '15/05/2023', status: 'Pagado' },
          { id: 2, type: 'Capital de Trabajo', amount: 12000, date: '12/10/2023', status: 'Pagado' },
          { id: 3, type: 'Consumo', amount: 2500, date: '05/01/2024', status: 'En Curso' }
        ],
        savings: [
          { id: 1, type: 'Ahorro a la Vista', amount: 150.50, date: '20/02/2024', method: 'Ventanilla' },
          { id: 2, type: 'Ahorro a Plazo Fijo', amount: 3000.00, date: '01/12/2023', method: 'Transferencia' },
          { id: 3, type: 'Depósito Diario', amount: 25.00, date: '26/02/2024', method: 'Caja Recaudadora' }
        ]
      }

      // Special case for initials if name is provided
      if (!searchQuery.value.match(/^\d+$/)) {
        const parts = searchQuery.value.split(' ')
        client.value.initials = parts.map(p => p[0]).join('').toUpperCase().substring(0, 2)
      }
    } else {
      clientFound.value = false
    }
  }, 800)
}
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

.animate-in {
  animation-duration: 0.5s;
  animation-fill-mode: both;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes zoomIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.fade-in {
  animation-name: fadeIn;
}

.zoom-in {
  animation-name: zoomIn;
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
            <div class="text-muted small">Busque un cliente para ver su historial de créditos, ahorros y nivel de riesgo.</div>
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
          
                    <button class="btn btn-primary" type="button" @click="handleSearch">
                      <i class="bi bi-search me-1"></i> Buscar
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
                          Si el cliente es encontrado, verás el resumen, riesgo y sus movimientos en pestañas.
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
                            <span class="badge bg-light text-dark border">
                              Cliente desde {{ client.memberSince }}
                            </span>
                          </div>
                        </div>
          
                        <!-- quick metrics -->
                        <div class="row g-2 mt-3">
                          <div class="col-12 col-md-4">
                            <div class="metric-box">
                              <div class="text-muted small">Créditos</div>
                              <div class="metric-val">{{ client.credits.length }}</div>
                            </div>
                          </div>
                          <div class="col-12 col-md-4">
                            <div class="metric-box">
                              <div class="text-muted small">En curso</div>
                              <div class="metric-val">{{ activeCredits }}</div>
                            </div>
                          </div>
                          <div class="col-12 col-md-4">
                            <div class="metric-box">
                              <div class="text-muted small">Ahorros total</div>
                              <div class="metric-val">S/ {{ totalSavings }}</div>
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
                      <tr v-for="c in client.credits" :key="c.id">
                        <td>
                          <div class="fw-semibold">{{ c.type }}</div>
                          <div class="text-muted small">Operación #{{ c.id }}</div>
                        </td>
                        <td class="text-muted">{{ c.date }}</td>
                        <td class="text-end fw-bold">S/ {{ c.amount }}</td>
                        <td class="text-end">
                          <span
                            class="badge border"
                            :class="c.status === 'Pagado'
                              ? 'bg-success-subtle text-success'
                              : 'bg-warning-subtle text-warning'"
                          >
                            {{ c.status }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
          
                <!-- SAVINGS -->
                <div v-else class="table-responsive">
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
          
              </div>
            </div>
          </div>
          
          <!-- NOT FOUND -->
          <div v-else-if="!loading && searchQuery && searched" class="card shadow-sm">
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
              <div class="text-muted">Ingrese el DNI o el nombre completo del cliente para visualizar su perfil crediticio.</div>
            </div>
          </div>
        </div>
      </div>

      </div>
    </section>
  </div>
</template>