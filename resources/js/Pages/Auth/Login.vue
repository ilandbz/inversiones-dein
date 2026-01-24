<script setup>
    import { useAutenticacion } from '@/Composables/autenticacion'
    import { ref, computed } from 'vue'

    const { loginUsuario, errors } = useAutenticacion()

    const form = ref({
    name: '',
    password: '',
    remember: false
    })

    const loading = ref(false)
    const errorMsg = ref('')

    const hasFieldErrors = computed(() =>
    (errors.value?.name?.length || 0) > 0 || (errors.value?.password?.length || 0) > 0
    )

    const clearErrors = () => {
    errorMsg.value = ''
    // importante: limpiar el errors del composable (si existe como ref)
    if (errors.value) {
        errors.value = {}
    }
    }

    const guardar = async () => {
    clearErrors()
    loading.value = true

    try {
        await loginUsuario(form.value)
    } catch (error) {
        const data = error.response?.data

        // si el backend manda message
        if (data?.message) errorMsg.value = data.message

        // si por alguna razón no vino ni message ni errors
        if (!errorMsg.value && !hasFieldErrors.value) {
        errorMsg.value = 'Error al iniciar sesión'
        }
    } finally {
        loading.value = false
    }
    }
</script>

<template>

    <div class="card my-4 overflow-hidden" style="z-index: 1">
        <div class="row flex-1 g-0">
            <div class="col-lg-6 h-100 my-auto order-1 order-lg-0">
                <div class="wd-50 bg-white p-0 rounded-circle shadow-lg position-absolute translate-middle top-50 start-50 d-none d-lg-block">
                    <img src="imagenes/logo_redondo.png" alt="" class="img-fluid p-0">
                </div>
                <div class="creative-card-body card-body p-sm-5">
                    <img src="imagenes/logo.jpeg" class="img-fluid img-thumbnail p-2 mb-3" alt="">
                    <h2 class="fs-20 fw-bolder mb-4">Ingresar</h2>
                    <h4 class="fs-13 fw-bold mb-2">Ingresar a su cuenta</h4>
                    <form @submit.prevent="guardar" class="w-100 mt-4 pt-2">
                        <div class="mb-4">
                            <input type="text" class="form-control" placeholder="Usuario" v-model="form.name" required>
                            <small class="text-danger" v-for="err in (errors?.name || [])" :key="err">
                                {{ err }}
                            </small>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" placeholder="Contraseña" v-model="form.password" required>
                            <small class="text-danger" v-for="err in (errors?.password || [])" :key="err">
                                {{ err }}
                </small>
                        </div>
                        <div class="mt-5">
                            <button type="submit" class="btn btn-lg btn-primary w-100" :disabled="loading">
                                <span v-if="!loading">
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                Ingresar
                                </span>
                                <span v-else class="d-inline-flex align-items-center justify-content-center gap-2">
                                <span class="spinner-border spinner-border-sm"></span>
                                Verificando...
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            <div class="col-lg-6 bg-primary order-0 order-lg-1">
                <div class="h-100 d-flex align-items-center justify-content-center">
                    <img src="duralux/images/auth/auth-user.png" alt="" class="img-fluid">
                </div>
            </div>
        </div>
    </div>


</template>
