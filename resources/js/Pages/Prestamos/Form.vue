<script setup>
import { ref, toRefs, computed, watch, nextTick, onMounted } from 'vue'
import useHelper from '@/Helpers'
import usePersona from '@/Composables/Persona.js'
import useAsesor from '@/Composables/Asesor.js'
import usePlazo from '@/Composables/Plazo.js' 
import useCredito from '@/Composables/Credito.js' 
import useOrigenFinanciamiento from '@/Composables/OrigenFinanciamiento.js'

const { asesores, listaAsesores } = useAsesor()
const { origenes, listaOrigenesFinanciamientos } = useOrigenFinanciamiento()
const { plazos, listaPlazos } = usePlazo()
const { creditos, listaCreditos, agregarCredito, actualizarCredito, eliminarCredito, respuesta } = useCredito()

const props = defineProps({
  form: Object,
})
const emit = defineEmits(['cargar'])
const { form } = toRefs(props)
const { Toast, soloNumeros, Swal, hideModal } = useHelper()

/* ----------------- PERSONA (AVAL) ----------------- */
const { persona, errors: personaErrors, respuesta: personaRespuesta, obtenerPorDni, agregarPersona } = usePersona()
const aval = ref({
  dni: '',
  encontrado: false,
  loading: false,
  form: {
    dni: '',
    ape_pat: '',
    ape_mat: '',
    primernombre: '',
    otrosnombres: '',
    celular: '',
    email: '',
    genero:'M',
    fecha_nac:'2000-01-01',
    direccion: ''
  },
  errors: {}
})

const clearAvalErrors = () => { aval.value.errors = {} }
const hasAvalError = (k) => Array.isArray(aval.value.errors?.[k]) && aval.value.errors[k].length > 0
const firstAvalError = (k) => (aval.value.errors?.[k]?.[0] || '')

const syncAvalErrorsFromComposable = () => {
  // personaErrors viene como objeto {campo: [..]}
  aval.value.errors = personaErrors.value && typeof personaErrors.value === 'object' ? personaErrors.value : {}
}


/* ----------------- ERR HELPERS ----------------- */
const toArr = (v) => (Array.isArray(v) ? v.map(String) : v ? [String(v)] : [])
const hasError = (name) => toArr(form.value.errors?.[name]).length > 0
const firstError = (name) => toArr(form.value.errors?.[name])[0] || ''
const clearFieldError = (name) => { if (form.value.errors?.[name]) delete form.value.errors[name] }
const clearErrors = () => { form.value.errors = {} }

