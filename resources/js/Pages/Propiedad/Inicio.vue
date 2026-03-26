<script setup>
import { ref, onMounted, nextTick } from 'vue'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import usePropiedad from '@/Composables/Propiedad.js'
import FormPropiedad from './Form.vue'
import useHelper from '@/Helpers';

const { openModal, Toast, Swal } = useHelper();
const { listaPropiedades, propiedades, obtenerPropiedad, eliminarPropiedad, respuesta, propiedad, errors } = usePropiedad()

const modalTitle = ref('')
const lastOpener = ref(null)

const form = ref({
    id: '', nombre: '', estadoCrud:'', errors:[]
})

const limpiar = ()=> {
    form.value = { id: '', nombre: '', estadoCrud: '', errors: [] }
    errors.value = []
}

const editar = async (id, e) => {
    lastOpener.value = e.currentTarget
    limpiar()
    await obtenerPropiedad(id);
    if(propiedad.value) {
        form.value.id = propiedad.value.id;
        form.value.nombre = propiedad.value.nombre;
    }
    form.value.estadoCrud = 'editar'
    modalTitle.value = 'Editar Propiedad'
    await nextTick()
    openModal('#modalpropiedad')
}

const nuevo = async (e) => {
    lastOpener.value = e.currentTarget
    limpiar()
    form.value.estadoCrud = 'nuevo'
    modalTitle.value = 'Nueva Propiedad'
    await nextTick()
    openModal('#modalpropiedad')
}

const eliminar = (id) => {
    Swal.fire({
        title: '¿Eliminar Propiedad?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'SÍ, ELIMINAR',
        cancelButtonText: 'CANCELAR',
        customClass: { confirmButton: 'btn btn-danger rounded-pill px-4', cancelButton: 'btn btn-light rounded-pill px-4' },
        buttonsStyling: false
    }).then(async (result) => {
        if (result.isConfirmed) {
            await eliminarPropiedad(id)
            if (respuesta.value?.ok === 1) {
                Toast.fire({icon:'success', title:respuesta.value.mensaje})
                listaPropiedades()
            }
        }
    })
}

onMounted(() => {
    listaPropiedades()
})
</script>

<template>
    <AppLayoutDefault title="Gestión de Propiedades">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4 align-items-center">
                    <div class="col">
                        <h3 class="fw-bold text-dark mb-1">Catálogo de Propiedades</h3>
                        <p class="text-muted small mb-0">Administración de tipos de propiedad y garantías</p>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" @click.prevent="nuevo($event)">
                            <i class="fas fa-plus-circle me-1"></i> NUEVA PROPIEDAD
                        </button>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4" style="width: 80px;">#</th>
                                        <th>Nombre / Descripción</th>
                                        <th class="pe-4 text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!propiedades.length" class="text-center py-5">
                                        <td colspan="3" class="py-5 text-muted italic">No hay propiedades registradas.</td>
                                    </tr>
                                    <tr v-for="(p, index) in propiedades" :key="p.id">
                                        <td class="ps-4 small fw-bold">#{{ index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-box bg-info-subtle text-info rounded-circle me-3">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <span class="fw-bold text-dark text-uppercase">{{ p.nombre }}</span>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                                <button class="btn btn-white btn-sm px-3 border-end" title="Editar" @click.prevent="editar(p.id, $event)"><i class="fas fa-edit text-warning"></i></button>
                                                <button class="btn btn-white btn-sm px-3" title="Eliminar" @click.prevent="eliminar(p.id)"><i class="fas fa-trash-alt text-danger"></i></button>
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
            @onListar="listaPropiedades"
        />
    </AppLayoutDefault>
</template>

<style scoped>
.btn-white { background: #fff; }
.btn-white:hover { background: #f8f9fa; }
.bg-info-subtle { background-color: #e0f7fa !important; }
.icon-box { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
</style>