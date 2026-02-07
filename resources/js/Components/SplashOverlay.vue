<template>
  <div class="splash" @click.self="allowClose ? $emit('close') : null">
    <div class="box" :class="{ pop: true }">
      <!-- LOADING -->
      <div v-if="state === 'loading'" class="icon-wrap">
        <div class="spinner"></div>
      </div>

      <!-- SUCCESS -->
      <div v-else class="icon-wrap">
        <div class="check">
          <svg viewBox="0 0 52 52">
            <path d="M14 27 L23 36 L38 18"></path>
          </svg>
        </div>
      </div>

      <h3 class="title">{{ title }}</h3>
      <p class="msg">{{ message }}</p>

      <button
        v-if="showButton"
        class="btn btn-light mt-2"
        @click="$emit('close')"
      >
        Continuar
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'Procesando...' },
  message: { type: String, default: 'Validando credenciales...' },
  state: { type: String, default: 'loading' }, // 'loading' | 'success'
  showButton: { type: Boolean, default: false },
  allowClose: { type: Boolean, default: false }, // cerrar clic fuera
})
</script>

<style scoped>
/* Backdrop */
.splash{
  position: fixed;
  inset: 0;
  display:flex;
  align-items:center;
  justify-content:center;
  background: rgba(0,0,0,.45);
  z-index: 99999;
  animation: fadeIn .18s ease-out;
}

@keyframes fadeIn { from { opacity: 0 } to { opacity: 1 } }

/* Card */
.box{
  background: #ffffff;
  width: min(440px, 92vw);
  border-radius: 18px;
  padding: 26px 24px;
  text-align:center;
  box-shadow: 0 18px 45px rgba(0,0,0,.18);
  transform: scale(.96);
  animation: pop .18s ease-out forwards;
}

@keyframes pop {
  to { transform: scale(1); }
}

.icon-wrap{
  display:flex;
  justify-content:center;
  margin-bottom: 14px;
}

/* Spinner */
.spinner{
  width: 52px;
  height: 52px;
  border: 6px solid #e6e6e6;
  border-top-color: #0d6efd;
  border-radius: 50%;
  animation: spin .85s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Check animation */
.check{
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: rgba(13,110,253,.12);
  display:flex;
  align-items:center;
  justify-content:center;
}

.check svg{
  width: 46px;
  height: 46px;
}

.check path{
  fill: none;
  stroke: #0d6efd;
  stroke-width: 6;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-dasharray: 60;
  stroke-dashoffset: 60;
  animation: draw .35s ease-out forwards;
}

@keyframes draw {
  to { stroke-dashoffset: 0; }
}

/* Text */
.title{
  margin: 0;
  font-weight: 800;
  font-size: 20px;
}

.msg{
  margin: 6px 0 0;
  color: #5b5b5b;
}
</style>
