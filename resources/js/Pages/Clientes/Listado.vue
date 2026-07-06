<script setup>
import { ref, onMounted, nextTick } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useCliente from '@/Composables/Cliente.js'
import useHelper from '@/Helpers'
import useDatosSession from '@/Composables/session.js'
import useAsesor from '@/Composables/Asesor.js'
import FormCliente from './FormCliente.vue'

const { openModal, Toast, Swal } = useHelper()
const {
  obtenerClientes,
  clientes,
  obtenerCliente,
  eliminarCliente,
  respuesta,
  cliente,
  errors,
  asignarAsesorMasivo
} = useCliente()

const { role } = useDatosSession()
const { listaAsesores, asesores } = useAsesor()

const dato = ref({
    page: 1,
    buscar: '',
    paginacion: 10
});
const modalTitle = ref('')
const loading = ref(false)

const seleccionados = ref([])
const seleccionarTodos = ref(false)

const toggleSeleccionarTodos = () => {
    if (seleccionarTodos.value) {
        seleccionados.value = clientes.value.data.map(c => c.id)
    } else {
        seleccionados.value = []
    }
}

const modalAsignacion = ref({
    asesor_id: '',
    convigentes: false
})

const abrirModalAsignacion = () => {
    listaAsesores()
    modalAsignacion.value = { asesor_id: '', convigentes: false }
    openModal('#modalasignar')
}

const guardarAsignacion = async () => {
    if (!modalAsignacion.value.asesor_id) {
        Toast.fire({ icon: 'warning', title: 'Seleccione un asesor' })
        return
    }
    
    let data = {
        asesor_id: modalAsignacion.value.asesor_id,
        selected: seleccionados.value.map(id => ({id: id})),
        convigentes: modalAsignacion.value.convigentes
    }
    
    await asignarAsesorMasivo(data)
    if (respuesta.value?.ok == 1) {
        Toast.fire({ icon: 'success', title: 'Clientes asignados correctamente' })
        useHelper().closeModal('#modalasignar')
        seleccionados.value = []
        seleccionarTodos.value = false
        listarClientes(dato.value.page)
    }
}

const NEGOCIO_DEFAULT = () => ({
  id: '', razonsocial: '', ruc: '', celular: '', actividad_negocio_id: '',
  detalle_actividad_id: '', inicioactividad: '', direccion: '',
})
const REFERENTE_DEFAULT = () => ({
  id: '', dni: '', ape_pat: '', ape_mat: '', primernombre: '',
  otrosnombres: '', celular: '', parentesco: '', direccion: '',
})
const FORM_DEFAULT = () => ({
  id: '', dni: '', ruc: '', celular: '', celular2: '', email: '',
  ape_pat: '', ape_mat: '', primernombre: '', otrosnombres: '',
  fecha_nac: '', genero: 'M', estado_civil: 'SOLTERO', ubigeo_nac: '', ubigeo_dom: '',
  profesion: '', grado_instr: '', origen_labor: 'INDEPENDIENTE', ocupacion: '',
  institucion_lab: '', direccion: '', latitud_longitud: '',
  negocio: NEGOCIO_DEFAULT(), referente: REFERENTE_DEFAULT(),
  estado: 'ACTIVO', fecha_reg: '', hora_reg: '', estadoCrud: 'editar', errors: {}
})

const form = ref(FORM_DEFAULT())

const limpiar = () => {
  Object.assign(form.value, FORM_DEFAULT())
  errors.value = []
}

const obtenerDatos = async (id) => {
  await obtenerCliente(id)
  const c = cliente.value
  if (!c) return
  const p = c.persona ?? {}
  const n = c.negocio ?? {}
  const r = c.referente ?? {}

  form.value.id = c.id ?? ''
  form.value.estado = c.estado ?? 'ACTIVO'
  form.value.dni = p.dni ?? ''
  form.value.ruc = p.ruc ?? ''
  form.value.celular = p.celular ?? ''
  form.value.celular2 = p.celular2 ?? ''
  form.value.email = p.email ?? ''
  form.value.ape_pat = p.ape_pat ?? ''
  form.value.ape_mat = p.ape_mat ?? ''
  form.value.primernombre = p.primernombre ?? ''
  form.value.otrosnombres = p.otrosnombres ?? ''
  form.value.fecha_nac = p.fecha_nac ?? ''
  form.value.genero = p.genero ?? 'M'
  form.value.estado_civil = p.estado_civil ?? 'SOLTERO'
  form.value.ubigeo_nac = p.ubigeo_nac ?? ''
  form.value.ubigeo_dom = p.ubigeo_dom ?? ''
  form.value.profesion = p.profesion ?? ''
  form.value.grado_instr = p.grado_instr ?? ''
  form.value.origen_labor = p.origen_labor ?? 'INDEPENDIENTE'
  form.value.ocupacion = p.ocupacion ?? ''
  form.value.institucion_lab = p.institucion_lab ?? ''
  form.value.direccion = p.direccion ?? ''
  form.value.latitud_longitud = p.latitud_longitud ?? ''
  
  form.value.negocio = { ...NEGOCIO_DEFAULT(), ...n }
  form.value.referente = { 
    ...REFERENTE_DEFAULT(), 
    ...r, 
    parentesco: c.referente_parentesco ?? '' 
  }
}

