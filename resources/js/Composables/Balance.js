import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'

export default function useBalance() {
    const balances = ref([])
    const balance = ref({})
    const errors = ref('')
    const respuesta = ref([])

    const obtenerBalance = async (id) => {
        const respond = await axios.get('/balance/mostrar?id=' + id, getConfigHeader())
        balance.value = respond.data
    }

    const listaBalances = async () => {
        const respond = await axios.get('/balance/todos', getConfigHeader())
        balances.value = respond.data
    }

    const agregarBalance = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/balance/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const actualizarBalance = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/balance/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const eliminarBalance = async (id) => {
        const respond = await axios.post('/balance/eliminar', { id }, getConfigHeader())
        if (respond.data.ok == 1) respuesta.value = respond.data
    }


    return {
        errors,
        respuesta,
        balances,
        balance,
        listaBalances,
        obtenerBalance,
        agregarBalance,
        actualizarBalance,
        eliminarBalance,

    }
}