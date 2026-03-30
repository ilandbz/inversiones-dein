import { createRouter, createWebHistory } from "vue-router";

import LayoutLogin from '@/Layouts/AppLayoutLogin.vue'
import LayoutDefault from '@/Layouts/AppLayoutDefault.vue'
import Placeholder from '@/Pages/PlaceHolder.vue'
import PrestamosInicio from '@/Pages/Prestamos/Inicio.vue'
import PrestamosRegistrar from '@/Pages/Prestamos/Registrar.vue'
import Principal from '@/Pages/Principal.vue'
import RegistroClientes from '@/Pages/Clientes/Registro.vue'
import Usuario from '@/Pages/Usuario/Inicio.vue'
import PermisosInicio from '@/Pages/Permisos/Inicio.vue'
import ClientesListado from '@/Pages/Clientes/Listado.vue'
import RegistroPropiedades from '@/Pages/Propiedad/Inicio.vue'
import Login from '@/Pages/Auth/Login.vue'
import Perfil from '@/Pages/Usuario/Perfil.vue'
import Evaluacion from '@/Pages/Evaluacion/Inicio.vue'
import Desembolso from '@/Pages/Desembolso/Inicio.vue'
import CambiarClave from '@/Pages/Usuario/CambiarClave.vue'
import ActividadDeNegocio from '@/Pages/ActividadNegocio/Inicio.vue'
import Rol from '@/Pages/Rol/Inicio.vue'
import AutorizacionesInicio from '@/Pages/Autorizaciones/Inicio.vue'
import Desembolsar from '@/Pages/Caja/Desembolsar.vue'
import ClienteHistoria from '@/Pages/Clientes/Historia.vue'
import CajaPagos from '@/Pages/Caja/Pagos.vue'
import CajaApertura from '@/Pages/Caja/AperturaDeCaja.vue'
import CajaCierre from '@/Pages/Caja/CierreDeCaja.vue'
import CajaMovimientos from '@/Pages/Caja/MovimientosCaja.vue'
import AhorrosApertura from '@/Pages/Ahorros/AperturaDeCuenta.vue'
import AhorrosMovimientos from '@/Pages/Ahorros/DepositosRetiros.vue'
import AhorrosEstado from '@/Pages/Ahorros/EstadoDeCuenta.vue'
import BloqueoDePagos from '@/Pages/Riesgos/BloqueoDePagos.vue'
import MoraYCastigos from '@/Pages/Riesgos/MoraYCastigos.vue'
import Plazos from '@/Pages/Configuracion/Plazos/Inicio.vue'
import AsesoresHistorico from '@/Pages/Asesores/Historico.vue'
import PrestamosSimulacion from '@/Pages/Prestamos/Simulacion.vue'
import GerenciaReportes from '@/Pages/Gerencia/Reportes.vue'
import AsesoresMetas from '@/Pages/Asesores/Metas.vue'
import CajaOtrosIngresos from '@/Pages/Caja/OtrosIngresos.vue'
import AsesoresCartilla from '@/Pages/Asesores/Cartilla.vue'
import PrestamosCronograma from '@/Pages/Prestamos/Cronograma.vue'
import PagosReporteIngresos from '@/Pages/Pagos/ReporteIngresos.vue'
import PagosEstadisticas from '@/Pages/Pagos/EstadisticasIngresos.vue'


