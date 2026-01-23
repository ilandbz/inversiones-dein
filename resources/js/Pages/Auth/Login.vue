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

      <!-- COLUMNA IZQUIERDA (solo fondo) -->
      <div class="col-lg-5 d-none d-lg-block">
        <div id="auth-left" class="h-100"></div>
      </div>

      <!-- COLUMNA DERECHA (logo + login) -->
      <div class="col-lg-7 col-12">
        <div
          id="auth-right"
          class="h-100 d-flex flex-column align-items-center justify-content-center"
        >
          <div class="right-content text-center text-white mb-2">
            <div class="right-logo mb-3">
              <img src="/assets/imagenes/logo.jpeg" alt="Logo grande" />
            </div>

            <h1 class="fw-bold mb-2">Bienvenido</h1>

          </div>

          <!-- CARD LOGIN (AHORA DEBAJO DEL LOGO) -->
          <div class="auth-card">

            <div class="text-center mb-4">
              <h6 class="auth-title mb-2">
                INGRESE DATOS PARA INICIAR SESION
              </h6>
              <p class="auth-subtitle mb-0">
                
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
                <!-- <div class="form-control-icon">
                  <i class="bi bi-person"></i>
                </div> -->

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
                <!-- <div class="form-control-icon">
                  <i class="bi bi-shield-lock"></i>
                </div> -->

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
                  <span class="spinner-border spinner-border-sm"></span>
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

    </div>
  </div>
</template>


<style scoped>

.auth-card{
  /* mismo degradado (puedes variar opacidades si quieres más suave) */
  background: linear-gradient(135deg,
    rgba(30, 60, 114, .92) 0%,
    rgba(42, 82, 152, .88) 50%,
    rgba(59, 130, 246, .82) 100%
  );

  /* efecto vidrio */
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);

  width: 100%;
  max-width: 360px;
  border-radius: 18px;
  padding: 28px;

  /* borde y sombra premium */
  border: 1px solid rgba(255,255,255,.18);
  box-shadow: 0 20px 70px rgba(0,0,0,.35);

  /* para que no se “coma” el brillo del fondo */
  position: relative;
  z-index: 2;
  overflow: hidden;
}

/* brillo sutil dentro del card (bonito) */
.auth-card::before{
  content: "";
  position: absolute;
  inset: -40%;
  background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.18), transparent 55%);
  transform: rotate(12deg);
  pointer-events: none;
}

/* Textos del card en blanco */
.auth-title{
  color: rgba(255,255,255,.95);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: .4px;
  text-transform: uppercase;
}

.auth-subtitle{
  color: rgba(255,255,255,.75);
  font-size: 13px;
}

.auth-footer{
  color: rgba(255,255,255,.65);
  font-size: 12px;
}

/* Inputs dentro del card: tipo vidrio también */
.auth-card .form-control{
  border-radius: 12px;
  background: rgba(255,255,255,.12);
  border: 1px solid rgba(255,255,255,.18);
  color: rgba(255,255,255,.95);
}

.auth-card .form-control::placeholder{
  color: rgba(255,255,255,.70);
}

.auth-card .form-control:focus{
  background: rgba(255,255,255,.16);
  border-color: rgba(255,255,255,.35);
  box-shadow: 0 0 0 .22rem rgba(255,255,255,.12);
}

/* alert no demasiado “chillón” sobre azul */
.auth-card .alert-danger{
  background: rgba(220, 38, 38, .18);
  border: 1px solid rgba(220, 38, 38, .25);
  color: rgba(255,255,255,.92);
}

/* Botón: que combine con el card */
.auth-card .btn-primary{
  background: rgba(255,255,255,.18);
  border: 1px solid rgba(255,255,255,.22);
  color: #fff;
}

.auth-card .btn-primary:hover{
  background: rgba(255,255,255,.25);
  border-color: rgba(255,255,255,.28);
}

.auth-card .btn-primary:disabled{
  opacity: .7;
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


/* --- LOGO: más grande, cuadrado, premium --- */
.logo-link{
  display: inline-flex;
  align-items: center;
  text-decoration: none;
}

/* Caja del logo (se ve bien con logo cuadrado) */
.logo-box{
  width: 62px;              /* tamaño del logo */
  height: 62px;
  padding: 8px;             /* “aire” interno */
  border-radius: 14px;      /* suave, moderno */
  display: grid;
  place-items: center;

  /* estilo premium tipo glass */
  background: rgba(255,255,255,.10);
  border: 1px solid rgba(255,255,255,.18);
  box-shadow: 0 10px 26px rgba(0,0,0,.22);

  /* para que no se vea pegado arriba */
  margin: 4px 0;
}

/* Imagen del logo: NO se recorta, se ajusta */
.logo-box img{
  width: 100%;
  height: 100%;
  object-fit: contain;      /* clave para logo cuadrado */
  border-radius: 10px;      /* leve redondeo dentro */
  display: block;
}

/* Si sientes que el toggle se “aprieta”, ajusta el header */
.sidebar-header .d-flex{
  gap: 10px;
}

@media (max-width: 420px){
  .logo-box{ width: 56px; height: 56px; }
}


</style>
