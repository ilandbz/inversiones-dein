<script setup>
import { ref, toRefs } from 'vue';
import axios from 'axios';
import useHelper from '@/Helpers';

const props = defineProps({
  creditoId: [String, Number],
  clienteNombre: String
});

const { creditoId, clienteNombre } = toRefs(props);
const { Toast, Swal, getConfigHeader } = useHelper();

const loadingPdf = ref(null);

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
    <div class="modal fade" id="archivosModal" tabindex="-1" aria-labelledby="archivosModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg">
        <div class="modal-content text-dark">
          <div class="modal-header">
            <div>
              <h4 class="modal-title fs-4 mb-0" id="archivosModalLabel">Archivos del Crédito #{{ creditoId }}</h4>
              <div class="text-muted small">
                Cliente: <b>{{ clienteNombre }}</b>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <!-- Generar PDFs -->
              <div class="col-md-12">
                <h6 class="mb-3">Documentos Generados</h6>
                <div class="row g-2">
                  <div v-for="opt in pdfOptions" :key="opt.tipo" class="col-md-6 col-lg-4">
                    <button 
                      class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-between p-3"
                      @click="descargarPdf(opt.tipo)"
                      :disabled="loadingPdf === opt.tipo"
                    >
                      <div class="d-flex align-items-center">
                        <i :class="[opt.icon, 'me-2 fs-5']"></i>
                        <span class="text-start lh-1 small fw-bold">{{ opt.label }}</span>
                      </div>
                      <span v-if="loadingPdf === opt.tipo" class="spinner-border spinner-border-sm" role="status"></span>
                      <i v-else class="fas fa-download"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Próximamente: Adjuntar Archivos -->
              <div class="col-md-12 mt-4">
                <h6 class="mb-3">Archivos Adjuntos (Digitalización)</h6>
                <div class="alert alert-info">
                  <i class="fas fa-info-circle me-2"></i>
                  La funcionalidad para subir fotos y documentos escaneados estará disponible en la próxima actualización.
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<style scoped>
.btn-outline-danger {
  border-width: 2px;
  transition: all 0.2s;
}
.btn-outline-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(220, 53, 69, 0.1);
}
</style>
