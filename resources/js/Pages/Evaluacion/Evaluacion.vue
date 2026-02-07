<script setup>
import { ref, watch, toRefs } from 'vue'
import useBalance from '@/Composables/Balance'
import useHelper from '@/Helpers'
const { listaBalances, agregarBalance, actualizarBalance, eliminarBalance, balance, balances, errors, respuesta } = useBalance()
const props = defineProps({ form: Object })
const { form } = toRefs(props)

const { hideModal } = useHelper()

// ---------- helpers ----------
const toNumber = (v) => {
  if (v === null || v === undefined || v === '') return 0
  const s = String(v).replace(/,/g, '')
  const n = Number(s)
  return Number.isFinite(n) ? n : 0
}

// ---------- INVENTARIOS ----------
const showDetInventarios = ref(false)

const verDetInventarios = () => {
  showDetInventarios.value = !showDetInventarios.value

  // por si viene null
  if (!form.value.detinventarios) {
    form.value.detinventarios = { inv_materiales: 0, inv_prodproc: 0, inv_prodtermi: 0 }
  }
}

const recalcularInventarios = () => {
  const d = form.value.detinventarios || {}
  const total =
    toNumber(d.inv_materiales) +
    toNumber(d.inv_prodproc) +
    toNumber(d.inv_prodtermi)

  form.value.activoinventarios = Number(total.toFixed(2))
  calcularBalance?.()
}

watch(
  () => [
    form.value.detinventarios?.inv_materiales,
    form.value.detinventarios?.inv_prodproc,
    form.value.detinventarios?.inv_prodtermi,
  ],
  () => recalcularInventarios()
)

// ---------- MUEBLES / MAQUINARIA ----------
const showMuebles = ref(false)

const verMuebleMaquinaria = () => {
  showMuebles.value = !showMuebles.value

  if (!Array.isArray(form.value.muebles)) form.value.muebles = []
  if (form.value.muebles.length === 0) form.value.muebles.push({ descripcion: '', valor: 0 })
}

const recalcularMuebles = () => {
  const arr = Array.isArray(form.value.muebles) ? form.value.muebles : []
  const total = arr.reduce((acc, it) => acc + toNumber(it?.valor), 0)
  form.value.activomueble = Number(total.toFixed(2))
  calcularBalance?.()
}

const addMueble = () => {
  if (!Array.isArray(form.value.muebles)) form.value.muebles = []
  form.value.muebles.push({ descripcion: '', valor: 0 })
}

const removeMueble = (idx) => {
  form.value.muebles.splice(idx, 1)
  if (form.value.muebles.length === 0) form.value.muebles.push({ descripcion: '', valor: 0 })
}

watch(
  () => form.value.muebles,
  () => recalcularMuebles(),
  { deep: true }
)

// ---------- DEUDAS ENTIDADES ----------
const showDeudasEnt = ref(false)

const verDeudaEntidades = () => {
  showDeudasEnt.value = !showDeudasEnt.value

  if (!Array.isArray(form.value.deudas)) form.value.deudas = []
  if (form.value.deudas.length === 0) form.value.deudas.push({ entidad: '', saldo: 0 })
}

const recalcularDeudasEnt = () => {
  const arr = Array.isArray(form.value.deudas) ? form.value.deudas : []
  const total = arr.reduce((acc, it) => acc + toNumber(it?.saldo), 0)
  form.value.pasivodeudaent = Number(total.toFixed(2))
  calcularBalance?.()
}

const addDeudaEnt = () => {
  if (!Array.isArray(form.value.deudas)) form.value.deudas = []
  form.value.deudas.push({ entidad: '', saldo: 0 })
}

const removeDeudaEnt = (idx) => {
  form.value.deudas.splice(idx, 1)
  if (form.value.deudas.length === 0) form.value.deudas.push({ entidad: '', saldo: 0 })
}

