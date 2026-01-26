import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'

export default function useAsesor() {
    const asesores = ref([])
    const actividadNegocio = ref({})
    const errors = ref('')
    const respuesta = ref([])

    const obtenerAsesor = async (id) => {
        const respond = await axios.get('/asesor/mostrar?id=' + id, getConfigHeader())
        actividadNegocio.value = respond.data
    }

    const listaAsesores = async () => {
        const respond = await axios.get('/asesor/todos', getConfigHeader())
        asesores.value = respond.data
    }



    const agregarAsesor = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/asesor/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const actualizarAsesor = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/asesor/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const eliminarAsesor = async (id) => {
        const respond = await axios.post('/asesor/eliminar', { id }, getConfigHeader())
        if (respond.data.ok == 1) respuesta.value = respond.data
    }


    return {
        errors,
        respuesta,
        asesores,
        actividadNegocio,
        listaAsesores,
        obtenerAsesor,
        agregarAsesor,
        actualizarAsesor,
        eliminarAsesor,

    }
}