import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'

export default function useOrigenFinanciamiento() {
    const origenes = ref([])
    const actividadNegocio = ref({})
    const errors = ref('')
    const respuesta = ref([])

    const obtenerOrigenFinanciamiento = async (id) => {
        const respond = await axios.get('/origen_financiamiento/mostrar?id=' + id, getConfigHeader())
        actividadNegocio.value = respond.data
    }

    const listaOrigenesFinanciamientos = async () => {
        const respond = await axios.get('/origen_financiamiento/todos', getConfigHeader())
        origenes.value = respond.data
    }



    const agregarOrigenFinanciamiento = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/origen_financiamiento/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const actualizarOrigenFinanciamiento = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/origen_financiamiento/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const eliminarOrigenFinanciamiento = async (id) => {
        const respond = await axios.post('/origen_financiamiento/eliminar', { id }, getConfigHeader())
        if (respond.data.ok == 1) respuesta.value = respond.data
    }


    return {
        errors,
        respuesta,
        origenes,
        actividadNegocio,
        listaOrigenesFinanciamientos,
        obtenerOrigenFinanciamiento,
        agregarOrigenFinanciamiento,
        actualizarOrigenFinanciamiento,

    }
}