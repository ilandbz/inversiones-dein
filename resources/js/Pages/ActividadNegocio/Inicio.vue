<script setup>
    import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
    import useActividadNegocio from '@/Composables/ActividadNegocio.js'

    const { listaActividadNegocios, actividadNegocios } = useActividadNegocio()



    const form = ref({
        nombre: '',
    })

    onMounted(() => {
        document.title = 'Actividad de Negocio - Inversiones DEIN'
        listaActividadNegocios()
    })


</script>
<template>
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h6 class="card-title">
                Listado de Actividades de Negocio
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-1 mb-1">
                    <button  type="button" class="btn btn-danger" @click.prevent="nuevo">
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
                            <thead class="table-dark">
                                <tr>
                                    <th colspan="8" class="text-center">Actividades de Negocio</th>
                                </tr>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="actividadNegocios.length == 0">
                                    <td class="text-danger text-center" colspan="7">
                                        -- Datos No Registrados - Tabla Vacía --
                                    </td>
                                </tr>
                                <tr v-else v-for="(actividad,index) in actividadNegocios" :key="actividad.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ actividad.nombre }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-xs" title="Editar" @click.prevent="editar(actividad.id)">
                                            <i class="fas fa-edit"></i>
                                        </button>&nbsp;
                                        <button class="btn btn-danger btn-xs" title="Enviar a Papelera" @click.prevent="eliminar(actividad.id, 'Temporal')">
                                            <i class="fas fa-trash"></i>
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
</template>