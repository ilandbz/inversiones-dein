import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader } from '@/Helpers'

export default function useAhorro() {
    const ahorros = ref([])
    const ahorro = ref({})
    const errors = ref('')
    const respuesta = ref([])
    const loading = ref(false)

    const listarAhorrosPorCliente = async (clienteId) => {
        loading.value = true
        try {
            const respond = await axios.get(`/ahorro/por-cliente?cliente_id=${clienteId}`, getConfigHeader())
            ahorros.value = respond.data
        } catch (error) {
            console.error('Error al listar ahorros:', error)
        } finally {
            loading.value = false
        }
    }

    const mostrarAhorro = async (id) => {
        try {
            const respond = await axios.get(`/ahorro/mostrar?id=${id}`, getConfigHeader())
            ahorro.value = respond.data
        } catch (error) {
            console.error('Error al mostrar ahorro:', error)
        }
    }

    const guardarAhorro = async (data) => {
        errors.value = ''
        loading.value = true
        try {
            const respond = await axios.post('/ahorro/guardar', data, getConfigHeader())
            respuesta.value = respond.data
            return respond.data
        } catch (error) {
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            } else {
                console.error('Error al guardar ahorro:', error)
            }
        } finally {
            loading.value = false
        }
    }

    const actualizarAhorro = async (data) => {
        errors.value = ''
        loading.value = true
        try {
            const respond = await axios.post('/ahorro/actualizar', data, getConfigHeader())
            respuesta.value = respond.data
            return respond.data
        } catch (error) {
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            } else {
                console.error('Error al actualizar ahorro:', error)
            }
        } finally {
            loading.value = false
        }
    }

    const eliminarAhorro = async (id) => {
        loading.value = true
        try {
            const respond = await axios.post('/ahorro/eliminar', { id }, getConfigHeader())
            respuesta.value = respond.data
            return respond.data
        } catch (error) {
            console.error('Error al eliminar ahorro:', error)
        } finally {
            loading.value = false
        }
    }

    return {
        ahorros,
        ahorro,
        errors,
        respuesta,
        loading,
        listarAhorrosPorCliente,
        mostrarAhorro,
        guardarAhorro,
        actualizarAhorro,
        eliminarAhorro
    }
}
