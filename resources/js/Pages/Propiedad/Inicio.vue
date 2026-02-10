<script setup>
    import { ref, onMounted, nextTick } from 'vue'
    import usePropiedad from '@/Composables/Propiedad.js'
    import FormPropiedad from './Form.vue'
    import { defineTitle } from '@/Helpers';
    import useHelper from '@/Helpers';
    const { openModal, Toast, Swal } = useHelper();


    const { listaPropiedades, propiedades, obtenerPropiedad,
        eliminarPropiedad, respuesta, propiedad, errors } = usePropiedad()

    const modalTitle = ref('')
    const lastOpener = ref(null)

    const form = ref({
        id: '',
        nombre: '',
        estadoCrud:'',
        errors:[]
    })
    const limpiar = ()=> {
        form.value.id='',
        form.value.nombre='',
        form.value.estadoCrud = '',          
        form.value.errors = []
        errors.value = []
    }
    const obtenerDatos = async(id) => {
        await obtenerPropiedad(id);
        if(propiedad.value)
        {
            form.value.id=propiedad.value.id;
            form.value.nombre=propiedad.value.nombre;
        }
    }

    const editar = async (id, e) => {
        lastOpener.value = e.currentTarget
        limpiar()
        await obtenerDatos(id)
        form.value.estadoCrud = 'editar'
        modalTitle.value = 'Editar Propiedad'
        await nextTick()
        openModal('#modalpropiedad')
    }
    const nuevo = async (e) => {
        lastOpener.value = e.currentTarget
        limpiar()
        form.value.estadoCrud = 'nuevo'
        modalTitle.value = 'Nuevo Propiedad'
        await nextTick()
        openModal('#modalpropiedad')
    }
    const eliminar = (id) => {
        Swal.fire({
            title: '¿Estás seguro de Eliminar?',
            text: "Feriado",
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
        await eliminarPropiedad(id)
        form.value.errors = []
        if(errors.value)
        {
            form.value.errors = errors.value
        }
        if(respuesta.value.ok==1){
            form.value.errors = []
            Toast.fire({icon:'success', title:respuesta.value.mensaje})
            listarPropiedades()
        }
    }
    const listarPropiedades = async() => {
        await listaPropiedades()
    }

    onMounted(() => {
        defineTitle('Propiedades')
        listarPropiedades()
    })


</script>
<template>
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h6 class="card-title">
                Listado de Propiedades
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-1 mb-1">
                    <button  type="button" class="btn btn-danger" @click.prevent="nuevo($event)">
                        <i class="fas fa-plus"></i> Nuevo
                    </button>                        
                </div>
                <div class="col-md-2 mb-1">
                    <div class="input-group mb-1">

                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group mb-1">

                    </div>
                </div>
                <div class="col-md-4 mb-1">

                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-1">
                    <div class="table-responsive">         
                        <table class="table table-bordered table-hover table-sm table-striped">
                            <thead class="">
                                <tr>
                                    <th colspan="8" class="text-center">Propiedades</th>
                                </tr>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="propiedades.length == 0">
                                    <td class="text-danger text-center" colspan="7">
                                        -- Datos No Registrados - Tabla Vacía --
                                    </td>
                                </tr>
                                <tr v-else v-for="(propiedad,index) in propiedades" :key="propiedad.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ propiedad.nombre }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-warning btn-sm" title="Editar" @click.prevent="editar(propiedad.id, $event)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" title="Enviar a Papelera" @click.prevent="eliminar(propiedad.id)">
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
    <FormPropiedad
        :form="form"
        :modalTitle="modalTitle"
        :lastOpener="lastOpener"
        @onListar="listarPropiedades"
    />
</template>