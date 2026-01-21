<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import MazerLayout from '@/Layouts/MazerLayout.vue'

/**
 * FORM según tu tabla
 */
const form = reactive({
  cliente_id: null,
  agencia_id: '',
  asesor_id: '',
  estado: 'ACTIVO',
  fecha_reg: new Date().toISOString().slice(0, 10),
  tipo: 'NUEVO',
  monto: '',
  producto: '',
  frecuencia: 'MENSUAL',
  plazo: '',
  medioorigen: '',
  dondepagara: 'NINGUNO',
  fuenterecursos: '',
  tasainteres: '0.000',
  total: '0.00',
  costomora: '0.00',
  mencion: 'PRINCIPAL',
  fecha_venc: ''
})

/**
 * ====== BUSCADOR DE CLIENTE EXISTENTE ======
 * (Por ahora es front-only con data mock.
 * Luego lo conectamos con endpoint real /clientes/buscar?q=...
)
 */
const q = ref('')
const isSearching = ref(false)
const selectedCliente = ref(null)

// MOCK: reemplazar luego por API
const clientesMock = [
  { id: 1, documento: '12345678', nombres: 'Juan', apellidos: 'Pérez', telefono: '999111222' },
  { id: 2, documento: '10456789012', nombres: 'María', apellidos: 'López', telefono: '988777666' },
  { id: 3, documento: '87654321', nombres: 'Carlos', apellidos: 'Díaz', telefono: '977555444' }
]

const results = computed(() => {
  const term = q.value.trim().toLowerCase()
  if (!term) return []
  return clientesMock.filter(c => {
    const full = `${c.nombres} ${c.apellidos}`.toLowerCase()
    return c.documento.includes(term) || full.includes(term)
  }).slice(0, 8)
})

const selectCliente = (c) => {
  selectedCliente.value = c
  form.cliente_id = c.id
  q.value = `${c.documento} - ${c.nombres} ${c.apellidos}`
}

const clearCliente = () => {
  selectedCliente.value = null
  form.cliente_id = null
  q.value = ''
}

/**
 * ====== CÁLCULOS PREVIEW (ajustamos después a tu lógica real) ======
 * total = monto + (monto * tasaInteres * plazo)
 * - tasa en porcentaje: ejemplo 3.500 => 3.5%
 */
const totalPreview = computed(() => {
  const monto = parseFloat(form.monto || 0)
  const tasa = parseFloat(form.tasainteres || 0) / 100
  const plazo = parseInt(form.plazo || 0)
  if (!monto || !plazo) return 0
  return monto + (monto * tasa * plazo)
})

watch(totalPreview, (v) => {
  form.total = Number.isFinite(v) ? v.toFixed(2) : '0.00'
})

/**
 * ====== SUBMIT ======
 * Lo dejo listo para que luego lo conectes al backend.
 */
const submit = () => {
  if (!form.cliente_id) {
    alert('Selecciona un cliente existente.')
    return
  }

  // Luego:
  // router.post('/prestamos', form)
  console.log('Enviar préstamo:', JSON.parse(JSON.stringify(form)))
  alert('OK (demo). Luego lo conectamos al POST real.')
}
</script>

