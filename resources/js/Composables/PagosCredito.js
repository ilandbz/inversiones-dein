import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader } from '@/Helpers'

export default function usePagosCredito() {
    const pagos = ref([])
    const pago = ref({})
    const errors = ref('')
    const respuesta = ref([])
    const loading = ref(false)

    const listarPagos = async (creditoId) => {
        loading.value = true
        try {
            const respond = await axios.get(`/kardex-credito/listar?credito_id=${creditoId}`, getConfigHeader())
            pagos.value = respond.data
        } catch (error) {
            console.error('Error al listar pagos:', error)
        } finally {
            loading.value = false
        }
    }

    const mostrarPago = async (creditoId, nro) => {
        try {
            const respond = await axios.get(`/kardex-credito/mostrar?credito_id=${creditoId}&nro=${nro}`, getConfigHeader())
            pago.value = respond.data
        } catch (error) {
            console.error('Error al mostrar pago:', error)
        }
    }

    const guardarPago = async (data) => {
        errors.value = ''
        loading.value = true
        try {
            const respond = await axios.post('/kardex-credito/guardar', data, getConfigHeader())
            respuesta.value = { ok: 1, mensaje: 'Pago registrado correctamente', data: respond.data }
            return respond.data
        } catch (error) {
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            } else {
                console.error('Error al guardar pago:', error)
            }
        } finally {
            loading.value = false
        }
    }

    const actualizarPago = async (data) => {
        errors.value = ''
        loading.value = true
        try {
            const respond = await axios.post('/kardex-credito/actualizar', data, getConfigHeader())
            respuesta.value = { ok: 1, mensaje: 'Pago actualizado correctamente', data: respond.data }
            return respond.data
        } catch (error) {
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            } else {
                console.error('Error al actualizar pago:', error)
            }
        } finally {
            loading.value = false
        }
    }

    const eliminarPago = async (creditoId, nro) => {
        loading.value = true
        try {
            const respond = await axios.post('/kardex-credito/eliminar', { credito_id: creditoId, nro }, getConfigHeader())
            respuesta.value = { ok: 1, mensaje: 'Pago eliminado correctamente' }
            return respond.data
        } catch (error) {
            console.error('Error al eliminar pago:', error)
        } finally {
            loading.value = false
        }
    }

    return {
        pagos,
        pago,
        errors,
        respuesta,
        loading,
        listarPagos,
        mostrarPago,
        guardarPago,
        actualizarPago,
        eliminarPago
    }
}
