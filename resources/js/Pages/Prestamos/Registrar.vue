<script setup>
import { computed, reactive, ref, watch, onMounted } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useHelper from '@/Helpers'
import useCliente from '@/Composables/Cliente.js'
import Prestamo from '@/Pages/Prestamos/Form.vue'

const { openModal, Toast, Swal } = useHelper()
const {
  clientesPorEstado,
  clientes,
  obtenerCliente,
  respuesta,
  cliente,
  errors
} = useCliente()

const q = ref('')

const formPrestamo = ref({
  id: '',
  cliente_id: '',
  cliente_apenom : '',
  asesor_id: '',
  aval_id: null,
  tipo: 'NUEVO',
  monto: '',
  origen_financiamiento_id: '',
  frecuencia: 'MENSUAL',
  plazo: '',
  fuenterecursos: '',
  tasainteres: '0.00',
  interes: '0.00',
  costomora: '0.00',
  total: '0.00',
  estadoCrud: 'nuevo',
  errors: {}
})

const abrirPrestamo = (c) => {
  formPrestamo.value.cliente_id = c.id
  formPrestamo.value.cliente_apenom = c.persona.apenom
  formPrestamo.value.estadoCrud = 'nuevo'
  // document.getElementById("prestamomodalLabel").innerHTML = 'Solicitar Prestamo'; // Se maneja en el componente Form.vue idealmente
  openModal('#prestamomodal')
}

const buscarCliente = async () => {
  await clientesPorEstado('REGISTRADO', q.value)
}

onMounted(() => {
  buscarCliente()
})
</script>

<template>
  <AppLayoutDefault title="Solicitud de Préstamo">
    <div class="page-content py-4">
      <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">Nueva Solicitud de Crédito</h3>
                <p class="text-muted small mb-0">Busque un cliente registrado para iniciar el proceso de evaluación crediticia</p>
            </div>
            <div class="col-auto">
                <div class="bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-bold">
                    <i class="fas fa-hand-holding-usd me-1"></i> Proceso de Colocación
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Buscador -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Buscador de Clientes</label>
                        <div class="input-group bg-light rounded-pill px-3 py-1 border-0 shadow-none">
                            <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                            <input v-model="q" type="text" class="form-control bg-transparent border-0 shadow-none" placeholder="DNI, RUC o Apellidos..." @keyup.enter="buscarCliente">
                            <button class="btn btn-primary rounded-pill px-3 ms-2 fw-bold small" @click="buscarCliente">BUSCAR</button>
                        </div>
                        <div class="form-text mt-3 small opacity-75">
                            <i class="fas fa-info-circle me-1"></i> Solo se muestran clientes con estado <b>REGISTRADO</b> que no tienen solicitudes pendientes.
                        </div>
                    </div>
                </div>

                <!-- Tips / Stats -->
                <div class="card border-0 bg-dark text-white shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i> Recordatorio</h6>
                        <p class="small opacity-75 mb-0">Asegúrese de que el cliente haya actualizado sus datos personales y de contacto en el Registro Maestro antes de proceder con una nueva solicitud.</p>
                    </div>
                </div>
            </div>

            <!-- Listado de Resultados -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h5 class="fw-bold text-dark mb-0">Clientes Aptos para Solicitud</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small text-uppercase fw-bold">
                                        <th class="ps-4 py-3">Documento</th>
                                        <th>Socio / Cliente</th>
                                        <th>Estado Actual</th>
                                        <th class="pe-4 text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!clientes.data?.length" class="text-center">
                                        <td colspan="4" class="py-5 text-muted">
                                            <i class="fas fa-user-clock fs-1 mb-3 d-block opacity-25"></i>
                                            No se encontraron clientes para la búsqueda actual.
                                        </td>
                                    </tr>
                                    <tr v-for="c in clientes.data" :key="c.id" class="transition-all">
                                        <td class="ps-4">
                                            <div class="fw-bold text-primary">{{ c.persona.dni }}</div>
                                            <div v-if="c.persona.ruc" class="small text-muted">RUC: {{ c.persona.ruc }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-uppercase">{{ c.persona.apenom }}</div>
                                            <div class="small text-muted"><i class="fas fa-phone-alt me-1 opacity-50"></i> {{ c.persona.celular }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3 py-1">
                                                {{ c.estado }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button class="btn btn-primary rounded-pill px-4 fw-bold small shadow-sm" @click="abrirPrestamo(c)">
                                                <i class="fas fa-file-invoice-dollar me-2"></i> SOLICITAR
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
    <Prestamo :form="formPrestamo" @onListar="buscarCliente" />
  </AppLayoutDefault>
</template>

<style scoped>
.transition-all { transition: all 0.2s ease-in-out; }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
</style>
