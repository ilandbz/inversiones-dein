<script setup>
import { ref } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useHelper from '@/Helpers';

const { formatoDinero } = useHelper();

const historico = ref([
    { periodo: '2025 - Q4', colocacion: 450000, mora: '1.8%', clientes: 45, bono: 3500 },
    { periodo: '2025 - Q3', colocacion: 380000, mora: '2.5%', clientes: 38, bono: 2800 },
    { periodo: '2025 - Q2', colocacion: 420000, mora: '2.1%', clientes: 42, bono: 3200 },
    { periodo: '2025 - Q1', colocacion: 350000, mora: '3.2%', clientes: 35, bono: 2400 }
]);
</script>

<template>
  <AppLayoutDefault title="Histórico del Asesor">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Histórico de Desempeño</h3>
                <p class="text-muted small mb-0">Evolución de su carrera comercial y cumplimiento de objetivos anuales</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h5 class="fw-bold text-dark mb-0">Trayectoria Cuatrimestral</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small text-uppercase fw-bold">
                                        <th class="ps-4 py-3">Periodo</th>
                                        <th>Colocación Total</th>
                                        <th>Cartera de Clientes</th>
                                        <th>Mora Promedio</th>
                                        <th class="pe-4 text-end">Incentivos Percibidos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="h in historico" :key="h.periodo" class="transition-all">
                                        <td class="ps-4 fw-bold text-primary">{{ h.periodo }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ formatoDinero(h.colocacion) }}</div>
                                            <div class="progress rounded-pill mt-1" style="height: 4px; width: 100px;">
                                                <div class="progress-bar bg-primary" :style="{ width: (h.colocacion / 500000 * 100) + '%' }"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ h.clientes }} Socios</span>
                                        </td>
                                        <td>
                                            <span :class="['fw-bold', Number(h.mora.replace('%','')) > 3 ? 'text-danger' : 'text-success']">{{ h.mora }}</span>
                                        </td>
                                        <td class="pe-4 text-end fw-bold text-success">{{ formatoDinero(h.bono) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen Acumulado -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4">
                    <h6 class="text-white-50 small fw-bold text-uppercase mb-3">Total Histórico Colocado</h6>
                    <h2 class="fw-bold mb-1">S/ 1,600,000</h2>
                    <p class="text-success small mb-0"><i class="fas fa-arrow-up me-1"></i> +15% vs año anterior</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    <h6 class="text-muted small fw-bold text-uppercase mb-3">Calidad de Cartera Histórica</h6>
                    <h2 class="fw-bold mb-1 text-primary">2.4%</h2>
                    <p class="text-muted small mb-0">Promedio ponderado de mora PAR 30</p>
                </div>
            </div>
            <div class="col-md-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4">
                    <h6 class="text-white-50 small fw-bold text-uppercase mb-3">Socio con Mayor Fidelidad</h6>
                    <h5 class="fw-bold mb-1">Empresa Agrícola del Norte</h5>
                    <p class="text-white-50 small mb-0">5 créditos cancelados puntualmente</p>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayoutDefault>
</template>

<style scoped>
.transition-all { transition: all 0.2s ease-in-out; }
</style>
