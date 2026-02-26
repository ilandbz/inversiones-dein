<script setup>
import { ref, toRefs } from 'vue';
import axios from 'axios';
import useHelper from '@/Helpers';
import useDesembolso from '@/Composables/Desembolso.js';

const props = defineProps({
  creditoId: [String, Number],
  clienteNombre: String,
  form: Object
});

const { creditoId, clienteNombre, form } = toRefs(props);
const { Toast, Swal, getConfigHeader, hideModal } = useHelper();
const { generarPdf, pdfUrl, errors } = useDesembolso();

const loadingPdf = ref(null);
const spinnerActivo = ref(false);

const cerrarModalArchivos = () => {
  const modalEl = document.getElementById('archivosModal')
  if (modalEl && modalEl.contains(document.activeElement)) {
    document.activeElement.blur()
  }
  hideModal('#archivosModal')
}

const descargarPdf = async (tipo) => {
  if (!creditoId.value) return;
  
  loadingPdf.value = tipo;
  try {
    const response = await axios.post('/credito/generar-pdf', {
      credito_id: creditoId.value,
      tipo: tipo
    }, {
      ...getConfigHeader(),
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `${tipo}_${creditoId.value}.pdf`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error('Error al generar PDF:', error);
    Toast.fire({ icon: 'error', title: 'No se pudo generar el PDF' });
  } finally {
    loadingPdf.value = null;
  }
};

const generarPDFPreview = async (archivo) => {
  if (!form.value) return;
  
  form.value.tipo = archivo;
  form.value.credito_id = creditoId.value;
  spinnerActivo.value = true;
  await generarPdf(form.value);
  spinnerActivo.value = false;
  form.value.url = pdfUrl.value;
}

const pdfOptions = [
  { label: 'Ficha de Solicitud', tipo: 'solicitud', icon: 'fas fa-file-invoice' },
  { label: 'Estados Financieros', tipo: 'Estados Financieros', icon: 'fas fa-chart-line' },
  { label: 'Análisis Cualitativo', tipo: 'Analisis Cualitativo', icon: 'fas fa-clipboard-list' },
  { label: 'Póliza de Seguro', tipo: 'Seguro', icon: 'fas fa-shield-alt' },
  { label: 'Propuesta de Crédito', tipo: 'Propuesta', icon: 'fas fa-file-contract' },
];

</script>

<template>
  <teleport to="body">
    <div
      class="modal fade"
      id="archivosModal"
      tabindex="-1"
      aria-labelledby="archivosModalLabel"
      aria-hidden="true"
      data-bs-backdrop="static"
      data-bs-keyboard="false"
    >
      <div class="modal-dialog modal-xl">
        <div class="modal-content text-dark">
          <div class="modal-header">
            <div>
              <h4 class="modal-title fs-4 mb-0" id="archivosModalLabel">Archivos del Crédito #{{ creditoId }}</h4>
              <div class="text-muted small">
                Cliente: <b>{{ clienteNombre }}</b>
              </div>
            </div>
            <button type="button" class="btn-close" @click="cerrarModalArchivos"></button>
          </div>
          <div class="modal-body p-0">

           
            <div class="card border-0 mb-0">
              <!-- Toolbar -->
              <div class="card-header bg-white sticky-top border-bottom py-3">
                <div class="d-flex flex-wrap align-items-center gap-2">
                  <button @click="generarPDFPreview('calendario')" class="btn btn-primary btn-sm px-3 py-2">
                    <i class="fas fa-calendar-alt me-1"></i> Calendario de Pagos
                  </button>
                  <button @click="generarPDFPreview('plan')" class="btn btn-primary btn-sm px-3 py-2">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Plan de Pagos
                  </button>
                  <button @click="generarPDFPreview('kardex')" class="btn btn-primary btn-sm px-3 py-2">
                    <i class="fas fa-file-alt me-1"></i> Kardex de Pagos
                  </button>
                  <button @click="generarPDFPreview('pagosmora')" class="btn btn-primary btn-sm px-3 py-2">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Pagos de Mora
                  </button>
                </div>
              </div>

              <div class="card-body p-3">
                <div class="row g-3">
                  <!-- Documentos para descargar -->
                  <div class="col-12">
                    <h6 class="mb-3 text-uppercase fw-bold small text-muted">Documentos para Descargar</h6>
                    <div class="row g-2">
                      <div v-for="opt in pdfOptions" :key="opt.tipo" class="col-md-4 col-lg-2">
                        <button 
                          class="btn btn-outline-danger btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2"
                          @click="descargarPdf(opt.tipo)"
                          :disabled="loadingPdf === opt.tipo"
                        >
                          <i :class="[opt.icon, 'mb-1 fs-5']"></i>
                          <span class="text-center lh-1 extra-small fw-bold">{{ opt.label }}</span>
                          <span v-if="loadingPdf === opt.tipo" class="spinner-border spinner-border-sm mt-1" role="status"></span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Previsualización -->
                  <div class="col-12 mt-4">
                    <div class="card border-info">
                      <div class="card-header bg-info text-white py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold">PRE VISUALIZACIÓN</span>
                        <span v-if="form?.url" class="badge bg-light text-info">{{ form.tipo?.toUpperCase() }}</span>
                      </div>
                      <div class="card-body p-0 bg-light" style="min-height: 500px;">
                        <div v-if="spinnerActivo" class="d-flex flex-column justify-content-center align-items-center" style="height: 500px;">
                          <div class="spinner-border text-primary mb-2" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Obteniendo Datos...</span>
                          </div>
                          <div class="text-primary fw-bold">Generando PDF...</div>
                        </div>
                        <div v-else>
                          <iframe v-if="form?.url" :src="form.url" class="w-100" style="height: 70vh; border:none;"></iframe>
                          <div v-else class="d-flex flex-column justify-content-center align-items-center text-muted" style="height: 500px;">
                            <i class="fas fa-file-pdf fa-4x mb-3 opacity-25"></i>
                            <p class="m-0 fw-bold">Seleccione un documento para previsualizar</p>
                            <small>Crédito ID: {{ creditoId }}</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Adjuntar Archivos (Próximamente) -->
                  <div class="col-12 mt-3">
                    <div class="alert alert-secondary py-2 mb-0 border-0">
                      <i class="fas fa-info-circle me-1"></i>
                      <small>La funcionalidad para adjuntar documentos escaneados estará disponible próximamente.</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="cerrarModalArchivos"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<style scoped>
.extra-small {
  font-size: 0.7rem;
}
.btn-outline-danger {
  border-width: 1px;
  transition: all 0.2s;
  background-color: #fff;
}
.btn-outline-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(220, 53, 69, 0.1);
  background-color: #f8dbdb;
  color: #dc3545;
}
.card-header.sticky-top {
  z-index: 1020;
}
</style>
