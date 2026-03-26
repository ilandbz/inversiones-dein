<script setup>
import { ref } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useHelper from '@/Helpers';

const { formatoDinero } = useHelper();

const stats = ref([
    { label: 'Cartera Total', value: 'S/ 1,250,400.00', icon: 'fa-briefcase', color: 'primary', trend: '+12%', trendUp: true },
    { label: 'Colocaciones Mes', value: 'S/ 85,000.00', icon: 'fa-hand-holding-usd', color: 'success', trend: '+5%', trendUp: true },
    { label: 'Mora PAR 30', value: '4.2%', icon: 'fa-exclamation-triangle', color: 'danger', trend: '-0.5%', trendUp: false },
    { label: 'Clientes Activos', value: '1,245', icon: 'fa-users', color: 'info', trend: '+24', trendUp: true }
]);

const reportes = [
    { title: 'Calidad de Cartera (PAR)', desc: 'Análisis detallado de mora por tramos de 30, 60 y 90 días.', icon: 'bi-graph-up-arrow', color: 'danger' },
    { title: 'Reporte de Colocaciones', desc: 'Resumen mensual de préstamos otorgados por agencia y asesor.', icon: 'bi-cash-coin', color: 'success' },
    { title: 'Conciliación de Caja', desc: 'Arqueos vs Movimientos bancarios de todas las agencias.', icon: 'bi-safe', color: 'primary' },
    { title: 'Rentabilidad Mensual', desc: 'Margen financiero, gastos operativos y utilidad proyectada.', icon: 'bi-pie-chart', color: 'info' }
];

const rankingAsesores = [
    { name: 'Carlos Mendoza', goal: '95%', sales: 'S/ 45,000', par: '2.1%' },
    { name: 'Ana Lucía Torres', goal: '102%', sales: 'S/ 52,000', par: '1.8%' },
    { name: 'Roberto Vizcarra', goal: '88%', sales: 'S/ 38,000', par: '4.5%' }
];
</script>

<template>
  <AppLayoutDefault title="Reportes Gerenciales">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Panel de Control Gerencial</h3>
                <p class="text-muted small mb-0">Visión estratégica de la salud financiera y operativa de la institución</p>
            </div>
            <div class="col-auto">
                <div class="dropdown">
                    <button class="btn btn-white border shadow-sm rounded-pill px-4 fw-bold small dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="far fa-calendar-alt me-2 text-primary"></i> Marzo 2026
                    </button>
                    <ul class="dropdown-menu border-0 shadow-lg rounded-3">
                        <li><a class="dropdown-item" href="#">Febrero 2026</a></li>
                        <li><a class="dropdown-item" href="#">Enero 2026</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-4 mb-4">
            <div v-for="s in stats" :key="s.label" class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 transition-all hover-translate">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div :class="[`bg-${s.color}-subtle text-${s.color} rounded-3 p-3`]">
                                <i :class="['fas', s.icon, 'fs-4']"></i>
                            </div>
                            <span :class="['badge rounded-pill px-2 py-1 small fw-bold', s.trendUp ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger']">
                                <i :class="['fas', s.trendUp ? 'fa-arrow-up' : 'fa-arrow-down', 'me-1']"></i> {{ s.trend }}
                            </span>
                        </div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">{{ s.label }}</h6>
                        <h3 class="fw-bold text-dark mb-0 font-numeric">{{ s.value }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Central Reports Grid -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h5 class="fw-bold text-dark mb-0">Informes Estratégicos</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row g-3">
                            <div v-for="r in reportes" :key="r.title" class="col-md-6">
                                <div class="p-4 rounded-4 border bg-light-subtle h-100 d-flex align-items-start transition-all cursor-pointer hover-bg-white shadow-hover">
                                    <div :class="[`bg-${r.color} text-white rounded-circle p-3 me-3 icon-fixed shadow-sm`]">
                                        <i :class="['bi', r.icon, 'fs-4']"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">{{ r.title }}</h6>
                                        <p class="text-muted small mb-0">{{ r.desc }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mini Chart Placeholder Area -->
                        <div class="mt-4 p-5 bg-light rounded-4 border border-dashed text-center">
                            <i class="bi bi-bar-chart-line fs-1 text-muted opacity-25 d-block mb-3"></i>
                            <h6 class="text-muted">Proyección de Crecimiento del Próximo Trimestre</h6>
                            <div class="progress mt-3 rounded-pill" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Table: Top Performers -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h5 class="fw-bold text-dark mb-0">Ranking de Asesores</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small text-uppercase fw-bold">
                                        <th class="ps-4 py-3">Asesor</th>
                                        <th>Logro</th>
                                        <th class="pe-4 text-end">Mora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="a in rankingAsesores" :key="a.name">
                                        <td class="ps-4">
                                            <div class="fw-bold small text-dark">{{ a.name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ a.sales }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="small fw-bold me-2">{{ a.goal }}</span>
                                                <div class="progress flex-grow-1 rounded-pill" style="height: 5px;">
                                                    <div class="progress-bar bg-primary" :style="{ width: a.goal }"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <span :class="['fw-bold', Number(a.par.replace('%','')) > 3 ? 'text-danger' : 'text-success']">{{ a.par }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 text-center border-top">
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">Ver Todos los Asesores</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayoutDefault>
</template>

<style scoped>
.transition-all { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
.hover-translate:hover { transform: translateY(-5px); }
.bg-light-subtle { background-color: #f8f9fa; }
.hover-bg-white:hover { background-color: #ffffff !important; }
.shadow-hover:hover { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important; }
.icon-fixed { width: 50px; height: 50px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.font-numeric { font-family: 'Inter', system-ui, -apple-system, sans-serif; letter-spacing: -0.5px; }
.btn-white { background: #fff; }
</style>
