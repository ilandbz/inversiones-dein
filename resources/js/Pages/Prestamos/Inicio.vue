<script setup>
import { ref, onMounted, toRefs } from 'vue';
import useCredito from '@/Composables/Credito';
import Prestamo from '@/Pages/Prestamos/Form.vue'
import Evaluacion from '@/Pages/Evaluacion/Evaluacion.vue'
import FormArchivos from '@/Pages/Evaluacion/FormArchivos.vue'
import useHelper from '@/Helpers'; 
const { obtenerCreditos, creditos, credito, obtenerCredito, eliminarCredito, errors, respuesta } = useCredito();
const { openModal, Toast, Swal, formatoFecha } = useHelper();

const selectedId = ref(null);
const selectedClienteNombre = ref('');

const dato = ref({
    page: '',
    buscar: '',
    paginacion: 10,
    estado: '',
});

const form = ref({
    id: '',
    cliente_id: '',
    cliente_apenom : '',
    asesor_id: '',
    aval_id: '',
    estado: 'ACTIVO',
    fecha_reg: formatoFecha(null, "YYYY-MM-DD"),
    fecha_venc: '',
    tipo: '',
    monto: 0.00,
    origen_financiamiento_id: '',
    frecuencia: 'DIARIO',
    plazo: 30,
    tasainteres: 0.09,
    total: 0.00,
    costomora: 0.00,
    created_at: '',
    updated_at: '',
    estadoCrud: '',
    errors: []
});

const limpiar = () => {
    form.value = {
        id: '',
        cliente_id: '',
        asesor_id: '',
        aval_id: '',
        estado: 'ACTIVO',
        fecha_reg: formatoFecha(null, "YYYY-MM-DD"),
        fecha_venc: '',
        tipo: '',
        monto: 0.00,
        origen_financiamiento_id: '',
        frecuencia: 'DIARIO',
        plazo: 30,
        tasainteres: 0.09,
        total: 0.00,
        costomora: 0.00,
        created_at: '',
        updated_at: '',
        estadoCrud: '',
        errors: []
    }
}

const obtenerDatos = async (id) => {
    await obtenerCredito(id); // asumo que llena credito.value

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

        form.value.tasainteres = Number(credito.value.tasainteres ?? 0.09);
        form.value.total = Number(credito.value.total ?? 0);
        form.value.costomora = Number(credito.value.costomora ?? 0);

        form.value.created_at = credito.value.created_at ?? '';
        form.value.updated_at = credito.value.updated_at ?? '';

    }
};

const editar = async(id) => {
    limpiar();
    await obtenerDatos(id)
    form.value.estadoCrud = 'editar'

    document.getElementById("prestamomodalLabel").innerHTML = 'Editar credito';
    openModal('#prestamomodal')
}

const archivos = async (id) => {
    await obtenerDatos(id);
    selectedId.value = id;
    selectedClienteNombre.value = form.value.cliente_apenom;
    openModal('#archivosModal');
};
const evaluacion = async(id) => {
    await obtenerDatos(id)
    formBalance.value.cliente_apenom = form.value.cliente_apenom;
    formBalance.value.credito_id = form.value.id;
    document.getElementById("evaluacionModalLabel").innerHTML = 'Evaluación Riesgo Crediticio';
    openModal('#evaluacionModal')
}
const formBalance = ref({
    credito_id : '',
    cliente_apenom : '',
    total_activo : 0,
    total_pasivo : 0,
    patrimonio : 0,
    paspatrimonio: 0,
    captrabajo: 0,
    fecha: '',
    activocaja: 0,
    activobancos: 0,
    activoctascobrar: 0,
    activoinventarios: 0,
    detinventarios: {
        inv_materiales: 0,
        inv_prodproc: 0,
        inv_prodtermi: 0,
    },
    totalacorriente: 0, 
    activomueble: 0,
    muebles: [],
    activootrosact: 0,
    activodepre: 0,
    totalancorriente: 0,
    pasivodeudaprove: 0,
    pasivodeudaent: 0,
    deudas: [],
    totalpcorriente: 0,
    pasivolargop: 0,
    otrascuentaspagar: 0,          
    totalpncorriente: 0,
    estadoCrud: '',
    errors: []
})

const listarCreditos = async(page=1) => {
    dato.value.page= page
    await obtenerCreditos(dato.value)
}
const eliminar = (id) => {
    Swal.fire({
        title: '¿Estás seguro de Eliminar?',
        text: "Credito",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, Eliminalo!'
    }).then((result) => {
        if (result.isConfirmed) {
            elimina(id)
        }
    })
}
const elimina = async(id) => {
    await eliminarCredito(id)
    form.value.errors = []
    if(errors.value)
    {
        form.value.errors = errors.value
    }
    if(respuesta.value.ok==1){
        form.value.errors = []
        Toast.fire({icon:'success', title:respuesta.value.mensaje})
        listarCreditos(creditos.value.current_page)
    }
}
// PAGINACION
const isActived = () => {
    return creditos.value.current_page
}
const offset = 2;

