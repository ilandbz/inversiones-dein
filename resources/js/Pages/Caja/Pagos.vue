<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useHelper from '@/Helpers'
import useCliente from '@/Composables/Cliente'
import useCredito from '@/Composables/Credito'
import usePagosCredito from '@/Composables/PagosCredito'
import useAhorro from '@/Composables/Ahorro'
import useDatosSession from '@/Composables/session'

const { Toast, Swal, formatoDinero, formatoFecha } = useHelper()
const { obtenerClientePorBusqueda, cliente: clienteEncontrado } = useCliente()
const { obtenerCreditos, creditos, obtenerCronograma } = useCredito()
const { pagos, guardarPago, listarPagos, loading: loadingPagos } = usePagosCredito()
const { ahorros, guardarAhorro, listarAhorrosPorCliente, loading: loadingAhorros } = useAhorro()
const { usuario } = useDatosSession()

// --- State ---
const activeTab = ref('credito') 
const searchQuery = ref('')
const loadingSearch = ref(false)
const searched = ref(false)
const clientFound = ref(false)
const selectedCredito = ref(null)
const cronograma = ref([])
const modoPago = ref('MONTO') 
const detallesForm = ref([])

const formPago = ref({
    credito_id: '',
    nro: 1,
    montopagado: 0,
    mediopago: 'EFECTIVO'
})

const formAhorro = ref({
    cliente_id: '',
    tipo_ahorro: 'DEPOSITO',
    monto: 0,
    fecha_movimiento: formatoFecha(null, 'YYYY-MM-DD'),
    metodo_pago: 'EFECTIVO',
    notas: ''
})

const cuotasPendientes = computed(() => {
  const startNro = Number(formPago.value.nro || 1)
  return (cronograma.value || [])
    .filter(c => Number(c.nrocuota) >= startNro)
    .sort((a,b) => Number(a.nrocuota) - Number(b.nrocuota))
})

// --- Logic ---
const handleSearch = async () => {
    if (!searchQuery.value) return
    loadingSearch.value = true
    searched.value = true
    clientFound.value = false
    selectedCredito.value = null
    cronograma.value = []
    
    try {
        await obtenerClientePorBusqueda(searchQuery.value)
        if (clienteEncontrado.value && clienteEncontrado.value.id) {
            clientFound.value = true
            await listarAhorrosPorCliente(clienteEncontrado.value.id)
            await obtenerCreditos({ cliente_id: clienteEncontrado.value.id, estado: 'DESEMBOLSADO' })
        }
    } catch (error) {
        console.error(error)
    } finally {
        loadingSearch.value = false
    }
}

const seleccionarCredito = async (credito) => {
    selectedCredito.value = credito
    formPago.value.credito_id = credito.id
    const res = await obtenerCronograma(credito.id)
    cronograma.value = res?.data || res || []
    await listarPagos(credito.id)
    formPago.value.nro = (pagos.value?.length || 0) + 1
}

const ejecutarPago = async () => {
    if (formPago.value.montopagado <= 0) return Swal.fire('Error', 'El monto a pagar debe ser mayor a 0', 'error')
    
    try {
        const res = await guardarPago({ ...formPago.value, detalles: detallesForm.value })
        if (res.ok) {
            Toast.fire({ icon: 'success', title: 'Pago realizado correctamente' })
            if (selectedCredito.value) seleccionarCredito(selectedCredito.value)
        }
    } catch (e) {
        Swal.fire('Error', 'No se pudo procesar el pago', 'error')
    }
}

const ejecutarAhorro = async () => {
    if (formAhorro.value.monto <= 0) return Swal.fire('Error', 'El monto debe ser mayor a 0', 'error')
    formAhorro.value.cliente_id = clienteEncontrado.value.id
    try {
        const res = await guardarAhorro(formAhorro.value)
        if (res.ok) {
            Toast.fire({ icon: 'success', title: 'Operación de ahorro exitosa' })
            await listarAhorrosPorCliente(clienteEncontrado.value.id)
            formAhorro.value.monto = 0
            formAhorro.value.notas = ''
        }
    } catch (e) {
         Swal.fire('Error', 'Error al procesar ahorro', 'error')
    }
}

const cuotaNumber = (x) => Number(String(x ?? '0').replace(',', '.')) || 0

watch(() => formPago.value.montopagado, (monto) => {
    if (!selectedCredito.value) return
    let restante = cuotaNumber(monto)
    detallesForm.value = []
    for (const c of cuotasPendientes.value) {
        const cuota = cuotaNumber(c.cuota)
        if (restante <= 0) break
        const pago = Math.min(restante, cuota)
        detallesForm.value.push({ cronograma_id: c.id, nrocuota: c.nrocuota, monto_pagado: pago })
        restante -= pago
    }
})

