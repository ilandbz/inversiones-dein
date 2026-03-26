<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useCredito from '@/Composables/Credito';
import useDesembolso from '@/Composables/Desembolso';
import useDatosSession from '@/Composables/session';
import FormArchivos from '@/Pages/Evaluacion/FormArchivos.vue';
import useHelper from '@/Helpers';

const { obtenerCreditos, creditos, loading: loadingCreditos } = useCredito();
const { agregarDesembolso, loading: loadingDesem } = useDesembolso();
const { usuario } = useDatosSession();
const { Toast, Swal, formatoFecha, formatoDinero } = useHelper();

const selectedId = ref(null);
const selectedClienteNombre = ref('');

const dato = ref({
    page: 1,
    buscar: '',
    paginacion: 10,
    estado: 'APROBADO',
});

const listarCreditos = async (page = 1) => {
    dato.value.page = page;
    await obtenerCreditos(dato.value);
};

const archivos = (credito) => {
    selectedId.value = credito.id;
    selectedClienteNombre.value = credito.cliente?.persona?.apenom || '';
    const modal = new bootstrap.Modal(document.getElementById('archivosModal'))
    modal.show()
};

const desembolsar = async (creditoObj) => {
    const { isConfirmed, value } = await Swal.fire({
        title: 'Confirmar Desembolso',
        html: `
            <div class="text-start p-2">
                <div class="alert alert-primary border-0 rounded-4 mb-4">
                    <div class="small fw-bold opacity-75">CLIENTE</div>
                    <div class="fs-5 fw-bold">${creditoObj.cliente?.persona?.apenom}</div>
                </div>
                
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">FECHA DE OPERACIÓN</label>
                        <input id="swal-fecha" type="date" class="form-control form-control-lg border-0 bg-light rounded-3" value="${formatoFecha(null, "YYYY-MM-DD")}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">MONTO CRÉDITO</label>
                        <input type="text" class="form-control border-0 bg-light rounded-3 fw-bold" value="${formatoDinero(creditoObj.monto)}" readonly>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">DESCONTADO (S/)</label>
                        <input id="swal-descontado" type="number" step="0.01" class="form-control border-0 bg-light rounded-3 fw-bold" value="0.00">
                    </div>
                    <div class="col-12 mt-3">
                        <label class="form-label small fw-bold text-primary">NETO A ENTREGAR (S/)</label>
                        <input id="swal-totalentregado" type="number" step="0.01" class="form-control form-control-lg border-primary bg-primary-subtle text-primary rounded-3 fw-bold fs-4" value="${Number(creditoObj.monto).toFixed(2)}">
                    </div>
                </div>
            </div>
        `,
        customClass: {
            confirmButton: 'btn btn-primary rounded-pill px-5 py-2 fw-bold shadow mt-3',
            cancelButton: 'btn btn-light rounded-pill px-4 fw-bold mt-3'
        },
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: 'REALIZAR DESEMBOLSO',
        cancelButtonText: 'CANCELAR',
        preConfirm: () => {
            const fecha = document.getElementById('swal-fecha').value;
            const descontado = document.getElementById('swal-descontado').value;
            const totalentregado = document.getElementById('swal-totalentregado').value;
            if (!fecha || !totalentregado) return Swal.showValidationMessage('Complete todos los campos obligatorios');
            return {
                credito_id: creditoObj.id,
                fecha: fecha,
                hora: new Date().toLocaleTimeString('it-IT'),
                user_id: usuario.value.id,
                descontado: descontado,
                totalentregado: totalentregado,
                rcsdebe: 'PAGO'
            };
        }
    });

    if (isConfirmed) {
        const res = await agregarDesembolso(value);
        if (res?.ok === 1) {
            Toast.fire({ icon: 'success', title: 'Desembolso procesado con éxito' });
            listarCreditos(creditos.value.current_page);
        }
    }
};

onMounted(() => {
    listarCreditos();
});
</script>

<template>
    <AppLayoutDefault title="Caja - Desembolsos">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4 align-items-center">
                    <div class="col">
                        <h3 class="fw-bold text-dark mb-1">Desembolsos Pendientes</h3>
                        <p class="text-muted small mb-0">Créditos aprobados listos para la entrega de efectivo</p>
                    </div>
                    <div class="col-auto">
                        <div class="input-group bg-white rounded-pill px-3 py-1 shadow-sm border">
                            <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                            <input v-model="dato.buscar" type="text" class="form-control bg-transparent border-0" placeholder="Buscar cliente..." @keyup.enter="listarCreditos(1)">
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Código</th>
                                        <th>Socio / Cliente</th>
                                        <th>Monto Aprobado</th>
                                        <th>Fecha Aprob.</th>
                                        <th class="pe-4 text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!creditos.data?.length" class="text-center py-5">
                                        <td colspan="5" class="py-5 text-muted">
                                            <i class="fas fa-check-circle fs-1 mb-3 d-block opacity-25"></i>
                                            No hay créditos pendientes de desembolso.
                                        </td>
                                    </tr>
                                    <tr v-for="c in creditos.data" :key="c.id" class="transition-all">
                                        <td class="ps-4 fw-bold">#{{ c.id }}</td>
                                        <td>
                                            <div class="fw-bold text-dark text-uppercase">{{ c.cliente?.persona?.apenom }}</div>
                                            <div class="small text-muted">DNI: {{ c.cliente?.persona?.dni }}</div>
                                        </td>
                                        <td>
                                            <div class="fs-5 fw-bold text-primary">{{ formatoDinero(c.monto) }}</div>
                                            <div class="small text-muted">{{ c.plazo }} cuotas</div>
                                        </td>
                                        <td class="small">{{ c.fecha_reg }}</td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                                <button class="btn btn-white btn-sm px-3" title="Ver Expediente" @click="archivos(c)">
                                                    <i class="fas fa-folder-open text-info"></i>
                                                </button>
                                                <button class="btn btn-success btn-sm px-4 fw-bold" @click="desembolsar(c)">
                                                    <i class="fas fa-hand-holding-usd me-1"></i> DESEMBOLSAR
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <FormArchivos :creditoId="selectedId" :clienteNombre="selectedClienteNombre" />
    </AppLayoutDefault>
</template>

<style scoped>
.btn-white { background: #fff; }
.btn-white:hover { background: #f8f9fa; }
.bg-primary-subtle { background-color: #e9f2ff !important; }
.transition-all { transition: all 0.2s ease; }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
</style>