<template>
  <Head title="Registrar Préstamo" />

  <MazerLayout>
    <div class="page-heading">
      <h3>Préstamos</h3>
      <p class="text-subtitle text-muted">Registro de préstamo para cliente existente</p>
    </div>

    <div class="page-content">
      <section class="row">

        <!-- IZQUIERDA: CLIENTE + PRÉSTAMO -->
        <div class="col-12 col-lg-8">
          <!-- Cliente -->
          <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <h4 class="card-title mb-0">Cliente</h4>
              <button v-if="selectedCliente" class="btn btn-light btn-sm" type="button" @click="clearCliente">
                <i class="bi bi-x-circle me-1"></i> Cambiar cliente
              </button>
            </div>

            <div class="card-body">
              <label class="form-label">Buscar cliente (DNI/RUC o nombres)</label>

              <div class="position-relative">
                <input
                  v-model="q"
                  class="form-control"
                  placeholder="Ej: 12345678 o Juan Pérez"
                  :disabled="!!selectedCliente"
                />

                <!-- Dropdown resultados -->
                <div
                  v-if="!selectedCliente && q.trim() && results.length"
                  class="list-group position-absolute w-100 shadow"
                  style="z-index: 50; max-height: 260px; overflow: auto;"
                >
                  <button
                    v-for="c in results"
                    :key="c.id"
                    type="button"
                    class="list-group-item list-group-item-action"
                    @click="selectCliente(c)"
                  >
                    <div class="d-flex justify-content-between">
                      <div>
                        <div class="fw-semibold">{{ c.nombres }} {{ c.apellidos }}</div>
                        <div class="small text-muted">{{ c.documento }} · {{ c.telefono }}</div>
                      </div>
                      <div class="text-muted small d-flex align-items-center">
                        Seleccionar <i class="bi bi-chevron-right ms-2"></i>
                      </div>
                    </div>
                  </button>
                </div>
              </div>

              <div v-if="!selectedCliente" class="form-text mt-2">
                Selecciona un cliente para continuar.
              </div>

              <div v-if="selectedCliente" class="alert alert-light mt-3 mb-0">
                <div class="d-flex align-items-start gap-3">
                  <i class="bi bi-person-badge fs-4"></i>
                  <div>
                    <div class="fw-semibold">
                      {{ selectedCliente.nombres }} {{ selectedCliente.apellidos }}
                    </div>
                    <div class="text-muted small">
                      Doc: {{ selectedCliente.documento }} · Tel: {{ selectedCliente.telefono }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Préstamo -->
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Datos del préstamo</h4>
            </div>

            <div class="card-body">
              <form @submit.prevent="submit">
                <div class="row g-3">

                  <div class="col-12 col-md-4">
                    <label class="form-label">Agencia (ID)</label>
                    <input v-model="form.agencia_id" class="form-control" placeholder="Ej: 1" />
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Asesor (ID)</label>
                    <input v-model="form.asesor_id" class="form-control" placeholder="Ej: 3" />
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Estado</label>
                    <select v-model="form.estado" class="form-select">
                      <option value="ACTIVO">ACTIVO</option>
                      <option value="PENDIENTE">PENDIENTE</option>
                      <option value="CANCELADO">CANCELADO</option>
                      <option value="MORA">MORA</option>
                    </select>
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Fecha registro</label>
                    <input v-model="form.fecha_reg" type="date" class="form-control" />
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Tipo</label>
                    <input v-model="form.tipo" class="form-control" placeholder="Ej: NUEVO / RENOVACIÓN" />
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Producto</label>
                    <input v-model="form.producto" class="form-control" placeholder="Ej: PERSONAL" />
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Monto</label>
                    <input v-model="form.monto" type="number" step="0.01" class="form-control" placeholder="Ej: 5000" />
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Frecuencia</label>
                    <select v-model="form.frecuencia" class="form-select">
                      <option value="DIARIO">DIARIO</option>
                      <option value="SEMANAL">SEMANAL</option>
                      <option value="QUINCENAL">QUINCENAL</option>
                      <option value="MENSUAL">MENSUAL</option>
                    </select>
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Plazo</label>
                    <input v-model="form.plazo" type="number" class="form-control" placeholder="Ej: 12" />
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Tasa interés (%)</label>
                    <input v-model="form.tasainteres" type="number" step="0.001" class="form-control" placeholder="Ej: 3.500" />
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Costo mora</label>
                    <input v-model="form.costomora" type="number" step="0.01" class="form-control" placeholder="Ej: 1.50" />
                  </div>

                  <div class="col-12 col-md-4">
                    <label class="form-label">Total (preview)</label>
                    <input v-model="form.total" class="form-control" readonly />
                  </div>

                  <div class="col-12 col-md-6">
                    <label class="form-label">Medio origen</label>
                    <input v-model="form.medioorigen" class="form-control" placeholder="Ej: REFERIDOS / FACEBOOK" />
                  </div>

                  <div class="col-12 col-md-6">
                    <label class="form-label">Fuente recursos</label>
                    <input v-model="form.fuenterecursos" class="form-control" placeholder="Ej: NEGOCIO / SUELDO" />
                  </div>

                  <div class="col-12 col-md-6">
                    <label class="form-label">Dónde pagará</label>
                    <input v-model="form.dondepagara" class="form-control" placeholder="Ej: NINGUNO / CAJA / BANCO" />
                  </div>

                  <div class="col-12 col-md-6">
                    <label class="form-label">Fecha vencimiento (opcional)</label>
                    <input v-model="form.fecha_venc" type="date" class="form-control" />
                  </div>

                  <div class="col-12">
                    <label class="form-label">Mención</label>
                    <input v-model="form.mencion" class="form-control" placeholder="Ej: PRINCIPAL" />
                  </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                  <button type="submit" class="btn btn-primary" :disabled="!form.cliente_id">
                    <i class="bi bi-save me-1"></i> Guardar Préstamo
                  </button>

                  <button type="button" class="btn btn-light" @click="router.visit('/dashboard')">
                    Cancelar
                  </button>
                </div>

              </form>
            </div>
          </div>
        </div>

        <!-- DERECHA: RESUMEN -->
        <div class="col-12 col-lg-4">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Resumen</h4>
            </div>

            <div class="card-body">
              <div class="mb-2 text-muted small">Cliente</div>
              <div class="mb-3">
                <div class="fw-semibold" v-if="selectedCliente">
                  {{ selectedCliente.nombres }} {{ selectedCliente.apellidos }}
                </div>
                <div class="text-muted" v-else>— Selecciona un cliente —</div>
              </div>

              <hr />

              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Monto</span>
                <span class="fw-semibold">S/ {{ Number(form.monto || 0).toFixed(2) }}</span>
              </div>

              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Tasa</span>
                <span class="fw-semibold">{{ form.tasainteres || '0.000' }}%</span>
              </div>

              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Plazo</span>
                <span class="fw-semibold">{{ form.plazo || 0 }}</span>
              </div>

              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Frecuencia</span>
                <span class="fw-semibold">{{ form.frecuencia }}</span>
              </div>

              <hr />

              <div class="d-flex justify-content-between">
                <span class="text-muted">Total (preview)</span>
                <span class="fw-bold">S/ {{ Number(form.total || 0).toFixed(2) }}</span>
              </div>

              <div class="alert alert-light mt-3 mb-0 small">
                Este total es solo referencia. Luego lo conectamos a tu fórmula real y cronograma.
              </div>
            </div>
          </div>
        </div>

      </section>
    </div>
  </MazerLayout>
</template>
