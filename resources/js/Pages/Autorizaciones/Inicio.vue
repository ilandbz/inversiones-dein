<script setup>
import { ref, onMounted } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import useEvaluacionPrestamo from '@/Composables/EvaluacionPrestamo';
import useDatosSession from '@/Composables/session';
import useCredito from '@/Composables/Credito';
import FormAutorizacion from '@/Pages/Autorizaciones/FormAutorizacion.vue'
import FormArchivos from '@/Pages/Evaluacion/FormArchivos.vue'
import useHelper from '@/Helpers'; 

const { obtenerCreditos, creditos, credito, obtenerCredito, eliminarCredito, errors, respuesta, cambiarEstadoCredito } = useCredito();
const { agregarEvaluacion, respuesta: respuestaEval } = useEvaluacionPrestamo();
const { usuario } = useDatosSession();
const { openModal, Toast, Swal, formatoFecha } = useHelper();

const selectedId = ref(null);
const selectedClienteNombre = ref('');

const dato = ref({
    page: 1,
    buscar: '',
    paginacion: 10,
    estado: 'REVISION',
});

const form = ref({
    id: '', cliente_id: '', cliente_apenom : '', asesor_id: '', aval_id: '', estado: 'REVISION',
    fecha_reg: formatoFecha(null, "YYYY-MM-DD"), fecha_venc: '', tipo: '', monto: 0.00,
    origen_financiamiento_id: '', frecuencia: 'DIARIO', plazo: 30, tasainteres: 0.09, total: 0.00,
    costomora: 0.00, created_at: '', updated_at: '', estadoCrud: '', errors: []
});

const limpiar = () => {
    form.value = {
        id: '', cliente_id: '', asesor_id: '', aval_id: '', estado: 'REVISION',
        fecha_reg: formatoFecha(null, "YYYY-MM-DD"), fecha_venc: '', tipo: '', monto: 0.00,
        origen_financiamiento_id: '', frecuencia: 'DIARIO', plazo: 30, tasainteres: 0.09, total: 0.00,
        costomora: 0.00, created_at: '', updated_at: '', estadoCrud: '', errors: []
    }
}

const obtenerDatos = async (id) => {
    await obtenerCredito(id);
    if (credito.value) {
        form.value.id = credito.value.id ?? '';
        form.value.cliente_id = credito.value.cliente_id ?? '';
        form.value.cliente_apenom = credito.value.cliente?.persona.apenom ?? '';
        form.value.asesor_id = credito.value.asesor_id ?? '';
        form.value.aval_id = credito.value.aval_id ?? '';
        form.value.estado = credito.value.estado ?? 'PENDIENTE';
        form.value.fecha_reg = credito.value.fecha_reg ?? formatoFecha(null, "YYYY-MM-DD");
        form.value.fecha_venc = credito.value.fecha_venc ?? '';
        form.value.tipo = credito.value.tipo ?? '';
        form.value.monto = Number(credito.value.monto ?? 0);
        form.value.origen_financiamiento_id = credito.value.origen_financiamiento_id ?? '';
        form.value.frecuencia = credito.value.frecuencia ?? 'DIARIO';
        form.value.plazo = Number(credito.value.plazo ?? 30);
        form.value.tasainteres = Number(credito.value.tasainteres)
        form.value.total = Number(credito.value.total ?? 0);
        form.value.costomora = Number(credito.value.costomora ?? 0);
    }
};

const ver = async(id) => {
    limpiar();
    await obtenerDatos(id);
    openModal('#autorizacionModal');
}

const archivos = async (id) => {
    await obtenerDatos(id);
    selectedId.value = id;
    selectedClienteNombre.value = form.value.cliente_apenom;
    openModal('#archivosModal');
};

