import { createRouter, createWebHistory } from "vue-router";

import LayoutLogin from '@/Layouts/AppLayoutLogin.vue'
import LayoutDefault from '@/Layouts/AppLayoutDefault.vue'

import Principal from '@/Pages/Principal.vue'
import RegistroClientes from '@/Pages/Clientes/Registro.vue'
import Login from '@/Pages/Auth/Login.vue'
import Perfil from '@/Pages/Usuario/Perfil.vue'
import CambiarClave from '@/Pages/Usuario/CambiarClave.vue'

const routes = [
  {
    path: '/',
    alias: '/principal',
    name: 'Principal',
    component: Principal,
    meta: { layout: LayoutDefault, requiresAuth: true }
  },
  { path: '/clientes/registro-de-clientes', name:'Registro de Clientes', component: RegistroClientes, meta:{ layout: LayoutDefault, requiresAuth: true } },
  { path: '/login', name:'Login', component: Login, meta:{ layout: LayoutLogin } },
  {
    path: '/',
    alias: '/perfil',
    name: 'Perfil',
    component: Perfil,
    meta: { layout: LayoutDefault, requiresAuth: true }
  },
  {
    path: '/',
    alias: '/cambiar-clave',
    name: 'CambiarClave',
    component: CambiarClave,
    meta: { layout: LayoutDefault, requiresAuth: true }
  },

]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to) => {
  const isLogged = !!localStorage.getItem('userSession')
  if (to.meta.requiresAuth && !isLogged) {
    return { name: 'Login', query: { redirect: to.fullPath } }
  }
  if (to.name === 'Login' && isLogged) {
    return { name: 'Principal' }
  }
  return true
})

export default router