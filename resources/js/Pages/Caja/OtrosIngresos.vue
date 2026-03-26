<script setup>
import { ref } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useHelper from '@/Helpers';

const { Toast, Swal, formatoDinero, formatoFecha } = useHelper();

const form = ref({
    fecha: formatoFecha(null, 'YYYY-MM-DD'),
    tipo_ingreso: 'OTRO',
    monto: 0,
    concepto: '',
    descripcion: '',
    metodo_pago: 'EFECTIVO'
});

const tipos = [
    { id: 'COMISION', nombre: 'Comisión por Servicio' },
    { id: 'MANTENIMIENTO', nombre: 'Mantenimiento de Cuenta' },
    { id: 'FORMULARIOS', nombre: 'Venta de Formularios / Carpeta' },
    { id: 'SEGURO', nombre: 'Seguro de Desgravamen' },
    { id: 'OTRO', nombre: 'Otros Ingresos Diversos' }
];

const historial = ref([
    { id: 101, fecha: '24/03/2026', concepto: 'Venta Carpeta Socia Nuevo', monto: 15.00, tipo: 'FORMULARIOS' },
    { id: 102, fecha: '24/03/2026', concepto: 'Comisión Giro Interplaza', monto: 25.00, tipo: 'COMISION' }
]);

const loading = ref(false);

const registrar = async () => {
    if (form.value.monto <= 0) return Swal.fire('Error', 'Ingrese un monto válido', 'error');
    loading.value = true;
    try {
        // Simulación de guardado
        setTimeout(() => {
            Toast.fire({ icon: 'success', title: 'Ingreso registrado correctamente' });
            historial.value.unshift({
                id: Math.floor(Math.random()*1000),
                fecha: formatoFecha(form.value.fecha, 'DD/MM/YYYY'),
                concepto: form.value.concepto,
                monto: form.value.monto,
                tipo: form.value.tipo_ingreso
            });
            form.value.monto = 0;
            form.value.concepto = '';
            form.value.descripcion = '';
            loading.value = false;
        }, 800);
    } catch (e) {
        loading.value = false;
        Swal.fire('Error', 'No se pudo registrar el ingreso', 'error');
    }
}
</script>

<template>
  <AppLayoutDefault title="Otros Ingresos de Caja">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Registro de Otros Ingresos</h3>
                <p class="text-muted small mb-0">Gestión de entradas de efectivo diversas ajenas a la cartera de créditos</p>
            </div>
            <div class="col-auto">
                <div class="bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small fw-bold">
                    <i class="fas fa-plus me-1"></i> Operación de Ingreso
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Formulario de Registro -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h5 class="fw-bold text-dark mb-0">Detalles del Ingreso</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Categoría de Ingreso</label>
                            <select v-model="form.tipo_ingreso" class="form-select rounded-pill bg-light border-0 px-3 shadow-none">
                                <option v-for="t in tipos" :key="t.id" :value="t.id">{{ t.nombre }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Concepto del Cobro</label>
                            <input v-model.trim="form.concepto" type="text" class="form-control rounded-pill bg-light border-0 px-3 shadow-none" placeholder="Ej: Pago de fotocopias o mantenimiento">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">Monto (S/)</label>
                                <div class="input-group bg-light rounded-pill px-3 py-1 border-0 shadow-none">
                                    <span class="input-group-text bg-transparent border-0 text-success fw-bold">S/</span>
                                    <input v-model="form.monto" type="number" step="0.10" class="form-control bg-transparent border-0 shadow-none fw-bold" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">Método</label>
                                <select v-model="form.metodo_pago" class="form-select rounded-pill bg-light border-0 px-3 shadow-none fw-bold text-primary">
                                    <option>EFECTIVO</option><option>TRANSFERENCIA</option><option>DEPOSITO</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Observación / Notas</label>
                            <textarea v-model.trim="form.descripcion" class="form-control rounded-4 bg-light border-0 px-3 shadow-none" rows="2" placeholder="Detalle adicional opcional..."></textarea>
                        </div>

                        <button @click="registrar" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold mt-2 shadow" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="fas fa-save me-2 opacity-50"></i>{{ loading ? 'REGISTRANDO...' : 'REGISTRAR INGRESO' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Auditoria / Historial Reciente -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold text-dark mb-0">Ingresos Recientes de Hoy</h5>
                        <div class="text-muted small fw-bold">TOTAL: <span class="text-success">{{ formatoDinero(historial.reduce((acc, h) => acc + h.monto, 0)) }}</span></div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small text-uppercase fw-bold">
                                        <th class="ps-4 py-3">ID</th>
                                        <th>Concepto</th>
                                        <th>Categoría</th>
                                        <th class="pe-4 text-end">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!historial.length" class="text-center">
                                        <td colspan="4" class="py-5 text-muted opacity-50 italic">No se han registrado ingresos en esta sesión.</td>
                                    </tr>
                                    <tr v-for="h in historial" :key="h.id" class="transition-all">
                                        <td class="ps-4 fw-bold text-muted" style="font-size: 0.8rem;">#{{ h.id }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ h.concepto }}</div>
                                            <div class="text-muted small" style="font-size: 0.7rem;">{{ h.fecha }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small fw-normal">{{ h.tipo }}</span>
                                        </td>
                                        <td class="pe-4 text-end fw-bold text-success">{{ formatoDinero(h.monto) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light-subtle border-0 p-3 text-center">
                        <button class="btn btn-sm btn-link text-primary text-decoration-none fw-bold">VER REPORTE DE CAJA COMPLETO</button>
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
.bg-light-subtle { background-color: rgba(var(--bs-success-rgb), 0.05); }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
</style>
