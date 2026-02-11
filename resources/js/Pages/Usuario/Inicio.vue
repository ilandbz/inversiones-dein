<script setup>
  import { ref, onMounted } from 'vue';
  import { defineTitle } from '@/Helpers';
  import useHelper from '@/Helpers';  
  import useUsuario from '@/Composables/Usuario.js';
  import RoleUserForm from './RoleUserForm.vue';
  import UsuarioForm from './Form.vue'
  const { openModal, Toast, Swal } = useHelper();
  const {
        usuarios, errors, usuario, respuesta,
        obtenerUsuarios, obtenerUsuario, eliminarUsuario,
        resetClaveUsuario, cambiarEstado, eliminarRole,
        eliminarAgencia, carpetaFotos, rolesDisponibles, agenciasDisponibles, roles, agencias
    } = useUsuario();
    const show_tipo = ref("Habilitados")
    const titleHeader = ref({
      titulo: "Usuario",
      subTitulo: "Inicio",
      icon: "",
      vista: ""
    });
    const dato = ref({
        page:'',
        buscar:'',
        paginacion: 10,
        show_tipo : 'habilitados',
    });
    const form = ref({
        id:'',
        apepat:'',
        apemat:'',
        primernombre:'',
        otrosnombres:'',
        celular:'',
        username : '',
        dni: '',
        role_id : '',
        foto : carpetaFotos+'default.png',
        errors:[]

    });
    const limpiar = () => {
        form.value.id = '';
        form.value.username = '';
        form.value.dni = '';
        form.value.role_id = '';
        form.value.foto = carpetaFotos+'default.png';
        form.value.apepat = '';
        form.value.apemat = '';
        form.value.primernombre = '';
        form.value.otrosnombres = '';
        form.value.celular = '';
        form.value.errors = [];
        errors.value = [];
    };
    const obtenerDatos = async(id) => {
        await obtenerUsuario(id);
        if(usuario.value)
        {
            form.value.id=usuario.value.id;
            form.value.username=usuario.value.name;
            form.value.dni=usuario.value.dni;
            form.value.apepat=usuario.value.persona.ape_pat;
            form.value.apemat=usuario.value.persona.ape_mat;
            form.value.primernombre=usuario.value.persona.primernombre;
            form.value.otrosnombres=usuario.value.persona.otrosnombres;
            form.value.celular=usuario.value.persona.celular;
            form.value.foto=carpetaFotos+'/'+usuario.value.name+'.webp';
        }
    }
    const editar = (id) => {
        limpiar();
        obtenerDatos(id)
        form.value.estadoCrud = 'editar'
        document.getElementById("modalusuarioLabel").innerHTML = 'Editar Usuario';
        openModal('#modalusuario')
    }
    const nuevo = () => {
        limpiar()
        form.value.estadoCrud = 'nuevo'
        openModal('#modalusuario')
        document.getElementById("modalusuarioLabel").innerHTML = 'Nuevo Usuario';
        //titulo.textContent = 'Editar Datos Personales';
    }
    const listarUsuarios = async(page=1) => {
        dato.value.page= page
        await obtenerUsuarios(dato.value)
    }
    const eliminar = (id) => {
        Swal.fire({
            title: '¿Estás seguro de Eliminar?',
            text: "Usuario",
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
    const resetear = async(id) => {
        Swal.fire({
            title: '¿Estás seguro de Resetear la clave?',
            text: "Usuarios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si'
        }).then((result) => {
            if (result.isConfirmed) {
                resetearclaveUsuario(id)
            }
        })
    }
    const resetearclaveUsuario = async(id) => {
        await resetClaveUsuario(id)
        if(respuesta.value.ok==1)
        {
            Toast.fire({icon:'success', title:respuesta.value.mensaje})
            listarUsuarios()
        }
    }
    const elimina = async(id) => {
        await eliminarUsuario(id)
        form.value.errors = []
        if(errors.value)
        {
            form.value.errors = errors.value
        }
        if(respuesta.value.ok==1){
            form.value.errors = []
            Toast.fire({icon:'success', title:respuesta.value.mensaje})
            listarUsuarios(usuarios.value.current_page)
        }
    }
    const cambEstado= async(id)=>{
        await cambiarEstado(id)
        if(respuesta.value.ok==1){
            Toast.fire({icon:'success', title:respuesta.value.mensaje})
            listarUsuarios()
        }
    }
    const mostrarTodos = async () => {
        show_tipo.value = 'Todos'
        dato.value.show_tipo = 'todos'
        listarUsuarios()
    }
    const mostrarHabilitados = async () => {
        show_tipo.value = 'Habilitados'
        dato.value.show_tipo = 'habilitados'
        listarUsuarios()
    }
    const mostrarInactivos = async () => {
        show_tipo.value = 'Inactivos'
        dato.value.show_tipo = 'inactivos'
        listarUsuarios()
    }
    // PAGINACION
    const isActived = () => {
        return usuarios.value.current_page
    }
    const offset = 2;

    const buscar = () => {
        listarUsuarios()
    }
    const cambiarPaginacion = () => {
        listarUsuarios()
    }
    const cambiarPagina =(pagina) => {
        listarUsuarios(pagina)
    }
    const pagesNumber = () => {
        if(!usuarios.value.to){
            return []
        }
        let from = usuarios.value.current_page - offset
        if(from < 1) from = 1
        let to = from + (offset*2)
        if( to >= usuarios.value.last_page) to = usuarios.value.last_page
        let pagesArray = []
        while(from <= to) {
            pagesArray.push(from)
            from ++
        }
        return pagesArray
    }
    const eliminaRol = async(roleid, userid) => {
        Swal.fire({
            title: '¿Estás seguro de Eliminar el Rol?',
            text: "Usuario",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Eliminalo!'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminaRole(roleid, userid)
            }
        })
    }
    const eliminaAgencia = async(roleid, userid) => {
        Swal.fire({
            title: '¿Estás seguro de Eliminar el Rol?',
            text: "Usuario",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Eliminalo!'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminaAg(roleid, userid)
            }
        })
    }
const crearFormulario = (extraFields = {}) => ({
    user_id: '',
    dni: '',
    apenom: '',
    errors: [],
    ...extraFields,
});

const formRoleUser = ref(crearFormulario({ role_id: '' }));
const formAgenciaUser = ref(crearFormulario({ agencia_id: '' }));
const limpiarFormulario = (form) => {
    Object.keys(form.value).forEach(key => {
        form.value[key] = Array.isArray(form.value[key]) ? [] : '';
    });
};
const obtenerDatosUsuario = async (id, form) => {
    limpiarFormulario(form);
    await obtenerUsuario(id);
    if (usuario.value) {
        form.value.user_id = usuario.value.id;
        form.value.dni = usuario.value.persona?.dni;
        form.value.apenom = usuario.value.persona?.apenom;
    }
};
const nuevoRegistro = async (id, form, modalLabel, modalId) => {
    await obtenerDatosUsuario(id, form);
    if(modalLabel==='Nuevo Rol'){
        await rolesDisponibles(id)
    }else{
        await agenciasDisponibles(id)
    }
    document.getElementById(modalId+"Label").innerHTML = modalLabel;
    openModal('#'+modalId);
};
const nuevoRole = (id) => nuevoRegistro(id, formRoleUser, 'Nuevo Rol', 'modalRoleUser');

const imagenNoEncontrada = (event)=>{
    event.target.src = "/storage/fotos/default.png";
}
const eliminaRole = async(roleid, userid) => {
    await eliminarRole(roleid, userid)
    form.value.errors = []
    if(errors.value)
    {
        form.value.errors = errors.value
    }
    if(respuesta.value.ok==1){
        form.value.errors = []
        Toast.fire({icon:'success', title:respuesta.value.mensaje})
        listarUsuarios(usuarios.value.current_page)
    }
}
onMounted(() => {
    defineTitle(titleHeader.value.titulo)
    listarUsuarios()
})
</script>
<template>
  <div class="app-content">
    <div class="container-fluid">
      <div class="card card-primary card-outline shadow-sm">
        <!-- HEADER -->
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-2">
            <h6 class="card-title mb-0">Listado de usuarios</h6>
            <span class="badge bg-light text-dark border">
              {{ usuarios?.total ?? 0 }} registros
            </span>
          </div>

          <button type="button" class="btn btn-danger btn-sm" @click.prevent="nuevo">
            <i class="fas fa-plus me-1"></i> Nuevo
          </button>
        </div>

        <div class="card-body">
          <!-- TOOLBAR -->
          <div class="toolbar mb-3">
            <div class="row g-2 align-items-center">
              <div class="col-12 col-md-2">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">Mostrar</span>
                  <select class="form-select" v-model="dato.paginacion" @change="cambiarPaginacion">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
                </div>
              </div>

              <div class="col-12 col-md-3">
                <div class="dropdown w-100">
                  <button class="btn btn-outline-primary btn-sm w-100 d-flex justify-content-between align-items-center"
                          data-bs-toggle="dropdown">
                    <span>
                      <i class="fas fa-filter me-1"></i>
                      {{ show_tipo }}
                    </span>
                    <i class="fas fa-chevron-down"></i>
                  </button>
                  <div class="dropdown-menu w-100">
                    <a href="" class="dropdown-item" @click.prevent="mostrarTodos">Todos</a>
                    <a href="" class="dropdown-item" @click.prevent="mostrarHabilitados">Habilitados</a>
                    <a href="" class="dropdown-item" @click.prevent="mostrarInactivos">Inactivos</a>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-4 ms-auto">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">Buscar</span>
                  <input
                    class="form-control"
                    placeholder="Nombre o DNI..."
                    type="text"
                    v-model="dato.buscar"
                    @keyup.enter="buscar"
                    @change="buscar"
                  />
                  <button class="btn btn-outline-secondary" type="button" @click="buscar" title="Buscar">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- TABLE -->
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-2 table-users">
              <thead>
                <tr>
                  <th colspan="7" class="text-center bg-light">
                    Usuarios {{ show_tipo }}
                  </th>
                </tr>
                <tr class="small text-uppercase text-muted">
                  <th class="text-center col-num">#</th>
                  <th>Usuario</th>
                  <th class="text-nowrap">DNI</th>
                  <th>Roles</th>
                  <th class="text-center">Estado</th>
                  <th class="text-center col-foto">Foto</th>
                  <th class="text-center col-actions">Acciones</th>
                </tr>
              </thead>

              <tbody>
                <tr v-if="usuarios.total == 0">
                  <td class="text-danger text-center py-4" colspan="7">
                    <i class="fas fa-circle-info me-1"></i>
                    -- Datos No Registrados - Tabla Vacía --
                  </td>
                </tr>

                <tr v-else v-for="(usuario,index) in usuarios.data" :key="usuario.id">
                  <td class="text-center fw-semibold">{{ index + usuarios.from }}</td>

                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="fw-semibold">{{ usuario.name }}</span>
                    </div>
                  </td>

                  <td class="text-nowrap">{{ usuario.dni }}</td>

                  <td>
                    <div class="d-flex flex-wrap gap-1">
                      <span v-for="role in usuario.roles" :key="role.id" class="badge bg-primary role-chip">
                        {{ role.nombre }}
                        <button
                          class="btn btn-link p-0 ms-1 role-trash"
                          title="Eliminar rol"
                          @click.prevent="eliminaRol(role.id, usuario.id)"
                        >
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </span>
                    </div>
                  </td>

                  <td class="text-center">
                    <button
                      type="button"
                      class="btn btn-sm"
                      :class="usuario.es_activo === 1 ? 'btn-success' : 'btn-secondary'"
                      @click="cambEstado(usuario.id)"
                      title="Cambiar estado"
                    >
                      <i class="fas" :class="usuario.es_activo === 1 ? 'fa-toggle-on' : 'fa-toggle-off'"></i>
                      <span class="ms-1">{{ usuario.es_activo === 1 ? 'Activo' : 'Inactivo' }}</span>
                    </button>
                  </td>

                  <td class="text-center">
                    <div class="avatar avatar-md mx-auto">
                      <img
                        :src="`/storage/fotos/usuarios/${usuario.name}.webp`"
                        alt="Foto"
                        @error="imagenNoEncontrada"
                      />
                    </div>
                  </td>

                  <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                      <button class="btn btn-warning" title="Editar" @click.prevent="editar(usuario.id)">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-danger" title="Eliminar" @click.prevent="eliminar(usuario.id)">
                        <i class="fas fa-trash"></i>
                      </button>
                      <button class="btn btn-success" title="Reset clave" @click.prevent="resetear(usuario.id)">
                        <i class="fas fa-key"></i>
                      </button>
                      <button class="btn btn-info" title="Agregar rol" @click.prevent="nuevoRole(usuario.id)">
                        <i class="fas fa-user-gear"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- FOOTER / PAGINATION -->
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
            <div class="small text-muted">
              Mostrando <b>{{ usuarios.from }}</b> a <b>{{ usuarios.to }}</b> de <b>{{ usuarios.total }}</b> registros
            </div>

            <nav>
              <ul class="pagination pagination-sm mb-0">
                <li v-if="usuarios.current_page >= 2" class="page-item">
                  <a href="#" class="page-link" title="Primera" @click.prevent="cambiarPagina(1)">
                    <i class="fas fa-backward"></i>
                  </a>
                </li>
                <li v-if="usuarios.current_page > 1" class="page-item">
                  <a href="#" class="page-link" title="Anterior" @click.prevent="cambiarPagina(usuarios.current_page - 1)">
                    <i class="fas fa-angle-left"></i>
                  </a>
                </li>

                <li
                  v-for="page in pagesNumber()"
                  :key="page"
                  class="page-item"
                  :class="[ page == isActived() ? 'active' : '' ]"
                >
                  <a href="#" class="page-link" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                </li>

                <li v-if="usuarios.current_page < usuarios.last_page" class="page-item">
                  <a href="#" class="page-link" title="Siguiente" @click.prevent="cambiarPagina(usuarios.current_page + 1)">
                    <i class="fas fa-angle-right"></i>
                  </a>
                </li>
                <li v-if="usuarios.current_page <= usuarios.last_page - 1" class="page-item">
                  <a href="#" class="page-link" title="Última" @click.prevent="cambiarPagina(usuarios.last_page)">
                    <i class="fas fa-step-forward"></i>
                  </a>
                </li>
              </ul>
            </nav>
          </div>

        </div>
      </div>
    </div>
  </div>

  <UsuarioForm :form="form" @onListar="listarUsuarios" :currentPage="usuarios.current_page" />
  <RoleUserForm :form="formRoleUser" :roles="roles" @onListar="listarUsuarios" :currentPage="usuarios.current_page" />
</template>

<style scoped>
/* toolbar container */
.toolbar{
  padding: .75rem;
  border: 1px solid rgba(0,0,0,.07);
  border-radius: .75rem;
  background: rgba(0,0,0,.02);
}

/* table tuning */
.table-users thead th{
  vertical-align: middle;
}
.col-num{ width: 55px; }
.col-foto{ width: 90px; }
.col-actions{ width: 210px; }

/* role chips */
.role-chip{
  display: inline-flex;
  align-items: center;
  gap: .25rem;
  padding: .35rem .5rem;
}
.role-trash{
  color: rgba(255,255,255,.9);
  text-decoration: none;
  line-height: 1;
}
.role-trash:hover{
  color: #ffd2d2;
}

/* avatar */
.avatar{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-radius: 50%;
  background: #f2f2f2;
  border: 1px solid rgba(0,0,0,.08);
}
.avatar-md{
  width: 46px;
  height: 46px;
}
.avatar img{
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* buttons */
.btn-group .btn{
  min-width: 38px;
}
</style>