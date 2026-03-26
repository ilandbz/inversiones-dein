<script setup>
import { ref, onMounted } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useHelper from '@/Helpers';

const { formatoDinero, formatoFecha } = useHelper();

const hoy = formatoFecha(null, 'DD/MM/YYYY');
const cartilla = ref([
    { id: 1, cliente: 'JUAN PEREZ GOMEZ', cuota: 12, monto: 45.00, vencimiento: hoy, estado: 'PENDIENTE', celular: '987654321', direccion: 'Jr. Libertad 123' },
    { id: 2, cliente: 'MARIA LOPEZ DIAZ', cuota: 5, monto: 120.00, vencimiento: hoy, estado: 'PENDIENTE', celular: '912345678', direccion: 'Av. Brasil 456' },
    { id: 3, cliente: 'CARLOS RUIZ SOTO', cuota: 8, monto: 60.00, vencimiento: '23/03/2026', estado: 'ATRASADO', celular: '955443322', direccion: 'Psje. Los Pinos 789' }
]);

const loading = ref(false);

const llamar = (tlf) => {
    window.location.href = `tel:${tlf}`;
}

const ubicar = (dir) => {
    window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(dir)}`, '_blank');
}
</script>

<template>
  <AppLayoutDefault title="Cartilla de Cobranza">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Cartilla de Cobranza Diaria</h3>
                <p class="text-muted small mb-0">Gestión de ruta y compromisos de pago para el día: <b>{{ hoy }}</b></p>
            </div>
            <div class="col-auto">
                <button class="btn btn-dark rounded-pill px-4 fw-bold small shadow-sm">
                    <i class="fas fa-print me-2"></i> IMPRIMIR RUTA
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Resumen de Ruta -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <i class="fas fa-route fs-1 mb-3 opacity-50"></i>
                        <h6 class="text-uppercase small fw-bold opacity-75">Recaudación Proyectada</h6>
                        <h2 class="fw-bold mb-0">{{ formatoDinero(cartilla.reduce((acc, c) => acc + c.monto, 0)) }}</h2>
                        <div class="mt-4 pt-3 border-top border-white-50">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="fw-bold mb-0">{{ cartilla.length }}</h5>
                                    <span class="small opacity-75">Clientes</span>
                                </div>
                                <div class="col-6">
                                    <h5 class="fw-bold mb-0">{{ cartilla.filter(x => x.estado === 'ATRASADO').length }}</h5>
                                    <span class="small opacity-75">Atrasados</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listado de Clientes en Ruta -->
            <div class="col-md-8">
                <div v-for="c in cartilla" :key="c.id" class="card border-0 shadow-sm rounded-4 mb-3 transition-all hover-translate">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div :class="['rounded-circle p-3 d-flex align-items-center justify-content-center', c.estado === 'ATRASADO' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary']" style="width: 50px; height: 50px;">
                                    <i :class="['fas', c.estado === 'ATRASADO' ? 'fa-exclamation-circle' : 'fa-clock']"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="fw-bold text-dark mb-1">{{ c.cliente }}</h6>
                                    <span :class="['badge rounded-pill px-2 py-1 small', c.estado === 'ATRASADO' ? 'bg-danger text-white' : 'bg-warning text-dark']">{{ c.estado }}</span>
                                </div>
                                <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt me-1"></i> {{ c.direccion }}</p>
                            </div>
                            <div class="col-auto text-end border-start px-4">
                                <div class="text-muted small fw-bold">CUOTA {{ c.cuota }}</div>
                                <div class="fs-5 fw-bold text-dark">{{ formatoDinero(c.monto) }}</div>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group gap-2">
                                    <button class="btn btn-light rounded-circle shadow-sm" @click="llamar(c.celular)" title="Llamar">
                                        <i class="fas fa-phone-alt text-success"></i>
                                    </button>
                                    <button class="btn btn-light rounded-circle shadow-sm" @click="ubicar(c.direccion)" title="Mapa">
                                        <i class="fas fa-location-arrow text-primary"></i>
                                    </button>
                                    <button class="btn btn-primary rounded-pill px-3 fw-bold small ms-2">COBRAR</button>
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
.transition-all { transition: all 0.2s ease-in-out; }
.hover-translate:hover { transform: translateY(-3px); }
.bg-primary-subtle { background-color: rgba(var(--bs-primary-rgb), 0.1); }
.bg-danger-subtle { background-color: rgba(var(--bs-danger-rgb), 0.1); }
</style>
