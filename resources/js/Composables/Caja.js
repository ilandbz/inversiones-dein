import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader } from '@/Helpers'

export default function useCaja() {
    const cajas = ref([])
    const cajaActiva = ref(null)
    const movimientos = ref([])
    const resumen = ref({})
    const errors = ref('')
    const loading = ref(false)

    const obtenerCajaActiva = async (agenciaId) => {
        try {
            const respond = await axios.get(`/caja/activa?agencia_id=${agenciaId}`, getConfigHeader())
            cajaActiva.value = respond.data.caja
            return respond.data.caja
        } catch (error) {
            cajaActiva.value = null
            return null
        }
    }

    const abrirCaja = async (data) => {
        errors.value = ''
        loading.value = true
        try {
            const respond = await axios.post('/caja/abrir', data, getConfigHeader())
            cajaActiva.value = respond.data.caja
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

    const cerrarCaja = async (data) => {
        errors.value = ''
        loading.value = true
        try {
            const respond = await axios.post('/caja/cerrar', data, getConfigHeader())
            cajaActiva.value = null
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

    const obtenerResumenCaja = async (cajaId) => {
        try {
            const respond = await axios.get(`/caja/resumen?caja_id=${cajaId}`, getConfigHeader())
            resumen.value = respond.data
        } catch (error) {
            console.error('Error al obtener resumen de caja:', error)
        }
    }

    const listarMovimientosCaja = async (cajaId) => {
        loading.value = true
        try {
            const respond = await axios.get(`/caja/movimientos?caja_id=${cajaId}`, getConfigHeader())
            movimientos.value = respond.data
        } catch (error) {
            console.error('Error al listar movimientos de caja:', error)
        } finally {
            loading.value = false
        }
    }

    const registrarMovimientoManual = async (data) => {
        loading.value = true
        try {
            const respond = await axios.post('/caja/movimiento-manual', data, getConfigHeader())
            return respond.data
        } catch (error) {
            console.error('Error al registrar movimiento manual:', error)
            throw error
        } finally {
            loading.value = false
        }
    }

    const listarCajas = async (params = {}) => {
        loading.value = true
        try {
            const query = new URLSearchParams(params).toString()
            const respond = await axios.get(`/caja/listar?${query}`, getConfigHeader())
            cajas.value = respond.data
        } catch (error) {
            console.error('Error al listar cajas:', error)
        } finally {
            loading.value = false
        }
    }

    return {
        cajas,
        cajaActiva,
        movimientos,
        resumen,
        errors,
        loading,
        obtenerCajaActiva,
        abrirCaja,
        cerrarCaja,
        obtenerResumenCaja,
        listarMovimientosCaja,
        registrarMovimientoManual,
        listarCajas
    }
}
