<script setup>
import { ref, onMounted } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useCaja from '@/Composables/Caja'
import useHelper from '@/Helpers'

const { cajaActiva, obtenerCajaActiva, listarMovimientosCaja, movimientos, registrarMovimientoManual, loading } = useCaja()
const { Toast, Swal, formatoDinero } = useHelper()

const showManualModal = ref(false)
const manualForm = ref({
    tipo: 'INGRESO',
    monto: 0,
    concepto: '',
    descripcion: ''
})

onMounted(async () => {
    const caja = await obtenerCajaActiva(1)
    if (caja) {
        await listarMovimientosCaja(caja.id)
    }
})

const handleManualMov = async () => {
    if (!cajaActiva.value) return
    try {
        await registrarMovimientoManual({
            caja_id: cajaActiva.value.id,
            ...manualForm.value
        })
        Toast.fire({ icon: 'success', title: 'Movimiento registrado' })
        showManualModal.value = false
        await listarMovimientosCaja(cajaActiva.value.id)
    } catch (e) {
        Swal.fire('Error', 'No se pudo registrar el movimiento', 'error')
    }
}
</script>

<template>
    <AppLayoutDefault title="Movimientos de Caja">
        <div class="page-content py-4">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Movimientos de Caja</h3>
                        <p class="text-muted small mb-0">Listado de ingresos y egresos de la caja actual</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary fw-bold px-4" @click="showManualModal = true">
                            <i class="fas fa-plus-circle me-1"></i> Movimiento Manual
                        </button>
                    </div>
                </div>

                <div v-if="cajaActiva" class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase fw-bold">
                                    <tr>
                                        <th class="ps-4">Hora</th>
                                        <th>Tipo</th>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>Referencia</th>
                                        <th class="pe-4 text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!movimientos.length">
                                        <td colspan="6" class="text-center py-5 text-muted italic">
                                            No se han registrado movimientos todavía.
                                        </td>
                                    </tr>
                                    <tr v-for="m in movimientos" :key="m.id">
                                        <td class="ps-4 small">{{ m.hora }}</td>
                                        <td>
                                            <span :class="['badge rounded-pill px-3', m.tipo === 'INGRESO' ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger']">
                                                {{ m.tipo }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark mb-0">{{ m.concepto }}</div>
                                            <div class="small text-muted text-truncate" style="max-width: 250px;">{{ m.descripcion || '-' }}</div>
                                        </td>
                                        <td :class="['fw-bold', m.tipo === 'INGRESO' ? 'text-success' : 'text-danger']">
                                            {{ m.tipo === 'INGRESO' ? '+' : '-' }} {{ formatoDinero(m.monto) }}
                                        </td>
                                        <td>
                                            <span v-if="m.entidad_tipo" class="badge bg-light text-dark border small">
                                                {{ m.entidad_tipo.split('\\').pop() }} #{{ m.entidad_id }}
                                            </span>
                                            <span v-else class="text-muted small">-</span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button class="btn btn-sm btn-link text-primary"><i class="fas fa-print"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-else class="alert alert-warning border-0 shadow-sm p-4 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fs-3 me-4 text-warning"></i>
                    <div>
                        <h5 class="fw-bold mb-1">Sin Caja Activa</h5>
                        <p class="mb-0">Debe abrir una caja para ver o registrar movimientos en tiempo real.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Movimiento Manual (Placeholder para implementación futura de Modal Component) -->
        <div v-if="showManualModal" class="modal-overlay d-flex align-items-center justify-content-center">
            <div class="modal-card bg-white rounded-4 shadow-lg p-4" style="width: 450px;">
                <h5 class="fw-bold mb-4">Registro de Movimiento Manual</h5>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tipo</label>
                    <div class="btn-group w-100 mb-3">
                        <input type="radio" class="btn-check" v-model="manualForm.tipo" value="INGRESO" id="min" autocomplete="off">
                        <label class="btn btn-outline-success fw-bold" for="min">INGRESO</label>
                        <input type="radio" class="btn-check" v-model="manualForm.tipo" value="EGRESO" id="meg" autocomplete="off">
                        <label class="btn btn-outline-danger fw-bold" for="meg">EGRESO</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Monto (S/)</label>
                    <input v-model="manualForm.monto" type="number" step="0.01" class="form-control form-control-lg fw-bold" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Concepto / Motivo</label>
                    <input v-model="manualForm.concepto" type="text" class="form-control" placeholder="Ej: Pago de Luz">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Nota Adicional</label>
                    <textarea v-model="manualForm.descripcion" class="form-control" rows="2"></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light w-100 fw-bold" @click="showManualModal = false">Cancelar</button>
                    <button class="btn btn-primary w-100 fw-bold" @click="handleManualMov" :disabled="loading">Guardar</button>
                </div>
            </div>
        </div>
    </AppLayoutDefault>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1050;
    backdrop-filter: blur(4px);
}
.table thead th {
    border-top: none;
    padding: 1.25rem 0.75rem;
}
</style>
