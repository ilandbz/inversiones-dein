import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader } from '@/Helpers'

export default function useNegocioCliente() {
    const negocios = ref([])
    const negocio = ref({})
    const errors = ref('')
    const respuesta = ref([])
    const loading = ref(false)

    // Obtener negocios por cliente_id
    const obtenerNegociosPorCliente = async (cliente_id) => {
        loading.value = true
        try {
            const respond = await axios.get('/negocio/por-cliente?cliente_id=' + cliente_id, getConfigHeader())
            negocios.value = respond.data
        } catch (error) {
            negocios.value = []
        } finally {
            loading.value = false
        }
    }

    // Obtener un negocio
    const obtenerNegocio = async (id) => {
        const respond = await axios.get('/negocio/mostrar?id=' + id, getConfigHeader())
        negocio.value = respond.data
    }

    // Guardar negocio
    const agregarNegocio = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/negocio/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) {
                respuesta.value = respond.data
            }
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    // Actualizar negocio
    const actualizarNegocio = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/negocio/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) {
                respuesta.value = respond.data
            }
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    // Eliminar negocio
    const eliminarNegocio = async (id) => {
        const respond = await axios.post('/negocio/eliminar', { id }, getConfigHeader())
        if (respond.data.ok == 1) {
            respuesta.value = respond.data
        }
    }

    return {
        negocios,
        negocio,
        errors,
        respuesta,
        loading,
        obtenerNegociosPorCliente,
        obtenerNegocio,
        agregarNegocio,
        actualizarNegocio,
        eliminarNegocio
    }
}