const routes = [
  {
    path: '/',
    name: 'Principal',
    component: Principal,
    meta: { requiresAuth: true, title: 'Dashboard' }
  },

  {
    path: '/clientes/registro-de-clientes',
    name: 'RegistroClientes',
    component: RegistroClientes,
    meta: { requiresAuth: true, title: 'Registro de Clientes' }
  },

  {
    path: '/actividad-negocio',
    name: 'ActividadDeNegocio',
    component: ActividadDeNegocio,
    meta: { requiresAuth: true, title: 'Actividad de Negocio' }
  },

  {
    path: '/prestamos/evaluacion',
    name: 'Evaluacion',
    component: Evaluacion,
    meta: { requiresAuth: true, title: 'Evaluación' }
  },

  {
    path: '/prestamos/desembolso',
    name: 'Desembolso',
    component: Desembolso,
    meta: { requiresAuth: true, title: 'Desembolso' }
  },

  {
    path: '/perfil',
    name: 'Perfil',
    component: Perfil,
    meta: { requiresAuth: true, title: 'Perfil' }
  },

  {
    path: '/cambiar-clave',
    name: 'CambiarClave',
    component: CambiarClave,
    meta: { requiresAuth: true, title: 'Cambiar Clave' }
  },

  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { layout: LayoutLogin, title: 'Login' }
  },

  { path: '/clientes/listado-de-clientes', name: 'ClientesListado', component: ClientesListado, meta: { requiresAuth: true, title: 'Listado del Cliente' } },
  { path: '/clientes/historial-del-cliente', name: 'ClientesHistorial', component: ClienteHistoria, meta: { requiresAuth: true, title: 'Historial del Cliente' } },


  // --- ASESORES ---
  { path: '/asesores/metas', name: 'AsesoresMetas', component: AsesoresMetas, meta: { requiresAuth: true, title: 'Metas' } },
  { path: '/asesores/cartilla-de-cobranza', name: 'AsesoresCartilla', component: AsesoresCartilla, meta: { requiresAuth: true, title: 'Cartilla de Cobranza' } },
  { path: '/asesores/historico-del-asesor', name: 'AsesoresHistorico', component: AsesoresHistorico, meta: { requiresAuth: true, title: 'Histórico del Asesor' } },

  // --- PAGOS ---
  { path: '/pagos/reportes', name: 'PagosReportes', component: PagosReporteIngresos, meta: { requiresAuth: true, title: 'Reportes' } },
  { path: '/pagos/estadisticas-de-ingresos', name: 'PagosEstadisticas', component: PagosEstadisticas, meta: { requiresAuth: true, title: 'Estadísticas de Ingresos' } },

  { path: '/usuarios', name: 'Usuarios', component: Usuario, meta: { requiresAuth: true, title: 'Usuarios' } },
  { path: '/roles', name: 'Roles', component: Rol, meta: { requiresAuth: true, title: 'Roles' } },
  { path: '/permisos', name: 'Permisos', component: PermisosInicio, meta: { requiresAuth: true, title: 'Permisos' } },
  { path: '/configuracion', name: 'Configuracion', component: RegistroClientes, meta: { layout: LayoutDefault, requiresAuth: true, title: 'Configuración' } },
  { path: '/configuracion/plazos', name: 'Plazos', component: Plazos, meta: { requiresAuth: true, title: 'Plazos' } },
  { path: '/propiedades', name: 'Propiedades', component: RegistroPropiedades, meta: { requiresAuth: true, title: 'Propiedades' } },

  // --- PRESTAMOS ---
  {
    path: '/prestamos/registrar',
    name: 'SolicitarPrestamo',
    component: PrestamosRegistrar,
    meta: { requiresAuth: true, title: 'Préstamos' }
  },
  { path: '/prestamos/simulacion', name: 'PrestamosSimulacion', component: PrestamosSimulacion, meta: { requiresAuth: true, title: 'Simulación' } },
  { path: '/prestamos/cronograma-de-pagos', name: 'PrestamosCronograma', component: PrestamosCronograma, meta: { requiresAuth: true, title: 'Cronograma de Pagos' } },
  { path: '/prestamos/historial-de-prestamos', name: 'PrestamosHistorial', component: PrestamosInicio, meta: { requiresAuth: true, title: 'Historial de Préstamos' } },

  // --- AHORROS ---
  { path: '/ahorros/apertura-de-cuenta', name: 'AhorrosApertura', component: AhorrosApertura, meta: { requiresAuth: true, title: 'Apertura de Cuenta' } },
  { path: '/ahorros/depositos-retiros', name: 'AhorrosMovimientos', component: AhorrosMovimientos, meta: { requiresAuth: true, title: 'Depósitos / Retiros' } },
  { path: '/ahorros/estado-de-cuenta', name: 'AhorrosEstado', component: AhorrosEstado, meta: { requiresAuth: true, title: 'Estado de Cuenta' } },

  // --- CAJA ---
  { path: '/caja/apertura-de-caja', name: 'CajaApertura', component: CajaApertura, meta: { requiresAuth: true, title: 'Apertura de Caja' } },
  { path: '/caja/cobros', name: 'CajaCobros', component: CajaOtrosIngresos, meta: { requiresAuth: true, title: 'Cobros' } },
  { path: '/caja/pagos', name: 'CajaPagos', component: CajaPagos, meta: { requiresAuth: true, title: 'Pagos' } },
  { path: '/caja/cierre-de-caja', name: 'CajaCierre', component: CajaCierre, meta: { requiresAuth: true, title: 'Cierre de Caja' } },
  { path: '/caja/movimientos', name: 'CajaMovimientos', component: CajaMovimientos, meta: { requiresAuth: true, title: 'Movimientos de Caja' } },
  { path: '/caja/desembolsar', name: 'CajaDesembolsar', component: Desembolsar, meta: { requiresAuth: true, title: 'Desembolsar' } },

  // --- RIESGOS ---
  { path: '/riesgos/mora-y-castigos', name: 'RiesgosMora', component: MoraYCastigos, meta: { requiresAuth: true, title: 'Mora y Castigos' } },
  { path: '/riesgos/bloqueo-de-pagos', name: 'RiesgosBloqueo', component: BloqueoDePagos, meta: { requiresAuth: true, title: 'Bloqueo de Pagos' } },

  // --- GERENCIA ---
  { path: '/gerencia/reportes-gerenciales', name: 'GerenciaReportes', component: GerenciaReportes, meta: { requiresAuth: true, title: 'Reportes Gerenciales' } },
  { path: '/gerencia/autorizaciones', name: 'GerenciaAutorizaciones', component: AutorizacionesInicio, meta: { requiresAuth: true, title: 'Autorizaciones' } },

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