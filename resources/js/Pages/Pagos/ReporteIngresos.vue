<script setup>
import { ref } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useHelper from '@/Helpers';

const { formatoDinero, formatoFecha } = useHelper();

const filtros = ref({
    inicio: formatoFecha(null, 'YYYY-MM-DD'),
    fin: formatoFecha(null, 'YYYY-MM-DD'),
    agencia_id: '',
    asesor_id: ''
});

const ingresos = ref([
    { id: 2001, fecha: '24/03/2026', cliente: 'MARCO ANTONIO SOLIS', concepto: 'Pago Cuota 04 - Cred. #125', monto: 150.00, metodo: 'EFECTIVO', categoria: 'CREDITO' },
    { id: 2002, fecha: '24/03/2026', cliente: 'ELENA PEREZ RIVERA', concepto: 'Depósito Ahorro Libre', monto: 500.00, metodo: 'TRANSFERENCIA', categoria: 'AHORRO' },
    { id: 2003, fecha: '24/03/2026', cliente: 'VARIOS', concepto: 'Venta 05 Formas de Solicitud', monto: 25.00, metodo: 'EFECTIVO', categoria: 'OTROS' }
]);

const loading = ref(false);

const consultar = () => {
    loading.value = true;
    setTimeout(() => { loading.value = false; }, 600);
}
</script>

<template>
  <AppLayoutDefault title="Reporte de Ingresos">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Reporte Consolidado de Ingresos</h3>
                <p class="text-muted small mb-0">Detalle de recaudación por créditos, ahorros e ingresos diversos</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-success rounded-pill px-4 fw-bold small shadow-sm text-uppercase">
                    <i class="fas fa-file-excel me-2"></i> Exportar Excel
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">FECHA INICIO</label>
                        <input v-model="filtros.inicio" type="date" class="form-control rounded-pill border-0 bg-light px-3 shadow-none">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">FECHA FIN</label>
                        <input v-model="filtros.fin" type="date" class="form-control rounded-pill border-0 bg-light px-3 shadow-none">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">CATEGORÍA</label>
                        <select class="form-select rounded-pill border-0 bg-light px-3 shadow-none">
                            <option value="">TODOS LOS INGRESOS</option>
                            <option value="CREDITO">CREDITOS</option>
                            <option value="AHORRO">AHORROS</option>
                            <option value="OTROS">OTROS INGRESOS</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button @click="consultar" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm" :disabled="loading">
                             <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                             <i v-else class="fas fa-filter me-2 opacity-50"></i>{{ loading ? 'CONSULTANDO...' : 'CONSULTAR' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
                <h5 class="fw-bold text-dark mb-0">Listado de Transacciones</h5>
                <div class="text-muted small fw-bold">MOSTRANDO {{ ingresos.length }} REGISTROS</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small text-uppercase fw-bold">
                                <th class="ps-4">ID</th>
                                <th>Fecha</th>
                                <th>Cliente / Socio</th>
                                <th>Concepto</th>
                                <th>Categoría</th>
                                <th class="text-center">Método</th>
                                <th class="pe-4 text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="i in ingresos" :key="i.id" class="transition-all">
                                <td class="ps-4 fw-bold text-muted" style="font-size: 0.8rem;">#{{ i.id }}</td>
                                <td class="small">{{ i.fecha }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ i.cliente }}</div>
                                </td>
                                <td class="small text-muted">{{ i.concepto }}</td>
                                <td>
                                    <span :class="['badge rounded-pill px-2 py-1', i.categoria === 'CREDITO' ? 'bg-primary-subtle text-primary' : (i.categoria === 'AHORRO' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary')]">
                                        {{ i.categoria }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="small fw-bold">{{ i.metodo }}</span>
                                </td>
                                <td class="pe-4 text-end fw-bold text-dark">{{ formatoDinero(i.monto) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light-subtle">
                            <tr class="fw-bold text-dark">
                                <td colspan="6" class="text-end py-3">TOTAL RECAUDADO:</td>
                                <td class="pe-4 text-end py-3 fs-5">{{ formatoDinero(ingresos.reduce((acc, x) => acc + x.monto, 0)) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayoutDefault>
</template>

<style scoped>
.transition-all { transition: all 0.2s ease-in-out; }
.bg-light-subtle { background-color: rgba(var(--bs-primary-rgb), 0.05); }
</style>
