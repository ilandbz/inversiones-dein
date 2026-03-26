<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useCredito from '@/Composables/Credito';
import useEvaluacionPrestamo from '@/Composables/EvaluacionPrestamo';
import useDatosSession from '@/Composables/session';
import Prestamo from '@/Pages/Prestamos/Form.vue';
import FormArchivos from '@/Pages/Evaluacion/FormArchivos.vue';
import useHelper from '@/Helpers';

const {
  obtenerCreditos,
  creditos,
  credito,
  obtenerCredito,
  eliminarCredito,
  errors,
  respuesta,
  cambiarEstadoCredito,
  loading: loadingCreditos
} = useCredito();

const { agregarEvaluacion, respuesta: respuestaEval } = useEvaluacionPrestamo();
const { usuario } = useDatosSession();
const { openModal, Toast, Swal, formatoFecha, formatoDinero } = useHelper();

const selectedId = ref(null);
const selectedClienteNombre = ref('');

const dato = ref({
  page: 1,
  buscar: '',
  paginacion: 10,
  estado: ['PENDIENTE', 'OBSERVADO'],
});

const defaultForm = () => ({
  id: '', cliente_id: '', cliente_apenom: '', asesor_id: '', aval_id: '', estado: 'PENDIENTE',
  fecha_reg: formatoFecha(null, 'YYYY-MM-DD'), fecha_venc: '', tipo: '', monto: 0.0,
  origen_financiamiento_id: '', frecuencia: 'DIARIO', plazo: 30, tasainteres: 0.09,
  total: 0.0, costomora: 0.0, created_at: '', updated_at: '', estadoCrud: '', errors: []
});

const form = ref(defaultForm());

const mapCreditoToForm = (c) => ({
  id: c?.id ?? '', cliente_id: c?.cliente_id ?? '',
  cliente_apenom: c?.cliente?.persona?.apenom ?? '',
  asesor_id: c?.asesor_id ?? '', aval_id: c?.aval_id ?? '',
  estado: c?.estado ?? 'PENDIENTE', fecha_reg: c?.fecha_reg ?? formatoFecha(null, 'YYYY-MM-DD'),
  fecha_venc: c?.fecha_venc ?? '', tipo: c?.tipo ?? '', monto: Number(c?.monto ?? 0),
  origen_financiamiento_id: c?.origen_financiamiento_id ?? '', frecuencia: c?.frecuencia ?? 'DIARIO',
  plazo: Number(c?.plazo ?? 30), tasainteres: Number(c?.tasainteres ?? 0.09),
  total: Number(c?.total ?? 0), costomora: Number(c?.costomora ?? 0)
});

const listarCreditos = async (page = 1) => {
  dato.value.page = page;
  await obtenerCreditos(dato.value);
};

const editar = async (id) => {
  await obtenerCredito(id);
  if (!credito.value) return;
  form.value = { ...defaultForm(), ...mapCreditoToForm(credito.value), estadoCrud: 'editar' };
  openModal('#prestamomodal');
};

const archivos = async (id) => {
  await obtenerCredito(id);
  selectedId.value = id;
  selectedClienteNombre.value = credito.value?.cliente?.persona?.apenom || '';
  openModal('#archivosModal');
};

const eliminar = (id) => {
  Swal.fire({
    title: '¿Eliminar crédito?',
    text: "Esta acción no se puede revertir.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'SÍ, BORRAR',
    cancelButtonText: 'CANCELAR',
    customClass: { confirmButton: 'btn btn-danger rounded-pill px-4', cancelButton: 'btn btn-light rounded-pill px-4' },
    buttonsStyling: false
  }).then(async (result) => {
    if (result.isConfirmed) {
        await eliminarCredito(id);
        if (respuesta.value?.ok === 1) { listarCreditos(creditos.value.current_page); Toast.fire({ icon: 'success', title: 'Borrado con éxito' }); }
    }
  });
};

const pasarRevision = async (creditoId) => {
  const { isConfirmed } = await Swal.fire({
    title: 'Enviar a Revisión',
    text: "El crédito pasará a etapa de autorización por gerencia.",
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'SÍ, ENVIAR',
    cancelButtonText: 'CANCELAR',
    customClass: { confirmButton: 'btn btn-success rounded-pill px-4', cancelButton: 'btn btn-light rounded-pill px-4' },
    buttonsStyling: false
  });

  if (!isConfirmed) return;

  await agregarEvaluacion({
    credito_id: creditoId, user_id: usuario.value?.id, cargo: usuario.value?.cargo ?? 'ANALISTA',
    estado: 'APROBADO', observacion: 'Enviado a revisión técnica',
  });

  if (respuestaEval.value?.ok !== 1) return Toast.fire({ icon: 'error', title: 'Error en evaluación' });

  await cambiarEstadoCredito({ id: creditoId, estado: 'REVISION' });

  if (respuesta.value?.ok === 1) {
    Toast.fire({ icon: 'success', title: `Crédito enviado a revisión` });
    listarCreditos(creditos.value.current_page);
  }
};

