<script setup>
import { ref, onMounted, nextTick } from 'vue'
import useCliente from '@/Composables/Cliente.js'
import { defineTitle } from '@/Helpers'
import useHelper from '@/Helpers'
import FormCliente from './FormCliente.vue'

const { openModal, Toast, Swal } = useHelper()

const {
  obtenerClientes,
  clientes,
  obtenerCliente,
  eliminarCliente,
  respuesta,
  cliente,
  errors
} = useCliente()

const dato = ref({
    page:'',
    buscar:'',
    paginacion: 10
});
const modalTitle = ref('')
const lastOpener = ref(null)

const NEGOCIO_DEFAULT = () => ({
  id: '',
  razonsocial: '',
  ruc: '',
  celular: '',
  actividad_negocio_id: '',
  detalle_actividad_id: '',
  inicioactividad: '',
  direccion: '',
})
const REFERENTE_DEFAULT = () => ({
  id: '',
  dni: '',
  ape_pat: '',
  ape_mat: '',
  primernombre: '',
  otrosnombres: '',
  celular: '',
  parentesco: '',
  direccion: '',
})

const FORM_DEFAULT = () => ({
  id: '',
  dni: '',
  ruc: '',
  celular: '',
  celular2: '',
  email: '',

  ape_pat: '',
  ape_mat: '',
  primernombre: '',
  otrosnombres: '',

  fecha_nac: '',
  genero: 'M',
  estado_civil: 'SOLTERO',
  ubigeo_nac: '',
  ubigeo_dom: '',

  profesion: '',
  grado_instr: '',
  origen_labor: 'INDEPENDIENTE',
  ocupacion: '',
  institucion_lab: '',

  direccion: '',
  latitud_longitud: '',

  negocio: NEGOCIO_DEFAULT(),
  referente: REFERENTE_DEFAULT(),

  estado: 'ACTIVO',
  fecha_reg: '',
  hora_reg: '',
  estadoCrud: 'editar',
  errors: {}
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

  // IDs / estado cliente
  form.value.id = c.id ?? ''
  form.value.estado = c.estado ?? 'ACTIVO'
  form.value.fecha_reg = c.fecha_reg ?? ''
  form.value.hora_reg = c.hora_reg ?? ''

  // PERSONA (OJO: todo viene aquí)
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

  form.value.negocio = {
    ...NEGOCIO_DEFAULT(),
    ...(n ?? {}),
    detalle_actividad_id: n?.detalle_actividad_id ?? '',
    actividad_negocio_id: n?.detalle_actividad?.actividad_negocio_id ?? '',
  }

  // Si tu API NO retorna objeto referente, no podrás llenar estos campos aquí
  form.value.referente = c.referente ?? REFERENTE_DEFAULT()

  // Si lo único que viene es parentesco:
  form.value.referente.parentesco = c.referente_parentesco ?? form.value.referente.parentesco
}


const editar = async (id, e) => {
  lastOpener.value = e.currentTarget
  limpiar()
  await obtenerDatos(id)
  form.value.estadoCrud = 'editar'
  modalTitle.value = 'Editar Cliente'
  await nextTick()
  openModal('#modalcliente')
}

const eliminar = (id) => {
  Swal.fire({
    title: '¿Estás seguro de Eliminar?',
    text: 'Cliente',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Elimínalo!'
  }).then((result) => {
    if (result.isConfirmed) {
      elimina(id)
    }
  })
}

const elimina = async (id) => {
  await eliminarCliente(id)

  form.value.errors = {}
  if (errors.value) {
    form.value.errors = errors.value
  }

  if (respuesta.value?.ok == 1) {
    form.value.errors = {}
    Toast.fire({ icon: 'success', title: respuesta.value.mensaje })
    listarClientes()
  }
}

const offset = 2;

const verFoto=(registro)=>{
  limpiar()
  cliente.value.dni = registro.persona.dni;
  cliente.value.foto = '/storage/fotos/clientes/'+registro.persona.dni+'.webp';
  cliente.value.apenom = registro.persona.apenom;  
  document.getElementById("fotoModalLabel").innerHTML = 'Imagen de Cliente';
  openModal('#fotoModal') 
}

const listarClientes = async(page=1) => {
    dato.value.page= page
    await obtenerClientes(dato.value)
}

onMounted(() => {
  defineTitle('Clientes')
  listarClientes()
})


</script>

<template>
  <div class="card card-primary card-outline">
    <div class="card-header">
      <h6 class="card-title">Listado de Clientes</h6>
    </div>

    <div class="card-body">
      <div class="row">

        <div class="col-md-10 mb-1">
          <div class="input-group mb-1">
            <!-- aquí puedes poner filtro/buscador si deseas -->
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12 mb-1">
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
                <tr v-if="clientes.total == 0">
                  <td class="text-danger text-center" colspan="8">
                    -- Datos No Registrados - Tabla Vacía --
                  </td>
                </tr>

                <tr v-else v-for="(c, index) in clientes.data" :key="c.id">
                  <td>{{ index + clientes.from }}</td>
                  <td>{{ c.persona.dni }}</td>
                  <td>{{ c.persona.ruc }}</td>
                  <td>{{ c.persona.apenom }}</td>
                  <td>{{ c.persona.celular }}</td>
                  <td>{{ c.persona.email }}</td>
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
  </div>

  <FormCliente
    :form="form"
    :currentPage="clientes.current_page"
    :modalTitle="modalTitle"
    @onListar="listarClientes"
  />
</template>
