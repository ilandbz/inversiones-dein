<script setup>
import { computed, reactive, ref, watch, onMounted } from 'vue'
import useCliente from '@/Composables/Cliente.js'

const {
  clientesPorEstado,
  clientes,
  obtenerCliente,
  respuesta,
  cliente,
  errors
} = useCliente()


const q=ref('')

const form = ref({
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


const buscarCliente = async () => {
  await clientesPorEstado('REGISTRADO',q.value)
  console.log(q.value)
}

onMounted(() => {
  buscarCliente()
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
            </div>

            <div class="card-body">
              <label class="form-label">Buscar cliente (DNI/RUC o nombres)</label>

              <div class="position-relative">
                <input
                  v-model="q"
                  class="form-control"
                  placeholder="Ej: 12345678 o Juan Pérez"
                  @change="buscarCliente()"
                />


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
