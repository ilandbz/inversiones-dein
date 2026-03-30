<script setup>
import { ref, toRefs, onMounted, watch } from 'vue'
import useHelper from '@/Helpers'
import axios from 'axios'

const props = defineProps({
    form: Object
})

const emit = defineEmits(['onListar'])

const { form } = toRefs(props)
const { Toast, Swal, hideModal } = useHelper()

const errors = ref({})
const sending = ref(false)

const hasError = (name) => errors.value?.[name]
const firstError = (name) => errors.value?.[name]?.[0] || ''

const frequencies = ['DIARIA', 'SEMANAL', 'QUINCENAL', 'MENSUAL']

const submitForm = async () => {
    sending.value = true
    errors.value = {}
    
    const isEdit = form.value.estadoCrud === 'editar'
    const url = isEdit ? '/plazo/actualizar' : '/plazo/guardar'
    
    try {
        const { data } = await axios.post(url, form.value)
        if (data.ok == 1) {
            Swal.fire('¡Éxito!', data.mensaje, 'success')
            hideModal('#plazomodal')
            emit('onListar')
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            Toast.fire({ icon: 'error', title: 'Error al procesar la solicitud' })
        }
    } finally {
        sending.value = false
    }
}

</script>

<template>
    <teleport to="body">
        <div class="modal fade" id="plazomodal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-primary text-white p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-white-20 rounded-circle p-2 me-3">
                                <i class="fas fa-calendar-alt fs-4"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">
                                    {{ form.estadoCrud === 'editar' ? 'Editar Tasa/Plazo' : 'Nueva Tasa/Plazo' }}
                                </h5>
                                <div class="small opacity-75">Configuración de parámetros financieros</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" :disabled="sending"></button>
                    </div>

                    <div class="modal-body p-4">
                        <form @submit.prevent="submitForm" class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Frecuencia de Pago</label>
                                <select v-model="form.frecuencia" class="form-select border-0 bg-light rounded-3 shadow-none" :class="{'is-invalid': hasError('frecuencia')}">
                                    <option value="">Seleccione frecuencia...</option>
                                    <option v-for="f in frequencies" :key="f" :value="f">{{ f }}</option>
                                </select>
                                <div v-if="hasError('frecuencia')" class="invalid-feedback extra-small">{{ firstError('frecuencia') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Plazo (Cuotas/Días)</label>
                                <input v-model="form.plazo" type="number" class="form-control border-0 bg-light rounded-3 shadow-none" placeholder="Ej. 30" :class="{'is-invalid': hasError('plazo')}">
                                <div v-if="hasError('plazo')" class="invalid-feedback extra-small">{{ firstError('plazo') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Tasa Interés (%)</label>
                                <div class="input-group">
                                    <input v-model="form.tasainteres" type="number" step="0.01" class="form-control border-0 bg-light rounded-start-3 shadow-none" placeholder="0.00" :class="{'is-invalid': hasError('tasainteres')}">
                                    <span class="input-group-text border-0 bg-light rounded-end-3 text-muted fw-bold">%</span>
                                </div>
                                <div v-if="hasError('tasainteres')" class="text-danger extra-small mt-1">{{ firstError('tasainteres') }}</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Costo Mora Diario (S/)</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-warning text-dark fw-bold rounded-start-3">S/</span>
                                    <input v-model="form.costomora" type="number" step="0.01" class="form-control border-0 bg-light rounded-end-3 shadow-none" placeholder="0.00" :class="{'is-invalid': hasError('costomora')}">
                                </div>
                                <div v-if="hasError('costomora')" class="text-danger extra-small mt-1">{{ firstError('costomora') }}</div>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal" :disabled="sending">Cancelar</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" @click="submitForm" :disabled="sending">
                            <span v-if="sending" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="fas fa-save me-2"></i>
                            Guardar Registro
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
.bg-white-20 { background: rgba(255,255,255,0.2); }
.extra-small { font-size: 0.75rem; }
.form-select, .form-control { padding: 0.75rem 1rem; }
.input-group-text { padding-top: 0.75rem; padding-bottom: 0.75rem; }
</style>
