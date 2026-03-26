<script setup>
import { ref, onMounted } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useHelper from '@/Helpers'
import axios from 'axios'

const { Toast, Swal } = useHelper()
const clientesBloqueados = ref([])
const loading = ref(false)

const obtenerBloqueos = async () => {
    loading.value = true
    try {
        // Simulación o endpoint real si existe
        const response = await axios.get('/riesgos/bloqueados')
        clientesBloqueados.value = response.data
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    obtenerBloqueos()
})
</script>

<template>
    <AppLayoutDefault title="Bloqueo de Pagos">
        <div class="page-content py-4">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-0">Gestión de Bloqueo de Pagos</h4>
                                <p class="text-muted small mb-0">Restricción de operaciones para clientes con alto riesgo o irregularidades.</p>
                            </div>
                            <button class="btn btn-primary shadow-sm rounded-pill px-4">
                                <i class="bi bi-plus-lg me-2"></i> Nuevo Bloqueo
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr class="small text-muted text-uppercase">
                                        <th>Cliente</th>
                                        <th>Motivo</th>
                                        <th>Fecha Inicio</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!clientesBloqueados.length">
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-shield-check fs-1 mb-3 d-block"></i>
                                            No se registran bloqueos activos actualmente.
                                        </td>
                                    </tr>
                                    <tr v-for="b in clientesBloqueados" :key="b.id">
                                        <td>
                                            <div class="fw-bold text-dark">{{ b.cliente?.persona?.apenom }}</div>
                                            <div class="small text-muted">{{ b.cliente?.dni }}</div>
                                        </td>
                                        <td>{{ b.motivo }}</td>
                                        <td>{{ b.fecha_inicio }}</td>
                                        <td><span class="badge bg-danger rounded-pill">BLOQUEADO</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-success rounded-pill ms-2">Desbloquear</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayoutDefault>
</template>
