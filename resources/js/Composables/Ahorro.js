import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader } from '@/Helpers'

export default function useAhorro() {
    const ahorros = ref([])
    const ahorro = ref({})
    const movimientos = ref([])
    const errors = ref('')
    const loading = ref(false)

    const listarAhorros = async (params = {}) => {
        loading.value = true
        try {
            const query = new URLSearchParams(params).toString()
            const respond = await axios.get(`/ahorro/listar?${query}`, getConfigHeader())
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

    const listarMovimientos = async (ahorroId) => {
        loading.value = true
        try {
            const respond = await axios.get(`/ahorro/movimientos?ahorro_id=${ahorroId}`, getConfigHeader())
            movimientos.value = respond.data
        } catch (error) {
            console.error('Error al listar movimientos de ahorro:', error)
        } finally {
            loading.value = false
        }
    }

    const abrirCuenta = async (data) => {
        errors.value = ''
        loading.value = true
        try {
            const respond = await axios.post('/ahorro/guardar', data, getConfigHeader())
            return respond.data
        } catch (error) {
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
            throw error
        } finally {
            loading.value = false
        }
    }

    const depositar = async (data) => {
        errors.value = ''
        loading.value = true
        try {
            const respond = await axios.post('/ahorro/depositar', data, getConfigHeader())
            return respond.data
        } catch (error) {
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
            throw error
        } finally {
            loading.value = false
        }
    }

    const retirar = async (data) => {
        errors.value = ''
        loading.value = true
        try {
            const respond = await axios.post('/ahorro/retirar', data, getConfigHeader())
            return respond.data
        } catch (error) {
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
            throw error
        } finally {
            loading.value = false
        }
    }

    const cerrarCuenta = async (ahorroId, data = {}) => {
        loading.value = true
        try {
            const respond = await axios.post('/ahorro/cerrar', { ahorro_id: ahorroId, ...data }, getConfigHeader())
            return respond.data
        } catch (error) {
            console.error('Error al cerrar cuenta:', error)
            throw error
        } finally {
            loading.value = false
        }
    }

    return {
        ahorros,
        ahorro,
        movimientos,
        errors,
        loading,
        listarAhorros,
        mostrarAhorro,
        listarMovimientos,
        abrirCuenta,
        depositar,
        retirar,
        cerrarCuenta
    }
}