const pasarRevision = async (creditoId) => {
  const { isConfirmed, value } = await Swal.fire({
    title: 'Autorización de Crédito',
    html: `
      <div class="text-start">
        <label class="form-label fw-bold small text-muted">RESULTADO</label>
        <select id="swal-estado" class="form-select rounded-pill mb-3">
          <option value="APROBADO">APROBAR CRÉDITO</option>
          <option value="OBSERVADO">OBSERVAR PARA CORRECCIÓN</option>
        </select>
        <label class="form-label fw-bold small text-muted">OBSERVACIÓN / COMENTARIO</label>
        <textarea id="swal-obs" class="form-control rounded-4" rows="3" placeholder="Escriba aquí los detalles..."></textarea>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: 'CONFIRMAR DECISIÓN',
    cancelButtonText: 'CANCELAR',
    customClass: { confirmButton: 'btn btn-primary rounded-pill px-4', cancelButton: 'btn btn-light rounded-pill px-4' },
    buttonsStyling: false,
    preConfirm: () => {
      const estado = document.getElementById('swal-estado')?.value;
      const obs = document.getElementById('swal-obs')?.value.trim();
      if (estado === 'OBSERVADO' && !obs) {
        Swal.showValidationMessage('La observación es obligatoria para el estado OBSERVADO.');
        return;
      }
      return { estado, observacion: obs };
    }
  });

  if (!isConfirmed) return;

  await agregarEvaluacion({
    credito_id: creditoId, user_id: usuario.value?.id, cargo: usuario.value?.cargo ?? 'AUTORIZADOR',
    estado: value.estado, observacion: value.observacion,
  });

  if (respuestaEval.value?.ok === 1) {
    await cambiarEstadoCredito({ id: creditoId, estado: value.estado });
    Toast.fire({ icon: 'success', title: `Operación registrada: ${value.estado}` });
    listarCreditos(creditos.value.current_page);
  }
};

const rechazar = async(id) => {
     Swal.fire({
        title: '¿Rechazar Crédito?',
        text: "Esta acción marcará el crédito como denegado definitivamente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'SÍ, RECHAZAR',
        cancelButtonText: 'CANCELAR',
        customClass: { confirmButton: 'btn btn-danger rounded-pill px-4', cancelButton: 'btn btn-light rounded-pill px-4' },
        buttonsStyling: false
    }).then(async (result) => {
        if (result.isConfirmed) {
            await cambiarEstadoCredito({ id, estado: 'RECHAZADO' });
            if (respuesta.value.ok == 1) {
                Toast.fire({ icon: 'success', title: 'Crédito rechazado correctamente' });
                listarCreditos(creditos.value.current_page);
            }
        }
    })
}

const listarCreditos = async(page=1) => {
    dato.value.page= page
    await obtenerCreditos(dato.value)
}

onMounted(() => {
    listarCreditos()
});
</script>

<template>
  <AppLayoutDefault title="Bandeja de Autorizaciones">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Gestión de Autorizaciones</h3>
                <p class="text-muted small mb-0">Revisión, aprobación y control de solicitudes de crédito vigentes</p>
            </div>
            <div class="col-auto">
                <div class="bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-bold">
                    <i class="fas fa-shield-alt me-1"></i> Módulo Seguro
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group bg-light rounded-pill px-3 py-1">
                            <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                            <input v-model="dato.buscar" type="text" class="form-control bg-transparent border-0 shadow-none" placeholder="Buscar por cliente, código o asesor..." @keyup.enter="listarCreditos(1)">
                            <button class="btn btn-primary rounded-pill px-4 ms-2 fw-bold" @click="listarCreditos(1)">FILTRAR</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-end h-100">
                            <span class="text-muted small me-2 text-uppercase fw-bold">Registros:</span>
                            <select v-model="dato.paginacion" class="form-select form-select-sm border-0 bg-light rounded-pill w-auto shadow-none" @change="listarCreditos(1)">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small text-uppercase fw-bold">
                                <th class="ps-4 py-3">ID</th>
                                <th>Cliente</th>
                                <th>Asesor / Operación</th>
                                <th>Monto Solicitado</th>
                                <th>Plazo / Frec.</th>
                                <th>Estado</th>
                                <th class="pe-4 text-end">Autorizar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!creditos.data?.length" class="text-center py-5">
                                <td colspan="7" class="py-5 text-muted">
                                    <i class="fas fa-clipboard-check fs-1 mb-3 d-block opacity-25"></i>
                                    No hay solicitudes pendientes de autorización.
                                </td>
                            </tr>
                            <tr v-for="c in creditos.data" :key="c.id" class="transition-all">
                                <td class="ps-4 fw-bold text-primary">#{{ c.id }}</td>
                                <td>
                                    <div class="fw-bold text-dark text-uppercase">{{ c.cliente.persona.apenom }}</div>
                                    <div class="small text-muted">DNI: {{ c.cliente.persona.dni }}</div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark"><i class="fas fa-user-tie me-1 opacity-50"></i> {{ c.asesor.user?.name }}</div>
                                    <div class="small text-muted mt-1 bg-light d-inline-block px-2 rounded-pill">{{ c.tipo }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">S/ {{ Number(c.monto).toFixed(2) }}</div>
                                    <div class="small text-success fw-semibold">Tasa: {{ (c.tasainteres * 100).toFixed(2) }}%</div>
                                </td>
                                <td>
                                    <div class="small"><i class="far fa-calendar-alt me-1 opacity-50"></i> {{ c.plazo }} cuotas</div>
                                    <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">{{ c.frecuencia }}</div>
                                </td>
                                <td>
                                    <span :class="['badge rounded-pill px-3 py-1', c.estado === 'REVISION' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-info-subtle text-info border border-info']">
                                        {{ c.estado }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                        <button class="btn btn-white btn-sm px-3" title="Detalles" @click="ver(c.id)">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                        <button class="btn btn-white btn-sm px-3" title="Autorizar / Observar" @click="pasarRevision(c.id)">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </button>
                                        <button class="btn btn-white btn-sm px-3" title="Documentos" @click="archivos(c.id)">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        </button>
                                        <button class="btn btn-white btn-sm px-3" title="Rechazar" @click="rechazar(c.id)">
                                            <i class="fas fa-times-circle text-muted"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <div class="card-footer bg-white border-0 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando <b>{{ creditos.from }}</b> a <b>{{ creditos.to }}</b> de <b>{{ creditos.total }}</b> solicitudes
                    </div>
                    <nav v-if="creditos.last_page > 1">
                        <ul class="pagination pagination-rounded mb-0">
                            <li class="page-item" :class="{ disabled: creditos.current_page === 1 }">
                                <button class="page-link border-0 shadow-none bg-light" @click="listarCreditos(creditos.current_page - 1)">
                                    <i class="fas fa-chevron-left small"></i>
                                </button>
                            </li>
                            <li v-for="p in creditos.last_page" :key="p" class="page-item" :class="{ active: p === creditos.current_page }">
                                <button class="page-link border-0 shadow-none" :class="p === creditos.current_page ? 'btn-primary' : 'bg-transparent text-dark'" @click="listarCreditos(p)">{{ p }}</button>
                            </li>
                            <li class="page-item" :class="{ disabled: creditos.current_page === creditos.last_page }">
                                <button class="page-link border-0 shadow-none bg-light" @click="listarCreditos(creditos.current_page + 1)">
                                    <i class="fas fa-chevron-right small"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
      </div>
    </div>
    <FormAutorizacion :form="form" @cargar="listarCreditos" />
    <FormArchivos :form="form" />
  </AppLayoutDefault>
</template>

<style scoped>
.transition-all { transition: all 0.2s ease-in-out; }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
.btn-white { background: #fff; border: 1px solid #f1f1f1; }
.btn-white:hover { background: #f8f9fa; }
.pagination-rounded .page-link { border-radius: 50% !important; margin: 0 3px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; }
</style>