const buscar = () => {
    listarCreditos()
}
const cambiarPaginacion = () => {
    listarCreditos()
}
const cambiarPagina =(pagina) => {
    listarCreditos(pagina)
}
const pagesNumber = () => {
    if(!creditos.value.to){
        return []
    }
    let from = creditos.value.current_page - offset
    if(from < 1) from = 1
    let to = from + (offset*2)
    if( to >= creditos.value.last_page) to = creditos.value.last_page
    let pagesArray = []
    while(from <= to) {
        pagesArray.push(from)
        from ++
    }
    return pagesArray
}
onMounted(() => {
    listarCreditos()
});
</script>
<style scoped>
.acciones-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 4px; /* separación mínima */
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
                    <h6 class="card-title">
                        Gestion de Creditos
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-1">
                            <div class="input-group mb-1">
                                <span class="input-group-text" id="basic-addon1">Mostrar</span>
                                <select class="form-select"  v-model="dato.paginacion" @change="cambiarPaginacion">
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
                                <input class="form-control" placeholder="Ingrese nombre, código" type="text" v-model="dato.buscar"
                                    @change="buscar" />
                            </div>
                        </div>
                        <div class="col-md-4 mb-1">
                            <nav>
                                <ul class="pagination">
                                    <li v-if="creditos.current_page >= 2" class="page-item">
                                        <a href="#" aria-label="Previous" class="page-link"
                                            title="Primera Página"
                                            @click.prevent="cambiarPagina(1)">
                                            <span><i class="fas fa-backward"></i></span>
                                        </a>
                                    </li>
                                    <li v-if="creditos.current_page > 1" class="page-item">
                                        <a href="#" aria-label="Previous" class="page-link"
                                            title="Página Anterior"
                                            @click.prevent="cambiarPagina(creditos.current_page - 1)">
                                            <span><i class="fas fa-angle-left"></i></span>
                                        </a>
                                    </li>
                                    <li v-for="page in pagesNumber()" class="page-item"
                                        :key="page"
                                        :class="[ page == isActived() ? 'active' : '']"
                                        :title="'Página '+ page">
                                        <a href="#" class="page-link"
                                            @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                    </li>
                                    <li v-if="creditos.current_page < creditos.last_page" class="page-item">
                                        <a href="#" aria-label="Next" class="page-link"
                                            title="Página Siguiente"
                                            @click.prevent="cambiarPagina(creditos.current_page + 1)">
                                            <span><i class="fas fa-angle-right"></i></span>
                                        </a>
                                    </li>
                                        <li v-if="creditos.current_page <= creditos.last_page-1" class="page-item">
                                        <a href="#" aria-label="Next" class="page-link"
                                            @click.prevent="cambiarPagina(creditos.last_page)"
                                            title="Última Página">
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
                                        <th colspan="12" class="text-center">Créditos</th>
                                    </tr>
                                    <tr>
                                        <th>#</th>
                                        <th>Cod</th>
                                        <th>Cliente</th>
                                        <th>Asesor</th>
                                        <th>Aval ID</th>
                                        <th>Monto</th>
                                        <th>Tipo</th>
                                        <th>Fecha Reg</th>
                                        <th>Fecha Venc</th>
                                        <th>Frecuencia</th>
                                        <th>Plazo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <tr v-if="creditos.total == 0">
                                        <td class="text-danger text-center" colspan="13">
                                        -- Datos No Registrados - Tabla Vacía --
                                        </td>
                                    </tr>

                                    <tr v-else v-for="(credito,index) in creditos.data" :key="credito.id">
                                        <td>{{ index + creditos.from }}</td>
                                        <td>{{ credito.id }}</td>

                                        <td>{{ credito.cliente.persona.apenom }}</td>
                                        <td>{{ credito.asesor.user?.name }}</td>
                                        <td>{{ credito.aval_id ?? '---' }}</td>

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

                                            <button
                                                class="btn btn-primary btn-sm"
                                                v-if="credito.estado === 'PENDIENTE'"
                                                title="Evaluación"
                                                @click.prevent="evaluacion(credito.id)"
                                            >
                                                <i class="fas fa-clipboard-check"></i>
                                            </button>

                                            <button
                                                class="btn btn-success btn-sm"
                                                title="Archivos"
                                                @click.prevent="archivos(credito.id)"
                                            >
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </button>
                                            </div>

                                        </td>
                                    </tr>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5 mb-1">
                            Mostrando <b>{{creditos.from}}</b> a <b>{{ creditos.to }}</b> de <b>{{ creditos.total}}</b> Registros
                        </div>
                        <div class="col-md-7 mb-1 text-right">
                            <nav>
                                <ul class="pagination">
                                    <li v-if="creditos.current_page >= 2" class="page-item">
                                        <a href="#" aria-label="Previous" class="page-link"
                                            title="Primera Página"
                                            @click.prevent="cambiarPagina(1)">
                                            <span><i class="fas fa-backward"></i></span>
                                        </a>
                                    </li>
                                    <li v-if="creditos.current_page > 1" class="page-item">
                                        <a href="#" aria-label="Previous" class="page-link"
                                            title="Página Anterior"
                                            @click.prevent="cambiarPagina(creditos.current_page - 1)">

                                            <span><i class="fas fa-angle-left"></i></span>
                                        </a>
                                    </li>
                                    <li v-for="page in pagesNumber()" class="page-item"
                                        :key="page"
                                        :class="[ page == isActived() ? 'active' : '']"
                                        :title="'Página '+ page">
                                        <a href="#" class="page-link"
                                            @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                    </li>
                                    <li v-if="creditos.current_page < creditos.last_page" class="page-item">
                                        <a href="#" aria-label="Next" class="page-link"
                                            title="Página Siguiente"
                                            @click.prevent="cambiarPagina(creditos.current_page + 1)">
                                            <span><i class="fas fa-angle-right"></i></span>
                                        </a>
                                    </li>
                                        <li v-if="creditos.current_page <= creditos.last_page-1" class="page-item">
                                        <a href="#" aria-label="Next" class="page-link"
                                            @click.prevent="cambiarPagina(creditos.last_page)"
                                            title="Última Página">
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
    <Evaluacion :form="formBalance" />
    <FormArchivos :creditoId="selectedId" :clienteNombre="selectedClienteNombre" :form="form" />
</template>