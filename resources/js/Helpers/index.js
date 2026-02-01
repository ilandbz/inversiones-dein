import { inject } from 'vue';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import advanced from 'dayjs/plugin/advancedFormat';
import 'dayjs/locale/es'; // Importa el idioma deseado, por ejemplo, español
import FingerprintJS from '@fingerprintjs/fingerprintjs'
// Configura el idioma de dayjs
dayjs.locale('es');
// export * from './body';

export * from './methods';

export default function useHelper() {
    const Swal = inject('Swal');

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1200,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })
    const meses = [
        { numero: '01', nombre: 'Enero' },
        { numero: '02', nombre: 'Febrero' },
        { numero: '03', nombre: 'Marzo' },
        { numero: '04', nombre: 'Abril' },
        { numero: '05', nombre: 'Mayo' },
        { numero: '06', nombre: 'Junio' },
        { numero: '07', nombre: 'Julio' },
        { numero: '08', nombre: 'Agosto' },
        { numero: '09', nombre: 'Septiembre' },
        { numero: '10', nombre: 'Octubre' },
        { numero: '11', nombre: 'Noviembre' },
        { numero: '12', nombre: 'Diciembre' }
    ];
    // const openModal = (modal_nombre) => {
    //     const myModal = bootstrap.Modal.getOrCreateInstance(modal_nombre);
    //     myModal.show();
    // }


    const openModal = (selectorOrEl) => {
        const el = typeof selectorOrEl === 'string'
            ? document.querySelector(selectorOrEl)
            : selectorOrEl

        if (!el) return console.warn('Modal no encontrado:', selectorOrEl)

        bootstrap.Modal.getOrCreateInstance(el).show()
    }


    // const hideModal = (modal_nombre) => {
    //     const myModal = bootstrap.Modal.getOrCreateInstance(modal_nombre);
    //     myModal.hide();
    // }

    const hideModal = (selectorOrEl) => {
        const el = typeof selectorOrEl === 'string'
            ? document.querySelector(selectorOrEl)
            : selectorOrEl

        if (!el) return console.warn('Modal no encontrado:', selectorOrEl)

        bootstrap.Modal.getOrCreateInstance(el).hide()
    }

    const soloNumeros = (evt) => {
        evt = (evt) ? evt : window.event
        var charCode = (evt.which) ? evt.which : evt.keyCode

        if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode != 48) {
            evt.preventDefault()
        } else {
            return true
        }
    }

    const calcularEdad = (fecha) => {
        // Si la fecha es correcta, calculamos la edad

        if (typeof fecha != "string" && fecha && esNumero(fecha.getTime())) {
            fecha = formatDate(fecha, "yyyy-MM-dd");
        }

        var values = fecha.split("-");
        var dia = values[2];
        var mes = values[1];
        var ano = values[0];

        // cogemos los valores actuales
        var fecha_hoy = new Date();
        var ahora_ano = fecha_hoy.getYear();
        var ahora_mes = fecha_hoy.getMonth() + 1;
        var ahora_dia = fecha_hoy.getDate();

        // realizamos el calculo
        var edad = (ahora_ano + 1900) - ano;
        if (ahora_mes < mes) {
            edad--;
        }
        if ((mes == ahora_mes) && (ahora_dia < dia)) {
            edad--;
        }
        if (edad > 1900) {
            edad -= 1900;
        }

        // calculamos los meses
        var meses = 0;

        if (ahora_mes > mes && dia > ahora_dia)
            meses = ahora_mes - mes - 1;
        else if (ahora_mes > mes)
            meses = ahora_mes - mes
        if (ahora_mes < mes && dia < ahora_dia)
            meses = 12 - (mes - ahora_mes);
        else if (ahora_mes < mes)
            meses = 12 - (mes - ahora_mes + 1);
        if (ahora_mes == mes && dia > ahora_dia)
            meses = 11;

        // calculamos los dias
        var dias = 0;
        if (ahora_dia > dia)
            dias = ahora_dia - dia;
        if (ahora_dia < dia) {
            ultimoDiaMes = new Date(ahora_ano, ahora_mes - 1, 0);
            dias = ultimoDiaMes.getDate() - (dia - ahora_dia);
        }

        return edad + " años, " + meses + " meses y " + dias + " días";
    }

    const formatoFecha = (fecha, formato) => {
        dayjs.extend(utc);
        dayjs.extend(timezone);
        dayjs.extend(advanced);

        let time_zone = import.meta.env.VITE_TIME_ZONE

        return (![null, '', undefined].includes(fecha)) ? dayjs(fecha).tz(time_zone).format(formato)
            : dayjs().tz(time_zone).format(formato)
    }
    const slugify = (text) => {
        return text
            .toString()
            .normalize()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
    }

    const formatearHora = (hora, fecha = '2000-01-01') => {
        if (!hora) return ''

        const dateTime = `${fecha} ${hora}` // Ejemplo: "2000-01-01 19:25:24"
        const parsed = dayjs(dateTime, 'YYYY-MM-DD HH:mm:ss', true)

        if (!parsed.isValid()) return 'Hora inválida'

        return parsed.format('hh:mm A') // Ej: 07:25 PM
    }

    const calcularNroCuotas = (fecha, frecuencia, plazo) => {
        let fechaActual = dayjs(fecha);
        let sumDias = 0;

        switch (frecuencia.toUpperCase()) {
            case 'DIARIO':
                for (let i = 0; i < plazo; i++) {
                    sumDias += fechaActual.daysInMonth();
                    fechaActual = fechaActual.add(1, 'month');
                }
                return sumDias;

            case 'SEMANAL':
                return plazo * 4; // Aproximación a 4 semanas por mes

            case 'DPF':
                return 1;

            case 'MENSUAL':
                return plazo;

            default:
                throw new Error('Frecuencia no válida');
        }
    };

    const obtenerFingerprint = async () => {
        // Cargar la librería
        const fp = await FingerprintJS.load()
        // Obtener el resultado
        const result = await fp.get()
        // Devolver el ID único
        return result.visitorId
    }

    return {
        Swal, Toast, openModal, hideModal, soloNumeros, calcularEdad, formatoFecha, formatearHora, meses, slugify, calcularNroCuotas,
        obtenerFingerprint
    }

}