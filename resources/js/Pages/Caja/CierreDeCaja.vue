<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useCaja from '@/Composables/Caja'
import useHelper from '@/Helpers'

const { cajaActiva, obtenerCajaActiva, cerrarCaja, obtenerResumenCaja, resumen, loading, errors } = useCaja()
const { Toast, Swal, formatoDinero } = useHelper()

const form = ref({
    caja_id: '',
    efectivo_declarado: 0,
    observacion: ''
})

const searched = ref(false)

onMounted(async () => {
    // Intentar buscar caja abierta (esto suele venir de un prop en Inertia o cargarse async)
    // Para el demo asumo que cargamos la del usuario actual
    const caja = await obtenerCajaActiva(1) // Harcodeado ID de agencia para demo, debería ser dinámico
    if (caja) {
        form.value.caja_id = caja.id
        await obtenerResumenCaja(caja.id)
    }
    searched.value = true
})

const diferencia = computed(() => {
    if (!resumen.value?.saldo_calculado) return 0
    return Number(form.value.efectivo_declarado) - Number(resumen.value.saldo_calculado)
})

const handleCerrar = async () => {
    const result_swal = await Swal.fire({
        title: '¿Confirmar cierre de caja?',
        text: 'Una vez cerrada, no podrá realizar más movimientos hoy.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar caja',
        cancelButtonText: 'Cancelar'
    })

    if (result_swal.isConfirmed) {
        try {
            const result = await cerrarCaja(form.value)
            if (result.ok) {
                await Swal.fire('Cierre Exitoso', result.msg, 'success')
                window.location.href = '/dashboard'
            }
        } catch (e) {
            Swal.fire('Error', e.response?.data?.msg || 'Error al cerrar caja', 'error')
        }
    }
}
</script>

<template>
    <AppLayoutDefault title="Cierre de Caja">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-dark text-white border-0 shadow-sm rounded-3 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="bg-primary rounded-circle p-3 shadow">
                                            <i class="fas fa-calculator fs-2"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h3 class="mb-0 fw-bold">Arqueo y Cierre de Caja</h3>
                                        <p class="mb-0 text-white-50">Resumen operativo y validación de efectivo</p>
                                    </div>
                                    <div class="col-auto" v-if="cajaActiva">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle p-2">CAJA: #{{ cajaActiva.id }}</span>
                                        <span class="ms-2 badge bg-light text-dark p-2">{{ cajaActiva.fecha }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="cajaActiva" class="row">
                    <!-- Resumen del día -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm mb-4 h-100">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between">
                                <h5 class="mb-0 fw-bold text-primary">Resumen del Día</h5>
                                <button class="btn btn-sm btn-outline-secondary" @click="obtenerResumenCaja(cajaActiva.id)">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6 col-xl-3">
                                        <div class="p-3 bg-light rounded text-center h-100 border">
                                            <div class="text-muted small text-uppercase fw-bold mb-1">Saldo Inicial</div>
                                            <h4 class="mb-0 fw-bold text-dark">{{ formatoDinero(resumen.caja?.saldo_inicial) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="p-3 bg-success-subtle rounded text-center h-100 border border-success-subtle">
                                            <div class="text-success small text-uppercase fw-bold mb-1">Ingresos</div>
                                            <h4 class="mb-0 fw-bold text-success">+{{ formatoDinero(resumen.total_ingresos) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="p-3 bg-danger-subtle rounded text-center h-100 border border-danger-subtle">
                                            <div class="text-danger small text-uppercase fw-bold mb-1">Egresos</div>
                                            <h4 class="mb-0 fw-bold text-danger">-{{ formatoDinero(resumen.total_egresos) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="p-3 bg-primary-subtle rounded text-center h-100 border border-primary-subtle">
                                            <div class="text-primary small text-uppercase fw-bold mb-1">Saldo en Caja</div>
                                            <h4 class="mb-0 fw-bold text-primary">{{ formatoDinero(resumen.saldo_calculado) }}</h4>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="fw-bold mb-3 border-bottom pb-2">Desglose por Concepto</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-muted small fw-bold mb-2">Ingresos</p>
                                        <ul class="list-group list-group-flush small mb-3">
                                            <li v-for="(val, key) in resumen.ingresos_por_concepto" :key="key" class="list-group-item d-flex justify-content-between p-2">
                                                <span>{{ key }}</span>
                                                <span class="text-success fw-bold">{{ formatoDinero(val) }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted small fw-bold mb-2">Egresos</p>
                                        <ul class="list-group list-group-flush small mb-3">
                                            <li v-for="(val, key) in resumen.egresos_por_concepto" :key="key" class="list-group-item d-flex justify-content-between p-2">
                                                <span>{{ key }}</span>
                                                <span class="text-danger fw-bold">{{ formatoDinero(val) }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de Arqueo -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm mb-4 h-100">
                            <div class="card-header bg-primary text-white py-3 border-0">
                                <h5 class="mb-0 fw-bold">Validación de Efectivo</h5>
                            </div>
                            <div class="card-body p-4">
                                <form @submit.prevent="handleCerrar">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark">Efectivo Físico Contado (S/)</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-white"><i class="fas fa-money-bill-wave text-success"></i></span>
                                            <input v-model="form.efectivo_declarado" type="number" step="0.01" class="form-control fw-bold border-start-0" placeholder="0.00" required>
                                        </div>
                                    </div>

                                    <div class="card bg-light border-0 mb-4 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-muted">Diferencia de Arqueo:</span>
                                                <span :class="['fs-4 fw-bold', diferencia > 0 ? 'text-success' : (diferencia < 0 ? 'text-danger' : 'text-dark')]">
                                                    {{ diferencia > 0 ? '+' : '' }}{{ formatoDinero(diferencia) }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-center small">
                                                <span v-if="diferencia > 0" class="badge bg-success-subtle text-success p-2 w-100">SOBRANTE DETECTADO</span>
                                                <span v-else-if="diferencia < 0" class="badge bg-danger-subtle text-danger p-2 w-100">FALTANTE DETECTADO</span>
                                                <span v-else class="badge bg-dark-subtle text-dark p-2 w-100">CAJA CUADRADA</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark small">Observaciones / Justificación</label>
                                        <textarea v-model="form.observacion" class="form-control" rows="3" placeholder="Indique el motivo si hay diferencias..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow mt-2" :disabled="loading">
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        <i v-else class="fas fa-lock me-2"></i>
                                        FINALIZAR Y CERRAR CAJA
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else-if="searched" class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-block p-5 mb-4 shadow-sm opacity-50">
                        <i class="fas fa-lock fs-1 text-muted"></i>
                    </div>
                    <h3 class="fw-bold text-muted">No hay una caja activa hoy</h3>
                    <p class="text-muted mb-4">Para realizar operaciones, primero debe abrir su caja del día.</p>
                    <a href="/caja-apertura" class="btn btn-primary btn-lg shadow px-5 fw-bold">Ir a Apertura</a>
                </div>
            </div>
        </div>
    </AppLayoutDefault>
</template>

<style scoped>
.page-content {
    background-color: #f8f9fa;
    min-height: 80vh;
}
.card {
    transition: all 0.2s ease;
}
.list-group-item {
    border-color: #f1f1f1;
}
</style>
