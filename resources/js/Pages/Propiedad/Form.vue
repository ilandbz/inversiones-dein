<script setup>
import { toRefs, onMounted, ref, onBeforeUnmount } from 'vue';
import usePropiedad from '@/Composables/Propiedad.js';
import useHelper from '@/Helpers';  

const { hideModal, Toast, slugify } = useHelper();

const props = defineProps({
  form: Object,
  currentPage: Number,
  modalTitle: String,
  lastOpener: Object,
})

const { form, currentPage, modalTitle, lastOpener } = toRefs(props)
const {
    agregarPropiedad, respuesta, errors, actualizarPropiedad
} = usePropiedad();

const modalEl = ref(null)
const emit = defineEmits(['onListar'])

const crud = {
    'nuevo': async() => {
        await agregarPropiedad(form.value)
        form.value.errors = []
        if(errors.value) {
            form.value.errors = errors.value
        }
        if(respuesta.value.ok==1){
            form.value.errors = []
            hideModal('#modalpropiedad')
            Toast.fire({icon:'success', title:respuesta.value.mensaje})
            emit('onListar')
        }
    },
    'editar': async() => {
        await actualizarPropiedad(form.value)
        form.value.errors = []
        if(errors.value) {
            form.value.errors = errors.value
        }
        if(respuesta.value.ok==1){
            form.value.errors = []
            hideModal('#modalpropiedad')
            Toast.fire({icon:'success', title:respuesta.value.mensaje})
            emit('onListar')
        }
    }
}

const guardar = () => {
    crud[form.value.estadoCrud]()
}

// Función para manejar el evento hide.bs.modal
const onHideHandler = () => {
    // Quitar el foco del modal antes de que Bootstrap aplique aria-hidden
    if (document.activeElement && modalEl.value?.contains(document.activeElement)) {
        document.activeElement.blur()
    }
}

// Función para restaurar el foco después de que el modal se haya ocultado
const onHiddenHandler = () => {
    // Restaurar el foco al elemento que abrió el modal
    if (lastOpener.value) {
        lastOpener.value.focus()
    }
}

onMounted(() => {
    if (modalEl.value) {
        modalEl.value.addEventListener('hide.bs.modal', onHideHandler)
        modalEl.value.addEventListener('hidden.bs.modal', onHiddenHandler)
    }
})

onBeforeUnmount(() => {
    if (modalEl.value) {
        modalEl.value.removeEventListener('hide.bs.modal', onHideHandler)
        modalEl.value.removeEventListener('hidden.bs.modal', onHiddenHandler)
    }
})
</script>

<template>
    <teleport to="body">
        <form @submit.prevent="guardar">
            <div ref="modalEl" class="modal fade" id="modalpropiedad" tabindex="-1" aria-labelledby="modalpropiedadLabel" aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="modalpropiedadLabel">{{ modalTitle }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" v-model="form.nombre" :class="{ 'is-invalid': form.errors.nombre }" placeholder="Nombre">
                                <small class="text-danger" v-for="error in form.errors.nombre" :key="error">{{ error }}</small>
                            </div>                                    
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">{{ (form.estadoCrud=='nuevo') ? 'Guardar' : 'Actualizar' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </teleport>
</template>