<script setup>
import { toRefs, onMounted, ref } from 'vue';
import useUsuario from '@/Composables/Usuario.js';
import usePersona from '@/Composables/Persona.js';
import useRol from '@/Composables/Rol.js';
import useHelper from '@/Helpers';  
const { hideModal, Toast, slugify } = useHelper();
const props = defineProps({
    form: Object,
    currentPage : Number
});
const { form, currentPage } = toRefs(props)
const {
    errors, respuesta, agregarUsuario, actualizarUsuario
} = useUsuario();
const {
    persona, obtenerPorDni
} = usePersona();
const {
    listaRoles, roles
} = useRol();
const  emit  =defineEmits(['onListar'])
const crud = {
    'nuevo': async() => {
        let formData = new FormData();
        formData.append('username', form.value.username);
        formData.append('dni', form.value.dni);
        formData.append('apepat', form.value.apepat);
        formData.append('apemat', form.value.apemat);
        formData.append('primernombre', form.value.primernombre);
        formData.append('otrosnombres', form.value.otrosnombres);
        formData.append('celular', form.value.celular);
        formData.append('role_id', form.value.role_id);
        formData.append('foto', file.value);
        await agregarUsuario(formData)
        form.value.errors = []
        if(errors.value)
        {
            form.value.errors = errors.value
        }
        if(respuesta.value.ok==1){
            form.value.errors = []
            hideModal('#modalusuario')
            Toast.fire({icon:'success', title:respuesta.value.mensaje})
            emit('onListar', currentPage.value)
        }
    },
    'editar': async() => {
        let formData = new FormData();
        formData.append('id', form.value.id);
        formData.append('username', form.value.username);
        formData.append('dni', form.value.dni);
        formData.append('apepat', form.value.apepat);
        formData.append('apemat', form.value.apemat);
        formData.append('primernombre', form.value.primernombre);
        formData.append('otrosnombres', form.value.otrosnombres);
        formData.append('celular', form.value.celular);
        formData.append('role_id', form.value.role_id);
        formData.append('foto', file.value);
        await actualizarUsuario(formData)
        form.value.errors = []
        if(errors.value)
        {
            form.value.errors = errors.value
        }
        if(respuesta.value.ok==1){
            form.value.errors = []
            hideModal('#modalusuario')
            Toast.fire({icon:'success', title:respuesta.value.mensaje})
            emit('onListar', currentPage.value)
        }
    }
}
const guardar = () => {
    crud[form.value.estadoCrud]()
}
const onlyNumbers=(event)=> {
    if (!/[0-9]/.test(event.key)) {
        event.preventDefault();
    }
}
const file = ref(null);
const cambiarFoto = (e)=>{
    file.value = e.target.files[0]
    if (file) {
        form.value.foto=URL.createObjectURL(file.value);
    }
}
const imagenNoEncontrada = (event)=>{
    event.target.src = "/storage/fotos/default.png";
}
const generarUserName = ()=>{
    let username = form.value.primernombre.toUpperCase().substring(0,1)+
    form.value.apepat.toUpperCase().substring(0,5)+
    form.value.apemat.toUpperCase().substring(0,3)
    form.value.username = username
}
const buscarPersona= async(dni)=>{
    await obtenerPorDni(dni)
    if(persona.value){
        form.value.apepat = persona.value.ape_pat
        form.value.apemat = persona.value.ape_mat
        form.value.primernombre = persona.value.primernombre
        form.value.otrosnombres = persona.value.otrosnombres
    }
}
onMounted(() => {
    listaRoles()
})
</script>
<template>
  <Teleport to="body">
    <form @submit.prevent="guardar">
      <div
        class="modal fade"
        id="modalusuario"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        tabindex="-1"
        aria-labelledby="modalusuarioLabel"
      >
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content shadow-sm border-0">
            <!-- Header -->
            <div class="modal-header">
              <div>
                <h1 class="modal-title fs-4 mb-0" id="modalusuarioLabel">
                  {{ form.estadoCrud === 'nuevo' ? 'Nuevo usuario' : 'Editar usuario' }}
                </h1>
                <small class="text-muted">
                  Completa los datos para {{ form.estadoCrud === 'nuevo' ? 'registrar' : 'actualizar' }} el usuario.
                </small>
              </div>

              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              />
            </div>

            <!-- Body -->
            <div class="modal-body">
              <div class="row g-3">
                <!-- Columna izquierda -->
                <div class="col-12 col-lg-7">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Datos personales</h6>
                        <span class="badge bg-light text-muted border">
                          {{ form.estadoCrud === 'nuevo' ? 'REGISTRO' : 'EDICIÓN' }}
                        </span>
                      </div>

                      <!-- DNI -->
                      <div class="mb-3">
                        <label class="form-label">DNI</label>
                        <div class="input-group">
                          <span class="input-group-text">
                            <i class="bi bi-person-vcard"></i>
                          </span>
                          <input
                            type="text"
                            class="form-control"
                            v-model="form.dni"
                            :class="{ 'is-invalid': form.errors.dni }"
                            maxlength="8"
                            placeholder="00000000"
                            inputmode="numeric"
                            @keypress="onlyNumbers"
                            @change="buscarPersona(form.dni)"
                            :readonly="form.estadoCrud=='editar'"
                          />
                        </div>
                        <div v-if="form.errors.dni" class="invalid-feedback d-block">
                          <div v-for="error in form.errors.dni" :key="error">{{ error }}</div>
                        </div>
                        <small class="text-muted">Se autocompletará si existe en la base de datos.</small>
                      </div>

                      <div class="row g-3">
                        <!-- Ape Pat -->
                        <div class="col-12 col-md-6">
                          <label class="form-label">Apellido paterno</label>
                          <input
                            type="text"
                            class="form-control"
                            v-model="form.apepat"
                            @input="form.apepat = form.apepat.toUpperCase()"
                            :class="{ 'is-invalid': form.errors.apepat }"
                            placeholder="PATERNO"
                            :readonly="form.estadoCrud=='editar'"
                          />
                          <div v-if="form.errors.apepat" class="invalid-feedback d-block">
                            <div v-for="error in form.errors.apepat" :key="error">{{ error }}</div>
                          </div>
                        </div>

                        <!-- Ape Mat -->
                        <div class="col-12 col-md-6">
                          <label class="form-label">Apellido materno</label>
                          <input
                            type="text"
                            class="form-control"
                            v-model="form.apemat"
                            @input="form.apemat = form.apemat.toUpperCase()"
                            :class="{ 'is-invalid': form.errors.apemat }"
                            placeholder="MATERNO"
                            :readonly="form.estadoCrud=='editar'"
                          />
                          <div v-if="form.errors.apemat" class="invalid-feedback d-block">
                            <div v-for="error in form.errors.apemat" :key="error">{{ error }}</div>
                          </div>
                        </div>

                        <!-- Primer Nombre -->
                        <div class="col-12 col-md-6">
                          <label class="form-label">Primer nombre</label>
                          <input
                            type="text"
                            class="form-control"
                            v-model="form.primernombre"
                            @input="form.primernombre = form.primernombre.toUpperCase()"
                            :class="{ 'is-invalid': form.errors.primernombre }"
                            placeholder="NOMBRE"
                            :readonly="form.estadoCrud=='editar'"
                          />
                          <div v-if="form.errors.primernombre" class="invalid-feedback d-block">
                            <div v-for="error in form.errors.primernombre" :key="error">{{ error }}</div>
                          </div>
                        </div>

                        <!-- Otros Nombres -->
                        <div class="col-12 col-md-6">
                          <label class="form-label">Otros nombres</label>
                          <input
                            type="text"
                            class="form-control"
                            v-model="form.otrosnombres"
                            @input="form.otrosnombres = form.otrosnombres.toUpperCase()"
                            :class="{ 'is-invalid': form.errors.otrosnombres }"
                            placeholder="OTROS"
                            :readonly="form.estadoCrud=='editar'"
                          />
                          <div v-if="form.errors.otrosnombres" class="invalid-feedback d-block">
                            <div v-for="error in form.errors.otrosnombres" :key="error">{{ error }}</div>
                          </div>
                        </div>

                        <!-- Rol (solo nuevo) -->
                        <div class="col-12" v-if="form.estadoCrud=='nuevo'">
                          <label class="form-label">Rol</label>
                          <select
                            v-model="form.role_id"
                            class="form-select"
                            :class="{ 'is-invalid': form.errors.role_id }"
                          >
                            <option value="" disabled>-- Seleccione --</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id" :title="role.nombre">
                              {{ role.nombre }}
                            </option>
                          </select>
                          <div v-if="form.errors.role_id" class="invalid-feedback d-block">
                            <div v-for="error in form.errors.role_id" :key="error">{{ error }}</div>
                          </div>
                        </div>

                        <!-- Celular -->
                        <div class="col-12">
                          <label class="form-label">Celular</label>
                          <div class="input-group">
                            <span class="input-group-text">
                              <i class="bi bi-telephone"></i>
                            </span>
                            <input
                              type="text"
                              class="form-control"
                              v-model="form.celular"
                              :class="{ 'is-invalid': form.errors.celular }"
                              maxlength="9"
                              placeholder="000000000"
                              inputmode="numeric"
                              @keypress="onlyNumbers"
                            />
                          </div>
                          <div v-if="form.errors.celular" class="invalid-feedback d-block">
                            <div v-for="error in form.errors.celular" :key="error">{{ error }}</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Columna derecha -->
                <div class="col-12 col-lg-5">
                  <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                      <h6 class="mb-3">Acceso y foto</h6>

                      <!-- Username -->
                      <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                          <span class="input-group-text">
                            <i class="bi bi-at"></i>
                          </span>
                          <input
                            type="text"
                            class="form-control"
                            v-model="form.username"
                            :class="{ 'is-invalid': form.errors.username }"
                            placeholder="USER NAME"
                            @focus="generarUserName()"
                          />
                        </div>
                        <div v-if="form.errors.username" class="invalid-feedback d-block">
                          <div v-for="error in form.errors.username" :key="error">{{ error }}</div>
                        </div>
                        <small class="text-muted">Se sugiere automáticamente al enfocar.</small>
                      </div>

                      <!-- Foto -->
                      <div class="mb-3">
                        <label class="form-label">Foto</label>
                        <input class="form-control" type="file" accept="image/*" @change="cambiarFoto" />
                        <div v-if="form.errors.foto" class="invalid-feedback d-block mt-2">
                          <div v-for="error in form.errors.foto" :key="error">{{ error }}</div>
                        </div>

                        <div class="mt-3 d-flex justify-content-center">
                          <div class="avatar-preview shadow-sm">
                            <img
                              :src="form.foto"
                              class="w-100 h-100"
                              alt="Foto de usuario"
                              @error="imagenNoEncontrada"
                            />
                          </div>
                        </div>

                        <div class="text-center mt-2">
                          <small class="text-muted">Formato recomendado: JPG/PNG, buena iluminación.</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer d-flex justify-content-between">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                Cancelar
              </button>

              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle me-1"></i>
                {{ form.estadoCrud === 'nuevo' ? 'Guardar' : 'Actualizar' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </Teleport>
</template>
<style scoped>
.avatar-preview {
  width: 220px;
  height: 220px;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(0,0,0,.08);
  background: #f8f9fa;
}
.avatar-preview img {
  object-fit: cover;
  display: block;
}
</style>
