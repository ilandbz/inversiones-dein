<script setup>
import { ref, watch } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useAhorro from '@/Composables/Ahorro'
import useCaja from '@/Composables/Caja'
import useCliente from '@/Composables/Cliente'
import useHelper from '@/Helpers'

const { listarAhorros, ahorros, depositar, retirar, loading } = useAhorro()
const { cajaActiva, obtenerCajaActiva } = useCaja()
const { obtenerClientePorBusqueda, cliente: clienteEncontrado, loading: clientLoading } = useCliente()
const { Toast, Swal, formatoDinero } = useHelper()

const searchQuery = ref('')
const selectedAhorro = ref(null)
const operacion = ref('DEPOSITO') // 'DEPOSITO' | 'RETIRO'

const form = ref({
    ahorro_id: '',
    monto: 0,
    metodo_pago: 'EFECTIVO',
    descripcion: '',
    caja_id: ''
})

watch(selectedAhorro, (newVal) => {
    if (newVal) form.value.ahorro_id = newVal.id
})

const handleSearch = async () => {
    if (!searchQuery.value) return
    selectedAhorro.value = null
    try {
        await obtenerClientePorBusqueda(searchQuery.value)
        if (clienteEncontrado.value) {
            await listarAhorros({ cliente_id: clienteEncontrado.value.id })
            await obtenerCajaActiva(1)
            if (cajaActiva.value) form.value.caja_id = cajaActiva.value.id
        }
    } catch (e) {
        console.error(e)
    }
}

const handleProcesar = async () => {
    if (!selectedAhorro.value) return
    if (form.value.monto <= 0) {
        Toast.fire({ icon: 'warning', title: 'Ingrese un monto válido' })
        return
    }

    try {
        if (operacion.value === 'DEPOSITO') {
            await depositar(form.value)
            await Swal.fire('Depósito Exitoso', `Se han depositado S/ ${form.value.monto} en la cuenta de ahorro.`, 'success')
        } else {
            await retirar(form.value)
            await Swal.fire('Retiro Exitoso', `Se han retirado S/ ${form.value.monto} de la cuenta de ahorro.`, 'success')
        }
        
        // Limpiar y refrescar
        form.value.monto = 0
        form.value.descripcion = ''
        await handleSearch() // Recargar datos
    } catch (e) {
        Swal.fire('Error', e.response?.data?.msg || 'No se pudo procesar la operación', 'error')
    }
}
</script>

