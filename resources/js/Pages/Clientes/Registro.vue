<script setup>
import { reactive, ref, onBeforeUnmount, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

onMounted(() => {
  document.title = 'Registro de Clientes'
})

const form = reactive({
  dni: '',
  ruc: '',
  celular: '',
  email: '',

  ape_pat: '',
  ape_mat: '',
  primernombre: '',
  otrosnombres: '',

  fecha_nac: '',
  genero: 'M',
  estado_civil: 'SOLTERO',
  ubigeo_nac: '',

  profesion: '',
  grado_instr: '',
  origen_labor: 'INDEPENDIENTE',
  ocupacion: '',
  institucion_lab: '',

  direccion: '',

  estado: 'ACTIVO',
  fecha_reg: '',
  hora_reg: ''
})

// FOTO
const photoFile = ref(null)
const photoPreview = ref(null)
let previewUrl = null

const handlePhotoChange = (e) => {
  const file = e.target.files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    alert('Selecciona una imagen válida.')
    e.target.value = ''
    return
  }

  const maxMB = 4
  if (file.size > maxMB * 1024 * 1024) {
    alert(`La imagen no debe superar ${maxMB}MB.`)
    e.target.value = ''
    return
  }

  photoFile.value = file

  if (previewUrl) URL.revokeObjectURL(previewUrl)
  previewUrl = URL.createObjectURL(file)
  photoPreview.value = previewUrl
}

const removePhoto = () => {
  photoFile.value = null
  if (previewUrl) URL.revokeObjectURL(previewUrl)
  previewUrl = null
  photoPreview.value = null
}

onBeforeUnmount(() => {
  if (previewUrl) URL.revokeObjectURL(previewUrl)
})

const submit = () => {
  // aquí luego lo conectas a tu API/Laravel con FormData si incluye foto
  console.log('Enviar', JSON.parse(JSON.stringify(form)), photoFile.value)
}

const cancelar = () => {
  // ajusta al nombre real de tu ruta principal
  router.push({ name: 'Principal' })
}
</script>

<template>
  <div class="page-heading">
    <h3>Clientes</h3>
    <p class="text-subtitle text-muted">Registro de clientes</p>
  </div>

  <div class="page-content">
    <section class="row">
      <!-- IZQUIERDA: FORM -->
      <div class="col-12 col-lg-8">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Formulario de Registro</h4>
          </div>

          <div class="card-body">
            <form @submit.prevent="submit">
              <div class="row g-3">
                <div class="col-12 col-md-3">
                  <label class="form-label">DNI</label>
                  <input v-model="form.dni" class="form-control" maxlength="8" placeholder="Ej: 12345678" />
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">RUC (opcional)</label>
                  <input v-model="form.ruc" class="form-control" maxlength="11" placeholder="Ej: 10456789123" />
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Celular</label>
                  <input v-model="form.celular" class="form-control" maxlength="11" placeholder="Ej: 999999999" />
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Email (opcional)</label>
                  <input v-model="form.email" type="email" class="form-control" placeholder="correo@dominio.com" />
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Apellido paterno</label>
                  <input v-model="form.ape_pat" class="form-control" placeholder="Ej: Pérez" />
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Apellido materno</label>
                  <input v-model="form.ape_mat" class="form-control" placeholder="Ej: Gómez" />
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Primer nombre</label>
                  <input v-model="form.primernombre" class="form-control" placeholder="Ej: Juan" />
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Otros nombres</label>
                  <input v-model="form.otrosnombres" class="form-control" placeholder="Ej: Carlos Alberto" />
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Fecha de nacimiento</label>
                  <input v-model="form.fecha_nac" type="date" class="form-control" />
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Género</label>
                  <select v-model="form.genero" class="form-select">
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                  </select>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Estado civil</label>
                  <select v-model="form.estado_civil" class="form-select">
                    <option>SOLTERO</option>
                    <option>CASADO</option>
                    <option>CONVIVIENTE</option>
                    <option>DIVORCIADO</option>
                    <option>VIUDO</option>
                  </select>
                </div>

                <div class="col-12 col-md-3">
                  <label class="form-label">Ubigeo Nacimiento (opcional)</label>
                  <input v-model="form.ubigeo_nac" class="form-control" maxlength="6" placeholder="Ej: 090101" />
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Profesión</label>
                  <input v-model="form.profesion" class="form-control" placeholder="Ej: Comerciante" />
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Grado de instrucción</label>
                  <input v-model="form.grado_instr" class="form-control" placeholder="Ej: Secundaria / Superior" />
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Origen laboral</label>
                  <select v-model="form.origen_labor" class="form-select">
                    <option>INDEPENDIENTE</option>
                    <option>DEPENDIENTE</option>
                  </select>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label">Ocupación</label>
                  <input v-model="form.ocupacion" class="form-control" placeholder="Ej: Vendedor" />
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label">Institución laboral</label>
                  <input v-model="form.institucion_lab" class="form-control" placeholder="Ej: NINGUNO / Empresa" />
                </div>

                <div class="col-12">
                  <label class="form-label">Dirección</label>
                  <textarea v-model="form.direccion" class="form-control" rows="2" placeholder="Av / Jr / Mz / Lt..."></textarea>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Estado (cliente)</label>
                  <input v-model="form.estado" class="form-control" disabled />
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Fecha registro</label>
                  <input v-model="form.fecha_reg" type="date" class="form-control" disabled />
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Hora registro</label>
                  <input v-model="form.hora_reg" type="time" class="form-control" disabled />
                </div>
              </div>

              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-save me-1"></i> Guardar
                </button>

                <button type="button" class="btn btn-light" @click="cancelar">
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- DERECHA: FOTO -->
      <div class="col-12 col-lg-4">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Foto del cliente</h4>
          </div>

          <div class="card-body">
            <div class="d-flex flex-column align-items-center gap-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center border"
                   style="width: 160px; height: 160px; overflow: hidden;">
                <img v-if="photoPreview" :src="photoPreview" alt="Foto"
                     style="width: 100%; height: 100%; object-fit: cover;" />
                <div v-else class="text-muted text-center px-3">
                  <i class="bi bi-person-circle" style="font-size: 3rem;"></i>
                  <div class="small mt-1">Sin foto</div>
                </div>
              </div>

              <div class="w-100">
                <label class="form-label">Subir imagen</label>
                <input type="file" accept="image/*" class="form-control" @change="handlePhotoChange" />
                <div class="form-text">JPG/PNG. Recomendado: 400x400.</div>
              </div>

              <div class="d-flex gap-2 w-100">
                <button type="button" class="btn btn-light w-100"
                        @click="removePhoto"
                        :disabled="!photoFile && !photoPreview">
                  <i class="bi bi-trash me-1"></i> Quitar
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>

    </section>
  </div>
</template>
