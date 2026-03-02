<script setup>
import { ref, watch, computed } from 'vue'
import useHelper from '@/Helpers'
import useCliente from '@/Composables/Cliente'
import useCredito from '@/Composables/Credito'
import usePagosCredito from '@/Composables/PagosCredito'
import useAhorro from '@/Composables/Ahorro'
import useDatosSession from '@/Composables/session'

const { openModal, closeModal, Toast, Swal, formatoFecha } = useHelper()
const { obtenerClientePorBusqueda, cliente: clienteEncontrado } = useCliente()
const { obtenerCreditos, creditos, obtenerCronograma } = useCredito()
const { pagos, guardarPago, listarPagos, loading: loadingPagos } = usePagosCredito()
const { ahorros, guardarAhorro, listarAhorrosPorCliente, loading: loadingAhorros } = useAhorro()
const { usuario } = useDatosSession()

// --- State ---
const activeTab = ref('credito') // 'credito' o 'ahorro'
const searchQuery = ref('')
const loadingSearch = ref(false)
const searched = ref(false)
const clientFound = ref(false)

const selectedCredito = ref(null)

const modoPago = ref('MONTO') // 'MONTO' | 'CUOTAS'
const cuotasSeleccionadas = ref(1)
const permitirParcial = ref(true) 
const cuotaNumber = (x) => Number(String(x ?? '0').replace(',', '.')) || 0
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
// --- Search ---
const handleSearch = async () => {
    if (!searchQuery.value) return
    loadingSearch.value = true
    searched.value = true
    clientFound.value = false
    
    try {
        await obtenerClientePorBusqueda(searchQuery.value)
        if (clienteEncontrado.value && clienteEncontrado.value.id) {
            clientFound.value = true
            formAhorro.value.cliente_id = clienteEncontrado.value.id
            await listarAhorrosPorCliente(clienteEncontrado.value.id)
            
            // Cargar créditos del cliente si estamos en pestaña crédito
            if (activeTab.value === 'credito') {
                await loadClientCredits()
            }
        }
    } catch (error) {
        console.error(error)
    } finally {
        loadingSearch.value = false
    }
}
function setDetallesFromCuotas(listaCuotas, totalMonto = null) {
  detallesForm.value = listaCuotas.map(c => ({
    cronograma_id: c.id,
    nrocuota: c.nrocuota,
    capital_pagado: cuotaNumber(c.cuota),
    interes_pagado: 0,
    mora_pagada: 0
  }))

  if (totalMonto != null) {
    formPago.value.montopagado = Number(totalMonto.toFixed(2))
  }
}

function buildDetallesByMonto(monto) {
  const pendientes = cuotasPendientes.value
  let restante = cuotaNumber(monto)
  const detalles = []

  for (const c of pendientes) {
    const cuota = cuotaNumber(c.cuota)
    if (cuota <= 0) continue

    if (restante >= cuota) {
      detalles.push({
        cronograma_id: c.id,
        nrocuota: c.nrocuota,
        capital_pagado: cuota,
        interes_pagado: 0,
        mora_pagada: 0
      })
      restante = Number((restante - cuota).toFixed(2))
    } else {
      // pago parcial (opcional)
      if (permitirParcial.value && restante > 0) {
        detalles.push({
          cronograma_id: c.id,
          nrocuota: c.nrocuota,
          capital_pagado: restante,
          interes_pagado: 0,
          mora_pagada: 0
        })
      }
      restante = 0
      break
    }
  }

  detallesForm.value = detalles.length
    ? detalles
    : [{ cronograma_id: '', capital_pagado: 0, interes_pagado: 0, mora_pagada: 0 }]
}

function buildDetallesByNumeroCuotas(n) {
  const pendientes = cuotasPendientes.value
  const take = pendientes.slice(0, Math.max(1, Number(n || 1)))
  const total = take.reduce((acc, c) => acc + cuotaNumber(c.cuota), 0)
  setDetallesFromCuotas(take, total)
}

