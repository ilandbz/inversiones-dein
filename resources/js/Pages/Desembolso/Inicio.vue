<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useDesembolso from '@/Composables/Desembolso';
import useHelper from '@/Helpers';

const { obtenerDesembolsos, desembolsos, generarPdf, pdfUrl, loading } = useDesembolso();
const { formatoFecha, formatoDinero } = useHelper();

const dato = ref({
    page: 1,
    buscar: '',
    paginacion: 15
});

const listar = async (page = 1) => {
    dato.value.page = page;
    await obtenerDesembolsos(dato.value);
};

const imprimirDocumento = async (creditoId, tipo) => {
    await generarPdf({ credito_id: creditoId, tipo: tipo });
    if (pdfUrl.value) {
        window.open(pdfUrl.value, '_blank');
    }
};

const offset = 2;
const pagesNumber = computed(() => {
    const c = desembolsos.value;
    if (!c?.to) return [];
    let from = c.current_page - offset;
    if (from < 1) from = 1;
    let to = from + offset * 2;
    if (to >= c.last_page) to = c.last_page;
    const pages = [];
    for (let p = from; p <= to; p++) pages.push(p);
    return pages;
});

onMounted(() => {
    listar();
});
</script>

<template>
    <AppLayoutDefault title="Historial de Desembolsos">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4 align-items-center">
                    <div class="col">
                        <h3 class="fw-bold text-dark mb-1">Registro de Desembolsos</h3>
                        <p class="text-muted small mb-0">Historial completo de entregas de efectivo realizadas</p>
                    </div>
                    <div class="col-auto">
                        <div class="input-group bg-white rounded-pill px-3 py-1 shadow-sm border">
                            <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                            <input v-model="dato.buscar" type="text" class="form-control bg-transparent border-0" placeholder="Buscar por socio..." @keyup.enter="listar(1)">
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Folio</th>
                                        <th>Fecha / Hora</th>
                                        <th>Socio / Cliente</th>
                                        <th>Monto Entregado</th>
                                        <th>Cajero / Usuario</th>
                                        <th class="pe-4 text-end">Documentación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="loading" class="text-center py-5">
                                        <td colspan="6" class="py-5"><span class="spinner-border text-primary"></span></td>
                                    </tr>
                                    <tr v-else-if="!desembolsos.data?.length" class="text-center py-5">
                                        <td colspan="6" class="py-5 text-muted">No se encontraron registros de desembolso.</td>
                                    </tr>
                                    <tr v-for="d in desembolsos.data" :key="d.id">
                                        <td class="ps-4 fw-bold text-primary">DS-{{ d.id }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ d.fecha }}</div>
                                            <div class="small text-muted">{{ d.hora }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-uppercase">{{ d.credito?.cliente?.persona?.apenom }}</div>
                                            <div class="small text-muted">Crédito #{{ d.credito_id }}</div>
                                        </td>
                                        <td>
                                            <div class="fs-5 fw-bold text-success">{{ formatoDinero(d.totalentregado) }}</div>
                                            <div class="small text-muted" v-if="d.descontado > 0">Descuento: S/ {{ d.descontado }}</div>
                                        </td>
                                        <td class="small">
                                            <i class="fas fa-headset me-1 opacity-50"></i> {{ d.user?.name }}
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                                <button class="btn btn-white btn-sm px-3 border-end" @click="imprimirDocumento(d.credito_id, 'calendario')" title="Cronograma">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </button>
                                                <button class="btn btn-white btn-sm px-3 border-end" @click="imprimirDocumento(d.credito_id, 'plan')" title="Plan de Pagos">
                                                    <i class="fas fa-file-invoice text-info"></i>
                                                </button>
                                                <button class="btn btn-white btn-sm px-3" @click="imprimirDocumento(d.credito_id, 'kardex')" title="Kardex">
                                                    <i class="fas fa-book text-warning"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer bg-white p-4 border-top-0 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Página {{ desembolsos.current_page }} de {{ desembolsos.last_page }} ({{ desembolsos.total }} registros)
                        </div>
                        <nav v-if="desembolsos.last_page > 1">
                            <ul class="pagination pagination-sm mb-0 gap-1 child-rounded-pill">
                                <li class="page-item" :class="{ disabled: desembolsos.current_page === 1 }">
                                    <button class="page-link border-0 shadow-none px-3" @click="listar(desembolsos.current_page - 1)"><i class="fas fa-chevron-left"></i></button>
                                </li>
                                <li v-for="p in pagesNumber" :key="p" class="page-item" :class="{ active: p === desembolsos.current_page }">
                                    <button class="page-link border-0 shadow-none px-3" @click="listar(p)">{{ p }}</button>
                                </li>
                                <li class="page-item" :class="{ disabled: desembolsos.current_page === desembolsos.last_page }">
                                    <button class="page-link border-0 shadow-none px-3" @click="listar(desembolsos.current_page + 1)"><i class="fas fa-chevron-right"></i></button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayoutDefault>
</template>

<style scoped>
.btn-white { background: #fff; }
.btn-white:hover { background: #f8f9fa; }
.child-rounded-pill .page-link { border-radius: 50px !important; }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
</style>
