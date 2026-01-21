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

const submit = async () => {
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
  <div id="auth" class="auth-wrapper">
    <div class="row g-0 h-100">

      <!-- COLUMNA IZQUIERDA -->
      <div class="col-lg-5 col-12">
        <div id="auth-left" class="h-100 d-flex align-items-center justify-content-center">
          <div class="auth-card">

            <div class="text-center mb-4">
              <h6 class="auth-title mb-2">Iniciar sesión</h6>
              <p class="auth-subtitle mb-0">
                Ingrese sus credenciales para acceder al sistema.
              </p>
            </div>

            <form @submit.prevent="submit" class="vstack gap-3">

              <!-- Usuario -->
              <div class="form-group position-relative has-icon-left">
                <input
                  v-model.trim="form.name"
                  type="text"
                  class="form-control form-control-lg"
                  :class="{ 'is-invalid': errors?.name?.length }"
                  placeholder="Nombre de usuario"
                  autocomplete="username"
                />
                <div class="form-control-icon">
                  <i class="bi bi-person"></i>
                </div>

                <small class="text-danger" v-for="err in (errors?.name || [])" :key="err">
                  {{ err }}
                </small>
              </div>

              <!-- Password -->
              <div class="form-group position-relative has-icon-left">
                <input
                  v-model="form.password"
                  type="password"
                  class="form-control form-control-lg"
                  :class="{ 'is-invalid': errors?.password?.length }"
                  placeholder="Contraseña"
                  autocomplete="current-password"
                />
                <div class="form-control-icon">
                  <i class="bi bi-shield-lock"></i>
                </div>

                <small class="text-danger" v-for="err in (errors?.password || [])" :key="err">
                  {{ err }}
                </small>
              </div>

              <!-- Error general -->
              <div v-if="errorMsg" class="alert alert-danger py-2 mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i>
                {{ errorMsg }}
              </div>

              <!-- Botón -->
              <button class="btn btn-primary btn-lg w-100" :disabled="loading">
                <span v-if="!loading">
                  <i class="bi bi-box-arrow-in-right me-1"></i>
                  Ingresar
                </span>
                <span v-else class="d-inline-flex align-items-center justify-content-center gap-2">
                  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  Verificando...
                </span>
              </button>

            </form>

            <div class="text-center mt-4 auth-footer">
              © 2026 - Sistema de Inversiones
            </div>

          </div>
        </div>
      </div>

      <!-- COLUMNA DERECHA -->
      <div class="col-lg-7 d-none d-lg-block">
        <div id="auth-right" class="h-100 d-flex align-items-center justify-content-center">
          <div class="right-content text-center text-white">
            <div class="right-logo mb-4">
              <img src="/assets/imagenes/logo.jpeg" alt="Logo grande" />
            </div>

            <h1 class="fw-bold mb-2">Bienvenido</h1>
            <p class="mb-0 opacity-75">
              Accede de forma segura a tu panel de gestión.
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.auth-wrapper {
  height: 100vh;
}

/* Izquierda */
#auth-left {
  background: radial-gradient(1200px 600px at 10% 10%, #ffffff 0%, #f5f7fb 55%, #eef2ff 100%);
  padding: 24px;
}

.auth-card {
  background: #fff;
  width: 100%;
  max-width: 360px;      /* antes 420px */
  border-radius: 16px;
  padding: 28px;         /* antes 36px */
  border: 1px solid rgba(17, 24, 39, .06);
  box-shadow: 0 12px 40px rgba(17, 24, 39, .12);
}

.auth-logo {
  width: 92px;
  height: 92px;
  margin: 0 auto 18px auto;
  border-radius: 999px;
  padding: 8px;
  background: #fff;
  border: 1px solid rgba(17, 24, 39, .08);
  box-shadow: 0 10px 24px rgba(17, 24, 39, .10);
  display: grid;
  place-items: center;
}
.auth-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 999px;
}

.auth-title {
  font-size: 12px;
  font-weight: 600;
}

.auth-subtitle {
  color: #6b7280;
  font-size: 13px;
}

.auth-footer {
  color: #9aa4b2;
  font-size: 13px;
}

/* Inputs: más suaves */
.form-control {
  border-radius: 12px;
}
.form-control:focus {
  box-shadow: 0 0 0 .20rem rgba(67, 94, 190, .18);
}

/* Derecha */
#auth-right {
  position: relative;
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #3b82f6 100%);
  overflow: hidden;
}

#auth-right::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(600px 300px at 20% 20%, rgba(255,255,255,.25), transparent 60%),
    radial-gradient(800px 400px at 80% 80%, rgba(255,255,255,.15), transparent 60%);
  pointer-events: none;
}

.right-content {
  position: relative;
  z-index: 1;
  padding: 32px;
}

.right-logo {
  width: 240px;
  margin: 0 auto;
  border-radius: 22px;
  padding: 14px;
  background: rgba(255,255,255,.10);
  border: 1px solid rgba(255,255,255,.18);
  box-shadow: 0 18px 60px rgba(0,0,0,.25);
}
.right-logo img {
  width: 100%;
  height: auto;
  border-radius: 18px;
  display: block;
}

/* Responsive */
@media (max-width: 576px) {
  .auth-card {
    padding: 28px;
    border-radius: 16px;
  }
  .auth-title {
    font-size: 28px;
  }
}
</style>
