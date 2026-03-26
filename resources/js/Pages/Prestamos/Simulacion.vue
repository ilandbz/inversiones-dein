<script setup>
import { ref, computed, watch } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useHelper from '@/Helpers';

const { formatoDinero, formatoFecha } = useHelper();

const form = ref({
    monto: 1000,
    plazo: 12,
    frecuencia: 'MENSUAL',
    tasa: 0.05,
    fecha_inicio: formatoFecha(null, 'YYYY-MM-DD')
});

const frecuencias = [
    { id: 'DIARIO', nombre: 'Diario', factor: 1 },
    { id: 'SEMANAL', nombre: 'Semanal', factor: 7 },
    { id: 'QUINCENAL', nombre: 'Quincenal', factor: 15 },
    { id: 'MENSUAL', nombre: 'Mensual', factor: 30 }
];

const cronograma = computed(() => {
    const cuotas = [];
    let saldo = Number(form.value.monto);
    const n = Number(form.value.plazo);
    const r = Number(form.value.tasa);
    
    // Cuota fija (Fórmula Francesa)
    // C = P * [r(1+r)^n] / [(1+r)^n - 1]
    const cuotaFija = (saldo * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
    
    let fecha = new Date(form.value.fecha_inicio);
    const f = frecuencias.find(x => x.id === form.value.frecuencia);

    for (let i = 1; i <= n; i++) {
        const interes = saldo * r;
        const capital = cuotaFija - interes;
        saldo -= capital;
        
        // Calcular fecha
        if (form.value.frecuencia === 'MENSUAL') {
            fecha.setMonth(fecha.getMonth() + 1);
        } else {
            fecha.setDate(fecha.getDate() + f.factor);
        }

        cuotas.push({
            numero: i,
            fecha: formatoFecha(fecha, 'DD/MM/YYYY'),
            capital: capital.toFixed(2),
            interes: interes.toFixed(2),
            cuota: cuotaFija.toFixed(2),
            saldo: Math.max(0, saldo).toFixed(2)
        });
    }
    return cuotas;
});

const totalInteres = computed(() => cronograma.value.reduce((acc, c) => acc + Number(c.interes), 0));
const totalPagar = computed(() => Number(form.value.monto) + totalInteres.value);

</script>

<template>
  <AppLayoutDefault title="Simulador de Créditos">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Simulador de Créditos</h3>
                <p class="text-muted small mb-0">Proyecte sus cuotas y el costo total del financiamiento al instante</p>
            </div>
            <div class="col-auto">
                <div class="bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small fw-bold">
                    <i class="fas fa-magic me-1"></i> Herramienta de Proyección
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Inputs -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Monto del Préstamo (S/)</label>
                            <div class="input-group input-group-lg bg-light rounded-pill px-3 py-1 border-0 shadow-none">
                                <span class="input-group-text bg-transparent border-0 text-success fw-bold">S/</span>
                                <input v-model="form.monto" type="number" class="form-control bg-transparent border-0 shadow-none fw-bold" placeholder="0.00">
                            </div>
                            <input v-model="form.monto" type="range" class="form-range mt-2" min="100" max="50000" step="100">
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">N° Cuotas</label>
                                <input v-model="form.plazo" type="number" class="form-control rounded-pill bg-light border-0 px-3 shadow-none fw-bold">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">Frecuencia</label>
                                <select v-model="form.frecuencia" class="form-select rounded-pill bg-light border-0 px-3 shadow-none fw-bold">
                                    <option v-for="f in frecuencias" :key="f.id" :value="f.id">{{ f.nombre }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Tasa de Interés Mensual (%)</label>
                            <div class="input-group bg-light rounded-pill px-3 py-1 border-0 shadow-none">
                                <input v-model="form.tasa" type="number" step="0.001" class="form-control bg-transparent border-0 shadow-none fw-bold">
                                <span class="input-group-text bg-transparent border-0 text-primary fw-bold">%</span>
                            </div>
                            <div class="form-text small">Tasa efectiva aplicada por periodo.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Fecha de Desembolso</label>
                            <input v-model="form.fecha_inicio" type="date" class="form-control rounded-pill bg-light border-0 px-3 shadow-none fw-bold">
                        </div>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div class="card border-0 bg-dark text-white rounded-4 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-3">
                            <span class="opacity-75">Interés Total:</span>
                            <span class="fw-bold text-warning">{{ formatoDinero(totalInteres) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-3">
                            <span class="opacity-75">Monto del Crédito:</span>
                            <span class="fw-bold">{{ formatoDinero(form.monto) }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-1">
                            <span class="fs-5">TOTAL A PAGAR:</span>
                            <span class="fs-5 fw-bold text-success">{{ formatoDinero(totalPagar) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Preview -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold text-dark mb-0">Cronograma Proyectado</h5>
                        <button class="btn btn-outline-dark btn-sm rounded-pill px-3" onclick="window.print()">
                            <i class="fas fa-print me-2"></i> Imprimir Simulación
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light sticky-top" style="z-index: 10;">
                                    <tr class="text-muted small text-uppercase fw-bold">
                                        <th class="ps-4">N°</th>
                                        <th>Fecha</th>
                                        <th>Capital</th>
                                        <th>Interés</th>
                                        <th>Cuota</th>
                                        <th class="pe-4 text-end">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="c in cronograma" :key="c.numero" class="transition-all">
                                        <td class="ps-4 fw-bold">{{ c.numero }}</td>
                                        <td class="small">{{ c.fecha }}</td>
                                        <td>{{ formatoDinero(c.capital) }}</td>
                                        <td class="text-primary">{{ formatoDinero(c.interes) }}</td>
                                        <td class="fw-bold">{{ formatoDinero(c.cuota) }}</td>
                                        <td class="pe-4 text-end text-muted small">{{ formatoDinero(c.saldo) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light-subtle border-0 p-3 text-center">
                        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Esta es una simulación informativa. Los valores reales pueden variar al momento del desembolso.</span>
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
.form-range::-webkit-slider-thumb { background: #0d6efd; }
.bg-light-subtle { background-color: rgba(var(--bs-primary-rgb), 0.05); }
@media print {
    .col-lg-4, .btn, .card-footer { display: none !important; }
    .col-lg-8 { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; }
    .card { box-shadow: none !important; }
    .table-responsive { max-height: none !important; overflow: visible !important; }
}
</style>