// WATCHERS
watch(
  () => modoPago.value,
  (m) => {
    if (!selectedCredito.value) return
    if (m === 'CUOTAS') {
      buildDetallesByNumeroCuotas(cuotasSeleccionadas.value)
    } else {
      buildDetallesByMonto(formPago.value.montopagado)
    }
  }
)

watch(
  () => formPago.value.montopagado,
  (monto) => {
    if (!selectedCredito.value) return
    if (modoPago.value !== 'MONTO') return
    if (cuotasPendientes.value.length === 0) return
    buildDetallesByMonto(monto)
  }
)

watch(
  () => cuotasSeleccionadas.value,
  (n) => {
    if (!selectedCredito.value) return
    if (modoPago.value !== 'CUOTAS') return
    buildDetallesByNumeroCuotas(n)
  }
)
const loadClientCredits = async () => {
    if (!clienteEncontrado.value?.id) return
    await obtenerCreditos({
        buscar: clienteEncontrado.value.persona?.dni || clienteEncontrado.value.id,
        estado: 'DESEMBOLSADO', // Solo créditos activos para pagar
        paginacion: 50
    })
}

// --- Credits Logic ---
const cronograma = ref([])

const seleccionarCredito = async (credito) => {
    selectedCredito.value = credito
    formPago.value.credito_id = credito.id
    
    await listarPagos(credito.id)
    formPago.value.nro = pagos.value.length + 1

    try {
        const data = await obtenerCronograma(credito.id)
        cronograma.value = data
        
        // Auto-fill first pending row if possible
        const nextInstallment = cronograma.value.find(cu => cu.nrocuota === formPago.value.nro)
        if (nextInstallment) {
            detallesForm.value = [{
                cronograma_id: nextInstallment.id, 
                capital_pagado: nextInstallment.cuota, // Simplistic, usually needs split
                interes_pagado: 0, 
                mora_pagada: 0,
                nrocuota: nextInstallment.nrocuota // for UI
            }]
            modoPago.value = 'MONTO'
            permitirParcial.value = true
            formPago.value.montopagado = cuotaNumber(nextInstallment?.cuota || 0)
            buildDetallesByMonto(formPago.value.montopagado)
        } else {
            detallesForm.value = [{ cronograma_id: '', capital_pagado: 0, interes_pagado: 0, mora_pagada: 0 }]
        }
    } catch (e) {
        console.error(e)
    }
}

const detallesForm = ref([
    { cronograma_id: '', capital_pagado: 0, interes_pagado: 0, mora_pagada: 0 }
])

const agregarFilaDetalle = () => {
    detallesForm.value.push({ cronograma_id: '', capital_pagado: 0, interes_pagado: 0, mora_pagada: 0 })
}

const eliminarFilaDetalle = (index) => {
    detallesForm.value.splice(index, 1)
}

const registrarPago = async () => {
    if (formPago.value.montopagado <= 0) {
        Toast.fire({ icon: 'warning', title: 'Ingrese un monto válido' })
        return
    }

    const payload = {
        ...formPago.value,
        detalles: detallesForm.value
    }

    const result = await guardarPago(payload)
    if (result) {
        Toast.fire({ icon: 'success', title: 'Pago y detalles registrados' })
        await listarPagos(selectedCredito.value.id)
        formPago.value.montopagado = 0
        formPago.value.nro++
        detallesForm.value = [{ cronograma_id: '', capital_pagado: 0, interes_pagado: 0, mora_pagada: 0 }]
    }
}

// --- Savings Logic ---
const registrarAhorro = async () => {
    if (formAhorro.value.monto <= 0) {
        Toast.fire({ icon: 'warning', title: 'Ingrese un monto válido' })
        return
    }

    const result = await guardarAhorro(formAhorro.value)
    if (result?.ok === 1) {
        Toast.fire({ icon: 'success', title: result.mensaje })
        await listarAhorrosPorCliente(clienteEncontrado.value.id)
        formAhorro.value.monto = 0
        formAhorro.value.notas = ''
    }
}

const changeTab = (tab) => {
    activeTab.value = tab
    if (clientFound.value) {
        if (tab === 'credito') loadClientCredits()
        else listarAhorrosPorCliente(clienteEncontrado.value.id)
    }
}
</script>

