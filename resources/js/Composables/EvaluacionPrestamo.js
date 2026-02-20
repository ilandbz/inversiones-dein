import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader } from '@/Helpers'

export default function useEvaluacionPrestamo() {
    const evaluaciones = ref([])
    const evaluacion = ref({})
    const errors = ref('')
    const respuesta = ref([])

    const obtenerEvaluacion = async (id) => {
        const respond = await axios.get('/evaluacion-prestamo/mostrar?id=' + id, getConfigHeader())
        evaluacion.value = respond.data
    }

    const listaEvaluaciones = async () => {
        const respond = await axios.get('/evaluacion-prestamo/todos', getConfigHeader())
        evaluaciones.value = respond.data
    }

    const listaEvaluacionesPorCredito = async (credito_id) => {
        const respond = await axios.get('/evaluacion-prestamo/por-credito?credito_id=' + credito_id, getConfigHeader())
        evaluaciones.value = respond.data
    }

    const agregarEvaluacion = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/evaluacion-prestamo/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const actualizarEvaluacion = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/evaluacion-prestamo/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const eliminarEvaluacion = async (id) => {
        const respond = await axios.post('/evaluacion-prestamo/eliminar', { id }, getConfigHeader())
        if (respond.data.ok == 1) respuesta.value = respond.data
    }

    return {
        errors,
        respuesta,
        evaluaciones,
        evaluacion,
        listaEvaluaciones,
        listaEvaluacionesPorCredito,
        obtenerEvaluacion,
        agregarEvaluacion,
        actualizarEvaluacion,
        eliminarEvaluacion,
    }
}
