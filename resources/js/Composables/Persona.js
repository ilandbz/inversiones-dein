import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'
export default function usePersona() {
    const persona = ref({})
    const errors = ref('')
    const respuesta = ref([])
    const respuesta2 = ref([])
    const obtenerPorDni = async (dni) => {
        let respuesta = await axios.get('/persona/mostrar-por-dni?dni=' + dni, getConfigHeader())
        persona.value = respuesta.data
    }
    const obtenerCargo = async (id) => {
        let respuesta = await axios.get('/persona/mostrar?id=' + id, getConfigHeader())
        cargo.value = respuesta.data
    }
    const listaPersonas = async () => {
        let respuesta = await axios.get('/persona/todos', getConfigHeader())
        cargos.value = respuesta.data
    }
    const obtenerPersonas = async (data) => {
        let respuesta = await axios.get('/persona/listar' + getdataParamsPagination(data), getConfigHeader())
        cargos.value = respuesta.data
    }
    const agregarPersona = async (data) => {
        errors.value = ''
        try {
            let respond = await axios.post('/persona/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) {
                respuesta.value = respond.data
            }
        } catch (error) {
            errors.value = ""
            if (error.response.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const actualizarPersona = async (data) => {
        errors.value = ''
        try {
            let respond = await axios.post('/persona/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) {
                respuesta.value = respond.data
            }
        } catch (error) {
            errors.value = ""
            if (error.response.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const actualizarCelular = async (data) => {
        errors.value = ''
        try {
            let respond = await axios.post('/persona/actualizar-celular', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) {
                respuesta2.value = respond.data
            }
        } catch (error) {
            errors.value = ""
            if (error.response.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const eliminarPersona = async (id) => {
        const respond = await axios.post('/persona/eliminar', { id: id }, getConfigHeader())
        if (respond.data.ok == 1) {
            respuesta.value = respond.data
        }
    }
    return {
        errors, persona, respuesta, obtenerPorDni, obtenerCargo, listaPersonas, obtenerPersonas,
        agregarPersona, actualizarPersona, eliminarPersona, actualizarCelular, respuesta2
    }
}