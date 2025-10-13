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
        path:'**', //Wild card : Atrapa cualquier ruta de tu dominio. SIEMPRE DEBE IR AL FINAL, ya que el buscador de rutas es secuencial, empieza en el index 0 de este array de rutes y devuelve la primera coincidencia
        redirectTo: '',
        title:'Error 404'
    }
];
