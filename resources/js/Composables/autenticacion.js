import axios from 'axios';
import { ref, provide } from 'vue';
import useHelper from '@/Helpers';

export const useAutenticacion = () => {
    const errors = ref('');
    const { Swal } = useHelper();
    const respuesta = ref('')
    const loginUsuario = async (data) => {
        errors.value = ''
        try {
            const res = await axios.post('/login', data)

            if (res.data) {
                // Splash animado
                await Swal.fire({
                    title: '¡Bienvenido!',
                    html: `<b>Ingreso correcto</b><br><small>Cargando tu panel...</small>`,
                    icon: 'success',
                    timer: 1500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    backdrop: true,
                })

                localStorage.setItem('userSession', res.data)
                window.location.href = '/'
            }
        } catch (error) {
            if (error.response?.status === 422) {
                errors.value = error.response.data.errors
            } else {
                await Swal.fire({
                    title: 'Error',
                    text: 'No se pudo iniciar sesión',
                    icon: 'error',
                })
            }
        }
    }

    const logoutUsuario = async () => {
        const respuesta = await axios.post('/logout')
        if (respuesta.data.ok == 1) {
            localStorage.removeItem('userSession')
            window.location.href = "/login"
        }
    }



    return {
        errors, loginUsuario, logoutUsuario, respuesta
    }

}