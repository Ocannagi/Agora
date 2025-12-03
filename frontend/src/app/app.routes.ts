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
        path: 'categorias',
        loadComponent: () => import(/* webpackChunkName: "indice-categorias" */ './categorias/indice-categorias/indice-categorias').then(m => m.IndiceCategorias),
        title: "Ver Categorias",
        canActivate: [soporteGuard]
    },
        {
        path: 'categorias/crear',
        loadComponent: () => import(/* webpackChunkName: "ver-categorias" */ './categorias/crear-editar-categoria/crear-editar-categoria').then(m => m.CrearEditarCategoria),
        title: "Crear Categoria",
        canActivate: [soporteGuard]
    },
        {
        path: 'categorias/editar/:id',
        loadComponent: () => import(/* webpackChunkName: "crear-categoria" */ './categorias/crear-editar-categoria/crear-editar-categoria').then(m => m.CrearEditarCategoria),
        title: "Editar Categoria",
        canActivate: [soporteGuard]
    },
    {
        path: 'subcategorias',
        loadComponent: () => import(/* webpackChunkName: "indice-subcategorias" */ './subcategorias/indice-subcategorias/indice-subcategorias').then(m => m.IndiceSubcategorias),
        title: "Ver Subcategorias",
        canActivate: [soporteGuard]
    },
    {
        path: 'subcategorias/crear',
        loadComponent: () => import(/* webpackChunkName: "crear-subcategoria" */ './subcategorias/crear-editar-subcategoria/crear-editar-subcategoria').then(m => m.CrearEditarSubcategoria),
        title: "Crear Subcategoria",
        canActivate: [soporteGuard]
    },
            {
        path: 'subcategorias/editar/:id',
        loadComponent: () => import(/* webpackChunkName: "crear-subcategoria" */ './subcategorias/crear-editar-subcategoria/crear-editar-subcategoria').then(m => m.CrearEditarSubcategoria),
        title: "Editar Subcategoria",
        canActivate: [soporteGuard]
    },
    {
        path: 'localidades',
        loadComponent: () => import(/* webpackChunkName: "indice-localidades" */ './localidades/indice-localidades/indice-localidades').then(m => m.IndiceLocalidades),
        title: "Ver Localidades",
        canActivate: [soporteGuard]
    },
            {
        path: 'localidades/crear',
        loadComponent: () => import(/* webpackChunkName: "crear-localidades" */ './localidades/crear-editar-localidad/crear-editar-localidad').then(m => m.CrearEditarLocalidad),
        title: "Crear Localidades",
        canActivate: [soporteGuard]
    },
            {
        path: 'localidades/editar/:id',
        loadComponent: () => import(/* webpackChunkName: "editar-localidades" */ './localidades/crear-editar-localidad/crear-editar-localidad').then(m => m.CrearEditarLocalidad),
        title: "Editar Localidades",
        canActivate: [soporteGuard]
    },
    {
        path: '**', //Wild card : Atrapa cualquier ruta de tu dominio. SIEMPRE DEBE IR AL FINAL, ya que el buscador de rutas es secuencial, empieza en el index 0 de este array de rutes y devuelve la primera coincidencia
        redirectTo: '',
        title: 'Error 404'
    }
];
