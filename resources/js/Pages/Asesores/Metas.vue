<script setup>
import { ref } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useHelper from '@/Helpers';

const { formatoDinero } = useHelper();

const metas = ref([
    { mes: 'Marzo 2026', meta_monto: 150000, actual_monto: 85000, meta_clientes: 20, actual_clientes: 12, estado: 'EN PROCESO' },
    { mes: 'Febrero 2026', meta_monto: 120000, actual_monto: 115000, meta_clientes: 18, actual_clientes: 17, estado: 'CUMPLIDO' },
    { mes: 'Enero 2026', meta_monto: 100000, actual_monto: 105000, meta_clientes: 15, actual_clientes: 16, estado: 'CUMPLIDO' }
]);

const miDesempeno = {
    cartera_activa: 'S/ 850,000.00',
    mora_actual: '2.5%',
    ranking_agencia: '#2',
    incentivos_proyectados: 'S/ 1,200.00'
};
</script>

<template>
  <AppLayoutDefault title="Metas y Desempeño">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Mis Metas y Desempeño</h3>
                <p class="text-muted small mb-0">Seguimiento en tiempo real de objetivos comerciales e incentivos</p>
            </div>
            <div class="col-auto">
                <div class="bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-bold">
                    <i class="fas fa-bullseye me-1"></i> Periodo Vigente
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Stats Grid -->
            <div class="col-lg-12">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 bg-white">
                            <div class="card-body p-4">
                                <h6 class="text-muted small fw-bold text-uppercase mb-2">Cartera Administrada</h6>
                                <h4 class="fw-bold text-dark mb-0 font-numeric">{{ miDesempeno.cartera_activa }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 bg-white">
                            <div class="card-body p-4">
                                <h6 class="text-muted small fw-bold text-uppercase mb-2">Mora PAR 0</h6>
                                <h4 class="fw-bold text-success mb-0 font-numeric">{{ miDesempeno.mora_actual }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 bg-white">
                            <div class="card-body p-4">
                                <h6 class="text-muted small fw-bold text-uppercase mb-2">Ranking Agencia</h6>
                                <h4 class="fw-bold text-primary mb-0 font-numeric">{{ miDesempeno.ranking_agencia }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 bg-white">
                            <div class="card-body p-4">
                                <h6 class="text-muted small fw-bold text-uppercase mb-2">Bonificación Est.</h6>
                                <h4 class="fw-bold text-warning mb-0 font-numeric">{{ miDesempeno.incentivos_proyectados }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meta Principal (Current Month) -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h5 class="fw-bold text-dark mb-3">Progreso de Colocación - Marzo 2026</h5>
                                <div class="d-flex align-items-end mb-2">
                                    <h2 class="fw-bold text-primary mb-0 me-2">{{ formatoDinero(metas[0].actual_monto) }}</h2>
                                    <span class="text-muted small pb-1"> de {{ formatoDinero(metas[0].meta_monto) }}</span>
                                </div>
                                <div class="progress rounded-pill mb-4" style="height: 12px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" :style="{ width: (metas[0].actual_monto / metas[0].meta_monto * 100) + '%' }"></div>
                                </div>
                                <div class="row text-center mt-3">
                                    <div class="col-6 border-end">
                                        <h4 class="fw-bold mb-0">{{ metas[0].actual_clientes }}</h4>
                                        <span class="text-muted small">Nuevos Socios</span>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="fw-bold mb-0">{{ (metas[0].actual_monto / metas[0].meta_monto * 100).toFixed(1) }}%</h4>
                                        <span class="text-muted small">Avance Global</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 d-none d-md-block text-center border-start">
                                <div class="bg-light rounded-circle p-4 d-inline-block mb-3 border border-primary border-3">
                                    <i class="fas fa-trophy fs-1 text-warning"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">¡Sigue así, Carlos!</h6>
                                <p class="text-muted small">Estás a S/ 65,000 de alcanzar tu meta del trimestre.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historico Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h5 class="fw-bold text-dark mb-0">Historial de Cumplimiento</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li v-for="m in metas.slice(1)" :key="m.mes" class="list-group-item px-4 py-3 border-0 border-bottom border-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark">{{ m.mes }}</span>
                                    <span :class="['badge rounded-pill px-2 py-1 small', m.estado === 'CUMPLIDO' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning']">
                                        {{ m.estado }}
                                    </span>
                                </div>
                                <div class="progress rounded-pill mt-2" style="height: 5px;">
                                    <div class="progress-bar bg-success" :style="{ width: (m.actual_monto / m.meta_monto * 100) + '%' }"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="text-muted small" style="font-size: 0.7rem;">{{ formatoDinero(m.actual_monto) }}</span>
                                    <span class="text-muted small" style="font-size: 0.7rem;">{{ (m.actual_monto / m.meta_monto * 100).toFixed(0) }}%</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayoutDefault>
</template>

<style scoped>
.font-numeric { font-family: 'Inter', system-ui, -apple-system, sans-serif; letter-spacing: -0.5px; }
.card-body p { margin-bottom: 0; }
.progress-bar { transition: width 1s ease-in-out; }
</style>
