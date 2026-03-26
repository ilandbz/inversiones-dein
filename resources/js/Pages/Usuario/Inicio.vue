<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import useHelper from '@/Helpers';  
import useUsuario from '@/Composables/Usuario.js';
import RoleUserForm from './RoleUserForm.vue';
import UsuarioForm from './Form.vue'

const { openModal, Toast, Swal } = useHelper();
const {
    usuarios, usuario, respuesta, errors,
    obtenerUsuarios, obtenerUsuario, eliminarUsuario,
    resetClaveUsuario, cambiarEstado, rolesDisponibles, agenciasDisponibles, roles, carpetaFotos
} = useUsuario();

const show_tipo = ref("Habilitados")
const dato = ref({
    page: 1,
    buscar: '',
    paginacion: 10,
    show_tipo : 'habilitados',
});

const form = ref({
    id:'', apepat:'', apemat:'', primernombre:'', otrosnombres:'', celular:'',
    username : '', dni: '', role_id : '', foto : carpetaFotos+'default.png', errors:[]
});

const listarUsuarios = async (page = 1) => {
    dato.value.page = page;
    await obtenerUsuarios(dato.value);
};

const nuevo = () => {
    form.value = { id:'', username:'', dni:'', role_id:'', foto: carpetaFotos+'default.png', apepat:'', apemat:'', primernombre:'', otrosnombres:'', celular:'', errors:[], estadoCrud: 'nuevo' };
    openModal('#modalusuario');
};

const editar = async (id) => {
    await obtenerUsuario(id);
    if (usuario.value) {
        form.value = {
            id: usuario.value.id, username: usuario.value.name, dni: usuario.value.dni,
            apepat: usuario.value.persona.ape_pat, apemat: usuario.value.persona.ape_mat,
            primernombre: usuario.value.persona.primernombre, otrosnombres: usuario.value.persona.otrosnombres,
            celular: usuario.value.persona.celular, foto: carpetaFotos+'/'+usuario.value.name+'.webp',
            estadoCrud: 'editar', errors: []
        };
        openModal('#modalusuario');
    }
};

const eliminar = (id) => {
    Swal.fire({
        title: '¿Eliminar Usuario?',
        text: "Esta acción inhabilitará el acceso al sistema.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'SÍ, ELIMINAR',
        cancelButtonText: 'CANCELAR',
        customClass: { confirmButton: 'btn btn-danger rounded-pill px-4', cancelButton: 'btn btn-light rounded-pill px-4' },
        buttonsStyling: false
    }).then(async (result) => {
        if (result.isConfirmed) {
            await eliminarUsuario(id);
            if (respuesta.value?.ok === 1) listarUsuarios();
        }
    });
};

const resetear = (id) => {
    Swal.fire({
        title: '¿Resetear Clave?',
        text: "La clave se restaurará al valor por defecto.",
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'SÍ, RESETEAR',
        cancelButtonText: 'CANCELAR',
        customClass: { confirmButton: 'btn btn-primary rounded-pill px-4', cancelButton: 'btn btn-light rounded-pill px-4' },
        buttonsStyling: false
    }).then(async (result) => {
        if (result.isConfirmed) {
            await resetClaveUsuario(id);
            if(respuesta.value.ok==1) Toast.fire({icon:'success', title:respuesta.value.mensaje});
        }
    });
};

const cambiaEstado = async (id) => {
    await cambiarEstado(id);
    if(respuesta.value.ok==1) listarUsuarios();
};

const filtrar = (tipo) => {
    show_tipo.value = tipo.charAt(0).toUpperCase() + tipo.slice(1);
    dato.value.show_tipo = tipo;
    listarUsuarios(1);
};

// Role Logic
const formRoleUser = ref({ user_id: '', dni: '', apenom: '', role_id: '', errors: [] });
const nuevoRole = async (id) => {
    await obtenerUsuario(id);
    if (usuario.value) {
        formRoleUser.value = { user_id: usuario.value.id, dni: usuario.value.dni, apenom: usuario.value.persona?.apenom, role_id: '', errors: [] };
        await rolesDisponibles(id);
        openModal('#modalRoleUser');
    }
};

const offset = 2;
const pagesNumber = computed(() => {
    const u = usuarios.value;
    if(!u?.to) return [];
    let from = u.current_page - offset;
    if(from < 1) from = 1;
    let to = from + (offset*2);
    if( to >= u.last_page) to = u.last_page;
    const pages = [];
    for (let p = from; p <= to; p++) pages.push(p);
    return pages;
});

onMounted(() => { listarUsuarios(); });
</script>

