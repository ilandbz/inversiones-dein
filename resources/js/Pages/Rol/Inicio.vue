<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useHelper from '@/Helpers';  
import useRol from '@/Composables/Rol.js';
import RoleForm from './Form.vue'

const { openModal, Toast, Swal } = useHelper();
const { roles, role, respuesta, errors, obtenerRoles, obtenerRole, eliminarRole } = useRol();

const dato = ref({
    page: 1,
    buscar: '',
    paginacion: 10
});

const form = ref({
    id: '', nombre: '', estadoCrud: '', errors: []
});

const listarRoles = async (page = 1) => {
    dato.value.page = page;
    await obtenerRoles(dato.value);
};

const nuevo = () => {
    form.value = { id: '', nombre: '', estadoCrud: 'nuevo', errors: [] };
    openModal('#modalRole');
};

const editar = async (id) => {
    await obtenerRole(id);
    if (role.value) {
        form.value = { id: role.value.id, nombre: role.value.nombre, estadoCrud: 'editar', errors: [] };
        openModal('#modalRole');
    }
};

const eliminar = (id) => {
    Swal.fire({
        title: '¿Eliminar Rol?',
        text: "Esta acción podría afectar los permisos de varios usuarios.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'SÍ, ELIMINAR',
        cancelButtonText: 'CANCELAR',
        customClass: { confirmButton: 'btn btn-danger rounded-pill px-4', cancelButton: 'btn btn-light rounded-pill px-4' },
        buttonsStyling: false
    }).then(async (result) => {
        if (result.isConfirmed) {
            await eliminarRole(id);
            if (respuesta.value?.ok === 1) listarRoles(roles.value.current_page);
        }
    });
};

const offset = 2;
const pagesNumber = computed(() => {
    const r = roles.value;
    if(!r?.to) return [];
    let from = r.current_page - offset;
    if(from < 1) from = 1;
    let to = from + (offset*2);
    if( to >= r.last_page) to = r.last_page;
    const pages = [];
    for (let p = from; p <= to; p++) pages.push(p);
    return pages;
});

onMounted(() => { listarRoles(); });
</script>

<template>
    <AppLayoutDefault title="Configuración de Roles">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4 align-items-center">
                    <div class="col">
                        <h3 class="fw-bold text-dark mb-1">Roles de Usuario</h3>
                        <p class="text-muted small mb-0">Definición de perfiles y niveles de acceso al sistema</p>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" @click="nuevo">
                            <i class="fas fa-plus-circle me-1"></i> NUEVO ROL
                        </button>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white p-4 border-0 pb-0">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="input-group bg-light rounded-pill px-3 py-1">
                                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                                    <input v-model="dato.buscar" type="text" class="form-control bg-transparent border-0" placeholder="Buscar rol..." @keyup.enter="listarRoles(1)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 mt-3">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4" style="width: 80px;">#</th>
                                        <th>Nombre del Rol</th>
                                        <th class="pe-4 text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!roles.data?.length" class="text-center py-5">
                                        <td colspan="3" class="py-5 text-muted">No hay roles registrados.</td>
                                    </tr>
                                    <tr v-for="(r, index) in roles.data" :key="r.id">
                                        <td class="ps-4 small fw-bold">{{ index + roles.from }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-box bg-primary-subtle text-primary rounded-circle me-3">
                                                    <i class="fas fa-user-shield"></i>
                                                </div>
                                                <span class="fw-bold text-dark text-uppercase">{{ r.nombre }}</span>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                                <button class="btn btn-white btn-sm px-3 border-end" title="Editar" @click="editar(r.id)"><i class="fas fa-edit text-warning"></i></button>
                                                <button class="btn btn-white btn-sm px-3" title="Eliminar" @click="eliminar(r.id)"><i class="fas fa-trash-alt text-danger"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer bg-white p-4 border-top-0 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Paginación: {{ roles.current_page }} de {{ roles.last_page }}
                        </div>
                        <nav v-if="roles.last_page > 1">
                            <ul class="pagination pagination-sm mb-0 gap-1 child-rounded-pill">
                                <li class="page-item" :class="{ disabled: roles.current_page === 1 }">
                                    <button class="page-link border-0 shadow-none px-3" @click="listarRoles(roles.current_page - 1)"><i class="fas fa-chevron-left"></i></button>
                                </li>
                                <li v-for="p in pagesNumber" :key="p" class="page-item" :class="{ active: p === roles.current_page }">
                                    <button class="page-link border-0 shadow-none px-3" @click="listarRoles(p)">{{ p }}</button>
                                </li>
                                <li class="page-item" :class="{ disabled: roles.current_page === roles.last_page }">
                                    <button class="page-link border-0 shadow-none px-3" @click="listarRoles(roles.current_page + 1)"><i class="fas fa-chevron-right"></i></button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <RoleForm :form="form" @onListar="listarRoles" :currentPage="roles.current_page" />
    </AppLayoutDefault>
</template>

<style scoped>
.btn-white { background: #fff; }
.btn-white:hover { background: #f8f9fa; }
.bg-primary-subtle { background-color: #e9f2ff !important; }
.icon-box { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; }
.child-rounded-pill .page-link { border-radius: 50px !important; }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
</style>