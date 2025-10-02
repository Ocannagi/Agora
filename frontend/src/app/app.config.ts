import { ApplicationConfig, importProvidersFrom, provideBrowserGlobalErrorListeners, provideZoneChangeDetection } from '@angular/core';
import { provideRouter, withComponentInputBinding } from '@angular/router';
import { MAT_FORM_FIELD_DEFAULT_OPTIONS } from '@angular/material/form-field';
import { MAT_DATE_LOCALE } from '@angular/material/core';
import {provideMomentDateAdapter} from '@angular/material-moment-adapter';
import { provideSweetAlert2} from '@sweetalert2/ngx-sweetalert2';


import { routes } from './app.routes';
import { provideHttpClient, withFetch, withInterceptors } from '@angular/common/http';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes,withComponentInputBinding()),
    {provide: MAT_FORM_FIELD_DEFAULT_OPTIONS, useValue: {subscriptSizing : 'dynamic'}}, //Esto hace que los elementos matFormField no se superpongan cuando son largos
    {provide: MAT_DATE_LOCALE, useValue: 'es-AR'} //Configuramos la fecha al local de acá
     ,provideMomentDateAdapter({
      parse: {dateInput:['DD/MM/YYYY']},
      display: {dateInput:'DD/MM/YYYY', monthYearLabel:'MMMM YYYY', dateA11yLabel:'LL', monthYearA11yLabel:'MMMM YYYY',monthLabel:''}
    }),
    provideHttpClient(withFetch()/*, withInterceptors([authInterceptor])*/),
    provideSweetAlert2()
  ]
};
