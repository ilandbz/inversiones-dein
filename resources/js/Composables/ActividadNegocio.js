import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'

export default function useActividadNegocio() {
  const actividadNegocios = ref([])
  const actividadNegocio = ref({})
  const detalleActividadNegocios = ref([]) // opcional: hijos
  const errors = ref('')
  const respuesta = ref([])

  // ---- ACTIVIDAD NEGOCIO (PADRES) ----
  const obtenerActividadNegocio = async (id) => {
    const respond = await axios.get('/actividadnegocio/mostrar?id=' + id, getConfigHeader())
    actividadNegocio.value = respond.data
  }

  const listaActividadNegocios = async () => {
    const respond = await axios.get('/actividadnegocio/todos', getConfigHeader())
    actividadNegocios.value = respond.data
  }

  const obtenerActividadNegocios = async (data) => {
    const respond = await axios.get(
      '/actividadnegocio/listar' + getdataParamsPagination(data),
      getConfigHeader()
    )
    actividadNegocios.value = respond.data
  }

  const agregarActividadNegocio = async (data) => {
    errors.value = ''
    try {
      const respond = await axios.post('/actividadnegocio/guardar', data, getConfigHeader())
      errors.value = ''
      if (respond.data.ok == 1) respuesta.value = respond.data
    } catch (error) {
      errors.value = ''
      if (error?.response?.status === 422) {
        errors.value = error.response.data.errors
      }
    }
  }

  const actualizarActividadNegocio = async (data) => {
    errors.value = ''
    try {
      const respond = await axios.post('/actividadnegocio/actualizar', data, getConfigHeader())
      errors.value = ''
      if (respond.data.ok == 1) respuesta.value = respond.data
    } catch (error) {
      errors.value = ''
      if (error?.response?.status === 422) {
        errors.value = error.response.data.errors
      }
    }
  }

  const eliminarActividadNegocio = async (id) => {
    const respond = await axios.post('/actividadnegocio/eliminar', { id }, getConfigHeader())
    if (respond.data.ok == 1) respuesta.value = respond.data
  }

  // ---- DETALLE ACTIVIDAD NEGOCIO (HIJOS) [OPCIONAL] ----
  // Si no lo necesitas, puedes borrar esto.
  const listaDetalleActividadNegocios = async (actividad_negocio_id) => {
    // ejemplo de endpoint: detalleactividadnegocio/todos?actividad_negocio_id=1
    const respond = await axios.get(
      '/actividadnegocio/detalleactividadnegocio?actividad_negocio_id=' + actividad_negocio_id,
      getConfigHeader()
    )
    detalleActividadNegocios.value = respond.data
  }

  return {
    errors,
    respuesta,

    // padres
    actividadNegocios,
    actividadNegocio,
    listaActividadNegocios,
    obtenerActividadNegocio,
    obtenerActividadNegocios,
    agregarActividadNegocio,
    actualizarActividadNegocio,
    eliminarActividadNegocio,

    // hijos (opcional)
    detalleActividadNegocios,
    listaDetalleActividadNegocios
  }
}
