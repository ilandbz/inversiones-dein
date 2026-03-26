<script setup>
import { ref, onMounted } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useCaja from '@/Composables/Caja'
import useHelper from '@/Helpers'
import useAgencia from '@/Composables/Agencia'

const { cajaActiva, obtenerCajaActiva, abrirCaja, loading, errors } = useCaja()
const { agencias, listarAgencias } = useAgencia()
const { Toast, Swal } = useHelper()

const form = ref({
    agencia_id: '',
    saldo_inicial: 0
})

onMounted(async () => {
    await listarAgencias()
    // Intentar obtener caja activa
    await obtenerCajaActiva()
})

const handleAbrir = async () => {
    try {
        const result = await abrirCaja(form.value)
        if (result.ok) {
            Toast.fire({ icon: 'success', title: result.msg })
            // Redirigir o actualizar estado
        }
    } catch (e) {
        Swal.fire('Error', e.response?.data?.msg || 'No se pudo abrir la caja', 'error')
    }
}
</script>

<template>
    <AppLayoutDefault title="Apertura de Caja">
        <div class="page-content py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-header bg-primary text-white p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white-50 rounded-circle p-3 me-3">
                                        <i class="fas fa-door-open fs-3"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0 fw-bold">Apertura de Caja</h4>
                                        <p class="mb-0 opacity-75 small">Inicie sus operaciones del día</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4 p-lg-5">
                                <template v-if="!cajaActiva">
                                    <form @submit.prevent="handleAbrir">
                                        <div class="mb-4">
                                            <label class="form-label fw-bold text-muted small text-uppercase">Agencia</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-building text-primary"></i></span>
                                                <select v-model="form.agencia_id" class="form-select border-start-0 bg-light" required>
                                                    <option value="">Seleccione una agencia...</option>
                                                    <option v-for="a in agencias" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                                                </select>
                                            </div>
                                            <div v-if="errors.agencia_id" class="text-danger small mt-1">{{ errors.agencia_id[0] }}</div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-bold text-muted small text-uppercase">Saldo Inicial (S/)</label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-coins text-success"></i></span>
                                                <input v-model="form.saldo_inicial" type="number" step="0.01" class="form-control border-start-0 bg-light fw-bold" placeholder="0.00">
                                            </div>
                                            <div v-if="errors.saldo_inicial" class="text-danger small mt-1">{{ errors.saldo_inicial[0] }}</div>
                                        </div>

                                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                                            <i class="fas fa-info-circle fs-4 me-3"></i>
                                            <div class="small">El saldo inicial debe coincidir con el efectivo físico entregado por tesorería o el saldo final del día anterior.</div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow mt-2" :disabled="loading">
                                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                            <i v-else class="fas fa-check-circle me-2"></i>
                                            ABRIR CAJA AHORA
                                        </button>
                                    </form>
                                </template>

                                <template v-else>
                                    <div class="text-center py-4">
                                        <div class="bg-success-subtle text-success rounded-circle p-4 d-inline-block mb-4 shadow-sm">
                                            <i class="fas fa-lock-open fs-1"></i>
                                        </div>
                                        <h4 class="fw-bold">Ya tienes una caja abierta</h4>
                                        <p class="text-muted mb-4 small">Tu caja para hoy en <strong>{{ cajaActiva.agencia?.nombre }}</strong> está activa desde las {{ cajaActiva.hora_apertura }}.</p>
                                        
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <a href="/caja-pagos" class="btn btn-outline-primary w-100 py-2 fw-bold">Ir a Pagos</a>
                                            </div>
                                            <div class="col-6">
                                                <a href="/caja-cierre" class="btn btn-outline-danger w-100 py-2 fw-bold">Ir a Cierre</a>
                                            </div>
                                        </div>
                                    </div>
                                </template>
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
.card {
    transition: transform 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
}
</style>
