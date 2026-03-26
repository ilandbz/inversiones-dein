<script setup>
import { ref, onMounted } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useAhorro from '@/Composables/Ahorro'
import useHelper from '@/Helpers'

const props = defineProps({
    ahorro_id: { type: [String, Number], default: null }
})

const { ahorro, movimientos, mostrarAhorro, listarMovimientos, loading } = useAhorro()
const { formatoDinero, formatoFecha } = useHelper()

onMounted(async () => {
    if (props.ahorro_id) {
        await mostrarAhorro(props.ahorro_id)
        await listarMovimientos(props.ahorro_id)
    }
})

const colorTipo = (tipo) => {
    switch(tipo) {
        case 'DEPOSITO': return 'text-success'
        case 'INTERES': return 'text-primary'
        case 'RETIRO': return 'text-danger'
        default: return 'text-dark'
    }
}
</script>

<template>
    <AppLayoutDefault title="Estado de Cuenta - Ahorros">
        <div class="page-content py-4">
            <div class="container">
                <div v-if="ahorro" class="row">
                    <!-- Cabecera de Cuenta -->
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-0">
                                <div class="row g-0">
                                    <div class="col-md-4 bg-primary text-white p-4 d-flex flex-column justify-content-center align-items-center">
                                        <div class="small opacity-75 text-uppercase fw-bold mb-1">Saldo Disponible</div>
                                        <h1 class="fw-bold mb-0">{{ formatoDinero(ahorro.saldo) }}</h1>
                                        <div class="mt-3 badge bg-white-50 p-2 px-3 rounded-pill fw-normal">Cuenta #{{ ahorro.id }}</div>
                                    </div>
                                    <div class="col-md-8 p-4 d-flex flex-column justify-content-center">
                                        <div class="row g-4">
                                            <div class="col-sm-6">
                                                <div class="small text-muted fw-bold text-uppercase mb-1">Titular</div>
                                                <div class="fw-bold fs-5 text-dark">{{ ahorro.cliente?.persona?.apenom }}</div>
                                                <div class="small text-muted">DNI: {{ ahorro.cliente?.persona?.dni }}</div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="small text-muted fw-bold text-uppercase mb-1">Apertura</div>
                                                <div class="fw-bold text-dark">{{ ahorro.fecha_apertura }}</div>
                                            </div>
                                            <div class="col-sm-3 text-end">
                                                <span class="badge bg-success-subtle text-success border border-success px-3">{{ ahorro.estado }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Movimientos -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Historial de Movimientos</h5>
                                <button class="btn btn-sm btn-outline-primary" @click="listarMovimientos(ahorro.id)">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th class="ps-4">Fecha / Hora</th>
                                                <th>Tipo</th>
                                                <th>Concepto / Referencia</th>
                                                <th class="text-end">Monto</th>
                                                <th class="text-end pe-4">Saldo Post</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!movimientos.length">
                                                <td colspan="5" class="text-center py-5 text-muted">No hay movimientos registrados.</td>
                                            </tr>
                                            <tr v-for="m in movimientos" :key="m.id">
                                                <td class="ps-4">
                                                    <div class="fw-bold">{{ m.fecha }}</div>
                                                    <div class="small text-muted">{{ m.hora }}</div>
                                                </td>
                                                <td>
                                                    <span :class="['badge rounded-pill', m.tipo === 'DEPOSITO' ? 'bg-success-subtle text-success' : (m.tipo === 'RETIRO' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary')]">
                                                        {{ m.tipo }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-dark small fw-bold">{{ m.descripcion || '-' }}</div>
                                                    <div class="text-muted extra-small" v-if="m.metodo_pago">{{ m.metodo_pago }} - {{ m.referencia || 'Caja' }}</div>
                                                </td>
                                                <td :class="['text-end fw-bold', colorTipo(m.tipo)]">
                                                    {{ m.tipo === 'RETIRO' ? '-' : '+' }} {{ formatoDinero(m.monto) }}
                                                </td>
                                                <td class="text-end pe-4 fw-bold text-dark">
                                                    {{ formatoDinero(m.saldo_posterior) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else-if="!loading" class="text-center py-5">
                    <i class="fas fa-search fs-1 mb-3 opacity-25"></i>
                    <h4>Cuenta no encontrada</h4>
                </div>
            </div>
        </div>
    </AppLayoutDefault>
</template>

<style scoped>
.bg-white-50 { background-color: rgba(255, 255, 255, 0.2); }
.extra-small { font-size: 0.75rem; }
</style>
