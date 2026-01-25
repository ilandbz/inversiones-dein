import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'
export default function useUbigeo() {
    const departamentos = ref([])
    const provincias = ref([])
    const distritos = ref([])
    const errors = ref('')
    const respuesta = ref([])
    const registro = ref([])
    const obtenerDistritos = async (data) => {
        const respond = await axios.get('/ubigeo/lista-distritos' + getdataParamsPagination(data), getConfigHeader())
        distritos.value = respond.data
    }
    const obtenerProvincias = async (data) => {
        const respond = await axios.get('/ubigeo/lista-provincias' + getdataParamsPagination(data), getConfigHeader())
        provincias.value = respond.data
    }
    const obtenerDepartamentos = async () => {
        const respond = await axios.get('/ubigeo/departamentos', getConfigHeader())
        departamentos.value = respond.data
    }
    const obtenerUbigeo = async (ubigeo) => {
        try {
            let respond = await axios.get('/ubigeo/obtener?ubigeo=' + ubigeo, getConfigHeader())
            registro.value = respond.data
        } catch (error) {
            errors.value = error.response.data
        }
    }
    const buscarDistritos = async ({ buscar = '', paginacion = 10, page = 1 } = {}) => {
        const params = new URLSearchParams()
        params.set('buscar', buscar)
        params.set('paginacion', paginacion)
        params.set('page', page)
        const respond = await axios.get('/ubigeo/lista-distritos?' + params.toString(), getConfigHeader())
        distritos.value = respond.data // paginado
    }
    const obtenerProvinciasPorDepartamento = async (departamento_id) => {
        const respond = await axios.get('/ubigeo/provincias?departamento_id=' + departamento_id, getConfigHeader())
        provincias.value = respond.data // array
    }

    const obtenerDistritosPorProvincia = async (provincia_id) => {
        const respond = await axios.get('/ubigeo/distritos?provincia_id=' + provincia_id, getConfigHeader())
        distritos.value = respond.data // array
    }
    const agregarUbicacion = async (data) => {
        errors.value = ''
        try {
            let respond = await axios.post('/ubigeo/guardar', data, getConfigHeader())
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
    return {
        errors, distritos, obtenerDistritos, obtenerDistritosPorProvincia,
        provincias, obtenerProvincias, obtenerProvinciasPorDepartamento,
        departamentos, obtenerDepartamentos,
        respuesta, obtenerUbigeo, registro,
        buscarDistritos,
    }
}