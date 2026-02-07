<script setup>
    import { useAutenticacion } from '@/Composables/autenticacion'
    import { ref, computed } from 'vue'
    import SplashOverlay from '@/Components/SplashOverlay.vue'

    const { loginUsuario, errors } = useAutenticacion()

    const form = ref({
    name: '',
    password: '',
    remember: false
    })
const showSplash = ref(false)
const splashState = ref('loading') // 'loading' | 'success'
const splashTitle = ref('Procesando...')
const splashMessage = ref('Validando credenciales...')
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

  // mostrar splash en modo loading
  showSplash.value = true
  splashState.value = 'loading'
  splashTitle.value = 'Procesando...'
  splashMessage.value = 'Validando credenciales...'

  try {
    const ok = await loginUsuario(form.value) // <- que retorne true/false

    if (ok) {
      splashState.value = 'success'
      splashTitle.value = '¡Bienvenido!'
      splashMessage.value = 'Ingreso correcto. Entrando...'

      setTimeout(() => (window.location.href = '/'), 900)
    } else {
      // si fue 422, ocultas splash (y se ven los errores)
      showSplash.value = false
    }
  } catch (e) {
    showSplash.value = false
    errorMsg.value = 'Error al iniciar sesión'
  } finally {
    loading.value = false
  }
}
</script>
<template>
  <div class="login-page">
    <div class="card login-card shadow-lg text-center">
      <!-- LOGO -->
      <div class="mb-3">
        <img
          src="imagenes/logo.jpeg"
          alt="logo"
          class="login-logo"
        />
      </div>
    <h3>BIENVENIDO AL EXITO</h3>
    <h4 class="fs-13 fw-bold mb-4">INGRESAR SUS DATOS</h4>

      <form @submit.prevent="guardar" class="w-100">
        <div class="mb-3 text-start">
          <input
            type="text"
            class="form-control"
            placeholder="Usuario"
            v-model="form.name"
            required
          />
          <small class="text-danger" v-for="err in (errors?.name || [])" :key="err">
            {{ err }}
          </small>
        </div>

        <div class="mb-3 text-start">
          <input
            type="password"
            class="form-control"
            placeholder="Contraseña"
            v-model="form.password"
            required
          />
          <small class="text-danger" v-for="err in (errors?.password || [])" :key="err">
            {{ err }}
          </small>
        </div>

        <div class="mt-4">
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
  <SplashOverlay
  v-if="showSplash"
  :state="splashState"
  :title="splashTitle"
  :message="splashMessage"
/>
</template>

<style scoped>
/* Tapa TODO el viewport y evita que el padding/sidebars del layout afecten el centrado */
.login-page{
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: #f5f6f8; /* opcional */
  z-index: 9999;       /* por encima del layout */
}

.login-card{
  width: 100%;
  max-width: 480px;     /* más ancho */
  padding: 40px;        /* más grande */
  border-radius: 18px;
  background: #2950F0; /* celeste suave */
  border: none;
}
.login-card h3{
  font-size: 28px;
  font-weight: 700;
  color: #fff;
}
.login-card h4{
  font-size: 16px;
  color: #fff;
}
/* Logo con bordes redondeados y gruesos */
.login-logo{
  width: 200px;
  height: 200px;
  object-fit: cover;
  border-radius: 18px;   /* bordes redondeados cuadrados */
  border: 8px solid #0d6efd; /* borde grueso */
  padding: 6px;
  background: #fff;
  display: inline-block;
}
</style>
