<script setup>
import { ref, onMounted } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useAhorro from '@/Composables/Ahorro'
import useCliente from '@/Composables/Cliente'
import useHelper from '@/Helpers'

const { abrirCuenta, loading, errors } = useAhorro()
const { obtenerClientePorBusqueda, cliente: clienteEncontrado, loading: clientLoading } = useCliente()
const { Toast, Swal, formatoDinero } = useHelper()

const searchQuery = ref('')
const form = ref({
    cliente_id: '',
    agencia_id: 1, // Default para demo
    asesor_id: '',
    tipo_ahorro: 'LIBRE',
    monto: 0,
    tasa_interes: 0,
    metodo_pago: 'EFECTIVO',
    notas: ''
})

const handleSearch = async () => {
    if (!searchQuery.value) return
    try {
        await obtenerClientePorBusqueda(searchQuery.value)
        if (clienteEncontrado.value) {
            form.value.cliente_id = clienteEncontrado.value.id
        }
    } catch (e) {
        console.error(e)
    }
}

const handleAbrir = async () => {
    if (!form.value.cliente_id) {
        Toast.fire({ icon: 'warning', title: 'Seleccione un cliente primero' })
        return
    }

    try {
        const result = await abrirCuenta(form.value)
        await Swal.fire('Cuenta Creada', `Se ha habilitado la cuenta de ahorro con saldo S/ ${form.value.monto}`, 'success')
        window.location.href = '/ahorros'
    } catch (e) {
        Swal.fire('Error', 'No se pudo abrir la cuenta de ahorro', 'error')
    }
}
</script>

<template>
    <AppLayoutDefault title="Apertura de Cuenta de Ahorro">
        <div class="page-content py-4">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-success text-white border-0 shadow-sm rounded-4">
                            <div class="card-body p-4 d-flex align-items-center">
                                <div class="bg-white-50 rounded-circle p-3 me-4">
                                    <i class="fas fa-piggy-bank fs-1"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0">Nueva Cuenta de Ahorro</h3>
                                    <p class="mb-0 text-white-75">Fomente el hábito del ahorro en sus socios</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-5">
                        <!-- Búsqueda de Cliente -->
                        <div class="card border-0 shadow-sm mb-4 rounded-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-muted mb-3 text-uppercase small">1. Vincular Socio</h6>
                                <div class="input-group mb-3">
                                    <input v-model="searchQuery" type="text" class="form-control" placeholder="DNI o Apellidos..." @keyup.enter="handleSearch">
                                    <button class="btn btn-primary" @click="handleSearch" :disabled="clientLoading">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>

                                <div v-if="clienteEncontrado" class="bg-light rounded-3 p-3 border">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar bg-primary text-white rounded-circle me-3 p-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-5">{{ clienteEncontrado.persona?.apenom }}</div>
                                            <div class="small text-muted">DNI: {{ clienteEncontrado.persona?.dni }}</div>
                                        </div>
                                    </div>
                                    <hr class="my-2 opacity-10">
                                    <div class="small text-muted">Dirección: {{ clienteEncontrado.persona?.direccion }}</div>
                                </div>
                                <div v-else class="text-center py-4 border border-dashed rounded-3">
                                    <i class="fas fa-user-plus fs-2 text-muted mb-2 opacity-50"></i>
                                    <p class="text-muted small mb-0">Busque un cliente para comenzar</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <!-- Configuración de Cuenta -->
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-muted mb-4 text-uppercase small">2. Condiciones de la Cuenta</h6>
                                <form @submit.prevent="handleAbrir">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Tipo de Ahorro</label>
                                            <select v-model="form.tipo_ahorro" class="form-select bg-light border-0">
                                                <option value="LIBRE">AHORRO LIBRE DISPONIBILIDAD</option>
                                                <option value=" PLAZO_FIJO">AHORRO A PLAZO FIJO (INTERÉS +)</option>
                                                <option value="PROGRAMADO">AHORRO PROGRAMADO</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Tasa de Interés Anual (%)</label>
                                            <input v-model="form.tasa_interes" type="number" step="0.1" class="form-control bg-light border-0">
                                        </div>
                                        <div class="col-12">
                                            <hr class="my-2">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-success">Depósito Inicial (S/)</label>
                                            <input v-model="form.monto" type="number" step="0.01" class="form-control form-control-lg fw-bold border-success" placeholder="0.00">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Medio de Pago</label>
                                            <select v-model="form.metodo_pago" class="form-select bg-light border-0">
                                                <option value="EFECTIVO">EFECTIVO (CAJA)</option>
                                                <option value="TRANSFERENCIA">TRANSFERENCIA BANCARIA</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Notas / Observaciones</label>
                                            <textarea v-model="form.notas" class="form-control bg-light border-0" rows="3" placeholder="Información adicional..."></textarea>
                                        </div>

                                        <div class="col-12 mt-4">
                                            <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow" :disabled="loading">
                                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                                <i v-else class="fas fa-plus-circle me-2"></i>
                                                APERTURAR CUENTA DE AHORRO
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayoutDefault>
</template>

<style scoped>
.bg-white-50 {
    background-color: rgba(255, 255, 255, 0.2);
}
.border-dashed {
    border-style: dashed !important;
}
.avatar {
    width: 50px; height: 50px;
    display: flex; align-items: center; justify-content: center;
}
</style>
