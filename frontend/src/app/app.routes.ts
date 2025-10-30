import { Routes } from '@angular/router';

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
    },
    {
        path: 'antiguedades',
        loadComponent: () => import(/* webpackChunkName: "indice-antiguedades" */ './antiguedades/indice-antiguedades/indice-antiguedades').then(m => m.IndiceAntiguedades),
        title: "Mis Antigüedades",
    },
    {
        path: 'antiguedades/crear',
        loadComponent: () => import(/* webpackChunkName: "crear-editar-antiguedad" */ './antiguedades/crear-editar-antiguedad/crear-editar-antiguedad').then(m => m.CrearEditarAntiguedad),
        title: "Crear Antigüedad",
    },
    {
        path: 'antiguedades/editar/:id',
        loadComponent: () => import(/* webpackChunkName: "crear-editar-antiguedad" */ './antiguedades/crear-editar-antiguedad/crear-editar-antiguedad').then(m => m.CrearEditarAntiguedad),
        title: "Editar Antigüedad",
    },
    {
        path: 'antiguedadesAlaVenta',
        loadComponent: () => import(/* webpackChunkName: "indice-antiguedades-venta" */ './antiguedades-venta/indice-antiguedades-venta/indice-antiguedades-venta').then(m => m.IndiceAntiguedadesVenta),
        title: "Antigüedades a la Venta",
    },
    {
        path: 'antiguedadesAlaVenta/crear',
        loadComponent: () => import(/* webpackChunkName: "crear-editar-antiguedad-venta" */ './antiguedades-venta/crear-editar-antiguedad-venta/crear-editar-antiguedad-venta').then(m => m.CrearEditarAntiguedadVenta),
        title: "Publicar Antigüedad",
    },
    {
        path: 'antiguedadesAlaVenta/editar/:id',
        loadComponent: () => import(/* webpackChunkName: "crear-editar-antiguedad-venta" */ './antiguedades-venta/crear-editar-antiguedad-venta/crear-editar-antiguedad-venta').then(m => m.CrearEditarAntiguedadVenta),
        title: "Editar publicación",
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
    },
    {
        path: 'carrito',
        loadComponent: () => import(/* webpackChunkName: "carrito" */ './carrito/carrito/carrito').then(m => m.Carrito),
        title: "Carrito de Compras",
    },
    {
        path:'**', //Wild card : Atrapa cualquier ruta de tu dominio. SIEMPRE DEBE IR AL FINAL, ya que el buscador de rutas es secuencial, empieza en el index 0 de este array de rutes y devuelve la primera coincidencia
        redirectTo: '',
        title:'Error 404'
    }
];
