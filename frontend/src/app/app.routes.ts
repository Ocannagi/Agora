import { inject } from '@angular/core';
import { CanActivate, CanActivateFn, Router, Routes, UrlTree } from '@angular/router';
import { AutenticacionStore } from './seguridad/store/autenticacion.store';


//GUARDS
const soporteGuard: CanActivateFn = (): boolean | UrlTree => {
  const auth = inject(AutenticacionStore);
  const router = inject(Router);
  return auth.isSoporteTecnico() ? true : auth.isLoggedIn() ? router.parseUrl('/') : router.parseUrl('/login');
};

const autenticadoGuard: CanActivateFn = (): boolean | UrlTree => {
  const auth = inject(AutenticacionStore);
  const router = inject(Router);
  return auth.isLoggedIn() ? true : router.parseUrl('/login');
}

const compraVentaGuard: CanActivateFn = (): boolean | UrlTree => {
  const auth = inject(AutenticacionStore);
  const router = inject(Router);
  return auth.isCompradorVendedor() ? true : auth.isLoggedIn() ? router.parseUrl('/') :  router.parseUrl('/login');
}

export const routes: Routes = [
    {
        path: '',
        loadComponent: () => import(/* webpackChunkName: "landing-page" */ './landing-page/landing-page').then(c => c.LandingPage),
        title: "Home Page"
    },
    {
        path: 'login',
        loadComponent: () => import(/* webpackChunkName: "login" */ './seguridad/login/login').then(m => m.Login),
        title: "Login",
    },
    {
        path: 'usuarios',
        loadComponent: () => import(/* webpackChunkName: "indice-usuarios" */ './usuarios/indice-usuarios/indice-usuarios').then(m => m.IndiceUsuarios),
        title: "Usuarios",
        canActivate: [soporteGuard]
    },
    {
        path: 'usuarios/crear',
        loadComponent: () => import(/* webpackChunkName: "crear-usuario" */ './usuarios/crear-editar-usuario/crear-editar-usuario').then(m => m.CrearEditarUsuario),
        title: "Crear Usuario",
    },
    {
        path: 'usuarios/editar/:id',
        loadComponent: () => import(/* webpackChunkName: "crear-usuario" */ './usuarios/crear-editar-usuario/crear-editar-usuario').then(m => m.CrearEditarUsuario),
        title: "Editar Usuario",
        canActivate: [autenticadoGuard]
    },
    {
        path: 'antiguedades',
        loadComponent: () => import(/* webpackChunkName: "indice-antiguedades" */ './antiguedades/indice-antiguedades/indice-antiguedades').then(m => m.IndiceAntiguedades),
        title: "Mis Antigüedades",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'antiguedades/crear',
        loadComponent: () => import(/* webpackChunkName: "crear-editar-antiguedad" */ './antiguedades/crear-editar-antiguedad/crear-editar-antiguedad').then(m => m.CrearEditarAntiguedad),
        title: "Crear Antigüedad",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'antiguedades/editar/:id',
        loadComponent: () => import(/* webpackChunkName: "crear-editar-antiguedad" */ './antiguedades/crear-editar-antiguedad/crear-editar-antiguedad').then(m => m.CrearEditarAntiguedad),
        title: "Editar Antigüedad",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'antiguedadesRetiradasNd',
        loadComponent: () => import(/* webpackChunkName: "indice-antiguedades-retiradas-nd" */ './antiguedades/antiguedades-retiradas-nd/indice-antiguedades-retiradas-nd/indice-antiguedades-retiradas-nd').then(m => m.IndiceAntiguedadesRetiradasNd),
        title: "Antigüedades Retiradas - No Disponibles",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'antiguedadesRetiradasNd/:id',
        loadComponent: () => import(/* webpackChunkName: "ver-antiguedad-retirada-nd" */ './antiguedades/antiguedades-retiradas-nd/ver-antiguedad-retirada-nd/ver-antiguedad-retirada-nd').then(m => m.VerAntiguedadRetiradaNd),
        title: "Ver Antigüedad Retirada - No Disponible",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'antiguedadesAlaVenta',
        loadComponent: () => import(/* webpackChunkName: "indice-antiguedades-venta" */ './antiguedades-venta/indice-antiguedades-venta/indice-antiguedades-venta').then(m => m.IndiceAntiguedadesVenta),
        title: "Antigüedades a la Venta",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'antiguedadesAlaVenta/crear',
        loadComponent: () => import(/* webpackChunkName: "crear-editar-antiguedad-venta" */ './antiguedades-venta/crear-editar-antiguedad-venta/crear-editar-antiguedad-venta').then(m => m.CrearEditarAntiguedadVenta),
        title: "Publicar Antigüedad",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'antiguedadesAlaVenta/editar/:id',
        loadComponent: () => import(/* webpackChunkName: "crear-editar-antiguedad-venta" */ './antiguedades-venta/crear-editar-antiguedad-venta/crear-editar-antiguedad-venta').then(m => m.CrearEditarAntiguedadVenta),
        title: "Editar publicación",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'galeriaVertical',
        loadComponent: () => import(/* webpackChunkName: "galeria" */ './galeria/galeria-vertical/galeria-vertical').then(m => m.GaleriaVertical),
        title: "Galería",
    },
    {
        path: 'galeriaVertical/:id',
        loadComponent: () => import(/* webpackChunkName: "ver-antiguedad-galeria" */ './galeria/ver-antiguedad-galeria/ver-antiguedad-galeria').then(m => m.VerAntiguedadGaleria),
        title: "Ver Antigüedad",
        canActivate: [autenticadoGuard]
    },
    {
        path: 'carrito',
        loadComponent: () => import(/* webpackChunkName: "carrito" */ './carrito/carrito/carrito').then(m => m.Carrito),
        title: "Carrito de Compras",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'compras',
        loadComponent: () => import(/* webpackChunkName: "indice-compras" */ './compras-ventas/indice-compras-ventas/indice-compras-ventas').then(m => m.IndiceComprasVentas),
        title: "Mis Compras",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'compras/:id',
        loadComponent: () => import(/* webpackChunkName: "ver-compras-ventas" */ './compras-ventas/ver-compras-ventas/ver-compras-ventas').then(m => m.VerComprasVentas),
        title: "Ver Compras",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'checkout',
        loadComponent: () => import(/* webpackChunkName: "checkout" */ './carrito/checkout/checkout').then(m => m.Checkout),
        title: "Checkout",
        canActivate: [compraVentaGuard]
    },
    {
        path: 'ventas',
        loadComponent: () => import(/* web  packChunkName: "indice-ventas" */ './compras-ventas/indice-ventas/indice-ventas').then(m => m.IndiceVentas),
        title: "Mis Ventas",
        canActivate: [compraVentaGuard]
        
    },
    {
        path: 'ventas/:id',
        loadComponent: () => import(/* webpackChunkName: "ver-ventas" */ './compras-ventas/ver-ventas/ver-ventas').then(m => m.VerVentas),
        title: "Ver Venta",
        canActivate: [compraVentaGuard]
    },
    {
        path: '**', //Wild card : Atrapa cualquier ruta de tu dominio. SIEMPRE DEBE IR AL FINAL, ya que el buscador de rutas es secuencial, empieza en el index 0 de este array de rutes y devuelve la primera coincidencia
        redirectTo: '',
        title: 'Error 404'
    }
];
