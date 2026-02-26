import axios from 'axios'
import { ref } from 'vue'

import { getConfigHeader, getConfigHeaderpdf } from '@/Helpers'

export default function useDesembolso() {
    const desembolsos = ref([])
    const desembolso = ref({})
    const errors = ref('')
    const respuesta = ref([])
    const pdfUrl = ref('')
    const obtenerDesembolso = async (id) => {
        const respond = await axios.get('/desembolso/mostrar?id=' + id, getConfigHeader())
        desembolso.value = respond.data
    }

    const listaDesembolsos = async () => {
        const respond = await axios.get('/desembolso/todos', getConfigHeader())
        desembolsos.value = respond.data
    }

    const agregarDesembolso = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/desembolso/guardar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const generarPdf = async (data) => {
        errors.value = "";
        try {
            let response = await axios.post("/desembolso/generar-pdf", data, getConfigHeaderpdf());

            const file = new Blob([response.data], { type: "application/pdf" });
            pdfUrl.value = URL.createObjectURL(file);

        } catch (error) {
            console.error("Error al generar el PDF:", error);
            if (error.response && error.response.status === 422) {
                errors.value = error.response.data.errors;
            }
        }
    }

    const obtenerDesembolsos = async (data) => {
        let respuesta = await axios.get('/desembolso/listar', { params: data, ...getConfigHeader() })
        desembolsos.value = respuesta.data
    }

    const actualizarDesembolso = async (data) => {
        errors.value = ''
        try {
            const respond = await axios.post('/desembolso/actualizar', data, getConfigHeader())
            errors.value = ''
            if (respond.data.ok == 1) respuesta.value = respond.data
        } catch (error) {
            errors.value = ''
            if (error?.response?.status === 422) {
                errors.value = error.response.data.errors
            }
        }
    }

    const eliminarDesembolso = async (id) => {
        const respond = await axios.post('/desembolso/eliminar', { id }, getConfigHeader())
        if (respond.data.ok == 1) respuesta.value = respond.data
    }

    return {
        errors,
        respuesta,
        desembolsos,
        desembolso,
        listaDesembolsos,
        obtenerDesembolso,
        agregarDesembolso,
        actualizarDesembolso,
        eliminarDesembolso,
        obtenerDesembolsos,
        generarPdf,
        pdfUrl,
    }
}