<template>
    <AppLayoutDefault title="Administración de Usuarios">
        <div class="page-content py-4">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4 align-items-center">
                    <div class="col">
                        <h3 class="fw-bold text-dark mb-1">Usuarios del Sistema</h3>
                        <p class="text-muted small mb-0">Gestión de accesos, roles y perfiles de personal</p>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-white border rounded-pill px-3 dropdown-toggle shadow-sm" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-1 text-primary"></i> {{ show_tipo }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2">
                                <li><a class="dropdown-item px-4 py-2" @click="filtrar('todos')">Todos</a></li>
                                <li><a class="dropdown-item px-4 py-2" @click="filtrar('habilitados')">Habilitados</a></li>
                                <li><a class="dropdown-item px-4 py-2" @click="filtrar('inactivos')">Inactivos</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" @click="nuevo">
                            <i class="fas fa-user-plus me-1"></i> NUEVO USUARIO
                        </button>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white p-4 border-0 pb-0">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="input-group bg-light rounded-pill px-3 py-1">
                                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                                    <input v-model="dato.buscar" type="text" class="form-control bg-transparent border-0" placeholder="Buscar por nombre o DNI..." @keyup.enter="listarUsuarios(1)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 mt-3">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Usuario / Personal</th>
                                        <th>DNI</th>
                                        <th>Roles Asignados</th>
                                        <th class="text-center">Estado</th>
                                        <th class="pe-4 text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!usuarios.data?.length" class="text-center py-5">
                                        <td colspan="6" class="py-5 text-muted">No hay usuarios que mostrar.</td>
                                    </tr>
                                    <tr v-for="(u, index) in usuarios.data" :key="u.id">
                                        <td class="ps-4 small fw-bold">{{ index + usuarios.from }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-md me-3 shadow-sm border">
                                                    <img :src="u.persona?.foto || `/storage/fotos/usuarios/default.png`" @error="(e) => e.target.src = '/storage/fotos/usuarios/default.png'">
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark text-uppercase">{{ u.name }}</div>
                                                    <div class="small text-muted">{{ u.persona?.apenom }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="small">{{ u.dni }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <span v-for="r in u.roles" :key="r.id" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
                                                    {{ r.nombre }}
                                                </span>
                                                <button class="btn btn-sm btn-white border-0 rounded-circle" @click="nuevoRole(u.id)" title="Gestionar Roles">
                                                    <i class="fas fa-plus-circle text-primary"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm rounded-pill px-3 fw-bold" :class="u.es_activo === 1 ? 'btn-success-subtle text-success border-success-subtle' : 'btn-light border'" @click="cambiaEstado(u.id)">
                                                <i class="fas" :class="u.es_activo === 1 ? 'fa-check-circle me-1' : 'fa-times-circle me-1'"></i>
                                                {{ u.es_activo === 1 ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                                <button class="btn btn-white btn-sm px-3 border-end" title="Editar" @click="editar(u.id)"><i class="fas fa-pen-nib text-warning"></i></button>
                                                <button class="btn btn-white btn-sm px-3 border-end" title="Reset Clave" @click="resetear(u.id)"><i class="fas fa-key text-info"></i></button>
                                                <button class="btn btn-white btn-sm px-3" title="Inhabilitar" @click="eliminar(u.id)"><i class="fas fa-trash-alt text-danger"></i></button>
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
                            Página {{ usuarios.current_page }} de {{ usuarios.last_page }} ({{ usuarios.total }} registros)
                        </div>
                        <nav v-if="usuarios.last_page > 1">
                            <ul class="pagination pagination-sm mb-0 gap-1 child-rounded-pill">
                                <li class="page-item" :class="{ disabled: usuarios.current_page === 1 }">
                                    <button class="page-link border-0 shadow-none px-3" @click="listarUsuarios(usuarios.current_page - 1)"><i class="fas fa-chevron-left"></i></button>
                                </li>
                                <li v-for="p in pagesNumber" :key="p" class="page-item" :class="{ active: p === usuarios.current_page }">
                                    <button class="page-link border-0 shadow-none px-3" @click="listarUsuarios(p)">{{ p }}</button>
                                </li>
                                <li class="page-item" :class="{ disabled: usuarios.current_page === usuarios.last_page }">
                                    <button class="page-link border-0 shadow-none px-3" @click="listarUsuarios(usuarios.current_page + 1)"><i class="fas fa-chevron-right"></i></button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <UsuarioForm :form="form" @onListar="listarUsuarios" :currentPage="usuarios.current_page" />
        <RoleUserForm :form="formRoleUser" :roles="roles" @onListar="listarUsuarios" :currentPage="usuarios.current_page" />
    </AppLayoutDefault>
</template>

<style scoped>
.btn-white { background: #fff; }
.btn-white:hover { background: #f8f9fa; }
.bg-primary-subtle { background-color: #e9f2ff !important; border-color: #cfe2ff !important; }
.bg-success-subtle { background-color: #e6ffed !important; border-color: #c6f6d5 !important; }
.avatar-md { width: 42px; height: 42px; overflow: hidden; border-radius: 50%; }
.avatar-md img { width: 100%; height: 100%; object-fit: cover; }
.child-rounded-pill .page-link { border-radius: 50px !important; }
.table-hover tbody tr:hover { background-color: rgba(var(--bs-primary-rgb), 0.02); }
</style>