const offset = 2;
const pagesNumber = computed(() => {
  const c = creditos.value;
  if (!c?.to) return [];
  let from = c.current_page - offset;
  if (from < 1) from = 1;
  let to = from + offset * 2;
  if (to >= c.last_page) to = c.last_page;
  const pages = [];
  for (let p = from; p <= to; p++) pages.push(p);
  return pages;
});

onMounted(() => { listarCreditos(); });
</script>

<template>
  <AppLayoutDefault title="Evaluación de Créditos">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Bandeja de Evaluación</h3>
                <p class="text-muted small mb-0">Gestión de solicitudes de crédito pendientes de análisis</p>
            </div>
            <div class="col-auto">
                <div class="input-group bg-white rounded-pill px-3 py-1 shadow-sm border">
                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                    <input v-model="dato.buscar" type="text" class="form-control bg-transparent border-0" placeholder="Socio o #Crédito..." @keyup.enter="listarCreditos(1)">
                </div>
            </div>
        </div>

        <!-- Stats Quick View -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-3">
                    <div class="small fw-bold opacity-75">PENDIENTES</div>
                    <div class="fs-3 fw-bold">{{ creditos.total || 0 }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                    <div class="small fw-bold text-muted">A REVISIÓN</div>
                    <div class="fs-4 fw-bold text-dark">Lote Semanal</div>
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
                                <th>Condiciones</th>
                                <th>Asesor</th>
                                <th>Estado</th>
                                <th class="pe-4 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loadingCreditos" class="text-center py-5">
                                <td colspan="6" class="py-5"><span class="spinner-border text-primary"></span></td>
                            </tr>
                            <tr v-else-if="!creditos.data?.length" class="text-center py-5">
                                <td colspan="6" class="py-5 text-muted italic">No hay solicitudes para evaluar.</td>
                            </tr>
                            <tr v-for="c in creditos.data" :key="c.id">
                                <td class="ps-4 fw-bold">#{{ c.id }}</td>
                                <td>
                                    <div class="fw-bold text-dark text-uppercase">{{ c.cliente?.persona?.apenom }}</div>
                                    <div class="small text-muted">DNI: {{ c.cliente?.persona?.dni }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ formatoDinero(c.monto) }}</div>
                                    <div class="small text-muted">{{ c.plazo }} cuotas ({{ c.frecuencia }})</div>
                                </td>
                                <td class="small">{{ c.asesor?.user?.name }}</td>
                                <td>
                                    <span class="badge rounded-pill px-3" :class="c.estado === 'OBSERVADO' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-info-subtle text-info border border-info'">
                                        {{ c.estado }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2 text-nowrap">
                                        <button class="btn btn-sm btn-light border rounded-pill px-3" @click="archivos(c.id)" title="Expediente Digital">
                                            <i class="fas fa-file-pdf text-danger me-1"></i> DOCS
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning rounded-pill px-2" v-if="c.estado === 'PENDIENTE' || c.estado === 'OBSERVADO'" @click="editar(c.id)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-2" v-if="c.estado === 'PENDIENTE'" @click="eliminar(c.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" @click="pasarRevision(c.id)">
                                            <i class="fas fa-check-double me-1"></i> EVALUAR
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white p-4 border-top-0 d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Mostrando {{ creditos.from || 0 }} - {{ creditos.to || 0 }} de {{ creditos.total || 0 }}
                </div>
                <nav v-if="creditos.last_page > 1">
                    <ul class="pagination pagination-sm mb-0 gap-1 child-rounded-pill">
                        <li class="page-item" :class="{ disabled: creditos.current_page === 1 }">
                            <button class="page-link border-0 shadow-none" @click="listarCreditos(creditos.current_page - 1)"><i class="fas fa-chevron-left"></i></button>
                        </li>
                        <li v-for="p in pagesNumber" :key="p" class="page-item" :class="{ active: p === creditos.current_page }">
                            <button class="page-link border-0 shadow-none px-3" @click="listarCreditos(p)">{{ p }}</button>
                        </li>
                        <li class="page-item" :class="{ disabled: creditos.current_page === creditos.last_page }">
                            <button class="page-link border-0 shadow-none" @click="listarCreditos(creditos.current_page + 1)"><i class="fas fa-chevron-right"></i></button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
      </div>
    </div>

    <Prestamo :form="form" @cargar="listarCreditos" />
    <FormArchivos :creditoId="selectedId" :clienteNombre="selectedClienteNombre" :form="form" />
  </AppLayoutDefault>
</template>

<style scoped>
.bg-primary { background-color: #0d6efd !important; }
.bg-info-subtle { background-color: #e0f7fa !important; }
.bg-warning-subtle { background-color: #fff8e1 !important; }
.child-rounded-pill .page-link { border-radius: 50px !important; }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
</style>