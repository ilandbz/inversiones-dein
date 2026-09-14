<script setup>
import { ref, onMounted, watch } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useHelper from '@/Helpers'
import useOrigenFinanciamiento from '@/Composables/OrigenFinanciamiento'
import axios from 'axios'
import FormPlazo from './Form.vue'

const { Toast, Swal, openModal, hideModal, formatoDinero, truncate } = useHelper()
const { origenes, listaOrigenesFinanciamientos } = useOrigenFinanciamiento()

const plazos = ref([])
const loading = ref(false)
const buscar = ref('')
const origenSeleccionado = ref(null)

const paginacion = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
})

const formPlazo = ref({
    id: null,
    origen_financiamiento_id: null,
    frecuencia: '',
    plazo: '',
    tasainteres: '',
    costomora: '',
    gastos_administrativos: 5.00,
    estadoCrud: 'nuevo'
})

const listarPlazos = async (page = 1) => {
    loading.value = true
    try {
        const { data } = await axios.get('/plazo/listar', {
            params: {
                buscar: buscar.value,
                paginacion: paginacion.value.per_page,
                page: page,
                origen_financiamiento_id: origenSeleccionado.value
            }
        })
        plazos.value = data.data
        paginacion.value.current_page = data.current_page
        paginacion.value.last_page = data.last_page
        paginacion.value.total = data.total
    } catch (error) {
        Toast.fire({ icon: 'error', title: 'Error al cargar los datos' })
    } finally {
        loading.value = false
    }
}

const seleccionarOrigen = (id) => {
    origenSeleccionado.value = id
    listarPlazos(1)
}

const nuevoPlazo = () => {
    if (!origenSeleccionado.value) {
        return Toast.fire({ icon: 'warning', title: 'Seleccione un Origen primero' })
    }
    formPlazo.value = {
        id: null,
        origen_financiamiento_id: origenSeleccionado.value,
        frecuencia: '',
        plazo: '',
        tasainteres: '',
        costomora: '',
        gastos_administrativos: 5.00,
        estadoCrud: 'nuevo'
    }
    openModal('#plazomodal')
}

const editarPlazo = (p) => {
    formPlazo.value = {
        ...p,
        estadoCrud: 'editar'
    }
    openModal('#plazomodal')
}

const eliminarPlazo = async (id) => {
    const { isConfirmed } = await Swal.fire({
        title: '¿Eliminar registro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    })

    if (isConfirmed) {
        try {
            const { data } = await axios.post('/plazo/eliminar', { id })
            if (data.ok == 1) {
                Swal.fire('Eliminado', data.mensaje, 'success')
                listarPlazos()
            }
        } catch (error) {
            Toast.fire({ icon: 'error', title: 'Error al eliminar' })
        }
    }
}

watch(buscar, () => {
    listarPlazos(1)
})

onMounted(async () => {
    await listaOrigenesFinanciamientos()
    if (origenes.value.length > 0) {
        origenSeleccionado.value = origenes.value[0].id
    }
    listarPlazos()
})
</script>

<template>
    <AppLayoutDefault title="Configuración de Tasas y Plazos">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4 align-items-center">
                    <div class="col">
                        <h2 class="fw-bold text-dark mb-1">Gestión de Tasas y Plazos</h2>
                        <p class="text-muted mb-0">Configura los parámetros financieros para el cálculo de préstamos.</p>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary shadow-sm rounded-pill px-4 fw-bold" @click="nuevoPlazo">
                            <i class="fas fa-plus me-2"></i> Nuevo Plazo
                        </button>
                    </div>
                </div>

                <!-- Filters & Tabs -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-md-4">
                                <div class="input-group bg-light rounded-pill border-0 px-3">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                                    <input v-model="buscar" type="text" class="form-control bg-transparent border-0 shadow-none" placeholder="Buscar por frecuencia...">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tabs de Orígenes -->
                        <ul class="nav nav-pills gap-2">
                            <li class="nav-item" v-for="o in origenes" :key="o.id">
                                <button 
                                    class="nav-link rounded-pill px-4 fw-bold shadow-none" 
                                    :class="origenSeleccionado === o.id ? 'active' : 'bg-light text-dark'"
                                    @click="seleccionarOrigen(o.id)">
                                    {{ o.nombre }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Frecuencia</th>
                                        <th>Plazo</th>
                                        <th>Tasa Interés</th>
                                        <th>Mora Diaria</th>
                                        <th>Gastos Admin.</th>
                                        <th class="pe-4 text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="loading">
                                        <td colspan="5" class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-else-if="!plazos.length">
                                        <td colspan="5" class="text-center py-5 text-muted fst-italic">No se encontraron registros.</td>
                                    </tr>
                                    <tr v-for="p in plazos" :key="p.id" class="transition-all">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary-subtle p-2 rounded-circle me-3 text-primary">
                                                    <i class="fas fa-calendar-check"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ p.frecuencia }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark rounded-pill px-3">{{ p.plazo }} {{ p.frecuencia === 'DIARIA' ? 'Días' : 'Cuotas' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">{{ p.tasainteres }}%</span>
                                        </td>
                                        <td>
                                            <span class="text-danger fw-bold">S/ {{ p.costomora }}</span>
                                        </td>
                                        <td>
                                            <span class="text-info fw-bold">S/ {{ p.gastos_administrativos }}</span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group gap-2">
                                                <button class="btn btn-sm btn-outline-primary border-0 rounded-circle" title="Editar" @click="editarPlazo(p)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Eliminar" @click="eliminarPlazo(p.id)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="small text-muted">Mostrando {{ plazos.length }} de {{ paginacion.total }} registros</div>
                    <nav v-if="paginacion.last_page > 1">
                        <ul class="pagination pagination-sm mb-0 gap-1">
                            <li class="page-item" :class="{'disabled': paginacion.current_page === 1}">
                                <button class="page-link rounded-circle border-0" @click="listarPlazos(paginacion.current_page - 1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                            </li>
                            <li v-for="p in paginacion.last_page" :key="p" class="page-item" :class="{'active': p === paginacion.current_page}">
                                <button class="page-link rounded-circle border-0 px-3" @click="listarPlazos(p)">{{ p }}</button>
                            </li>
                            <li class="page-item" :class="{'disabled': paginacion.current_page === paginacion.last_page}">
                                <button class="page-link rounded-circle border-0" @click="listarPlazos(paginacion.current_page + 1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <FormPlazo :form="formPlazo" @on-listar="listarPlazos(1)" />
    </AppLayoutDefault>
</template>

<style scoped>
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1) !important; }
.transition-all { transition: all 0.2s ease; }
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.pagination .page-link { color: #6c757d; font-weight: 500; }
.pagination .page-item.active .page-link { background-color: #0d6efd; color: white; }
</style>
