import axios from 'axios';
import { ref,provide } from 'vue';
import useHelper from '@/Helpers';

export const useAutenticacion = () => {
    const errors = ref('');
    const {Swal } = useHelper();
    const respuesta = ref('')
    const loginUsuario = async (data) => {
        errors.value = ''
        try {
            const respuesta = await axios.post('/login',data)
            if(respuesta.data)
            {
                localStorage.setItem('userSession',respuesta.data);
                window.location.href = '/';
            }
        } catch (error) {
            if(error.response.status === 422){
                errors.value = error.response.data.errors
            }
        }
    }

    const logoutUsuario = async() => {
        const respuesta = await axios.post('/logout')
        if(respuesta.data.ok==1)
        {
            localStorage.removeItem('userSession')
            window.location.href="/login"
        }
    }



    return {
        errors, loginUsuario, logoutUsuario, respuesta
    }

}