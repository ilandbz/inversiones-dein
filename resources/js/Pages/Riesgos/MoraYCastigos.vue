<script setup>
import { ref, onMounted } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useHelper from '@/Helpers'
import axios from 'axios'

const { Toast, Swal, formatoFecha } = useHelper()
const creditosVencidos = ref([])
const loading = ref(false)

const obtenerMora = async () => {
    loading.value = true
    try {
        // En un escenario real, esto vendría de un Servicio de Riesgos
        const response = await axios.get('/riesgos/mora-y-castigos')
        creditosVencidos.value = response.data
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    obtenerMora()
})
</script>

<template>
    <AppLayoutDefault title="Mora y Castigos">
        <div class="page-content py-4">
            <div class="container-fluid">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 bg-danger text-white">
                            <div class="card-body p-4">
                                <h6 class="text-uppercase small opacity-75">Cartera Vencida (S/)</h6>
                                <h2 class="fw-bold mb-0">S/ 45,230.00</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom p-4">
                        <h4 class="fw-bold mb-0">Listado de Mora y Castigos</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr class="small text-muted text-uppercase">
                                        <th>Crédito</th>
                                        <th>Cliente</th>
                                        <th>Días Atraso</th>
                                        <th>Monto Pendiente</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!creditosVencidos.length">
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            No se registran créditos en mora actualmente.
                                        </td>
                                    </tr>
                                    <tr v-for="c in creditosVencidos" :key="c.id">
                                        <td>#{{ c.id }}</td>
                                        <td>{{ c.cliente?.persona?.apenom }}</td>
                                        <td><span class="badge bg-danger rounded-pill">{{ c.dias_mora }} días</span></td>
                                        <td class="fw-bold text-danger">S/ {{ c.total_deuda }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill">Ver Plan de Pagos</button>
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
