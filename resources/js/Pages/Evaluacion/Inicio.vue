<script setup>
import { ref, onMounted, computed } from 'vue';
import useCredito from '@/Composables/Credito';
import useEvaluacionPrestamo from '@/Composables/EvaluacionPrestamo';
import useDatosSession from '@/Composables/session';
import Prestamo from '@/Pages/Prestamos/Form.vue';
import useHelper from '@/Helpers';

const {
  obtenerCreditos,
  creditos,
  credito,
  obtenerCredito,
  eliminarCredito,
  errors,
  respuesta,
  cambiarEstadoCredito
} = useCredito();

const { agregarEvaluacion, respuesta: respuestaEval } = useEvaluacionPrestamo();
const { usuario } = useDatosSession();
const { openModal, Toast, Swal, formatoFecha } = useHelper();

const dato = ref({
  page: 1,
  buscar: '',
  paginacion: 10,
  estado: 'PENDIENTE',
});

const defaultForm = () => ({
  id: '',
  cliente_id: '',
  cliente_apenom: '',
  asesor_id: '',
  aval_id: '',
  estado: 'PENDIENTE',
  fecha_reg: formatoFecha(null, 'YYYY-MM-DD'),
  fecha_venc: '',
  tipo: '',
  monto: 0.0,
  origen_financiamiento_id: '',
  frecuencia: 'DIARIO',
  plazo: 30,
  tasainteres: 0.09,
  total: 0.0,
  costomora: 0.0,
  created_at: '',
  updated_at: '',
  estadoCrud: '',
  errors: []
});

const form = ref(defaultForm());

const limpiar = () => {
  form.value = defaultForm();
};

const mapCreditoToForm = (c) => ({
  id: c?.id ?? '',
  cliente_id: c?.cliente_id ?? '',
  cliente_apenom: c?.cliente?.persona?.apenom ?? '',
  asesor_id: c?.asesor_id ?? '',
  aval_id: c?.aval_id ?? '',
  estado: c?.estado ?? 'PENDIENTE',
  fecha_reg: c?.fecha_reg ?? formatoFecha(null, 'YYYY-MM-DD'),
  fecha_venc: c?.fecha_venc ?? '',
  tipo: c?.tipo ?? '',
  monto: Number(c?.monto ?? 0),
  origen_financiamiento_id: c?.origen_financiamiento_id ?? '',
  frecuencia: c?.frecuencia ?? 'DIARIO',
  plazo: Number(c?.plazo ?? 30),
  tasainteres: Number(c?.tasainteres ?? 0.09),
  total: Number(c?.total ?? 0),
  costomora: Number(c?.costomora ?? 0),
  created_at: c?.created_at ?? '',
  updated_at: c?.updated_at ?? '',
});

const listarCreditos = async (page = 1) => {
  dato.value.page = page;
  await obtenerCreditos(dato.value);
};

const obtenerDatos = async (id) => {
  await obtenerCredito(id);
  if (!credito.value) return;

  const keep = { estadoCrud: form.value.estadoCrud, errors: form.value.errors };
  Object.assign(form.value, defaultForm(), keep, mapCreditoToForm(credito.value));
};

const editar = async (id) => {
  limpiar();
  await obtenerDatos(id);
  form.value.estadoCrud = 'editar';

  const el = document.getElementById('prestamomodalLabel');
  if (el) el.innerHTML = 'Editar credito';
  openModal('#prestamomodal');
};

const eliminar = (id) => {
  Swal.fire({
    title: '¿Estás seguro de Eliminar?',
    text: 'Credito',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Eliminalo!'
  }).then((result) => {
    if (result.isConfirmed) elimina(id);
  });
};

const elimina = async (id) => {
  await eliminarCredito(id);

  form.value.errors = [];
  if (errors.value) form.value.errors = errors.value;

  if (respuesta.value?.ok === 1) {
    form.value.errors = [];
    Toast.fire({ icon: 'success', title: respuesta.value.mensaje });
    listarCreditos(creditos.value.current_page);
  }
};

// PAGINACIÓN (optimizada)
const offset = 2;
const isActived = () => creditos.value?.current_page;

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

const buscar = () => listarCreditos(1);
const cambiarPaginacion = () => listarCreditos(1);
const cambiarPagina = (pagina) => listarCreditos(pagina);

/**
 * PASAR A REVISION / OBSERVADO
 * - Si estado evaluación = OBSERVADO => crédito pasa a OBSERVADO (observación obligatoria)
 * - Si estado evaluación = APROBADO => crédito pasa a REVISION (observación opcional)
 */
