import axios from 'axios'
import { ref } from 'vue'
import { getConfigHeader, getdataParamsPagination } from '@/Helpers'
export default function useUsuario() {
    const usuarios = ref([])
    const errors = ref('')
    const usuario = ref({})
    const carpetaFotos =  '/storage/fotos/usuarios';
    const respuesta = ref([])
    const roles = ref([])
    const agencias = ref([])

    const obtenerUsuario = async(id) => {
        let respuesta = await axios.get('usuario/mostrar?id='+id,getConfigHeader())
        usuario.value = respuesta.data
    }
    const obtenerUsuarios = async(data) => {
        let respuesta = await axios.get('usuario/listar-'+data.show_tipo + getdataParamsPagination(data),getConfigHeader())
        usuarios.value =respuesta.data
    }
    const cambiarImagen = async(data) => {
        errors.value = ''
        try {
            let respond = await axios.post('usuario/cambiar-imagen',data,getConfigHeader())
            errors.value =''
            if(respond.data.ok==1){
                respuesta.value=respond.data
            }
        } catch (error) {
            errors.value=""
            if(error.response.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const rolesDisponibles = async(user_id) => {
        let respuesta = await axios.get('usuario/roles-disponibles?user_id='+user_id,getConfigHeader())
        roles.value =respuesta.data
    }
    const agenciasDisponibles = async(user_id) => {
        let respuesta = await axios.get('usuario/agencias-disponibles?user_id='+user_id,getConfigHeader())
        agencias.value =respuesta.data
    }    
    const obtenerUsuariosTipoAgencia = async(role_id, agencia_id) => {
        let respuesta = await axios.get('usuario/users-tipo-agencia?role_id='+ role_id + '&agencia_id=' + agencia_id,getConfigHeader())
        usuarios.value =respuesta.data
    }
    const obtenerUsuariosOperadores = async() => {
        let respuesta = await axios.get('usuario/obtener-usuarios-operaciones',getConfigHeader())
        usuarios.value =respuesta.data
    }

    const agregarUsuario = async(data) => {
        errors.value = ''
        try {
            let respond = await axios.post('usuario/guardar',data,getConfigHeader())
            errors.value =''
            if(respond.data.ok==1){
                respuesta.value=respond.data
            }
        } catch (error) {
            errors.value=""
            if(error.response.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const actualizarUsuario = async(data) => {
        errors.value = ''
        try {
            let respond = await axios.post('usuario/actualizar',data,getConfigHeader())
            errors.value =''
            if(respond.data.ok==1){
                respuesta.value=respond.data
            }

        } catch (error) {
            errors.value=""
            if(error.response.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const eliminarUsuario = async(id) => {
        const respond = await axios.post('usuario/eliminar', {id:id},getConfigHeader())
        if(respond.data.ok==1){
            respuesta.value = respond.data
        }
    }
    const cambiarEstado = async(id)=>{
        errors.value = ''
        try {
            let respond = await axios.get('usuario/cambiar-estado?id='+id,getConfigHeader())
            errors.value =''
            if(respond.data.ok==1){
                respuesta.value=respond.data
            }

        } catch (error) {
            errors.value=""
            if(error.response.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const resetClaveUsuario = async(id) => {
        errors.value = ''
        try {
            let respond = await axios.post('usuario/reset-password',{id:id},getConfigHeader())
            errors.value =''
            if(respond.data.ok==1){
                respuesta.value=respond.data
            }

        } catch (error) {
            errors.value=""
            if(error.response.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const cambiarClave=async(data)=>{
        errors.value = ''
        try {
            let respond = await axios.post('usuario/cambiar-clave',data,getConfigHeader())
            errors.value =''
            if(respond.data.ok==1){
                respuesta.value=respond.data
            }

        } catch (error) {
            errors.value=""
            if(error.response.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }
    const eliminarRole = async(roleid, userid) => {
       const respond = await axios.post('usuario/eliminar-role', {role_id:roleid, user_id:userid},getConfigHeader())
        if(respond.data.ok==1){
            respuesta.value = respond.data
        }
    }
    const agregarRole = async(data) => {
       const respond = await axios.post('usuario/agregar-role', data,getConfigHeader())
        if(respond.data.ok==1){
            respuesta.value = respond.data
        }
    }    
    const eliminarAgencia = async(agenciaid, userid) => {
       const respond = await axios.post('usuario/eliminar-agencia', {agencia_id:agenciaid, user_id:userid},getConfigHeader())
        if(respond.data.ok==1){
            respuesta.value = respond.data
        }
    }
    const agregarAgencia = async(data) => {
        const respond = await axios.post('usuario/agregar-agencia', data,getConfigHeader())
        if(respond.data.ok==1){
             respuesta.value = respond.data
        }
    }

    return {
        errors, usuarios, usuario, obtenerUsuario, obtenerUsuarios, 
        agregarUsuario, actualizarUsuario, eliminarUsuario, respuesta,
        resetClaveUsuario, cambiarEstado, cambiarClave, eliminarRole,
        eliminarAgencia, carpetaFotos, obtenerUsuariosTipoAgencia,
        agregarRole, agregarAgencia, agencias, roles, rolesDisponibles, agenciasDisponibles,
        cambiarImagen, obtenerUsuariosOperadores
    }
}