<script setup>
import { toRefs, onMounted, computed  } from 'vue';
const props = defineProps({
    url : String
});

const { url } = toRefs(props)


</script>
<template>
<teleport to="body">
    <div class="modal fade" id="impresionresumen" data-bs-backdrop="static" data-bs-keyboard="false" >
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title fs-4" id="impresionresumenLabel">Modal title</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="url">
                        <iframe 
                            :src="url" 
                            class="w-100" 
                            style="min-height: 800px; border: none;">
                        </iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</teleport>

</template>
