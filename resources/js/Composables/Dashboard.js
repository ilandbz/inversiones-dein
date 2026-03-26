import { ref } from 'vue'
import axios from 'axios'

export default function useDashboard() {
    const stats = ref({
        total_cartera: 0,
        total_ahorros: 0,
        saldo_caja: 0,
        creditos_mora: 0,
        caja_abierta: false
    })
    const actividad = ref([])
    const loading = ref(false)

    const obtenerDashboard = async () => {
        loading.value = true
        try {
            const response = await axios.get('/dashboard/stats')
            stats.value = response.data.stats
            actividad.value = response.data.actividad
        } catch (e) {
            console.error(e)
        } finally {
            loading.value = false
        }
    }

    return {
        stats,
        actividad,
        loading,
        obtenerDashboard
    }
}
