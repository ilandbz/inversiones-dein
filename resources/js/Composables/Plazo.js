import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'

export default function usePlazo() {
    const plazos = ref([])
    const plazo = ref({})
    const errors = ref('')
    const respuesta = ref([])

    const obtenerPlazo = async (id) => {
        const respond = await axios.get('/plazo/mostrar?id=' + id, getConfigHeader())
        plazo.value = respond.data
    }

    const listaPlazos = async () => {
        const respond = await axios.get('/plazo/todos', getConfigHeader())
        plazos.value = respond.data
    }

    const agregarPlazo = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/plazo/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const actualizarPlazo = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/plazo/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const eliminarPlazo = async (id) => {
        const respond = await axios.post('/plazo/eliminar', { id }, getConfigHeader())
        if (respond.data.ok == 1) respuesta.value = respond.data
    }

    return {
        errors,
        respuesta,
        plazos,
        plazo,
        listaPlazos,
        obtenerPlazo,
        agregarPlazo,
        actualizarPlazo,
        eliminarPlazo,
    }
}
