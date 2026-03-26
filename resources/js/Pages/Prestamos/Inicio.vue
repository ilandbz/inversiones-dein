<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useCredito from '@/Composables/Credito';
import Prestamo from '@/Pages/Prestamos/Form.vue'
import Evaluacion from '@/Pages/Evaluacion/Evaluacion.vue'
import FormArchivos from '@/Pages/Evaluacion/FormArchivos.vue'
import useHelper from '@/Helpers'; 

const { obtenerCreditos, creditos, credito, obtenerCredito, eliminarCredito, errors, respuesta } = useCredito();
const { openModal, Toast, Swal, formatoFecha, formatoDinero, getStatusBadge } = useHelper();

const selectedId = ref(null);
const selectedClienteNombre = ref('');
const loading = ref(false);

const dato = ref({
    page: 1,
    buscar: '',
    paginacion: 10,
    estado: '',
});

const form = ref({
    id: '', cliente_id: '', cliente_apenom : '', asesor_id: '', aval_id: '',
    estado: 'ACTIVO', fecha_reg: formatoFecha(null, "YYYY-MM-DD"), fecha_venc: '',
    tipo: '', monto: 0.00, origen_financiamiento_id: '', frecuencia: 'DIARIO',
    plazo: 30, tasainteres: 0.09, total: 0.00, costomora: 0.00,
    created_at: '', updated_at: '', estadoCrud: '', errors: []
});

const limpiar = () => {
    form.value = {
        id: '', cliente_id: '', asesor_id: '', aval_id: '',
        estado: 'ACTIVO', fecha_reg: formatoFecha(null, "YYYY-MM-DD"),
        fecha_venc: '', tipo: '', monto: 0.00, origen_financiamiento_id: '',
        frecuencia: 'DIARIO', plazo: 30, tasainteres: 0.09, total: 0.00,
        costomora: 0.00, created_at: '', updated_at: '', estadoCrud: '', errors: []
    }
}

const obtenerDatos = async (id) => {
    await obtenerCredito(id);
    if (credito.value) {
        form.value.id = credito.value.id ?? '';
        form.value.cliente_id = credito.value.cliente_id ?? '';
        form.value.cliente_apenom = credito.value.cliente?.persona.apenom ?? '';
        form.value.asesor_id = credito.value.asesor_id ?? '';
        form.value.aval_id = credito.value.aval_id ?? '';
        form.value.estado = credito.value.estado ?? 'PENDIENTE';
        form.value.fecha_reg = credito.value.fecha_reg ?? formatoFecha(null, "YYYY-MM-DD");
        form.value.fecha_venc = credito.value.fecha_venc ?? '';
        form.value.tipo = credito.value.tipo ?? '';
        form.value.monto = Number(credito.value.monto ?? 0);
        form.value.origen_financiamiento_id = credito.value.origen_financiamiento_id ?? '';
        form.value.frecuencia = credito.value.frecuencia ?? 'DIARIO';
        form.value.plazo = Number(credito.value.plazo ?? 30);
        form.value.tasainteres = Number(credito.value.tasainteres ?? 0.09);
        form.value.total = Number(credito.value.total ?? 0);
        form.value.costomora = Number(credito.value.costomora ?? 0);
    }
};

const editar = async(id) => {
    limpiar();
    await obtenerDatos(id);
    form.value.estadoCrud = 'editar';
    openModal('#modalprestamo');
}

const evaluacion = async(id) => {
    selectedId.value = id;
    openModal('#modalevaluacion');
}

const archivos = (creditoObj) => {
    selectedId.value = creditoObj.id;
    selectedClienteNombre.value = creditoObj.cliente?.persona?.apenom || '';
    openModal('#archivosModal');
}

const eliminar = (id) => {
    Swal.fire({
        title: '¿Eliminar crédito?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light' }
    }).then((result) => {
        if (result.isConfirmed) ejecutaEliminar(id);
    });
}

const ejecutaEliminar = async (id) => {
    await eliminarCredito(id);
    if (respuesta.value?.ok == 1) {
        Toast.fire({ icon: 'success', title: 'Crédito eliminado' });
        listarCreditos();
    }
}

const listarCreditos = async (page = 1) => {
    dato.value.page = page;
    loading.value = true;
    await obtenerCreditos(dato.value);
    loading.value = false;
};

onMounted(() => {
    listarCreditos();
});
</script>

