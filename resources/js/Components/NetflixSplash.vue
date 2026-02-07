
<template>
  <teleport to="body">
    <div class="netflix-splash" :class="{ hide: !visible }">
      <div class="brand">
        <img v-if="logo" :src="logo" class="logo" alt="logo" />
        <h1 v-else class="text-logo">{{ text }}</h1>

        <div class="sub" v-if="subtitle">{{ subtitle }}</div>

        <div class="loader" aria-hidden="true">
          <span></span>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  duration: { type: Number, default: 1400 }, // ms
  logo: { type: String, default: '' },       // ej: '/imagenes/logo.jpeg'
  text: { type: String, default: 'MI APP' },
  subtitle: { type: String, default: 'Bienvenido' },
  modelValue: { type: Boolean, default: true }, // v-model
})

const emit = defineEmits(['update:modelValue', 'done'])

const visible = ref(props.modelValue)

onMounted(() => {
  // se muestra y luego se desvanece
  window.setTimeout(() => {
    visible.value = false
    // espera el fade-out y cierra (para v-if)
    window.setTimeout(() => {
      emit('update:modelValue', false)
      emit('done')
    }, 450)
  }, props.duration)
})
</script>

<style scoped>
.netflix-splash{
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  background: #000;
  display: grid;
  place-items: center;
  z-index: 999999;
  animation: splashIn .35s ease-out both;
}

.netflix-splash.hide{
  animation: splashOut .45s ease-in both;
}

@keyframes splashIn{
  from { opacity: 0 }
  to { opacity: 1 }
}
@keyframes splashOut{
  to { opacity: 0; transform: scale(1.02) }
}

.brand{
  text-align: center;
  transform: translateY(-4px);
}

.logo{
  width: min(320px, 70vw);
  max-height: 40vh;
  object-fit: contain;
  animation: logoPop 1.0s cubic-bezier(.2,.9,.2,1) both;
  filter: drop-shadow(0 0 0 rgba(255,0,0,0));
}

@keyframes logoPop{
  0%   { transform: scale(.88); opacity: .0; filter: drop-shadow(0 0 0 rgba(255,0,0,0)); }
  55%  { transform: scale(1.02); opacity: 1; filter: drop-shadow(0 0 18px rgba(255,0,0,.30)); }
  100% { transform: scale(1); opacity: 1; filter: drop-shadow(0 0 10px rgba(255,0,0,.20)); }
}

.text-logo{
  font-size: clamp(40px, 7vw, 86px);
  font-weight: 900;
  letter-spacing: .04em;
  color: #e50914; /* rojo Netflix-ish */
  animation: logoPop 1.0s cubic-bezier(.2,.9,.2,1) both;
}

.sub{
  margin-top: 10px;
  color: rgba(255,255,255,.78);
  font-weight: 600;
  letter-spacing: .02em;
  opacity: 0;
  animation: subIn .55s ease-out .45s forwards;
}

@keyframes subIn{
  to { opacity: 1; transform: translateY(0) }
}

.loader{
  width: min(260px, 70vw);
  height: 3px;
  background: rgba(255,255,255,.14);
  border-radius: 999px;
  margin: 18px auto 0;
  overflow: hidden;
  opacity: .9;
}

.loader span{
  display:block;
  height: 100%;
  width: 35%;
  background: rgba(229,9,20,.95);
  border-radius: 999px;
  animation: loading 1.0s ease-in-out infinite;
}

@keyframes loading{
  0%   { transform: translateX(-120%) }
  100% { transform: translateX(320%) }
}


.netflix-splash { pointer-events: auto; }
.netflix-splash.hide { pointer-events: none; }
</style>
