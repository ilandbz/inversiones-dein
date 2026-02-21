<script setup>
import { ref, watch, computed, toRefs } from 'vue'
import useCredito from '@/Composables/Credito.js'
import useHelper from '@/Helpers'

const props = defineProps({
  form: { type: Object, required: true }
})

const { form } = toRefs(props)
const emit = defineEmits(['cargar'])

const { actualizarCredito, respuesta } = useCredito()
const { Toast, Swal, hideModal } = useHelper()

const isSaving = ref(false)

/* ----------------- HELPERS NUMÉRICOS ----------------- */
const toNumber = (v) => {
  const n = Number(String(v ?? '').replace(',', '.'))
  return Number.isFinite(n) ? n : 0
}

const toMoney2 = (n) =>
  (Math.round((Number(n) + Number.EPSILON) * 100) / 100).toFixed(2)

/* ----------------- TASA (%) EDITABLE ----------------- */
const tasa = computed({
  get: () => toNumber(form.value.tasainteres) * 100,
  set: (val) => {
    form.value.tasainteres = toNumber(val) / 100
  }
})

/* ----------------- TOTAL CALCULADO ----------------- */
const totalCalc = computed(() => {
  const monto = toNumber(form.value.monto)
  return toMoney2(monto * (1 + tasa.value / 100))
})

/* Sin necesidad de watch: usamos otro computed */
const totalMostrar = computed(() => totalCalc.value)

/* ----------------- MODAL ----------------- */
const cerrarModal = () => {
  const active = document.activeElement
  if (active instanceof HTMLElement) active.blur()
  hideModal('#autorizacionModal')
}

/* ----------------- GUARDAR ----------------- */
const guardarTasa = async () => {
  isSaving.value = true
  try {
    await actualizarCredito(form.value)

    if (respuesta.value?.ok === 1) {
      Toast.fire({
        icon: 'success',
        title: 'Tasa actualizada correctamente'
      })
      emit('cargar')
      cerrarModal()
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: respuesta.value?.mensaje || 'No se pudo actualizar'
      })
    }
  } catch (error) {
    console.error(error)
    Toast.fire({
      icon: 'error',
      title: 'Error de servidor'
    })
  } finally {
    isSaving.value = false
  }
}

/* ----------------- BADGE ----------------- */
const badgeMap = {
  PENDIENTE: 'bg-warning',
  REVISION: 'bg-info',
  APROBADO: 'bg-success',
  RECHAZADO: 'bg-danger'
}

const getBadgeClass = (estado) =>
  badgeMap[estado] || 'bg-secondary'

/* ----------------- FOTO CLIENTE ----------------- */
const clientPhoto = computed(() =>
  form.value.cliente_dni
    ? `/storage/fotos/clientes/${form.value.cliente_dni}.webp`
    : '/storage/fotos/default.png'
)

const handleImageError = (e) => {
  e.target.src = '/storage/fotos/default.png'
}
</script>