const pasarRevision = async (creditoId) => {
  const { isConfirmed, value } = await Swal.fire({
    title: 'Enviar / Observar crédito',
    html: `
      <div class="text-start">
        <label class="form-label">Resultado de evaluación</label>
        <select id="swal-estado" class="swal2-input" style="width:100%">
          <option value="APROBADO">APROBADO (Enviar a REVISIÓN)</option>
          <option value="OBSERVADO">OBSERVADO (Marcar como OBSERVADO)</option>
        </select>

        <label class="form-label mt-2">Observación (obligatoria si es OBSERVADO)</label>
        <textarea id="swal-obs" class="swal2-textarea" placeholder="Escribe la observación..."></textarea>
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Confirmar',
    cancelButtonText: 'Cancelar',
    preConfirm: () => {
      const estado = document.getElementById('swal-estado')?.value ?? 'APROBADO';
      const obs = (document.getElementById('swal-obs')?.value ?? '').trim();

      if (estado === 'OBSERVADO' && !obs) {
        Swal.showValidationMessage('La observación es obligatoria cuando el estado es OBSERVADO.');
        return;
      }
      return { estado, observacion: obs || null };
    }
  });

  if (!isConfirmed) return;

  // 1) Registrar evaluación
  await agregarEvaluacion({
    credito_id: creditoId,
    user_id: usuario.value?.id,
    cargo: usuario.value?.cargo ?? 'ANALISTA',
    estado: value.estado,              // APROBADO | OBSERVADO
    observacion: value.observacion,    // null u obligatorio si observado
  });

  if (respuestaEval.value?.ok !== 1) {
    Toast.fire({ icon: 'error', title: 'No se pudo registrar la evaluación' });
    return;
  }

  // 2) Estado del crédito según resultado
  const nuevoEstadoCredito = value.estado === 'OBSERVADO' ? 'OBSERVADO' : 'REVISION';

  await cambiarEstadoCredito({ id: creditoId, estado: nuevoEstadoCredito });

  if (respuesta.value?.ok === 1) {
    Toast.fire({ icon: 'success', title: `Crédito actualizado a ${nuevoEstadoCredito}` });
    listarCreditos(creditos.value.current_page);
  } else {
    Toast.fire({ icon: 'error', title: 'Error al cambiar el estado del crédito' });
  }
};

onMounted(() => {
  listarCreditos();
});
</script>

<style scoped>
.acciones-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 4px;
}
.acciones-grid .btn {
  width: 100%;
  padding: 4px 0;
}
</style>

<template>
  <div class="page-content">
    <div class="container-fluid">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h6 class="card-title">Gestion de Creditos</h6>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-md-2 mb-1">
              <div class="input-group mb-1">
                <span class="input-group-text" id="basic-addon1">Mostrar</span>
                <select class="form-select" v-model="dato.paginacion" @change="cambiarPaginacion">
                  <option value="10">10</option>
                  <option value="20">20</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                  <option value="1000">1000</option>
                </select>
              </div>
            </div>

            <div class="col-md-5">
              <div class="input-group mb-1">
                <span class="input-group-text" id="basic-addon1">Buscar</span>
                <input
                  class="form-control"
                  placeholder="Ingrese nombre, código"
                  type="text"
                  v-model="dato.buscar"
                  @change="buscar"
                />
              </div>
            </div>

            <div class="col-md-4 mb-1">
              <nav>
                <ul class="pagination">
                  <li v-if="creditos.current_page >= 2" class="page-item">
                    <a href="#" class="page-link" title="Primera Página" @click.prevent="cambiarPagina(1)">
                      <span><i class="fas fa-backward"></i></span>
                    </a>
                  </li>
                  <li v-if="creditos.current_page > 1" class="page-item">
                    <a
                      href="#"
                      class="page-link"
                      title="Página Anterior"
                      @click.prevent="cambiarPagina(creditos.current_page - 1)"
                    >
                      <span><i class="fas fa-angle-left"></i></span>
                    </a>
                  </li>

                  <li
                    v-for="page in pagesNumber"
                    class="page-item"
                    :key="page"
                    :class="[page == isActived() ? 'active' : '']"
                    :title="'Página ' + page"
                  >
                    <a href="#" class="page-link" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                  </li>

                  <li v-if="creditos.current_page < creditos.last_page" class="page-item">
                    <a
                      href="#"
                      class="page-link"
                      title="Página Siguiente"
                      @click.prevent="cambiarPagina(creditos.current_page + 1)"
                    >
                      <span><i class="fas fa-angle-right"></i></span>
                    </a>
                  </li>

                  <li v-if="creditos.current_page <= creditos.last_page - 1" class="page-item">
                    <a
                      href="#"
                      class="page-link"
                      title="Última Página"
                      @click.prevent="cambiarPagina(creditos.last_page)"
                    >
                      <span><i class="fas fa-step-forward"></i></span>
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-1">
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-xs table-striped">
                  <thead>
                    <tr>
                      <th colspan="13" class="text-center">Créditos</th>
                    </tr>
                    <tr>
                      <th>#</th>
                      <th>Cod</th>
                      <th>Cliente</th>
                      <th>Asesor</th>
                      <th>Monto</th>
                      <th>Tipo</th>
                      <th>Fecha Reg</th>
                      <th>Fecha Venc</th>
                      <th>Frecuencia</th>
                      <th>Plazo</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                      <th>Enviar</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-if="creditos.total == 0">
                      <td class="text-danger text-center" colspan="13">
                        -- Datos No Registrados - Tabla Vacía --
                      </td>
                    </tr>

                    <tr v-else v-for="(credito, index) in creditos.data" :key="credito.id">
                      <td>{{ index + creditos.from }}</td>
                      <td>{{ credito.id }}</td>
                      <td>{{ credito.cliente.persona.apenom }}</td>
                      <td>{{ credito.asesor.user?.name }}</td>
                      <td>{{ 'S/. ' + Number(credito.monto ?? 0).toFixed(2) }}</td>
                      <td>{{ credito.tipo }}</td>
                      <td>{{ credito.fecha_reg }}</td>
                      <td>{{ credito.fecha_venc }}</td>
                      <td>{{ credito.frecuencia }}</td>
                      <td>{{ credito.plazo }}</td>
                      <td>{{ credito.estado }}</td>

                      <td>
                        <div class="acciones-grid">
                          <!-- EDITAR -->
                          <button
                            class="btn btn-warning btn-sm"
                            v-if="credito.estado === 'PENDIENTE' || credito.estado === 'OBSERVADO'"
                            title="Editar"
                            @click.prevent="editar(credito.id)"
                          >
                            <i class="fas fa-edit"></i>
                          </button>

                          <!-- ELIMINAR -->
                          <button
                            class="btn btn-danger btn-sm"
                            v-if="credito.estado === 'PENDIENTE'"
                            title="Eliminar"
                            @click.prevent="eliminar(credito.id)"
                          >
                            <i class="fas fa-trash"></i>
                          </button>

                          <!-- ARCHIVOS (se deja igual que tú) -->
                          <button
                            class="btn btn-success btn-sm"
                            v-if="['PENDIENTE','EVALUACION','DESEMBOLSADO','FINALIZADO'].includes(credito.estado)"
                            title="Archivos"
                            @click.prevent="archivos(credito.id)"
                          >
                            <i class="fa-solid fa-file-pdf"></i>
                          </button>

                          <button
                            class="btn btn-primary btn-sm"
                            v-if="credito.estado === 'PENDIENTE'"
                            title="Evaluación"
                            @click.prevent="historiaEvaluacion(credito.id)"
                          >
                            <i class="fas fa-clipboard-check"></i>
                          </button>
                        </div>
                      </td>

                      <td>
                        <button
                          class="btn btn-success btn-sm"
                          title="Enviar a Revisión / Observar"
                          @click.prevent="pasarRevision(credito.id)"
                        >
                          <i class="fas fa-check"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-5 mb-1">
              Mostrando <b>{{ creditos.from }}</b> a <b>{{ creditos.to }}</b> de <b>{{ creditos.total }}</b> Registros
            </div>

            <div class="col-md-7 mb-1 text-right">
              <nav>
                <ul class="pagination">
                  <li v-if="creditos.current_page >= 2" class="page-item">
                    <a href="#" class="page-link" title="Primera Página" @click.prevent="cambiarPagina(1)">
                      <span><i class="fas fa-backward"></i></span>
                    </a>
                  </li>

                  <li v-if="creditos.current_page > 1" class="page-item">
                    <a
                      href="#"
                      class="page-link"
                      title="Página Anterior"
                      @click.prevent="cambiarPagina(creditos.current_page - 1)"
                    >
                      <span><i class="fas fa-angle-left"></i></span>
                    </a>
                  </li>

                  <li
                    v-for="page in pagesNumber"
                    class="page-item"
                    :key="page"
                    :class="[page == isActived() ? 'active' : '']"
                    :title="'Página ' + page"
                  >
                    <a href="#" class="page-link" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                  </li>

                  <li v-if="creditos.current_page < creditos.last_page" class="page-item">
                    <a
                      href="#"
                      class="page-link"
                      title="Página Siguiente"
                      @click.prevent="cambiarPagina(creditos.current_page + 1)"
                    >
                      <span><i class="fas fa-angle-right"></i></span>
                    </a>
                  </li>

                  <li v-if="creditos.current_page <= creditos.last_page - 1" class="page-item">
                    <a
                      href="#"
                      class="page-link"
                      title="Última Página"
                      @click.prevent="cambiarPagina(creditos.last_page)"
                    >
                      <span><i class="fas fa-step-forward"></i></span>
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <Prestamo :form="form" @cargar="listarCreditos" />
</template>