const guardar=async()=>{
    await agregarBalance(form.value)
    form.value.errors = []
    if(errors.value)
    {
        form.value.errors = errors.value
    }
    if(respuesta.value.ok==1){
        form.value.errors = []
        // Toast.fire({icon:'success', title:respuesta.value.mensaje})
        
        Swal.fire({
            title: 'Registro exitoso',
            text: respuesta.value.mensaje,
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
        hideModal('#evaluacionModal')

        // emit('onListar', currentPage.value)

        
    }
    
}

const round2 = (n) => Number((toNumber(n)).toFixed(2))

const calcularBalance = () => {
  // --- ACTIVO CORRIENTE ---
  const ac_caja       = toNumber(form.value.activocaja)
  const ac_bancos     = toNumber(form.value.activobancos)
  const ac_ctas       = toNumber(form.value.activoctascobrar)
  const ac_invent     = toNumber(form.value.activoinventarios)

  const totalACorr = ac_caja + ac_bancos + ac_ctas + ac_invent
  form.value.totalacorriente = round2(totalACorr)

  // --- ACTIVO NO CORRIENTE ---
  const anc_mueble    = toNumber(form.value.activomueble)
  const anc_otros     = toNumber(form.value.activootrosact)
  const anc_depre     = toNumber(form.value.activodepre) // ojo: usualmente es (-), pero tu UI lo maneja como número

  // Si tu depreciación debe RESTAR, usa:
  // const totalANCorr = anc_mueble + anc_otros - anc_depre
  // Si la quieres SUMAR como estás haciendo ahora:
  const totalANCorr = anc_mueble + anc_otros + anc_depre

  form.value.totalancorriente = round2(totalANCorr)

  // --- TOTAL ACTIVO ---
  const totalActivo = totalACorr + totalANCorr
  form.value.total_activo = round2(totalActivo)

  // --- PASIVO CORRIENTE ---
  const pc_prov      = toNumber(form.value.pasivodeudaprove)
  const pc_ent       = toNumber(form.value.pasivodeudaent)

  const totalPCorr = pc_prov + pc_ent
  form.value.totalpcorriente = round2(totalPCorr)

  // --- PASIVO NO CORRIENTE ---
  const pnc_largo    = toNumber(form.value.pasivolargop)
  const pnc_otras    = toNumber(form.value.otrascuentaspagar)

  const totalPNCorr = pnc_largo + pnc_otras
  form.value.totalpncorriente = round2(totalPNCorr)

  // --- TOTAL PASIVO ---
  const totalPasivo = totalPCorr + totalPNCorr
  form.value.total_pasivo = round2(totalPasivo)

  // --- PASIVO + PATRIMONIO (paspatrimonio) ---
  const patr = toNumber(form.value.patrimonio)
  form.value.paspatrimonio = round2(totalPasivo + patr)

  // --- CAPITAL DE TRABAJO ---
  form.value.captrabajo = round2(totalACorr - totalPCorr)
}

const clearZeroOnFocus = (e) => {
  if (e.target.value === '0') {
    e.target.value = ''
  }
}

watch(
  () => form.value.deudas,
  () => recalcularDeudasEnt(),
  { deep: true }
)
</script>


<template>
  <teleport to="body">
    <div class="modal fade" id="evaluacionModal" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h4 class="modal-title fs-4 mb-0" id="evaluacionModalLabel">Evaluacion de Riesgo Crediticio</h4>
              <div class="text-muted small">
                Cliente: <b>{{ form.cliente_apenom }}</b>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Evaluacion de Riesgo Crediticio</h6>

                    <!-- ACTIVO + PASIVO -->
                    <div class="row g-3">
                        <!-- ACTIVO -->
                        <div class="col-12 col-lg-6">
                            <div class="card h-100">
                                <div class="card-header">ACTIVO</div>
                                <div class="card-body">
                                    <h5 class="mb-3">CORRIENTE</h5>

                                    <div class="row g-2">
                                        <!-- CAJA DE NEGOCIO -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label mb-1">CAJA DE NEGOCIO</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.activocaja"
                                            :class="{ 'is-invalid': form.errors.activocaja?.length }"
                                            placeholder="CAJA DE NEGOCIO"
                                            @focus="clearZeroOnFocus"
                                            @change="calcularBalance()"
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.activocaja?.length">
                                            <div v-for="error in form.errors.activocaja" :key="error">{{ error }}</div>
                                            </div>
                                        </div>

                                        <!-- FONDO BANCOS -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label mb-1">FONDO BANCOS</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.activobancos"
                                            @focus="clearZeroOnFocus"
                                            :class="{ 'is-invalid': form.errors.activobancos?.length }"
                                            placeholder="FONDO BANCOS"
                                            @change="calcularBalance()"
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.activobancos?.length">
                                            <div v-for="error in form.errors.activobancos" :key="error">{{ error }}</div>
                                            </div>
                                        </div>

                                        <!-- ACREEDORES -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label mb-1">ACREEDORES</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.activoctascobrar"
                                            @focus="clearZeroOnFocus"
                                            :class="{ 'is-invalid': form.errors.activoctascobrar?.length }"
                                            placeholder="ACREEDORES"
                                            @change="calcularBalance()"
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.activoctascobrar?.length">
                                            <div v-for="error in form.errors.activoctascobrar" :key="error">{{ error }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label mb-1">INVENTARIOS</label>

                                            <div class="input-group">
                                                <input
                                                type="text"
                                                class="form-control form-control-sm"
                                                :class="{ 'is-invalid': form.errors.activoinventarios?.length }"
                                                :value="'S/. ' + Number(form.activoinventarios || 0).toFixed(2)"
                                                placeholder="INVENTARIOS"
                                                readonly
                                                />

                                                <button
                                                class="btn btn-outline-secondary btn-sm"
                                                :title="showDetInventarios ? 'Ocultar' : 'Ver'"
                                                type="button"
                                                @click="verDetInventarios()"
                                                >
                                                <i class="fas" :class="showDetInventarios ? 'fa-eye-slash' : 'fa-eye'"></i>
                                                </button>
                                            </div>

                                            <div class="invalid-feedback d-block" v-if="form.errors.activoinventarios?.length">
                                                <div v-for="error in form.errors.activoinventarios" :key="error">{{ error }}</div>
                                            </div>

                                        </div>

                                        <!-- DETALLE (debajo, sin modal) -->
                                        <div class="col-12">
                                            <div v-if="showDetInventarios" class="border rounded p-2 mt-2 bg-light">
                                                <div class="row mb-2">
                                                <label class="control-label col-md-8">INVENTARIO DE MATERIALES</label>
                                                    <div class="col-md-4">
                                                        <div class="input-group input-group-sm">
                                                        <span class="input-group-text">S/.</span>
                                                        <input
                                                            type="text"
                                                            v-model="form.detinventarios.inv_materiales"
                                                            @focus="clearZeroOnFocus"
                                                            class="form-control form-control-sm"
                                                            @keypress="onlyNumbersAndDecimal"
                                                        />
                                                        </div>
                                                    </div>
                                                </div>
                
                                                <div class="row mb-2">
                                                <label class="control-label col-md-8">INVENTARIO DE PROD EN PROCESO</label>
                                                <div class="col-md-4">
                                                    <div class="input-group input-group-sm">
                                                    <span class="input-group-text">S/.</span>
                                                    <input
                                                        type="text"
                                                        v-model="form.detinventarios.inv_prodproc"
                                                        @focus="clearZeroOnFocus"
                                                        class="form-control form-control-sm"
                                                        @keypress="onlyNumbersAndDecimal"
                                                    />
                                                    </div>
                                                </div>
                                                </div>
                
                                                <div class="row">
                                                <label class="control-label col-md-8">INVENTARIO DE PROD TERMINADOS</label>
                                                <div class="col-md-4">
                                                    <div class="input-group input-group-sm">
                                                    <span class="input-group-text">S/.</span>
                                                    <input
                                                        type="text"
                                                        v-model="form.detinventarios.inv_prodtermi"
                                                        @focus="clearZeroOnFocus"
                                                        class="form-control form-control-sm"
                                                        @keypress="onlyNumbersAndDecimal"
                                                    />
                                                    </div>
                                                </div>
                                                </div>
                
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                <small class="text-muted">Total inventarios</small>
                                                <b>S/. {{ Number(form.activoinventarios || 0).toFixed(2) }}</b>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- TOTAL ACTIVO CORRIENTE -->
                                        <div class="col-12">
                                            <label class="form-label mb-1">TOTAL ACTIVO CORRIENTE</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm bg-light"
                                            :value="'S/. ' + Number(form.totalacorriente || 0).toFixed(2)"
                                            placeholder="TOTAL ACTIVO CORRIENTE"
                                            readonly
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.totalacorriente?.length">
                                            <div v-for="error in form.errors.totalacorriente" :key="error">{{ error }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-3" />

                                    <h5 class="mb-3">NO CORRIENTE</h5>

                                    <div class="row g-2">
                                        <div class="col-6 col-md-6">
                                            <label class="form-label mb-1">MUEBLES, MAQUINARIA Y EQUIPO</label>

                                            <div class="input-group">
                                                <input
                                                type="text"
                                                class="form-control form-control-sm"
                                                :class="{ 'is-invalid': form.errors.activomueble?.length }"
                                                :value="'S/. ' + Number(form.activomueble || 0).toFixed(2)"
                                                placeholder="MUEBLES, MAQUINARIA Y EQUIPO"
                                                readonly
                                                />

                                                <button
                                                class="btn btn-outline-secondary btn-sm"
                                                :title="showMuebles ? 'Ocultar' : 'Ver'"
                                                type="button"
                                                @click="verMuebleMaquinaria()"
                                                >
                                                <i class="fas" :class="showMuebles ? 'fa-eye-slash' : 'fa-eye'"></i>
                                                </button>
                                            </div>

                                            <div class="invalid-feedback d-block" v-if="form.errors.activomueble?.length">
                                                <div v-for="error in form.errors.activomueble" :key="error">{{ error }}</div>
                                            </div>

                                        </div>
                                        <!-- OTROS ACTIVOS -->
                                        <div class="col-6 col-md-6">
                                            <label class="form-label mb-1">OTROS ACTIVOS</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.activootrosact"
                                            @focus="clearZeroOnFocus"
                                            :class="{ 'is-invalid': form.errors.activootrosact?.length }"
                                            placeholder="OTROS ACTIVOS"
                                            @change="calcularBalance()"
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.activootrosact?.length">
                                            <div v-for="error in form.errors.activootrosact" :key="error">{{ error }}</div>
                                            </div>
                                        </div>
                                        <div v-if="showMuebles" class="col-12 col-md-12">
                                            <div class="border rounded p-2 mt-2 bg-light">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">Detalle de muebles/maquinaria/equipo</small>

                                                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addMueble()">
                                                    <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>

                                                <div v-for="(item, idx) in form.muebles" :key="idx" class="row g-2 align-items-center mb-2">
                                                    <!-- Descripción -->
                                                    <div class="col-12 col-md-7">
                                                    <input
                                                        type="text"
                                                        class="form-control form-control-sm"
                                                        v-model="item.descripcion"
                                                        placeholder="Descripción"
                                                    />
                                                    </div>

                                                    <!-- Monto -->
                                                    <div class="col-9 col-md-4">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">S/.</span>
                                                        <input
                                                        type="text"
                                                        class="form-control form-control-sm text-end"
                                                        v-model="item.valor"
                                                        @focus="clearZeroOnFocus"
                                                        @keypress="onlyNumbersAndDecimal"
                                                        placeholder="0.00"
                                                        />
                                                    </div>
                                                    </div>

                                                    <!-- Eliminar -->
                                                    <div class="col-3 col-md-1 text-end">
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-danger btn-sm w-100"
                                                        title="Eliminar"
                                                        @click="removeMueble(idx)"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <small class="text-muted">Total</small>
                                                    <b>S/. {{ Number(form.activomueble || 0).toFixed(2) }}</b>
                                                </div>
                                            </div>
                                        </div>    
                                        <!-- DEPRECIACION... -->
                                        <div class="col-12">
                                            <label class="form-label mb-1">DEPRECIACION, AMORTIZACION Y AGOTAMIENTO ACUMULADO</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.activodepre"
                                            @focus="clearZeroOnFocus"
                                            :class="{ 'is-invalid': form.errors.activodepre?.length }"
                                            placeholder="DEPRECIACION, AMORTIZACION Y AGOTAMIENTO ACUMULADO"
                                            @change="calcularBalance()"
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.activodepre?.length">
                                            <div v-for="error in form.errors.activodepre" :key="error">{{ error }}</div>
                                            </div>
                                        </div>
                                        <!-- TOTAL ACTIVO NO CORRIENTE -->
                                        <div class="col-12">
                                            <label class="form-label mb-1">TOTAL ACTIVO NO CORRIENTE</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm bg-light"
                                            :value="'S/. ' + Number(form.totalancorriente || 0).toFixed(2)"
                                            placeholder="TOTAL ACTIVO NO CORRIENTE"
                                            readonly
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.totalancorriente?.length">
                                                <div v-for="error in form.errors.totalancorriente" :key="error">{{ error }}</div>
                                            </div>
                                        </div>
                                        <!-- TOTAL ACTIVO -->
                                        <div class="col-12">
                                            <label class="form-label mb-1">TOTAL ACTIVO</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm bg-light"
                                            :value="'S/. ' + Number(form.total_activo || 0).toFixed(2)"
                                            placeholder="TOTAL ACTIVO"
                                            readonly
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.total_activo?.length">
                                            <div v-for="error in form.errors.total_activo" :key="error">{{ error }}</div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- PASIVO -->
                        <div class="col-12 col-lg-6">
                            <div class="card h-100">
                                <div class="card-header">PASIVO</div>
                                <div class="card-body">
                                    <h5 class="mb-3">CORRIENTE</h5>

                                    <div class="row g-2">
                                        <!-- PROVEEDORES -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label mb-1">PROVEEDORES</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.pasivodeudaprove"
                                            @focus="clearZeroOnFocus"
                                            :class="{ 'is-invalid': form.errors.pasivodeudaprove?.length }"
                                            placeholder="PROVEEDORES"
                                            @change="calcularBalance()"
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.pasivodeudaprove?.length">
                                            <div v-for="error in form.errors.pasivodeudaprove" :key="error">{{ error }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label mb-1" title="DEUDAS CON ENTIDADES FINANCIERAS">DEUDAS CON ENTIDADES</label>

                                            <div class="input-group">
                                                <input
                                                type="text"
                                                class="form-control form-control-sm"
                                                :class="{ 'is-invalid': form.errors.pasivodeudaent?.length }"
                                                :value="'S/. ' + Number(form.pasivodeudaent || 0).toFixed(2)"
                                                placeholder="DEUDAS CON ENTIDADES FINANCIERAS"
                                                readonly
                                                />

                                                <button
                                                class="btn btn-outline-secondary btn-sm"
                                                :title="showDeudasEnt ? 'Ocultar' : 'Ver'"
                                                type="button"
                                                @click="verDeudaEntidades()"
                                                >
                                                <i class="fas" :class="showDeudasEnt ? 'fa-eye-slash' : 'fa-eye'"></i>
                                                </button>
                                            </div>

                                            <div class="invalid-feedback d-block" v-if="form.errors.pasivodeudaent?.length">
                                                <div v-for="error in form.errors.pasivodeudaent" :key="error">{{ error }}</div>
                                            </div>

                                        </div>

                                        <div v-if="showDeudasEnt" class="col-12">
                                            <div class="border rounded p-2 mt-2 bg-light">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">Detalle de deudas con entidades</small>

                                                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addDeudaEnt()">
                                                    <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>

                                                <div v-for="(item, idx) in form.deudas" :key="idx" class="row g-2 align-items-center mb-2">
                                                    <!-- Entidad -->
                                                    <div class="col-12 col-md-7">
                                                    <input
                                                        type="text"
                                                        class="form-control form-control-sm"
                                                        v-model="item.entidad"
                                                        placeholder="Entidad financiera"
                                                    />
                                                    </div>

                                                    <!-- Saldo -->
                                                    <div class="col-9 col-md-4">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">S/.</span>
                                                        <input
                                                        type="text"
                                                        class="form-control form-control-sm text-end"
                                                        v-model="item.saldo"
                                                        @focus="clearZeroOnFocus"
                                                        @keypress="onlyNumbersAndDecimal"
                                                        placeholder="0.00"
                                                        />
                                                    </div>
                                                    </div>

                                                    <!-- Eliminar -->
                                                    <div class="col-3 col-md-1 text-end">
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-danger btn-sm w-100"
                                                        title="Eliminar"
                                                        @click="removeDeudaEnt(idx)"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <small class="text-muted">Total</small>
                                                    <b>S/. {{ Number(form.pasivodeudaent || 0).toFixed(2) }}</b>
                                                </div>
                                            </div>                                
                                        </div>


                                        <!-- TOTAL PASIVO CORRIENTE -->
                                        <div class="col-12">
                                            <label class="form-label mb-1">TOTAL PASIVO CORRIENTE</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm bg-light"
                                            :value="'S/. ' + Number(form.totalpcorriente || 0).toFixed(2)"
                                            placeholder="TOTAL PASIVO CORRIENTE"
                                            readonly
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.totalpcorriente?.length">
                                            <div v-for="error in form.errors.totalpcorriente" :key="error">{{ error }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-3" />

                                    <h5 class="mb-3">NO CORRIENTE</h5>

                                    <div class="row g-2">
                                        <!-- PASIVO LARGO PLAZO -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label mb-1">PASIVO LARGO PLAZO</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.pasivolargop"
                                            @focus="clearZeroOnFocus"
                                            :class="{ 'is-invalid': form.errors.pasivolargop?.length }"
                                            placeholder="PASIVO LARGO PLAZO"
                                            @change="calcularBalance()"
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.pasivolargop?.length">
                                            <div v-for="error in form.errors.pasivolargop" :key="error">{{ error }}</div>
                                            </div>
                                        </div>

                                        <!-- OTRAS CUENTAS POR PAGAR -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label mb-1">OTRAS CUENTAS POR PAGAR</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.otrascuentaspagar"
                                            @focus="clearZeroOnFocus"
                                            :class="{ 'is-invalid': form.errors.otrascuentaspagar?.length }"
                                            placeholder="OTRAS CUENTAS POR PAGAR"
                                            @change="calcularBalance()"
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.otrascuentaspagar?.length">
                                            <div v-for="error in form.errors.otrascuentaspagar" :key="error">{{ error }}</div>
                                            </div>
                                        </div>

                                        <!-- TOTAL PASIVO NO CORRIENTE -->
                                        <div class="col-12">
                                            <label class="form-label mb-1">TOTAL PASIVO NO CORRIENTE</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.totalpncorriente"
                                            :class="{ 'is-invalid': form.errors.totalpncorriente?.length }"
                                            placeholder="TOTAL PASIVO NO CORRIENTE"
                                            readonly
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.totalpncorriente?.length">
                                            <div v-for="error in form.errors.totalpncorriente" :key="error">{{ error }}</div>
                                            </div>
                                        </div>

                                        <!-- TOTAL PASIVO -->
                                        <div class="col-12">
                                            <label class="form-label mb-1">TOTAL PASIVO</label>
                                            <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="form.total_pasivo"
                                            :class="{ 'is-invalid': form.errors.total_pasivo?.length }"
                                            placeholder="TOTAL PASIVO"
                                            readonly
                                            @keypress="onlyNumbersAndDecimal"
                                            />
                                            <div class="invalid-feedback d-block" v-if="form.errors.total_pasivo?.length">
                                            <div v-for="error in form.errors.total_pasivo" :key="error">{{ error }}</div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PATRIMONIO -->
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <div class="card">
                            <div class="card-header">PATRIMONIO</div>
                            <div class="card-body">
                                <div class="row g-2 align-items-end">
                                <!-- PATRIMONIO EMPRESARIAL -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label mb-1">PATRIMONIO EMPRESARIAL</label>
                                    <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    v-model="form.patrimonio"
                                    @focus="clearZeroOnFocus"
                                    :class="{ 'is-invalid': form.errors.patrimonio?.length }"
                                    placeholder="PATRIMONIO EMPRESARIAL"
                                    @keypress="onlyNumbersAndDecimal"
                                    />
                                    <div class="invalid-feedback d-block" v-if="form.errors.patrimonio?.length">
                                    <div v-for="error in form.errors.patrimonio" :key="error">{{ error }}</div>
                                    </div>
                                </div>

                                <!-- TOTAL PATRIMONIO (si es otro campo, cambia el v-model) -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label mb-1">TOTAL PATRIMONIO</label>
                                    <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    v-model="form.patrimonio"
                                    :class="{ 'is-invalid': form.errors.patrimonio?.length }"
                                    placeholder="TOTAL PATRIMONIO"
                                    @keypress="onlyNumbersAndDecimal"
                                    />
                                    <div class="invalid-feedback d-block" v-if="form.errors.patrimonio?.length">
                                    <div v-for="error in form.errors.patrimonio" :key="error">{{ error }}</div>
                                    </div>
                                </div>

                                <!-- PASIVO Y PATRIMONIO -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label mb-1">PASIVO Y PATRIMONIO</label>
                                    <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    :value="'S/. ' + Number(form.paspatrimonio || 0).toFixed(2)"
                                    placeholder="PASIVO Y PATRIMONIO"
                                    readonly
                                    />
                                </div>

                                <!-- CAPITAL DE TRABAJO -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label mb-1">CAPITAL DE TRABAJO</label>
                                    <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    :value="'S/. ' + Number(form.captrabajo || 0).toFixed(2)"
                                    placeholder="CAPITAL DE TRABAJO"
                                    readonly
                                    />
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" @click="guardar">
              Guardar
            </button>
          </div>

        </div>
      </div>
    </div>
  </teleport>
</template>
