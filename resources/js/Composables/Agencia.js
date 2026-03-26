import { ref } from 'vue'
import axios from 'axios'

export default function useAgencia() {
    const agencias = ref([])
    const loading = ref(false)
    const errors = ref({})

    const listarAgencias = async () => {
        loading.value = true
        try {
            const response = await axios.get('/agencia/index')
            agencias.value = response.data
        } catch (e) {
            console.error(e)
            errors.value = e.response?.data?.errors || {}
        } finally {
            loading.value = false
        }
    }

    return {
        agencias,
        loading,
        errors,
        listarAgencias
    }
}
