<script setup>
import { computed, reactive, ref, watch, onMounted } from 'vue'
import useCliente from '@/Composables/Cliente.js'

const {
  clientesPorEstado,
  clientes,
  obtenerCliente,
  eliminarCliente,
  respuesta,
  cliente,
  errors
} = useCliente()

const form = reactive({
  cliente_id: null,
  agencia_id: '',
  asesor_id: '',
  estado: 'ACTIVO',
  fecha_reg: new Date().toISOString().slice(0, 10),
  tipo: 'NUEVO',
  monto: '',
  producto: '',
  frecuencia: 'MENSUAL',
  plazo: '',
  medioorigen: '',
  dondepagara: 'NINGUNO',
  fuenterecursos: '',
  tasainteres: '0.000',
  total: '0.00',
  costomora: '0.00',
  mencion: 'PRINCIPAL',
  fecha_venc: ''
})

const q = ref('')
const isSearching = ref(false)
const selectedCliente = ref(null)

const clientesMock = [
  { id: 1, documento: '12345678', nombres: 'Juan', apellidos: 'Pérez', telefono: '999111222' },
  { id: 2, documento: '10456789012', nombres: 'María', apellidos: 'López', telefono: '988777666' },
  { id: 3, documento: '87654321', nombres: 'Carlos', apellidos: 'Díaz', telefono: '977555444' }
]

const results = computed(() => {
  const term = q.value.trim().toLowerCase()
  if (!term) return []
  return clientesMock.filter(c => {
    const full = `${c.nombres} ${c.apellidos}`.toLowerCase()
    return c.documento.includes(term) || full.includes(term)
  }).slice(0, 8)
})

const selectCliente = (c) => {
  selectedCliente.value = c
  form.cliente_id = c.id
  q.value = `${c.documento} - ${c.nombres} ${c.apellidos}`
}

const clearCliente = () => {
  selectedCliente.value = null
  form.cliente_id = null
  q.value = ''
}


const totalPreview = computed(() => {
  const monto = parseFloat(form.monto || 0)
  const tasa = parseFloat(form.tasainteres || 0) / 100
  const plazo = parseInt(form.plazo || 0)
  if (!monto || !plazo) return 0
  return monto + (monto * tasa * plazo)
})

watch(totalPreview, (v) => {
  form.total = Number.isFinite(v) ? v.toFixed(2) : '0.00'
})

/**
 * ====== SUBMIT ======
 * Lo dejo listo para que luego lo conectes al backend.
 */
const submit = () => {
  if (!form.cliente_id) {
    alert('Selecciona un cliente existente.')
    return
  }

  // Luego:
  // router.post('/prestamos', form)
  console.log('Enviar préstamo:', JSON.parse(JSON.stringify(form)))
  alert('OK (demo). Luego lo conectamos al POST real.')
}
onMounted(async () => {
  await clientesPorEstado('REGISTRADO')
})
</script>

<template>
    <div class="page-heading">
      <h3>Préstamos</h3>
      <p class="text-subtitle text-muted">Registro de préstamo para cliente existente</p>
    </div>

    <div class="page-content">
      <section class="row">
        <div class="col-12 col-lg-4">
          <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <h4 class="card-title mb-0">Cliente</h4>
              <button v-if="selectedCliente" class="btn btn-light btn-sm" type="button" @click="clearCliente">
                <i class="bi bi-x-circle me-1"></i> Cambiar cliente
              </button>
            </div>

            <div class="card-body">
              <label class="form-label">Buscar cliente (DNI/RUC o nombres)</label>

              <div class="position-relative">
                <input
                  v-model="q"
                  class="form-control"
                  placeholder="Ej: 12345678 o Juan Pérez"
                  :disabled="!!selectedCliente"
                />

                <div
                  v-if="!selectedCliente && q.trim() && results.length"
                  class="list-group position-absolute w-100 shadow"
                  style="z-index: 50; max-height: 260px; overflow: auto;"
                >
                  <button
                    v-for="c in results"
                    :key="c.id"
                    type="button"
                    class="list-group-item list-group-item-action"
                    @click="selectCliente(c)"
                  >
                    <div class="d-flex justify-content-between">
                      <div>
                        <div class="fw-semibold">{{ c.nombres }} {{ c.apellidos }}</div>
                        <div class="small text-muted">{{ c.documento }} · {{ c.telefono }}</div>
                      </div>
                      <div class="text-muted small d-flex align-items-center">
                        Seleccionar <i class="bi bi-chevron-right ms-2"></i>
                      </div>
                    </div>
                  </button>
                </div>
              </div>

              <div v-if="!selectedCliente" class="form-text mt-2">
                Selecciona un cliente para continuar.
              </div>

              <div v-if="selectedCliente" class="alert alert-light mt-3 mb-0">
                <div class="d-flex align-items-start gap-3">
                  <i class="bi bi-person-badge fs-4"></i>
                  <div>
                    <div class="fw-semibold">
                      {{ selectedCliente.nombres }} {{ selectedCliente.apellidos }}
                    </div>
                    <div class="text-muted small">
                      Doc: {{ selectedCliente.documento }} · Tel: {{ selectedCliente.telefono }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="row">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Datos del préstamo</h4>
            </div>

            <div class="card-body">
              <div class="col-12">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover table-sm table-striped">
                    <thead>
                      <tr>
                        <th colspan="8" class="text-center">Clientes</th>
                      </tr>
                      <tr>
                        <th>#</th>
                        <th>DNI</th>
                        <th>RUC</th>
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>

                    <tbody>
                      <tr v-if="clientes.length == 0">
                        <td class="text-danger text-center" colspan="8">
                          -- Datos No Registrados - Tabla Vacía --
                        </td>
                      </tr>

                      <tr v-else v-for="(c, index) in clientes" :key="c.id">
                        <td>{{ index + 1 }}</td>
                        <td>{{ c.dni }}</td>
                        <td>{{ c.ruc }}</td>
                        <td>{{ c.apenom }}</td>
                        <td>{{ c.celular }}</td>
                        <td>{{ c.email }}</td>
                        <td>
                          <span class="badge" :class="c.estado === 'ACTIVO' ? 'bg-success' : 'bg-secondary'">
                            {{ c.estado }}
                          </span>
                        </td>
                        <td>
                          <div class="btn-group">
                            <button class="btn btn-warning btn-sm" title="Editar" @click.prevent="editar(c.id, $event)">
                              <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" title="Enviar a Papelera" @click.prevent="eliminar(c.id)">
                              <i class="fas fa-trash"></i>
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
      </section>
    </div>
</template>