<template>
    <div class="page-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card shadow-sm border-0 bg-primary text-white">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-lg bg-white-50 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-cash-stack fs-2 text-white"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold">Gestión de Caja - Pagos</h3>
                                    <p class="mb-0 opacity-75">Registro de pagos de créditos y movimientos de ahorros</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Buscar Cliente (DNI o Nombre)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input 
                                    v-model="searchQuery" 
                                    type="text" 
                                    class="form-control border-start-0" 
                                    placeholder="Ingrese DNI o nombre del cliente..."
                                    @keyup.enter="handleSearch"
                                />
                                <button class="btn btn-primary px-4" @click="handleSearch" :disabled="loadingSearch">
                                    <span v-if="loadingSearch" class="spinner-border spinner-border-sm me-1"></span>
                                    Buscar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end" v-if="clientFound">
                            <div class="p-3 bg-light rounded w-100 d-flex align-items-center">
                                <div class="flex-shrink-0 bg-primary rounded-circle p-2 me-3">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Cliente Seleccionado</div>
                                    <div class="fw-bold">{{ clienteEncontrado.persona?.apenom || 'Cargando...' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="card shadow-sm border-0 overflow-hidden" v-if="clientFound">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav nav-tabs nav-justified border-0" role="tablist">
                        <li class="nav-item">
                            <button 
                                class="nav-link py-3 border-0 transition-all fw-bold fs-5" 
                                :class="{ 'active bg-primary-subtle text-primary border-bottom border-primary border-3': activeTab === 'credito' }"
                                @click="changeTab('credito')"
                            >
                                <i class="bi bi-bank me-2"></i> Créditos
                            </button>
                        </li>
                        <li class="nav-item">
                            <button 
                                class="nav-link py-3 border-0 transition-all fw-bold fs-5" 
                                :class="{ 'active bg-success-subtle text-success border-bottom border-success border-3': activeTab === 'ahorro' }"
                                @click="changeTab('ahorro')"
                            >
                                <i class="bi bi-piggy-bank me-2"></i> Ahorros
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <!-- Tab Content: Crédito -->
                    <div v-if="activeTab === 'credito'">
                        <div class="row">
                            <!-- Lista de Créditos -->
                            <div class="col-lg-4">
                                <h5 class="fw-bold mb-3 border-bottom pb-2">Créditos Desembolsados</h5>
                                <div class="list-group list-group-flush border rounded overflow-hidden">
                                    <div v-if="!creditos?.data?.length" class="p-4 text-center text-muted">
                                        No se encontraron créditos activos.
                                    </div>
                                    <button 
                                        v-for="c in creditos.data" 
                                        :key="c.id"
                                        class="list-group-item list-group-item-action p-3 border-bottom"
                                        :class="{ 'active': selectedCredito?.id === c.id }"
                                        @click="seleccionarCredito(c)"
                                    >
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fw-bold">Exp: {{ c.id }} - {{ c.tipo }}</div>
                                                <div class="small">Monto: S/ {{ Number(c.monto).toFixed(2) }}</div>
                                                <div class="small opacity-75">Fecha: {{ c.fecha_reg }}</div>
                                            </div>
                                            <span class="badge bg-success rounded-pill">Activo</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Panel de Pago Detallado -->
                            <div class="col-lg-8" v-if="selectedCredito">
                                <div class="card bg-light border-0 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h5 class="fw-bold mb-0">Registrar Pago Detallado</h5>
                                            <div class="badge bg-primary text-white p-2">Siguiente Pago: #{{ formPago.nro }}</div>
                                        </div>

                                        <div class="row g-3 mb-4">
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">Modo</label>
                                            <select v-model="modoPago" class="form-select form-select-lg">
                                            <option value="MONTO">Por monto</option>
                                            <option value="CUOTAS">Por # cuotas</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3" v-if="modoPago === 'CUOTAS'">
                                            <label class="form-label small fw-bold">Cuotas a pagar</label>
                                            <input v-model="cuotasSeleccionadas" type="number" min="1" class="form-control form-control-lg" />
                                        </div>

                                        <div class="col-md-6" v-if="modoPago === 'MONTO'">
                                            <label class="form-label small fw-bold">Monto Total Recibido (S/)</label>
                                            <input v-model="formPago.montopagado" type="number" step="0.01"
                                            class="form-control form-control-lg text-primary fw-bold" placeholder="0.00">
                                        </div>

                                        <div class="col-md-6" v-else>
                                            <label class="form-label small fw-bold">Monto Total Calculado (S/)</label>
                                            <input :value="Number(formPago.montopagado).toFixed(2)" disabled
                                            class="form-control form-control-lg text-primary fw-bold">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Medio de Pago</label>
                                            <select v-model="formPago.mediopago" class="form-select form-select-lg">
                                            <option value="EFECTIVO">Efectivo</option>
                                            <option value="TRANSFERENCIA">Transferencia</option>
                                            <option value="DEPOSITO">Depósito</option>
                                            </select>
                                        </div>
                                        </div>

                                        <!-- Sección de Distribución (Detalles) -->
                                        <div class="bg-white p-3 rounded border mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold mb-0">Distribución del Pago</h6>
                                                <button class="btn btn-sm btn-outline-primary" @click="agregarFilaDetalle">
                                                    <i class="bi bi-plus-circle me-1"></i> Añadir Cuota
                                                </button>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-borderless align-middle mb-0">
                                                    <thead>
                                                        <tr class="small text-muted text-uppercase">
                                                            <th style="width: 30%">Cuota / ID</th>
                                                            <th>Capital</th>
                                                            <th>Interés</th>
                                                            <th>Mora</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(det, index) in detallesForm" :key="index">
                                                            <td>
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text" v-if="det.nrocuota">#{{ det.nrocuota }}</span>
                                                                    <input v-model="det.cronograma_id" type="number" class="form-control" placeholder="ID">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <input v-model="det.capital_pagado" type="number" step="0.01" class="form-control form-control-sm" placeholder="0.00">
                                                            </td>
                                                            <td>
                                                                <input v-model="det.interes_pagado" type="number" step="0.01" class="form-control form-control-sm" placeholder="0.00">
                                                            </td>
                                                            <td>
                                                                <input v-model="det.mora_pagada" type="number" step="0.01" class="form-control form-control-sm" placeholder="0.00">
                                                            </td>
                                                            <td class="text-end">
                                                                <button class="btn btn-link text-danger p-0" @click="eliminarFilaDetalle(index)">
                                                                    <i class="bi bi-x-circle"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <button class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" @click="registrarPago" :disabled="loadingPagos">
                                            <i class="bi bi-cash-coin me-2"></i> Procesar Pago Completo
                                        </button>
                                    </div>
                                </div>

                                <!-- Historial de Pagos -->
                                <h5 class="fw-bold mb-3">Historial de Pagos (Kardex)</h5>
                                <div class="table-responsive bg-white rounded shadow-sm border">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Cuota</th>
                                                <th>Fecha / Hora</th>
                                                <th>Monto</th>
                                                <th>Medio</th>
                                                <th>Usuario</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!pagos.length">
                                                <td colspan="5" class="text-center py-4 text-muted">No hay pagos registrados para este crédito.</td>
                                            </tr>
                                            <tr v-for="p in pagos" :key="p.id">
                                                <td class="fw-bold">#{{ p.nro }}</td>
                                                <td>
                                                    <div>{{ p.fecha }}</div>
                                                    <div class="small text-muted">{{ p.hora }}</div>
                                                </td>
                                                <td class="text-primary fw-bold">S/ {{ Number(p.montopagado).toFixed(2) }}</td>
                                                <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ p.mediopago }}</span></td>
                                                <td>{{ p.user?.name || 'Sistema' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-8" v-else>
                                <div class="h-100 d-flex flex-column align-items-center justify-content-center py-5 text-muted border rounded bg-light border-dashed">
                                    <i class="bi bi-arrow-left-circle fs-1 mb-3"></i>
                                    <h5>Seleccione un crédito de la lista</h5>
                                    <p>Para ver el historial y registrar nuevos pagos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content: Ahorro -->
                    <div v-if="activeTab === 'ahorro'">
                        <div class="row">
                            <!-- Formulario Ahorro -->
                            <div class="col-lg-5">
                                <div class="card bg-success-subtle border-0 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold mb-4 text-success"><i class="bi bi-plus-circle me-2"></i>Nuevo Movimiento</h5>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Tipo Movimiento</label>
                                                <select v-model="formAhorro.tipo_ahorro" class="form-select">
                                                    <option value="DEPOSITO">Depósito</option>
                                                    <option value="RETIRO">Retiro</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Fecha</label>
                                                <input v-model="formAhorro.fecha_movimiento" type="date" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Monto (S/)</label>
                                                <input v-model="formAhorro.monto" type="number" step="0.01" class="form-control form-control-lg text-success fw-bold" placeholder="0.00">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Medio de Pago</label>
                                                <select v-model="formAhorro.metodo_pago" class="form-select">
                                                    <option value="EFECTIVO">Efectivo</option>
                                                    <option value="TRANSFERENCIA">Transferencia</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Notas / Concepto</label>
                                                <textarea v-model="formAhorro.notas" class="form-control" rows="2" placeholder="Opcional..."></textarea>
                                            </div>
                                            <div class="col-12 mt-4">
                                                <button class="btn btn-success btn-lg w-100 fw-bold shadow-sm" @click="registrarAhorro" :disabled="loadingAhorros">
                                                    <i class="bi bi-check-circle me-2"></i> Registrar Movimiento
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Historial Ahorros -->
                            <div class="col-lg-7">
                                <h5 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                                    Historial de Movimientos
                                    <span class="badge bg-success py-2 px-3 rounded-pill fw-normal h6 mb-0">
                                        Saldo Total: S/ {{ ahorros.reduce((acc, a) => a.tipo_ahorro === 'DEPOSITO' ? acc + Number(a.monto) : acc - Number(a.monto), 0).toFixed(2) }}
                                    </span>
                                </h5>
                                <div class="table-responsive bg-white rounded shadow-sm border">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tipo</th>
                                                <th class="text-end">Monto</th>
                                                <th>Medio</th>
                                                <th>Concepto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!ahorros.length">
                                                <td colspan="5" class="text-center py-4 text-muted">Aún no hay movimientos de ahorro.</td>
                                            </tr>
                                            <tr v-for="a in ahorros" :key="a.id">
                                                <td>{{ a.fecha_movimiento }}</td>
                                                <td>
                                                    <span 
                                                        class="badge rounded-pill px-3" 
                                                        :class="a.tipo_ahorro === 'DEPOSITO' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'"
                                                    >
                                                        {{ a.tipo_ahorro }}
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold" :class="a.tipo_ahorro === 'DEPOSITO' ? 'text-success' : 'text-danger'">
                                                    {{ a.tipo_ahorro === 'DEPOSITO' ? '+' : '-' }} S/ {{ Number(a.monto).toFixed(2) }}
                                                </td>
                                                <td><span class="badge bg-light text-dark border">{{ a.metodo_pago }}</span></td>
                                                <td class="small">{{ a.notas || '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="searched && !loadingSearch" class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-person-x fs-1 text-danger"></i>
                </div>
                <h4 class="fw-bold">Cliente no encontrado</h4>
                <p class="text-muted">No pudimos encontrar registros con los datos ingresados.</p>
            </div>
            
            <div v-else-if="!searched" class="text-center py-5 opacity-50">
                <div class="mb-3">
                    <i class="bi bi-search fs-1"></i>
                </div>
                <h4>Realice una búsqueda para comenzar</h4>
                <p>Busque clientes por DNI o apellidos para gestionar sus pagos</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.transition-all {
    transition: all 0.2s ease-in-out;
}

.nav-tabs .nav-link:hover {
    background-color: #f8f9fa;
}

.list-group-item.active {
    background-color: var(--bs-primary-bg-subtle) !important;
    color: var(--bs-primary) !important;
    border-color: var(--bs-primary) !important;
}

.border-dashed {
    border-style: dashed !important;
}

.bg-white-50 {
    background-color: rgba(255, 255, 255, 0.2);
}
</style>