const scrollToFirstInvalid = async () => {
  await nextTick()
  const el = document.querySelector('#prestamomodal .is-invalid')
  if (el?.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

/* ----------------- NUM HELPERS ----------------- */
const toNumber = (v) => {
  const n = Number(String(v ?? '').replace(',', '.'))
  return Number.isFinite(n) ? n : 0
}
const toMoney2 = (n) => (Math.round((Number(n) + Number.EPSILON) * 100) / 100).toFixed(2)

const sanitizeDecimalInput = (val, { maxInt = 9, maxDec = 2 } = {}) => {
  let s = String(val ?? '').replace(/[^\d.]/g, '')
  const parts = s.split('.')
  if (parts.length > 2) s = parts[0] + '.' + parts.slice(1).join('')
  const [i = '', d = ''] = s.split('.')
  const ii = i.slice(0, maxInt)
  const dd = d.slice(0, maxDec)
  return dd.length ? `${ii}.${dd}` : ii
}

const frecuenciaOptions = computed(() => {
  const arr = Array.isArray(plazos.value) ? plazos.value : []
  return [...new Set(arr.map(x => x.frecuencia))].filter(Boolean)
})

const plazoOptions = computed(() => {
  const f = form.value.frecuencia
  const arr = Array.isArray(plazos.value) ? plazos.value : []

  return arr
    .filter(x => x.frecuencia === f)
    .map(x => ({
      value: x.plazo,
      label:
        f === 'DIARIA' ? `${x.plazo} días` :
        f === 'SEMANAL' ? `${x.plazo} semanas` :
        f === 'QUINCENAL' ? `${x.plazo} quincenas` :
        `${x.plazo} meses`,
      tasa: x.tasainteres,
      mora: x.costomora,
    }))
    .sort((a, b) => Number(a.value) - Number(b.value))
})

const selectedPlazo = computed(() => {
  const f = form.value.frecuencia
  const p = form.value.plazo
  const arr = Array.isArray(plazos.value) ? plazos.value : []
  return arr.find(x => x.frecuencia === f && String(x.plazo) === String(p)) || null
})

watch(
  () => form.value.frecuencia,
  () => {
    form.value.plazo = plazoOptions.value?.[0]?.value ?? ''
    clearFieldError('frecuencia')
    clearFieldError('plazo')
  },
  { immediate: true }
)

watch(
  () => form.value.plazo,
  () => {
    const tasa = selectedPlazo.value?.tasainteres ?? '0.00'
    const mora = selectedPlazo.value?.costomora ?? '0.00'

    form.value.tasainteres = toMoney2(toNumber(tasa))   // % como string "5.00"
    form.value.costomora   = toMoney2(toNumber(mora))   // S/ como string "10.00"

    clearFieldError('plazo')
  },
  { immediate: true }
)

watch(
  () => plazos.value,
  (newVal) => {
    if (!Array.isArray(newVal) || !newVal.length) return

    // si la frecuencia actual no existe en data, asigna la primera
    if (!frecuenciaOptions.value.includes(form.value.frecuencia)) {
      form.value.frecuencia = frecuenciaOptions.value[0] || ''
    }

    // si el plazo actual no existe para esa frecuencia, asigna el primero
    const exists = plazoOptions.value.some(p => String(p.value) === String(form.value.plazo))
    if (!exists) {
      form.value.plazo = plazoOptions.value?.[0]?.value ?? ''
    }
  },
  { immediate: true }
)

const totalCalc = computed(() => {
  const monto = toNumber(form.value.monto)
  const tasa = toNumber(form.value.tasainteres)
  const total = monto + (monto * (tasa / 100))
  return toMoney2(total)
})

watch(
  () => [form.value.monto, form.value.tasainteres],
  () => { form.value.total = totalCalc.value },
  { immediate: true }
)

/* ----------------- AVAL: BUSCAR / SET ----------------- */
const resetAval = () => {
  aval.value.dni = ''
  aval.value.encontrado = false
  aval.value.loading = false
  aval.value.form = { dni:'', ape_pat:'', ape_mat:'', primernombre:'', otrosnombres:'', celular:'', email:'', direccion:'', fecha_nac:'2000-01-01', genero:'M' }
  aval.value.errors = {}
  form.value.aval_id = null
}

const buscarAval = async () => {
  const dni = String(aval.value.dni || '').trim()
  if (dni.length !== 8) {
    aval.value.encontrado = false
    form.value.aval_id = null
    Swal.fire({ icon:'warning', title:'DNI inválido', text:'Ingrese 8 dígitos.' })
    return
  }

  aval.value.loading = true
  aval.value.encontrado = false
  form.value.aval_id = null
  clearAvalErrors()

  try {
    await obtenerPorDni(dni)

    // Si tu endpoint devuelve {} o null cuando no existe:
    if (persona.value && persona.value.id) {
      aval.value.encontrado = true
      form.value.aval_id = persona.value.id
      // opcional: llenar form de registro para mostrar (solo lectura)
      aval.value.form.dni = persona.value.dni
      aval.value.form.ape_pat = persona.value.ape_pat
      aval.value.form.ape_mat = persona.value.ape_mat
      aval.value.form.primernombre = persona.value.primernombre
      aval.value.form.otrosnombres = persona.value.otrosnombres || ''
      aval.value.form.celular = persona.value.celular || ''
      aval.value.form.email = persona.value.email || ''
      aval.value.form.direccion = persona.value.direccion || ''
      Toast?.success ? Toast.success('Aval encontrado.') : null
    } else {
      // no existe: preparar registro
      aval.value.encontrado = false
      aval.value.form.dni = dni
      Swal.fire({ icon:'info', title:'No existe', text:'Ese DNI no está registrado. Puede registrarlo aquí mismo.' })
    }
  } catch (e) {
    // si tu backend responde 404/500, lo manejas aquí
    aval.value.encontrado = false
    form.value.aval_id = null
  } finally {
    aval.value.loading = false
  }
}

const registrarAval = async () => {
  clearAvalErrors()

  // validación rápida mínima (frontend)
  const e = {}
  const req = (k) => { if (!String(aval.value.form[k] || '').trim()) e[k] = ['* Dato Obligatorio'] }
  req('dni'); req('ape_pat'); req('ape_mat'); req('primernombre'); req('celular'); req('direccion')
  if (String(aval.value.form.dni || '').trim().length !== 8) e.dni = ['DNI debe tener 8 dígitos']
  if (String(aval.value.form.celular || '').trim() && String(aval.value.form.celular).trim().length !== 9) e.celular = ['Celular debe tener 9 dígitos']
  if (Object.keys(e).length) { aval.value.errors = e; return }

  try {
    await agregarPersona({ ...aval.value.form })

    if (personaErrors.value) {
      syncAvalErrorsFromComposable()
      return
    }

    if (personaRespuesta.value?.ok == 1) {
      // luego de registrar, volvemos a buscar para obtener id (tu composable no devuelve id)
      await obtenerPorDni(aval.value.form.dni)
      if (persona.value?.id) {
        aval.value.encontrado = true
        form.value.aval_id = persona.value.id
        Swal.fire({ icon:'success', title:'Aval registrado', text:'Se asignó correctamente como aval.' })
      }
    }
  } catch (e) {
    Toast?.error ? Toast.error('Error al registrar aval.') : console.error(e)
  }
}

/* ----------------- VALIDACIÓN FRONT CREDITO ----------------- */
const validateFront = () => {
  clearErrors()
  const e = {}

  const req = (k, msg = '* Dato Obligatorio') => {
    if (!String(form.value[k] ?? '').trim()) e[k] = [msg]
  }

  req('asesor_id')
  req('tipo')
  req('monto')
  req('origen_financiamiento_id')
  req('frecuencia')
  req('plazo')

  const monto = toNumber(form.value.monto)
  if (String(form.value.monto).trim() && monto <= 0) e.monto = ['Monto debe ser mayor a 0']

  const tasa = toNumber(form.value.tasainteres)
  if (tasa < 0 || tasa > 100) e.tasainteres = ['Tasa interés debe estar entre 0 y 100']

  const mora = toNumber(form.value.costomora)
  if (mora < 0) e.costomora = ['Costo mora no puede ser negativo']

  // aval opcional: si llenó DNI pero no está asignado id, avisar
  if (String(aval.value.dni || '').trim().length === 8 && !form.value.aval_id) {
    e.aval_id = ['Debe buscar/registrar el aval para asignarlo']
  }

  if (Object.keys(e).length) {
    form.value.errors = e
    return false
  }
  return true
}

/* ----------------- GUARDAR (placeholder) ----------------- */
const isSaving = ref(false)

const guardar = async () => {
  if (isSaving.value) return
  if (!validateFront()) {
    console.log('VALIDATION ERRORS:', form.value.errors)
    await scrollToFirstInvalid()
    return
  }
  const payload = {
    id: form.value.id,
    cliente_id: form.value.cliente_id,
    asesor_id: form.value.asesor_id,
    aval_id: form.value.aval_id,
    tipo: form.value.tipo,
    monto: form.value.monto,
    origen_financiamiento_id: form.value.origen_financiamiento_id,
    frecuencia: form.value.frecuencia,
    plazo: form.value.plazo,
    tasainteres: form.value.tasainteres,
    costomora: form.value.costomora,
    total: form.value.total,
  }
  isSaving.value = true
  try {
    if (form.value.estadoCrud==='editar') {
      await actualizarCredito(payload)
    } else {
      await agregarCredito(payload)
    }
    if (respuesta.value?.ok == 1) {
      await Swal.fire({
        title: 'Registro exitoso',
        text: respuesta.value.mensaje,
        icon: 'success',
        confirmButtonText: 'Aceptar'
      });
      //Toast?.success ? Toast.success(respuesta.value.msg) : console.log(respuesta.value.msg)
      hideModal('#prestamomodal')
      emit('cargar')
      limpiar()
    } else {
      Swal.fire({
        title: 'Error',
        text: respuesta.value?.msg || 'No se pudo guardar el registro',
        icon: 'error',
        confirmButtonText: 'Aceptar'
      });
    }
  } catch (e) {
    console.error('CATCH ERROR:', e)
    console.error('AXIOS RESPONSE:', e?.response?.data)
    Toast?.error ? Toast.error(e?.response?.data?.message || 'Error al guardar.') : null
  } finally {
    isSaving.value = false
  }
}

/* ----------------- LIMPIAR ----------------- */
const limpiar = () => {
  Object.assign(form.value, {
    id: '',
    asesor_id: '',
    aval_id: null,
    tipo: 'NUEVO',
    monto: '',
    origen_financiamiento_id: '',
    frecuencia: 'MENSUAL',
    plazo: plazoOptions.value?.[0]?.value ?? '',
    fuenterecursos: '',
    tasainteres: '0.00',
    costomora: '0.00',
    total: '0.00',
    estadoCrud: 'nuevo',
    errors: {}
  })
  resetAval()
}
onMounted(() => {
  listaAsesores()
  listaOrigenesFinanciamientos()
  listaPlazos()
})
</script>

<template>
  <teleport to="body">
    <div class="modal fade" id="prestamomodal" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h4 class="modal-title fs-4 mb-0" id="prestamomodalLabel">Crédito / Préstamo</h4>
              <div class="text-muted small">
                Cliente: <b>{{ form.cliente_apenom }}</b>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">

            <!-- CREDITO -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-body">
                <h6 class="mb-2">Datos del crédito</h6>

                <div class="row g-3">
                  <div class="col-12 col-md-3">
                    <label class="form-label">Asesor</label>
                    <select v-model="form.asesor_id" class="form-select"
                      :class="{ 'is-invalid': hasError('asesor_id') }"
                      @change="clearFieldError('asesor_id')">
                      <option value="" hidden>-- Seleccione --</option>
                      <option v-for="a in asesores" :key="a.id" :value="a.id">{{ a.user.name }}</option>
                    </select>
                    <div class="invalid-feedback" v-if="hasError('asesor_id')">{{ firstError('asesor_id') }}</div>
                  </div>

                  <div class="col-12 col-md-3">
                    <label class="form-label">Tipo</label>
                    <select v-model="form.tipo" class="form-select"
                      :class="{ 'is-invalid': hasError('tipo') }"
                      @change="clearFieldError('tipo')">
                      <option value="NUEVO">NUEVO</option>
                      <option value="RENOVACIÓN">RENOVACIÓN</option>
                      <option value="REPROGRAMACIÓN">REPROGRAMACIÓN</option>
                    </select>
                    <div class="invalid-feedback" v-if="hasError('tipo')">{{ firstError('tipo') }}</div>
                  </div>

                  <div class="col-12 col-md-3">
                    <label class="form-label">Origen financiamiento</label>
                    <select v-model="form.origen_financiamiento_id" class="form-select"
                      :class="{ 'is-invalid': hasError('origen_financiamiento_id') }"
                      @change="clearFieldError('origen_financiamiento_id')">
                      <option value="" hidden>-- Seleccione --</option>
                      <option v-for="o in origenes" :key="o.id" :value="o.id">{{ o.nombre }}</option>
                    </select>
                    <div class="invalid-feedback" v-if="hasError('origen_financiamiento_id')">
                      {{ firstError('origen_financiamiento_id') }}
                    </div>
                  </div>

                  <div class="col-12 col-md-3">
                    <label class="form-label">Frecuencia</label>
                    <select v-model="form.frecuencia" class="form-select"
                      :class="{ 'is-invalid': hasError('frecuencia') }"
                      @change="clearFieldError('frecuencia')">
                      <option value="">-- Seleccione --</option>
                      <option v-for="f in frecuenciaOptions" :key="f" :value="f">
                        {{ f }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="hasError('frecuencia')">{{ firstError('frecuencia') }}</div>
                  </div>

                  <div class="col-12 col-md-3">
                    <label class="form-label">Plazo</label>
                    <select v-model="form.plazo" class="form-select"
                      :class="{ 'is-invalid': hasError('plazo') }"
                      @change="clearFieldError('plazo')">
                      <option value="" hidden>-- Seleccione --</option>
                      <option v-for="p in plazoOptions" :key="p.value" :value="p.value">
                        {{ p.label }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="hasError('plazo')">{{ firstError('plazo') }}</div>
                  </div>

                  <div class="col-12 col-md-3">
                    <label class="form-label">Monto (S/)</label>
                    <input v-model="form.monto" class="form-control"
                      :class="{ 'is-invalid': hasError('monto') }"
                      placeholder="Ej: 1500.00"
                      @input="form.monto = sanitizeDecimalInput(form.monto, { maxInt: 7, maxDec: 2 }); clearFieldError('monto')" />
                    <div class="invalid-feedback" v-if="hasError('monto')">{{ firstError('monto') }}</div>
                  </div>

                  <div class="col-12 col-md-3">
                    <label class="form-label">Tasa interés (%)</label>
                    <input v-model="form.tasainteres" class="form-control" readonly />
                  </div>

                  <div class="col-12 col-md-3">
                    <label class="form-label">Costo mora (S/)</label>
                    <input v-model="form.costomora" class="form-control" readonly />
                    <div class="form-text">Automático según el plazo</div>
                  </div>

                  <div class="col-12 col-md-3">
                    <label class="form-label">Total (S/)</label>
                    <input v-model="form.total" class="form-control" readonly />
                    <div class="form-text">Auto</div>
                  </div>

                </div>

              </div>
            </div>

            <!-- AVAL -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <h6 class="mb-0">Aval (opcional)</h6>
                  <span class="badge" :class="form.aval_id ? 'bg-success' : 'bg-light text-dark'">
                    {{ form.aval_id ? 'Asignado' : 'No asignado' }}
                  </span>
                </div>

                <div class="row g-3 align-items-end">
                  <div class="col-12 col-md-3">
                    <label class="form-label">DNI Aval</label>
                    <input
                      v-model.trim="aval.dni"
                      class="form-control"
                      :class="{ 'is-invalid': hasError('aval_id') }"
                      maxlength="8"
                      placeholder="Ej: 12345678"
                      @keypress="soloNumeros"
                      @input="
                        aval.dni = aval.dni.replace(/\D/g,'').slice(0,8);
                        form.aval_id = null;
                        aval.encontrado = false;
                        aval.form = { dni:'', ape_pat:'', ape_mat:'', primernombre:'', otrosnombres:'', celular:'', email:'', direccion:'', fecha_nac:'2000-01-01', genero:'M' };
                        aval.errors = {};
                        persona.value = {};
                        clearFieldError('aval_id');
                      "
                    />
                    <div class="invalid-feedback" v-if="hasError('aval_id')">{{ firstError('aval_id') }}</div>
                  </div>

                  <div class="col-12 col-md-3">
                    <button type="button" class="btn btn-outline-primary w-100" :disabled="aval.loading" @click="buscarAval">
                      <span v-if="aval.loading" class="spinner-border spinner-border-sm me-2"></span>
                      Buscar aval
                    </button>
                  </div>

                  <div class="col-12 col-md-3" v-if="form.aval_id">
                    <button type="button" class="btn btn-outline-danger w-100" @click="resetAval">
                      Quitar aval
                    </button>
                  </div>
                </div>

                <!-- Vista rápida si encontrado -->
                <div v-if="aval.encontrado && form.aval_id" class="alert alert-light border mt-3 mb-0">
                  <div class="fw-semibold">
                    {{ aval.form.ape_pat }} {{ aval.form.ape_mat }}, {{ aval.form.primernombre }} {{ aval.form.otrosnombres }}
                  </div>
                  <div class="small text-muted">
                    DNI: {{ aval.form.dni }} · Cel: {{ aval.form.celular || '-' }} · {{ aval.form.direccion || '-' }}
                  </div>
                </div>

                <!-- Registro inline si NO existe -->
                <div v-if="!aval.encontrado && aval.form.dni" class="mt-3">
                  <div class="border rounded p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <div class="fw-semibold">Registrar aval</div>
                      <span class="badge bg-warning text-dark">No encontrado</span>
                    </div>

                    <div class="row g-3">
                      <div class="col-12 col-md-3">
                        <label class="form-label">DNI</label>
                        <input v-model.trim="aval.form.dni" class="form-control" maxlength="8" @keypress="soloNumeros"
                          :class="{ 'is-invalid': hasAvalError('dni') }"
                          placeholder="00000000" />
                        <div class="invalid-feedback" v-if="hasAvalError('dni')">{{ firstAvalError('dni') }}</div>
                      </div>

                      <div class="col-12 col-md-3">
                        <label class="form-label">Apellido paterno</label>
                        <input v-model.trim="aval.form.ape_pat" class="form-control"
                          placeholder="Ej: PEREZ"
                          :class="{ 'is-invalid': hasAvalError('ape_pat') }" />
                        <div class="invalid-feedback" v-if="hasAvalError('ape_pat')">{{ firstAvalError('ape_pat') }}</div>
                      </div>

                      <div class="col-12 col-md-3">
                        <label class="form-label">Apellido materno</label>
                        <input v-model.trim="aval.form.ape_mat" class="form-control"
                          placeholder="Ej: PEREZ"
                          :class="{ 'is-invalid': hasAvalError('ape_mat') }" />
                        <div class="invalid-feedback" v-if="hasAvalError('ape_mat')">{{ firstAvalError('ape_mat') }}</div>
                      </div>

                      <div class="col-12 col-md-3">
                        <label class="form-label">Primer nombre</label>
                        <input v-model.trim="aval.form.primernombre" class="form-control"
                          placeholder="Ej: JUAN" :class="{ 'is-invalid': hasAvalError('primernombre') }" />
                        <div class="invalid-feedback" v-if="hasAvalError('primernombre')">{{ firstAvalError('primernombre') }}</div>
                      </div>

                      <div class="col-12 col-md-3">
                        <label class="form-label">Otros nombres</label>
                        <input v-model.trim="aval.form.otrosnombres" class="form-control" />
                      </div>
                      <div class="col-12 col-md-3">
                        <label class="form-label">Genero</label>
                        <select v-model.trim="aval.form.genero" class="form-select">
                          <option value="M">Masculino</option>
                          <option value="F">Femenino</option>
                        </select>
                      </div>
                      <div class="col-12 col-md-3">
                        <label class="form-label">Celular</label>
                        <input v-model.trim="aval.form.celular" class="form-control" maxlength="9" @keypress="soloNumeros"
                          placeholder="Ej: 999999999"
                          :class="{ 'is-invalid': hasAvalError('celular') }" />
                        <div class="invalid-feedback" v-if="hasAvalError('celular')">{{ firstAvalError('celular') }}</div>
                      </div>

                      <div class="col-12 col-md-3">
                        <label class="form-label">Email</label>
                        <input v-model.trim="aval.form.email" class="form-control" type="email"
                          placeholder="Ej: me@.com.pe"
                          :class="{ 'is-invalid': hasAvalError('email') }" />
                        <div class="invalid-feedback" v-if="hasAvalError('email')">{{ firstAvalError('email') }}</div>
                      </div>
                      <div class="col-12 col-md-3">
                        <label class="form-label">Fecha nacimiento</label>
                        <input v-model.trim="aval.form.fecha_nac" class="form-control" type="date"
                          :class="{ 'is-invalid': hasAvalError('fecha_nac') }" />
                        <div class="invalid-feedback" v-if="hasAvalError('fecha_nac')">{{ firstAvalError('fecha_nac') }}</div>
                      </div>

                      <div class="col-12 col-md-12">
                        <label class="form-label">Dirección</label>
                        <input v-model.trim="aval.form.direccion" class="form-control"
                          placeholder="Ej: CALLE LOS ROSALES 123"
                          :class="{ 'is-invalid': hasAvalError('direccion') }" />
                        <div class="invalid-feedback" v-if="hasAvalError('direccion')">{{ firstAvalError('direccion') }}</div>
                      </div>

                      <div class="col-12">
                        <button type="button" class="btn btn-primary" @click="registrarAval">
                          Registrar y asignar aval
                        </button>
                      </div>
                    </div>

                  </div>
                </div>

              </div>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light" @click="limpiar">Limpiar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" :disabled="isSaving" @click="guardar">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
              {{ (form.estadoCrud=='nuevo') ? 'Guardar' : 'Actualizar' }}
            </button>
          </div>

        </div>
      </div>
    </div>
  </teleport>
</template>
