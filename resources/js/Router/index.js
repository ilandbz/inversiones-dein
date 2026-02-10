import { createRouter, createWebHistory } from "vue-router";

import LayoutLogin from '@/Layouts/AppLayoutLogin.vue'
import LayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import Placeholder from '@/Pages/PlaceHolder.vue'
import PrestamosInicio from '@/Pages/Prestamos/Inicio.vue'
import Principal from '@/Pages/Principal.vue'
import RegistroClientes from '@/Pages/Clientes/Registro.vue'
import ClientesInicio from '@/Pages/Clientes/Inicio.vue'
import RegistroPropiedades from '@/Pages/Propiedad/Inicio.vue'
import Login from '@/Pages/Auth/Login.vue'
import Perfil from '@/Pages/Usuario/Perfil.vue'
import Evaluacion from '@/Pages/Evaluacion/Inicio.vue'
import Desembolso from '@/Pages/Desembolso/Inicio.vue'
import CambiarClave from '@/Pages/Usuario/CambiarClave.vue'
import ActividadDeNegocio from '@/Pages/ActividadNegocio/Inicio.vue'

const routes = [
  {
    path: '/',
    name: 'Principal',
    component: Principal,
    meta: { layout: LayoutDefault, requiresAuth: true, title: 'Dashboard' }
  },

  {
    path: '/clientes/registro-de-clientes',
    name: 'RegistroClientes',
    component: RegistroClientes,
    meta: { layout: LayoutDefault, requiresAuth: true, title: 'Registro de Clientes' }
  },

  {
    path: '/actividad-negocio',
    name: 'ActividadDeNegocio',
    component: ActividadDeNegocio,
    meta: { layout: LayoutDefault, requiresAuth: true, title: 'Actividad de Negocio' }
  },

  {
    path: '/prestamos/evaluacion',
    name: 'Evaluacion',
    component: Evaluacion,
    meta: { layout: LayoutDefault, requiresAuth: true, title: 'Evaluación' }
  },

  {
    path: '/prestamos/desembolso',
    name: 'Desembolso',
    component: Desembolso,
    meta: { layout: LayoutDefault, requiresAuth: true, title: 'Desembolso' }
  },

  {
    path: '/perfil',
    name: 'Perfil',
    component: Perfil,
    meta: { layout: LayoutDefault, requiresAuth: true, title: 'Perfil' }
  },

  {
    path: '/cambiar-clave',
    name: 'CambiarClave',
    component: CambiarClave,
    meta: { layout: LayoutDefault, requiresAuth: true, title: 'Cambiar clave' }
  },

  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { layout: LayoutLogin, title: 'Login' }
  },

  { path: '/clientes/posicion-del-cliente', name: 'ClientesPosicion', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Posición del Cliente' } },
  { path: '/clientes/listado-de-clientes', name: 'ClientesListado', component: ClientesInicio, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Listado del Cliente' } },
  { path: '/clientes/historial-del-cliente', name: 'ClientesHistorial', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Historial del Cliente' } },

  // --- ASESORES ---
  { path: '/asesores/metas', name: 'AsesoresMetas', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Metas' } },
  { path: '/asesores/cartilla-de-cobranza', name: 'AsesoresCartilla', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Cartilla de Cobranza' } },
  { path: '/asesores/historico-del-asesor', name: 'AsesoresHistorico', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Histórico del Asesor' } },

  // --- PAGOS ---
  { path: '/pagos/reportes', name: 'PagosReportes', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Reportes' } },
  { path: '/pagos/estadisticas-de-ingresos', name: 'PagosEstadisticas', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Estadísticas de Ingresos' } },

  { path: '/usuarios', name: 'Usuarios', component: RegistroClientes, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Actividad de Negocio' } },
  { path: '/roles', name: 'Roles', component: RegistroClientes, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Actividad de Negocio' } },
  { path: '/permisos', name: 'Permisos', component: RegistroClientes, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Actividad de Negocio' } },
  { path: '/configuracion', name: 'Configuracion', component: RegistroClientes, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Actividad de Negocio' } },
  { path: '/propiedades', name: 'Propiedades', component: RegistroPropiedades, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Propiedades' } },

  // --- PRESTAMOS ---
  { path: '/prestamos/registrar', name: 'PrestamosRegistrar', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Registrar Préstamo' } },
  { path: '/prestamos/simulacion', name: 'PrestamosSimulacion', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Simulación' } },
  { path: '/prestamos/cronograma-de-pagos', name: 'PrestamosCronograma', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Cronograma de Pagos' } },
  { path: '/prestamos/historial-de-prestamos', name: 'PrestamosHistorial', component: PrestamosInicio, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Historial de Préstamos' } },

  // --- AHORROS ---
  { path: '/ahorros/apertura-de-cuenta', name: 'AhorrosApertura', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Apertura de Cuenta' } },
  { path: '/ahorros/depositos-retiros', name: 'AhorrosMovimientos', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Depósitos / Retiros' } },
  { path: '/ahorros/estado-de-cuenta', name: 'AhorrosEstado', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Estado de Cuenta' } },

  // --- CAJA ---
  { path: '/caja/cobros', name: 'CajaCobros', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Cobros' } },
  { path: '/caja/pagos', name: 'CajaPagos', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Pagos' } },
  { path: '/caja/cierre-de-caja', name: 'CajaCierre', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Cierre de Caja' } },

  // --- RIESGOS ---
  { path: '/riesgos/mora-y-castigos', name: 'RiesgosMora', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Mora y Castigos' } },
  { path: '/riesgos/bloqueo-de-pagos', name: 'RiesgosBloqueo', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Bloqueo de Pagos' } },

  // --- GERENCIA ---
  { path: '/gerencia/reportes-gerenciales', name: 'GerenciaReportes', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Reportes Gerenciales' } },
  { path: '/gerencia/autorizaciones', name: 'GerenciaAutorizaciones', component: Placeholder, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Autorizaciones' } },

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