onMounted(() => {
    if (searchQuery.value) handleSearch()
})
</script>

<template>
    <AppLayoutDefault title="Caja - Pagos y Depósitos">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Buscador Principal -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-4 bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h4 class="fw-bold mb-1">Centro de Pagos</h4>
                                <p class="opacity-75 small mb-0">Busque un cliente por DNI o Apellidos para iniciar una transacción.</p>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <div class="input-group input-group-lg bg-white rounded-pill px-3 shadow">
                                    <span class="input-group-text bg-transparent border-0 text-primary"><i class="fas fa-search"></i></span>
                                    <input v-model="searchQuery" type="text" class="form-control bg-transparent border-0 fs-5" placeholder="DNI o Apellidos..." @keyup.enter="handleSearch">
                                    <button class="btn btn-primary rounded-pill px-4 my-1 ms-2 fw-bold" @click="handleSearch" :disabled="loadingSearch">
                                        <span v-if="loadingSearch" class="spinner-border spinner-border-sm"></span>
                                        <span v-else>BUSCAR</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resultado de búsqueda / Info Cliente -->
                    <div v-if="clientFound" class="card-body p-4 border-top">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary-subtle text-primary rounded-circle p-3 me-4">
                                <i class="fas fa-user-check fs-2"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-uppercase">{{ clienteEncontrado.persona?.apenom }}</h4>
                                <div class="d-flex gap-3 text-muted small mt-1">
                                    <span><i class="fas fa-id-card me-1"></i> DNI: {{ clienteEncontrado.persona?.dni }}</span>
                                    <span><i class="fas fa-phone-alt me-1"></i> {{ clienteEncontrado.persona?.celular }}</span>
                                    <span class="badge bg-success-subtle text-success border border-success px-3">{{ clienteEncontrado.estado }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="clientFound" class="row">
                    <!-- Nav Tabs -->
                    <div class="col-12 mb-4">
                        <ul class="nav nav-pills bg-white p-2 rounded-pill shadow-sm d-inline-flex border">
                            <li class="nav-item">
                                <button class="nav-link rounded-pill px-4 py-2 fw-bold" :class="{ active: activeTab === 'credito' }" @click="activeTab = 'credito'">
                                    <i class="fas fa-file-invoice-dollar me-2"></i> PAGAR CRÉDITOS
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link rounded-pill px-4 py-2 fw-bold" :class="{ active: activeTab === 'ahorro' }" @click="activeTab = 'ahorro'">
                                    <i class="fas fa-piggy-bank me-2"></i> DEPÓSITOS/RETIROS
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Módulo de Créditos -->
                    <div v-if="activeTab === 'credito'" class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white p-4 border-0">
                                <h5 class="fw-bold mb-0">Créditos por Pagar</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr class="small text-muted text-uppercase fw-bold">
                                                <th class="ps-4">Código</th>
                                                <th>Monto Origen</th>
                                                <th>Saldo Deudor</th>
                                                <th>Estado</th>
                                                <th class="pe-4 text-end">Seleccionar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="c in (creditos.data || creditos)" :key="c.id" :class="{ 'bg-primary-subtle': selectedCredito?.id === c.id }">
                                                <td class="ps-4 fw-bold">#{{ c.id }}</td>
                                                <td>{{ formatoDinero(c.monto) }}</td>
                                                <td class="text-danger fw-bold">{{ formatoDinero(c.total_deuda || c.monto) }}</td>
                                                <td><span class="badge bg-info px-3">{{ c.estado }}</span></td>
                                                <td class="pe-4 text-end">
                                                    <button class="btn btn-sm btn-primary rounded-pill px-3" @click="seleccionarCredito(c)">
                                                        VER CRONOGRAMA
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Calculadora de Pago -->
                        <div v-if="selectedCredito" class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4">Calculadora de Pago - Crédito #{{ selectedCredito.id }}</h5>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Monto a Cobrar (S/)</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-primary text-white border-0 rounded-start-4">S/</span>
                                            <input v-model="formPago.montopagado" type="number" step="0.01" class="form-control border-0 bg-light rounded-end-4 shadow-none fw-bold" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="p-3 bg-light rounded-4">
                                            <div class="small text-muted mb-1 text-uppercase fw-bold">Cuota Próxima</div>
                                            <div v-if="cuotasPendientes.length" class="fs-3 fw-bold text-primary">
                                                {{ formatoDinero(cuotasPendientes[0].cuota) }}
                                            </div>
                                            <div v-else class="fs-4 text-success fw-bold">TOTAL PAGADO</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 mt-4" v-if="detallesForm.length">
                                        <h6 class="fw-bold mb-3">Distribución sugerida:</h6>
                                        <div class="table-responsive rounded-4 border overflow-hidden">
                                            <table class="table table-sm table-bordered mb-0 bg-white">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Nro Cuota</th>
                                                        <th class="text-end">Monto Aplicado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="d in detallesForm" :key="d.cronograma_id">
                                                        <td class="ps-3 fw-bold">Cuota {{ d.nrocuota }}</td>
                                                        <td class="text-end font-monospace fw-bold pe-3">S/ {{ d.monto_pagado.toFixed(2) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold shadow-sm mt-3" @click="ejecutarPago">
                                            <i class="fas fa-cash-register me-2"></i> REGISTRAR COBRO Y EMITIR TICKET
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Módulo de Ahorros -->
                    <div v-if="activeTab === 'ahorro'" class="col-lg-8">
                       <div class="card border-0 shadow-sm rounded-4 mb-4">
                           <div class="card-body p-4">
                               <h5 class="fw-bold mb-4">Nueva Operación de Ahorro</h5>
                               <div class="row g-3">
                                   <div class="col-md-4">
                                       <label class="form-label small fw-bold text-muted">Transacción</label>
                                       <select v-model="formAhorro.tipo_ahorro" class="form-select border-0 bg-light rounded-3">
                                           <option value="DEPOSITO">DEPÓSITO</option>
                                           <option value="RETIRO">RETIRO</option>
                                       </select>
                                   </div>
                                    <div class="col-md-4">
                                       <label class="form-label small fw-bold text-muted">Monto (S/)</label>
                                       <input v-model="formAhorro.monto" type="number" step="0.01" class="form-control border-0 bg-light rounded-3 fw-bold">
                                   </div>
                                   <div class="col-md-4">
                                       <label class="form-label small fw-bold text-muted">Cuenta</label>
                                       <select v-model="formAhorro.ahorro_id" class="form-select border-0 bg-light rounded-3">
                                           <option v-for="a in ahorros" :key="a.id" :value="a.id">Cuenta #{{ a.id }} - Saldo: S/ {{ a.saldo }}</option>
                                       </select>
                                   </div>
                                   <div class="col-12 mt-3">
                                       <button class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold shadow-sm" @click="ejecutarAhorro">
                                           <i class="fas fa-piggy-bank me-2"></i> PROCESAR OPERACIÓN
                                       </button>
                                   </div>
                               </div>
                           </div>
                       </div>
                    </div>

                    <!-- Sidebar Historial -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 mb-4 h-100 overflow-hidden">
                            <div class="card-header bg-white p-4 border-0">
                                <h6 class="fw-bold text-primary text-uppercase small mb-0">Actividad Reciente</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <div v-if="!pagos.length && activeTab === 'credito'" class="p-5 text-center text-muted italic">
                                        No hay pagos previos registrados.
                                    </div>
                                    <template v-if="activeTab === 'credito'">
                                        <div v-for="p in pagos" :key="p.id" class="list-group-item p-3 border-0 border-bottom">
                                            <div class="d-flex justify-content-between">
                                                <div class="fw-bold text-dark">S/ {{ p.monto }}</div>
                                                <div class="small text-muted">{{ p.fecha }}</div>
                                            </div>
                                            <div class="small text-muted mt-1"><i class="fas fa-clock opacity-50 me-1"></i> {{ p.hora }} · Ticket #{{ p.id }}</div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="searched && !loadingSearch" class="text-center py-5">
                    <img src="https://illustrations.popsy.co/blue/searching.svg" style="width: 250px;" class="mb-4 opacity-75">
                    <h3 class="fw-bold">No encontramos al cliente</h3>
                    <p class="text-muted">Verifique el DNI o Apellidos e intente nuevamente.</p>
                </div>
            </div>
        </div>
    </AppLayoutDefault>
</template>

<style scoped>
.transition-all { transition: all 0.2s ease-in-out; }
.bg-primary-subtle { background-color: #e9f2ff !important; }
.rounded-start-4 { border-top-left-radius: 1.5rem !important; border-bottom-left-radius: 1.5rem !important; }
.rounded-end-4 { border-top-right-radius: 1.5rem !important; border-bottom-right-radius: 1.5rem !important; }
.card { transition: all 0.3s ease; }
.font-monospace { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; }
</style>