<template>
    <AppLayoutDefault title="Historial de Créditos">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4 align-items-center">
                    <div class="col">
                        <h3 class="fw-bold text-dark mb-1">Historial de Créditos</h3>
                        <p class="text-muted small mb-0">Seguimiento y gestión de la cartera crediticia</p>
                    </div>
                    <div class="col-auto">
                        <router-link to="/prestamos/registrar" class="btn btn-primary shadow-sm rounded-pill px-4 py-2 fw-bold">
                            <i class="fas fa-plus me-2"></i> Nuevo Crédito
                        </router-link>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <div class="input-group bg-light rounded-pill px-3 py-1">
                                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                                    <input v-model="dato.buscar" type="text" class="form-control bg-transparent border-0" placeholder="Buscar por cliente o código..." @keyup.enter="listarCreditos(1)">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select v-model="dato.estado" class="form-select border-0 bg-light rounded-pill" @change="listarCreditos(1)">
                                    <option value="">Todos los estados</option>
                                    <option value="PENDIENTE">PENDIENTE</option>
                                    <option value="APROBADO">APROBADO</option>
                                    <option value="DESEMBOLSADO">DESEMBOLSADO</option>
                                    <option value="DORMIDO">DORMIDO</option>
                                    <option value="RECHAZADO">RECHAZADO</option>
                                </select>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-primary rounded-pill px-4" @click="listarCreditos(1)">FILTRAR</button>
                                <select v-model="dato.paginacion" class="form-select border-0 bg-light rounded-pill d-inline-block w-auto ms-2" @change="listarCreditos(1)">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small text-uppercase fw-bold">
                                        <th class="ps-4">Folio</th>
                                        <th>Cliente / Asesor</th>
                                        <th>Monto / Plazo</th>
                                        <th>Tasa / Frecuencia</th>
                                        <th>Estado</th>
                                        <th class="pe-4 text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="loading" class="text-center py-5">
                                        <td colspan="6" class="py-5">
                                            <div class="spinner-border text-primary" role="status"></div>
                                        </td>
                                    </tr>
                                    <tr v-else-if="!creditos.data?.length" class="text-center py-5">
                                        <td colspan="6" class="py-5 text-muted italic">No se encontraron créditos.</td>
                                    </tr>
                                    <tr v-for="c in creditos.data" :key="c.id" class="transition-all">
                                        <td class="ps-4">
                                            <div class="fw-bold text-primary">#{{ c.id }}</div>
                                            <div class="small text-muted">{{ c.fecha_reg }}</div>
                                        </td>
                                        <td>
                                            <div class="text-dark fw-bold text-uppercase">{{ c.cliente?.persona?.apenom }}</div>
                                            <div class="small text-muted"><i class="fas fa-user-tie me-1"></i> {{ c.asesor?.user?.name }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ formatoDinero(c.monto) }}</div>
                                            <div class="small text-muted">{{ c.plazo }} {{ c.frecuencia === 'DIARIO' ? 'días' : 'cuotas' }}</div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ Number(c.tasainteres * 100).toFixed(2) }}%</div>
                                            <div class="small text-muted">{{ c.frecuencia }}</div>
                                        </td>
                                        <td>
                                            <span :class="['badge rounded-pill px-3 py-1', getStatusBadge(c.estado)]">
                                                {{ c.estado }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group shadow-sm rounded-3">
                                                <button class="btn btn-white btn-sm" title="Editar" @click="editar(c.id)">
                                                    <i class="fas fa-edit text-warning"></i>
                                                </button>
                                                <button v-if="c.estado === 'PENDIENTE'" class="btn btn-white btn-sm" title="Evaluación" @click="evaluacion(c.id)">
                                                    <i class="fas fa-file-signature text-primary"></i>
                                                </button>
                                                <button class="btn btn-white btn-sm" title="Documentos" @click="archivos(c)">
                                                    <i class="fas fa-folder-open text-info"></i>
                                                </button>
                                                <button class="btn btn-white btn-sm" title="Eliminar" @click="eliminar(c.id)">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modales -->
        <Prestamo :form="form" :listarCreditos="listarCreditos" />
        <Evaluacion :idCredito="selectedId" @onListar="listarCreditos" />
        <FormArchivos :creditoId="selectedId" :clienteNombre="selectedClienteNombre" />
    </AppLayoutDefault>
</template>

<style scoped>
.transition-all { transition: all 0.2s ease-in-out; }
.btn-white { background: #fff; border: 1px solid #f1f1f1; }
.btn-white:hover { background: #f8f9fa; }
</style>