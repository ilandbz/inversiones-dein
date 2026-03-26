<script setup>
import { ref } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useHelper from '@/Helpers';

const { formatoDinero } = useHelper();

const stats = ref([
    { label: 'Recaudación Hoy', value: 'S/ 12,450.00', icon: 'fa-money-bill-wave', color: 'success' },
    { label: 'Proyección Mes', value: 'S/ 250,000.00', icon: 'fa-chart-line', color: 'primary' },
    { label: 'Eficiencia Cobro', value: '94.5%', icon: 'fa-user-check', color: 'info' },
    { label: 'Mora Generada', value: 'S/ 1,200.00', icon: 'fa-clock', color: 'danger' }
]);

const distribucion = [
    { cat: 'Créditos (Capital)', monto: 8500, pct: 68, color: 'bg-primary' },
    { cat: 'Intereses / Moras', monto: 2200, pct: 18, color: 'bg-success' },
    { cat: 'Ahorros', monto: 1200, pct: 10, color: 'bg-info' },
    { cat: 'Otros Ingresos', monto: 550, pct: 4, color: 'bg-warning' }
];
</script>

<template>
  <AppLayoutDefault title="Estadísticas de Ingresos">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Estadísticas de Ingresos</h3>
                <p class="text-muted small mb-0">Análisis visual de recaudación y tendencias de flujo de caja</p>
            </div>
            <div class="col-auto">
                <div class="btn-group rounded-pill overflow-hidden shadow-sm border">
                    <button class="btn btn-white btn-sm px-3 fw-bold active border-0">DIARIO</button>
                    <button class="btn btn-white btn-sm px-3 fw-bold border-0">SEMANAL</button>
                    <button class="btn btn-white btn-sm px-3 fw-bold border-0">MENSUAL</button>
                </div>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="row g-4 mb-4">
            <div v-for="s in stats" :key="s.label" class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div :class="[`bg-${s.color}-subtle text-${s.color} rounded-pill p-3 me-3`]">
                                <i :class="['fas', s.icon, 'fs-4']"></i>
                            </div>
                            <h6 class="text-muted small fw-bold text-uppercase mb-0">{{ s.label }}</h6>
                        </div>
                        <h3 class="fw-bold text-dark mb-0 font-numeric">{{ s.value }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Distribución de Ingresos -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h5 class="fw-bold text-dark mb-0">Distribución por Categoría</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div v-for="d in distribucion" :key="d.cat" class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-muted small">{{ d.cat }}</span>
                                <span class="fw-bold text-dark small font-numeric">{{ formatoDinero(d.monto) }} ({{ d.pct }}%)</span>
                            </div>
                            <div class="progress rounded-pill shadow-none" style="height: 12px; background: #f0f0f0;">
                                <div :class="['progress-bar', d.color, 'rounded-pill']" :style="{ width: d.pct + '%' }"></div>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <div class="mt-4 p-3 bg-light rounded-4 border border-primary-subtle d-flex align-items-center">
                            <i class="fas fa-info-circle text-primary me-3 fs-4"></i>
                            <p class="text-muted small mb-0">La recaudación de intereses ha incrementado un <b>12%</b> respecto al periodo anterior, optimizando el margen operativo.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Mini Charts (Visual Placeholders) -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 bg-dark text-white h-100 overflow-hidden">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Tendencia Mensual</h5>
                        
                        <div class="d-flex align-items-baseline gap-1" style="height: 150px;">
                            <div v-for="i in 12" :key="i" class="bg-primary flex-grow-1 rounded-top" :style="{ height: Math.random() * 80 + 20 + '%', opacity: i / 12 }"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 text-white-50 small fw-bold text-uppercase">
                            <span>Ene</span><span>Mar</span><span>May</span><span>Jul</span><span>Set</span><span>Dic</span>
                        </div>

                        <div class="mt-4 pt-3 border-top border-white-10">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small opacity-50">PROYECCIÓN CIERRE</div>
                                    <h4 class="fw-bold mb-0 text-success">S/ 285K</h4>
                                </div>
                                <div class="text-end">
                                    <div class="small opacity-50">VARIACIÓN</div>
                                    <h4 class="fw-bold mb-0">+14.2%</h4>
                                </div>
                            </div>
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
.font-numeric { font-family: 'Inter', system-ui, -apple-system, sans-serif; letter-spacing: -0.5px; }
.bg-primary-subtle { background-color: rgba(var(--bs-primary-rgb), 0.1); }
.bg-success-subtle { background-color: rgba(var(--bs-success-rgb), 0.1); }
.bg-info-subtle { background-color: rgba(var(--bs-info-rgb), 0.1); }
.bg-danger-subtle { background-color: rgba(var(--bs-danger-rgb), 0.1); }
.border-white-10 { border-color: rgba(255,255,255,0.1) !important; }
.btn-white { background: #fff; }
.btn-white.active { background: #f8f9fa; color: #0d6efd; }
</style>
