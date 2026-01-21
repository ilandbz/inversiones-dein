import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'

export default function useCliente() {
    const clientes = ref([])
    const errors = ref('')
    const cliente = ref({})
    const datos = ref({})
    const respuesta = ref([])
    const creditos = ref([])
    const juntas = ref([])
    
    const obtenerCliente = async(id) => {
        let respuesta = await axios.get('/cliente/mostrar?id='+id, getConfigHeader())
        cliente.value = respuesta.data
    }
    const obtenerClientePorDni = async(dni) => {
        let respuesta = await axios.get('/cliente/mostrar-dni?dni='+dni, getConfigHeader())
        cliente.value = respuesta.data
    }
    const datosCreditoJuntaPorDni = async(dni) => {
        let respuesta = await axios.get('/cliente/mostrar-con-registros-dni?dni='+dni, getConfigHeader())
        cliente.value = respuesta.data.cliente
        creditos.value = respuesta.data.creditos
        juntas.value = respuesta.data.juntas
    }    
    const obtenerDatosParaNuevoCredito = async(dni) => {
        let respuesta = await axios.get('/cliente/mostrar-dni-nuevo-credito?dni='+dni, getConfigHeader())
        datos.value = respuesta.data
    }        
    const listaClientes = async()=>{
        let respuesta = await axios.get('/cliente/todos', getConfigHeader())
        clientes.value = respuesta.data        
    }
    const obtenerClientesPosicion = async(data)=>{
        let respuesta = await axios.get('/cliente/listar-clientes-posicion' + getdataParamsPagination(data), getConfigHeader())
        clientes.value = respuesta.data        
    }    
    const obtenerClientes = async(data) => {
        let respuesta = await axios.get('/cliente/listar' + getdataParamsPagination(data), getConfigHeader())
        clientes.value = respuesta.data
    }
    const agregarCliente = async(data) => {
        errors.value = ''
        try {
            let respond = await axios.post('/cliente/guardar', data, getConfigHeader())
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
    const asignarAsesorMasivo = async(data) => {
        errors.value = ''
        try {
            let respond = await axios.post('/cliente/asignar-asesor-masivo', data, getConfigHeader())
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
    const actualizarCliente = async(data) => {
        errors.value = ''
        try {
            let respond = await axios.post('/cliente/actualizar', data, getConfigHeader())
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
    const eliminarCliente = async(id) => {
        const respond = await axios.post('/cliente/eliminar', { id: id }, getConfigHeader())
        if (respond.data.ok == 1) {
            respuesta.value = respond.data
        }
    }
    return {
        errors, clientes, listaClientes, cliente, obtenerCliente, obtenerClientes, obtenerDatosParaNuevoCredito,
        agregarCliente, actualizarCliente, eliminarCliente, respuesta, obtenerClientePorDni, datos, obtenerClientesPosicion,
        datosCreditoJuntaPorDni, creditos, juntas, asignarAsesorMasivo
    }
}