<template>
  <teleport to="body">
    <div
      class="modal fade"
      id="autorizacionModal"
      tabindex="-1"
      aria-labelledby="autorizacionModalLabel"
      aria-hidden="true"
      data-bs-backdrop="static"
      data-bs-keyboard="false"
    >
      <div class="modal-dialog modal-xl">
        <div class="modal-content text-dark border-0 shadow">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title fw-bold" id="autorizacionModalLabel">
              <i class="fas fa-file-contract me-2"></i> DETALLES DE EVALUACIÓN #{{ form.id }}
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="cerrarModal"></button>
          </div>

          <div class="modal-body bg-light position-relative p-4">
            <div class="row g-4">
              
              <!-- Columna Izquierda: Foto y Datos Principales -->
              <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                  <div class="position-relative">
                    <img 
                      :src="clientPhoto" 
                      @error="handleImageError"
                      class="img-fluid w-100 object-fit-cover" 
                      style="height: 350px;"
                      alt="Foto Cliente"
                    >
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark text-white">
                        <h4 class="mb-0 fw-bold">{{ form.cliente_apenom }}</h4>
                        <p class="mb-0 small opacity-75"><i class="fas fa-id-card me-1"></i> DNI: {{ form.cliente_dni }}</p>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="text-center mb-3">
                        <span :class="['badge px-3 py-2 rounded-pill', getBadgeClass(form.estado)]" style="font-size: 0.9rem;">
                          ESTADO: {{ form.estado }}
                        </span>
                    </div>
                    <hr class="text-muted">
                    <div class="row text-center g-2 mt-2">
                        <div class="col-6">
                            <p class="text-muted small mb-0 fw-bold">ASESOR</p>
                            <p class="mb-0 text-primary">{{ form.asesor_id || '---' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-0 fw-bold">TIPO</p>
                            <p class="mb-0 text-primary text-truncate px-1">{{ form.tipo }}</p>
                        </div>
                        <div class="col-12 mt-3">
                            <p class="text-muted small mb-0 fw-bold">AVAL / GARANTE</p>
                            <p class="mb-0 text-dark">{{ form.aval_id || 'NO ASIGNADO' }}</p>
                        </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Columna Derecha: Condiciones e Interés -->
              <div class="col-lg-8">
                <div class="row g-4">
                  
                  <!-- Condiciones actuales -->
                  <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-header bg-white py-3">
                        <h6 class="text-primary fw-bold mb-0 text-uppercase letter-spacing-1">
                            <i class="fas fa-coins me-2"></i>Condiciones del Préstamo
                        </h6>
                      </div>
                      <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-light">
                            <span class="text-muted fw-bold">Monto del Préstamo:</span>
                            <span class="fs-3 fw-bolder text-dark">S/ {{ Number(form.monto).toFixed(2) }}</span>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <p class="text-muted small mb-1 fw-bold text-uppercase">Frecuencia</p>
                                    <p class="mb-0 fs-5 fw-bold text-secondary">{{ form.frecuencia }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <p class="text-muted small mb-1 fw-bold text-uppercase">Plazo</p>
                                    <p class="mb-0 fs-5 fw-bold text-secondary">{{ form.plazo }} cuotas</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center p-2 rounded bg-info-subtle border-start border-info border-3">
                                    <i class="fas fa-calendar-alt text-info me-3 fs-4"></i>
                                    <div>
                                        <p class="text-muted small mb-0 text-uppercase fw-bold">Registrado el:</p>
                                        <p class="mb-0 fw-bold">{{ form.fecha_reg }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Área de Ajuste de Tasa -->
                  <div class="col-md-5">
                    <div class="card border-primary border shadow-sm h-100 bg-white">
                      <div class="card-header bg-primary py-3">
                        <h6 class="text-white fw-bold mb-0 text-uppercase letter-spacing-1 text-center">
                            Ajuste de Autorización
                        </h6>
                      </div>
                      <div class="card-body d-flex flex-column justify-content-center p-4">
                        <div class="mb-4 text-center">
                            <label class="form-label text-muted fw-bold mb-2">TASA DE INTERÉS (%)</label>
                            <div class="input-group input-group-lg mx-auto" style="max-width: 200px;">
                                <input 
                                    type="number" 
                                    class="form-control fw-bold border-primary text-center px-0 fs-4" 
                                    v-model="tasa"
                                    step="0.01"
                                    min="0"
                                >
                                <span class="input-group-text bg-primary text-white border-primary fs-5 border-0">%</span>
                            </div>
                            <small class="text-primary mt-2 d-block fst-italic">Solo este campo es editable</small>
                        </div>
                        
                        <div class="mt-auto pt-3 border-top">
                            <div class="p-4 bg-primary rounded-4 text-center text-white shadow">
                                <h6 class="small mb-1 opacity-75 fw-bold">TOTAL A RETORNAR</h6>
                                <h2 class="fw-bolder mb-0 display-6">S/ {{ totalMostrar }}</h2>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Mensaje Informativo -->
                  <div class="col-12">
                      <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center py-3 mb-0" role="alert">
                          <i class="fas fa-exclamation-triangle fs-4 me-3 text-warning"></i>
                          <div>
                              <strong class="d-block">Verificación de Crédito</strong>
                              <span class="small">Revise detenidamente los estados financieros y el análisis cualitativo antes de proceder con el ajuste de la tasa.</span>
                          </div>
                      </div>
                  </div>

                </div>
              </div>

            </div>
          </div>

          <div class="modal-footer bg-light border-top p-3">
            <button type="button" class="btn btn-lg btn-outline-secondary px-4 fw-bold shadow-sm" @click="cerrarModal">CERRAR</button>
            <button type="button" class="btn btn-lg btn-primary px-5 fw-bold shadow" :disabled="isSaving" @click="guardarTasa">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="fas fa-check-circle me-2"></i> ACTUALIZAR TASA Y TOTAL
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<style scoped>
.bg-gradient-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
}
.letter-spacing-1 {
    letter-spacing: 1px;
}
.card {
    border-radius: 12px;
}
.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
}
.bg-primary-subtle {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}
.bg-info-subtle {
    background-color: rgba(var(--bs-info-rgb), 0.1) !important;
}
.object-fit-cover {
    object-fit: cover;
}
.rounded-4 {
    border-radius: 1rem !important;
}
/* Estilo para quitar flechas del input number */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>
