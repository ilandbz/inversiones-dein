<script setup>
import { ref, onMounted } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useHelper from '@/Helpers';
import useCredito from '@/Composables/Credito';

const { formatoDinero, formatoFecha, openModal } = useHelper();
const { obtenerCreditos, creditos, obtenerCronograma } = useCredito();

const searchQuery = ref('');
const selectedCredito = ref(null);
const cronograma = ref([]);
const loading = ref(false);

const buscar = async () => {
    loading.value = true;
    await obtenerCreditos({ buscar: searchQuery.value, estado: 'DESEMBOLSADO' });
    loading.value = false;
};

const verCronograma = async (c) => {
    selectedCredito.value = c;
    const res = await obtenerCronograma(c.id);
    cronograma.value = res?.data || res || [];
};

onMounted(() => {
    buscar();
});
</script>

<template>
  <AppLayoutDefault title="Cronograma de Pagos">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Visor de Cronogramas</h3>
                <p class="text-muted small mb-0">Consulta detallada de cuotas, saldos y fechas de vencimiento por crédito</p>
            </div>
            <div class="col-auto">
                <div class="input-group bg-white border rounded-pill px-3 py-1 shadow-sm">
                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                    <input v-model="searchQuery" type="text" class="form-control bg-transparent border-0 shadow-none border-start ps-3" placeholder="DNI o Nombre del Cliente..." @keyup.enter="buscar">
                    <button class="btn btn-primary rounded-pill px-4 fw-bold small ms-2" @click="buscar" :disabled="loading">BUSCAR</button>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Listado de Créditos Encontrados -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h6 class="fw-bold text-dark mb-0 text-uppercase small">Créditos Vigentes</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div v-if="!creditos.data?.length" class="text-center py-5 text-muted px-4">
                                <i class="fas fa-search-dollar fs-2 mb-3 opacity-25"></i>
                                <p class="small mb-0">Busque un cliente para ver sus créditos desembolsados.</p>
                            </div>
                            <button v-for="c in creditos.data" :key="c.id" 
                                @click="verCronograma(c)"
                                :class="['list-group-item list-group-item-action border-0 border-bottom px-4 py-3', selectedCredito?.id === c.id ? 'bg-primary-subtle border-primary' : '']">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold text-dark">#{{ c.id }} - {{ c.monto }}</span>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2">VIGENTE</span>
                                </div>
                                <div class="small fw-bold text-dark text-truncate">{{ c.cliente?.persona?.apenom }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ c.tipo }} / {{ c.frecuencia }}</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visor de Cronograma -->
            <div class="col-lg-8">
                <div v-if="selectedCredito" class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div class="card-header bg-dark text-white border-0 py-4 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-1">CONTRATO #{{ selectedCredito.id }}</h5>
                            <p class="mb-0 small opacity-75">{{ selectedCredito.cliente?.persona?.apenom }}</p>
                        </div>
                        <div class="text-end">
                            <span class="d-block small opacity-75">Saldo Pendiente</span>
                            <h4 class="fw-bold mb-0 text-warning">{{ formatoDinero(selectedCredito.total) }}</h4>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small text-uppercase fw-bold">
                                        <th class="ps-4">Cuota</th>
                                        <th>Vencimiento</th>
                                        <th>Importe</th>
                                        <th>Mora</th>
                                        <th>Estado</th>
                                        <th class="pe-4 text-end">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="it in cronograma" :key="it.nrocuota" class="transition-all">
                                        <td class="ps-4 fw-bold">{{ it.nrocuota }}</td>
                                        <td class="small">{{ it.fechavence }}</td>
                                        <td>{{ formatoDinero(it.monto) }}</td>
                                        <td :class="Number(it.mora) > 0 ? 'text-danger fw-bold' : 'text-muted'">{{ formatoDinero(it.mora) }}</td>
                                        <td>
                                            <span :class="['badge rounded-pill px-2 py-1', it.estado === 'PAGADO' ? 'bg-success text-white' : 'bg-warning-subtle text-warning border border-warning']">
                                                {{ it.estado }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end text-muted small">{{ formatoDinero(it.saldo) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 p-4 text-end">
                        <button class="btn btn-outline-dark rounded-pill px-4 fw-bold small shadow-sm">
                            <i class="fas fa-file-pdf me-2 text-danger"></i> EXPORTAR CRONOGRAMA
                        </button>
                    </div>
                </div>
                <!-- Empty State -->
                <div v-else class="h-100 d-flex flex-column align-items-center justify-content-center text-center p-5 bg-white rounded-4 border border-dashed">
                    <div class="bg-light rounded-circle p-4 mb-4">
                        <i class="fas fa-file-invoice fs-1 text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-dark fw-bold">Seleccione un Crédito</h5>
                    <p class="text-muted small">Seleccione un crédito de la lista izquierda para visualizar su cronograma de pagos detallado y estados de cuenta.</p>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayoutDefault>
</template>

<style scoped>
.transition-all { transition: all 0.2s ease-in-out; }
.bg-primary-subtle { background-color: rgba(var(--bs-primary-rgb), 0.1); }
.list-group-item-action:hover { background-color: #f8f9fa; }
</style>
