import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'

export default function usePropiedad() {
    const propiedades = ref([])
    const propiedad = ref({})
    const errors = ref('')
    const respuesta = ref([])

    const obtenerPropiedad = async (id) => {
        const respond = await axios.get('/propiedad/mostrar?id=' + id, getConfigHeader())
        propiedad.value = respond.data
    }

    const listaPropiedades = async () => {
        const respond = await axios.get('/propiedad/todos', getConfigHeader())
        propiedades.value = respond.data
    }

    const agregarPropiedad = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/propiedad/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const actualizarPropiedad = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/propiedad/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const eliminarPropiedad = async (id) => {
        const respond = await axios.post('/propiedad/eliminar', { id }, getConfigHeader())
        if (respond.data.ok == 1) respuesta.value = respond.data
    }

    const listarPropiedades = async (data) => {
        let respuesta = await axios.get('/propiedad/listar' + getdataParamsPagination(data), getConfigHeader())
        propiedades.value = respuesta.data
    }

    return {
        errors,
        respuesta,
        propiedades,
        propiedad,
        listaPropiedades,
        listarPropiedades,
        obtenerPropiedad,
        agregarPropiedad,
        actualizarPropiedad,
        eliminarPropiedad,
    }
}
