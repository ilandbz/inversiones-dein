import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'

export default function useCredito() {
    const creditos = ref([])
    const credito = ref({})
    const errors = ref('')
    const respuesta = ref([])

    const obtenerCredito = async (id) => {
        const respond = await axios.get('/credito/mostrar?id=' + id, getConfigHeader())
        credito.value = respond.data
    }

    const listaCreditos = async () => {
        const respond = await axios.get('/credito/todos', getConfigHeader())
        creditos.value = respond.data
    }

    const agregarCredito = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/credito/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const obtenerCreditos = async (data) => {
        let respuesta = await axios.get('/credito/listar' + getdataParamsPagination(data), getConfigHeader())
        creditos.value = respuesta.data
    }
    const actualizarCredito = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/credito/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const eliminarCredito = async (id) => {
        const respond = await axios.post('/credito/eliminar', { id }, getConfigHeader())
        if (respond.data.ok == 1) respuesta.value = respond.data
    }

    return {
        errors,
        respuesta,
        creditos,
        credito,
        listaCreditos,
        obtenerCredito,
        agregarCredito,
        actualizarCredito,
        eliminarCredito,
        obtenerCreditos,
    }
}
