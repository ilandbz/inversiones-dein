<script setup>
import { onMounted } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useDashboard from '@/Composables/Dashboard'
import useHelper from '@/Helpers'

const { stats, actividad, loading, obtenerDashboard } = useDashboard()
const { formatoFecha, getStatusBadge, formatoDinero } = useHelper()

onMounted(async () => {
    await obtenerDashboard()
})

const quickActions = [
    { label: 'Registrar Cliente', icon: 'fa-user-plus', color: 'primary', route: '/clientes/registro-de-clientes' },
    { label: 'Nueva Solicitud', icon: 'fa-hand-holding-usd', color: 'success', route: '/prestamos/registrar' },
    { label: 'Simular Crédito', icon: 'fa-magic', color: 'info', route: '/prestamos/simulacion' },
    { label: 'Arqueo de Caja', icon: 'fa-cash-register', color: 'warning', route: '/caja/movimientos' }
];

</script>

<template>
    <AppLayoutDefault title="Panel General">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header & Greetings -->
                <div class="row mb-5 align-items-center">
                    <div class="col">
                        <h2 class="fw-bold text-dark mb-1">¡Bienvenido al Panel de Control!</h2>
                        <p class="text-muted mb-0">Monitoreo estratégico y operativo de INVERSIONES DEIN en tiempo real.</p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group gap-2">
                            <button class="btn btn-white shadow-sm border rounded-pill px-4 fw-bold small" @click="obtenerDashboard" :disabled="loading">
                                <i class="fas fa-sync-alt me-2" :class="{'fa-spin': loading}"></i> Actualizar
                            </button>
                            <button class="btn btn-primary shadow-sm rounded-pill px-4 fw-bold small">
                                <i class="fas fa-download me-2"></i> Reporte Diario
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Premium Stats Cards -->
                <div class="row g-4 mb-5">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden card-hover gradient-primary">
                            <div class="card-body p-4 text-white">
                                <div class="mb-3 d-flex justify-content-between align-items-start">
                                    <div class="glass-icon rounded-circle p-3">
                                        <i class="fas fa-wallet fs-4"></i>
                                    </div>
                                    <span class="badge bg-white text-primary rounded-pill fw-bold small">+3.2%</span>
                                </div>
                                <h6 class="text-white-50 fw-bold small text-uppercase mb-1">Cartera Activa</h6>
                                <h2 class="fw-bold mb-0 font-numeric">{{ formatoDinero(stats.total_cartera) }}</h2>
                                <p class="mb-0 mt-2 small opacity-75">Monto total en colocaciones</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden card-hover gradient-success">
                            <div class="card-body p-4 text-white">
                                <div class="mb-3 d-flex justify-content-between align-items-start">
                                    <div class="glass-icon rounded-circle p-3">
                                        <i class="fas fa-piggy-bank fs-4"></i>
                                    </div>
                                </div>
                                <h6 class="text-white-50 fw-bold small text-uppercase mb-1">Total Ahorros</h6>
                                <h2 class="fw-bold mb-0 font-numeric">{{ formatoDinero(stats.total_ahorros) }}</h2>
                                <p class="mb-0 mt-2 small opacity-75">Saldos en cuentas de socios</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden card-hover gradient-warning">
                            <div class="card-body p-4 text-white">
                                <div class="mb-3 d-flex justify-content-between align-items-start">
                                    <div class="glass-icon rounded-circle p-3">
                                        <i class="fas fa-cash-register fs-4"></i>
                                    </div>
                                    <span v-if="stats.caja_abierta" class="badge bg-white text-warning rounded-pill fw-bold small">ABIERTA</span>
                                    <span v-else class="badge bg-light text-muted rounded-pill fw-bold small">CERRADA</span>
                                </div>
                                <h6 class="text-white-50 fw-bold small text-uppercase mb-1">Efectivo en Caja</h6>
                                <h2 class="fw-bold mb-0 font-numeric">{{ formatoDinero(stats.saldo_caja) }}</h2>
                                <p class="mb-0 mt-2 small opacity-75">Saldo actual para operaciones</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden card-hover gradient-dark">
                            <div class="card-body p-4 text-white">
                                <div class="mb-3 d-flex justify-content-between align-items-start">
                                    <div class="glass-icon rounded-circle p-3 text-danger">
                                        <i class="fas fa-exclamation-triangle fs-4"></i>
                                    </div>
                                </div>
                                <h6 class="text-white-50 fw-bold small text-uppercase mb-1">Créditos en Mora</h6>
                                <h2 class="fw-bold mb-0 font-numeric">{{ stats.creditos_mora }} Casos</h2>
                                <p class="mb-0 mt-2 small opacity-75">Concentración: 4.2%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Quick Actions -->
                    <div class="col-lg-12 mb-2">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-dark text-uppercase small mb-4">Acciones Rápidas</h6>
                                <div class="row g-3">
                                    <div v-for="action in quickActions" :key="action.label" class="col-6 col-md-3">
                                        <RouterLink :to="action.route" class="btn-light-action w-100 py-3 rounded-4 d-flex flex-column align-items-center transition-all bg-light text-decoration-none shadow-sm">
                                            <div :class="['bg-white shadow-sm p-3 rounded-circle mb-2 text-' + action.color]">
                                                <i :class="['fas', action.icon, 'fs-4']"></i>
                                            </div>
                                            <span class="small fw-bold text-dark">{{ action.label }}</span>
                                        </RouterLink>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-dark mb-0">Actividad Reciente</h5>
                                <button class="btn btn-link text-primary text-decoration-none fw-bold small">Ver Todo</button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th class="ps-4">Operación</th>
                                                <th>Entidad / Socio</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                                <th class="pe-4 text-end">Hora</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!actividad.length">
                                                <td colspan="5" class="text-center py-5 text-muted italic">No se registra actividad reciente hoy.</td>
                                            </tr>
                                            <tr v-for="a in actividad" :key="a.id" class="transition-all">
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark text-uppercase small">{{ a.tipo }}</div>
                                                    <div class="small text-muted">{{ a.referencia }}</div>
                                                </td>
                                                <td>
                                                    <div class="small fw-bold">{{ a.entidad }}</div>
                                                </td>
                                                <td class="fw-bold text-primary">{{ a.monto ? formatoDinero(a.monto) : '--' }}</td>
                                                <td>
                                                    <span :class="['badge rounded-pill', getStatusBadge(a.estado)]">{{ a.estado }}</span>
                                                </td>
                                                <td class="pe-4 text-end small text-muted">{{ a.hora }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Side Insights -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                            <div class="card-header bg-white border-0 py-4 px-4">
                                <h5 class="fw-bold text-dark mb-0">Estado de Liquidación</h5>
                            </div>
                            <div class="card-body p-4 pt-0">
                                <div class="text-center py-4 bg-light rounded-4 mb-4 border">
                                    <div class="fs-1 fw-bold text-primary mb-0">82%</div>
                                    <div class="small fw-bold text-muted text-uppercase">Cumplimiento de Cobro</div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2 small fw-bold">
                                        <span>Meta Diaria Recaudación</span>
                                        <span class="text-primary">82%</span>
                                    </div>
                                    <div class="progress rounded-pill shadow-none bg-light" style="height: 8px;">
                                        <div class="progress-bar bg-primary" :style="{ width: '82%' }"></div>
                                    </div>
                                </div>
                                <hr class="opacity-10">
                                <div class="p-3 bg-primary-subtle rounded-4 d-flex align-items-center">
                                    <div class="bg-white p-2 rounded-circle me-3 shadow-sm text-primary">
                                        <i class="fas fa-lightbulb"></i>
                                    </div>
                                    <p class="small text-dark mb-0">Los depósitos de ahorro han crecido un <b>15%</b> respecto a la semana pasada.</p>
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
.card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.card-hover:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important; }

.gradient-primary { background: linear-gradient(135deg, #0d6efd, #004dc0); }
.gradient-success { background: linear-gradient(135deg, #198754, #12633d); }
.gradient-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
.gradient-dark { background: linear-gradient(135deg, #1f2937, #111827); }

.glass-icon { background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); display: inline-flex; width: 50px; height: 50px; align-items: center; justify-content: center; }
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1) !important; }
.btn-light-action { border: 1px solid transparent; }
.btn-light-action:hover { background-color: #ffffff !important; border-color: #dee2e6 !important; transform: translateY(-3px); }
.btn-white { background: #fff; }
</style>