const editar = async (id) => {
  limpiar()
  await obtenerDatos(id)
  form.value.estadoCrud = 'editar'
  modalTitle.value = 'Editar Cliente'
  openModal('#modalcliente')
}

const nuevoCliente = () => {
    limpiar()
    form.value.estadoCrud = 'nuevo'
    modalTitle.value = 'Registrar Nuevo Cliente'
    openModal('#modalcliente')
}

const eliminar = (id) => {
  Swal.fire({
    title: '¿Confirmar eliminación?',
    text: "El cliente pasará a estado inactivo.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light' }
  }).then((result) => {
    if (result.isConfirmed) ejecutaEliminar(id)
  })
}

const ejecutaEliminar = async (id) => {
  await eliminarCliente(id)
  if (respuesta.value?.ok == 1) {
    Toast.fire({ icon: 'success', title: 'Cliente eliminado correctamente' })
    listarClientes()
  }
}

const listarClientes = async(page = 1) => {
    dato.value.page = page
    loading.value = true
    await obtenerClientes(dato.value)
    loading.value = false
}

onMounted(() => {
  listarClientes()
})
</script>

<template>
  <AppLayoutDefault title="Cartera de Clientes">
    <div class="page-content py-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="row mb-4 align-items-center">
                <div class="col">
                    <h3 class="fw-bold text-dark mb-1">Listado de Clientes</h3>
                    <p class="text-muted small mb-0">Gestión centralizada de socios e información de contacto</p>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <button class="btn btn-warning shadow-sm rounded-pill px-4 py-2 fw-bold me-2 transition-all" 
                            v-if="seleccionados.length > 0" 
                            @click="abrirModalAsignacion">
                        <i class="fas fa-exchange-alt me-2"></i> Asignar Asesor ({{ seleccionados.length }})
                    </button>
                    <button class="btn btn-primary shadow-sm rounded-pill px-4 py-2 fw-bold" @click="nuevoCliente">
                        <i class="fas fa-user-plus me-2"></i> Nuevo Cliente
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group bg-light rounded-pill px-3 py-1">
                                <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                                <input v-model="dato.buscar" type="text" class="form-control bg-transparent border-0" placeholder="Buscar por DNI, RUC o Apellidos..." @keyup.enter="listarClientes(1)">
                                <button class="btn btn-primary rounded-pill px-4 ms-2" @click="listarClientes(1)">FILTRAR</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-end h-100">
                                <span class="text-muted small me-2 text-uppercase fw-bold">Mostrar:</span>
                                <select v-model="dato.paginacion" class="form-select form-select-sm border-0 bg-light rounded-pill w-auto" @change="listarClientes(1)">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small text-uppercase fw-bold">
                                    <th class="ps-4 py-3" style="width: 80px;">
                                        <div class="d-flex align-items-center gap-2" v-if="['SUPER USUARIO', 'ADMINISTRADOR', 'GERENTE', 'GERENTE AGENCIA'].includes(role?.nombre)">
                                            <input class="form-check-input mt-0" type="checkbox" v-model="seleccionarTodos" @change="toggleSeleccionarTodos">
                                            <span>#</span>
                                        </div>
                                        <span v-else>#</span>
                                    </th>
                                    <th>Documento</th>
                                    <th>Nombres y Apellidos</th>
                                    <th>Contacto</th>
                                    <th>Estado</th>
                                    <th class="pe-4 text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="loading" class="text-center py-5">
                                    <td colspan="6" class="py-5">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <div class="mt-2 text-muted">Cargando clientes...</div>
                                    </td>
                                </tr>
                                <tr v-else-if="!clientes.data?.length" class="text-center py-5">
                                    <td colspan="6" class="py-5 text-muted">
                                        <i class="fas fa-users-slash fs-1 mb-3 d-block opacity-25"></i>
                                        No se encontraron resultados.
                                    </td>
                                </tr>
                                <tr v-for="(c, index) in clientes.data" :key="c.id" class="transition-all" :class="{'bg-primary-subtle': seleccionados.includes(c.id)}">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <input v-if="['SUPER USUARIO', 'ADMINISTRADOR', 'GERENTE', 'GERENTE AGENCIA'].includes(role?.nombre)" 
                                                   class="form-check-input mt-0" type="checkbox" :value="c.id" v-model="seleccionados">
                                            <span class="small text-muted">{{ index + clientes.from }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ c.persona.dni }}</div>
                                        <div class="small text-muted" v-if="c.persona.ruc">{{ c.persona.ruc }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-uppercase">{{ c.persona.apenom }}</div>
                                        <div class="small text-muted"><i class="fas fa-calendar-alt me-1"></i> Reg: {{ c.fecha_reg }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><i class="fas fa-phone-alt text-success me-1 small"></i> {{ c.persona.celular }}</span>
                                            <span class="small text-muted"><i class="fas fa-envelope me-1 small"></i> {{ c.persona.email || '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span :class="['badge rounded-pill px-3 py-1', c.estado === 'ACTIVO' ? 'bg-success-subtle text-success border border-success' : 'bg-secondary-subtle text-secondary border border-secondary']">
                                            {{ c.estado }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            <button class="btn btn-white btn-sm px-3" title="Historial" @click="$router.push('/clientes/historial-del-cliente?id=' + c.id)">
                                                <i class="fas fa-history text-info"></i>
                                            </button>
                                            <button class="btn btn-white btn-sm px-3" title="Editar" @click="editar(c.id)">
                                                <i class="fas fa-edit text-warning"></i>
                                            </button>
                                            <button class="btn btn-white btn-sm px-3" title="Eliminar" @click="eliminar(c.id)">
                                                <i class="fas fa-trash text-danger"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Pagination Footer -->
                <div class="card-footer bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando <b>{{ clientes.from }}</b> a <b>{{ clientes.to }}</b> de <b>{{ clientes.total }}</b> registros
                        </div>
                        <nav v-if="clientes.last_page > 1">
                            <ul class="pagination pagination-rounded mb-0">
                                <li class="page-item" :class="{ disabled: clientes.current_page === 1 }">
                                    <button class="page-link border-0 shadow-none bg-light" @click="listarClientes(clientes.current_page - 1)">
                                        <i class="fas fa-chevron-left small"></i>
                                    </button>
                                </li>
                                <li v-for="p in clientes.last_page" :key="p" class="page-item" :class="{ active: p === clientes.current_page }">
                                    <button class="page-link border-0 shadow-none" :class="p === clientes.current_page ? 'btn-primary' : 'bg-transparent text-dark'" @click="listarClientes(p)">{{ p }}</button>
                                </li>
                                <li class="page-item" :class="{ disabled: clientes.current_page === clientes.last_page }">
                                    <button class="page-link border-0 shadow-none bg-light" @click="listarClientes(clientes.current_page + 1)">
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

    <FormCliente
      :form="form"
      :currentPage="clientes.current_page"
      :modalTitle="modalTitle"
      @onListar="listarClientes"
    />

    <!-- Modal Asignar Asesor -->
    <teleport to="body">
        <div class="modal fade" id="modalasignar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Asignar Asesor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Seleccione Asesor <span class="text-danger">*</span></label>

<select
    class="form-select"
    style="color:#212529;background:white;"
    v-model="modalAsignacion.asesor_id"
>
    <option value="">-- Seleccionar --</option>

    <option
        v-for="a in asesores"
        :key="a.id"
        :value="a.user.name"
        style="color:#212529;background:white;"
    >
        {{ a.user.name }}
    </option>
</select>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="checkvigentes" v-model="modalAsignacion.convigentes">
                            <label class="form-check-label ms-2 text-dark" for="checkvigentes">
                                ¿Reasignar también todos los créditos vigentes de estos clientes?
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4" @click="guardarAsignacion">Guardar Asignación</button>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
  </AppLayoutDefault>
</template>

<style scoped>
.transition-all { transition: all 0.2s ease-in-out; }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
.btn-white { background: #fff; border: 1px solid #f1f1f1; }
.btn-white:hover { background: #f8f9fa; }
.pagination-rounded .page-link { border-radius: 50% !important; margin: 0 3px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; }
</style>
