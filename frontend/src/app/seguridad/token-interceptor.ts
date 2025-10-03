import { HttpHandlerFn, HttpInterceptorFn, HttpRequest } from "@angular/common/http";
import { inject } from "@angular/core";
import { AutenticacionStore } from "./store/autenticacion.store";


export const authInterceptor: HttpInterceptorFn = (req : HttpRequest<any>, next : HttpHandlerFn) => {
    const store  = inject(AutenticacionStore)
    const token = store.jwt();

    if(token){
        const headers = req.headers.set('Authorization', `Bearer ${token}`);
        req = req.clone({headers});
    }

    return next(req);
}