<template>
    <AppLayoutDefault title="Depósitos y Retiros - Ahorros">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-primary text-white border-0 shadow-sm rounded-4">
                            <div class="card-body p-4 d-flex align-items-center">
                                <div class="bg-white-50 rounded-circle p-3 me-4">
                                    <i class="fas fa-exchange-alt fs-1"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0">Depósitos y Retiros</h3>
                                    <p class="mb-0 text-white-75">Gestione el flujo de efectivo en las cuentas de ahorro</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Búsqueda -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6 col-lg-8">
                                        <div class="input-group input-group-lg border-0 bg-light rounded-pill px-2">
                                            <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                                            <input v-model="searchQuery" type="text" class="form-control bg-transparent border-0" placeholder="Busque al socio por DNI o Apellidos..." @keyup.enter="handleSearch">
                                            <button class="btn btn-primary rounded-pill px-4 ms-2" @click="handleSearch" :disabled="clientLoading">BUSCAR</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4 text-end" v-if="clienteEncontrado">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <div class="text-end me-3">
                                                <div class="fw-bold text-dark">{{ clienteEncontrado.persona?.apenom }}</div>
                                                <div class="small text-muted">Socio #{{ clienteEncontrado.id }}</div>
                                            </div>
                                            <div class="bg-success rounded-circle overflow-hidden shadow-sm d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="ahorros.length > 0" class="row">
                    <!-- Selección de Cuenta -->
                    <div class="col-lg-4">
                        <h6 class="fw-bold text-muted mb-3 text-uppercase small">Cuentas Disponibles</h6>
                        <div v-for="a in ahorros" :key="a.id" 
                             :class="['card border-0 shadow-sm mb-3 rounded-4 cursor-pointer transition-all', selectedAhorro?.id === a.id ? 'bg-primary text-white' : 'bg-white']"
                             @click="selectedAhorro = a">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span :class="['badge rounded-pill', selectedAhorro?.id === a.id ? 'bg-white text-primary' : 'bg-primary-subtle text-primary']">CUENTA #{{ a.id }}</span>
                                    <span v-if="a.estado === 'ACTIVO'" class="small"><i class="fas fa-check-circle me-1"></i> {{ a.tipo_ahorro }}</span>
                                </div>
                                <div class="fs-4 fw-bold mb-0">{{ formatoDinero(a.saldo) }}</div>
                                <div :class="['small opacity-75', selectedAhorro?.id === a.id ? 'text-white' : 'text-muted']">Saldo Actual Disponible</div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de Operación -->
                    <div class="col-lg-8" v-if="selectedAhorro">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                            <div class="card-header bg-white p-0 border-bottom">
                                <ul class="nav nav-tabs nav-justified border-0" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link py-3 border-0 transition-all fw-bold fs-5 rounded-0" 
                                                :class="{ 'active bg-success-subtle text-success border-bottom border-success border-3': operacion === 'DEPOSITO' }"
                                                @click="operacion = 'DEPOSITO'">
                                            <i class="fas fa-arrow-down me-2"></i> DEPÓSITO
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link py-3 border-0 transition-all fw-bold fs-5 rounded-0" 
                                                :class="{ 'active bg-danger-subtle text-danger border-bottom border-danger border-3': operacion === 'RETIRO' }"
                                                @click="operacion = 'RETIRO'">
                                            <i class="fas fa-arrow-up me-2"></i> RETIRO
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-5">
                                <form @submit.prevent="handleProcesar">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Monto de la Operación (S/)</label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light border-0"><i class="fas fa-coins text-warning"></i></span>
                                                <input v-model="form.monto" type="number" step="0.01" class="form-control bg-light border-0 fw-bold fs-3 text-center" placeholder="0.00" required>
                                            </div>
                                            <div class="mt-2 text-center" v-if="operacion === 'RETIRO'">
                                                <span class="text-muted small">Saldo post-retiro: </span>
                                                <span class="fw-bold text-danger">{{ formatoDinero(selectedAhorro.saldo - form.monto) }}</span>
                                            </div>
                                            <div class="mt-2 text-center" v-else>
                                                <span class="text-muted small">Saldo post-depósito: </span>
                                                <span class="fw-bold text-success">{{ formatoDinero(Number(selectedAhorro.saldo) + Number(form.monto)) }}</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Medio de Pago</label>
                                            <div class="d-flex flex-column gap-2 mt-1">
                                                <div class="form-check p-3 border rounded-3 transition-all" :class="form.metodo_pago === 'EFECTIVO' ? 'border-primary bg-primary-subtle' : 'border-light bg-light'">
                                                    <input class="form-check-input ms-0 me-2" type="radio" v-model="form.metodo_pago" value="EFECTIVO" id="met-efe" checked>
                                                    <label class="form-check-label fw-bold d-block" for="met-efe">
                                                        <i class="fas fa-money-bill me-2"></i> EFECTIVO (Caja Activa)
                                                    </label>
                                                </div>
                                                <div class="form-check p-3 border rounded-3 transition-all" :class="form.metodo_pago === 'TRANSFERENCIA' ? 'border-primary bg-primary-subtle' : 'border-light bg-light'">
                                                    <input class="form-check-input ms-0 me-2" type="radio" v-model="form.metodo_pago" value="TRANSFERENCIA" id="met-tra">
                                                    <label class="form-check-label fw-bold d-block" for="met-tra">
                                                        <i class="fas fa-university me-2"></i> TRANSFERENCIA
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-dark">Concepto / Referencia</label>
                                            <textarea v-model="form.descripcion" class="form-control bg-light border-0" rows="3" placeholder="Descripción opcional del movimiento..."></textarea>
                                        </div>

                                        <div class="col-12 mt-5">
                                            <button v-if="cajaActiva || form.metodo_pago !== 'EFECTIVO'" type="submit" :class="['btn btn-lg w-100 py-3 fw-bold shadow-lg', operacion === 'DEPOSITO' ? 'btn-success' : 'btn-danger']" :disabled="loading">
                                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                                <i v-else class="fas fa-check-double me-2"></i>
                                                CONFIRMAR {{ operacion }}
                                            </button>
                                            <div v-else class="alert alert-warning border-0 p-3 text-center mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i> Debe tener una <strong>Caja Abierta</strong> para procesar movimientos en efectivo.
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-8" v-else>
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center py-5 text-muted border border-dashed rounded-4 bg-light">
                            <i class="fas fa-id-card fs-1 mb-3 opacity-25"></i>
                            <h5 class="fw-bold">Seleccione una cuenta para operar</h5>
                            <p class="small text-center px-4">Elija una de las cuentas activas del socio en el panel izquierdo.</p>
                        </div>
                    </div>
                </div>

                <div v-else-if="clienteEncontrado" class="text-center py-5">
                    <div class="bg-light d-inline-block rounded-4 p-5 mb-4 shadow-sm">
                        <i class="fas fa-piggy-bank fs-1 text-muted opacity-25"></i>
                    </div>
                    <h3 class="fw-bold">Este socio no tiene cuentas de ahorro</h3>
                    <p class="text-muted mb-4 px-5">Para proceder, el socio debe aperturar una cuenta de ahorro primero.</p>
                    <a href="/ahorros/apertura" class="btn btn-primary btn-lg shadow px-5 fw-bold">Aperturar Cuenta Ahora</a>
                </div>

                <div v-else class="text-center py-5 opacity-25">
                    <i class="fas fa-user-search fs-1 mb-3"></i>
                    <h4>Busque un socio para comenzar</h4>
                </div>
            </div>
        </div>
    </AppLayoutDefault>
</template>

<style scoped>
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.2s ease-in-out; }
.card:hover { transform: translateY(-3px); }
.nav-tabs .nav-link:hover { background-color: #f8f9fa; }
.bg-white-50 { background-color: rgba(255, 255, 255, 0.2); }
.border-dashed { border-style: dashed !important; }
</style>
