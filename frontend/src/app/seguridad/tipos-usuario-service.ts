import { HttpClient, HttpParams, httpResource } from '@angular/common/http';
import { inject, Injectable, Injector, Resource, ResourceRef } from '@angular/core';

import { TipoUsuarioDTO } from './seguridadDTO';
import { environment } from '../environments/environment.development';
import { rxResource } from '@angular/core/rxjs-interop';
import { UsuarioDTO } from '../usuarios/modelo/usuarioDTO';
import { of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class TiposUsuarioService {
  private urlBase = environment.apiURL + '/TiposUsuario';
  private http = inject(HttpClient);

  public tiposUsuarioResource(injector: Injector = inject(Injector)): Resource<TipoUsuarioDTO[]> {
    return httpResource<TipoUsuarioDTO[]>(
      () => ({
        url: `${this.urlBase}`
      }),
      { defaultValue: [], injector: injector }
    ).asReadonly();
  }

  public getByIdResource(user: () => UsuarioDTO, injector: Injector = inject(Injector)) : ResourceRef<TipoUsuarioDTO> {
    return rxResource<TipoUsuarioDTO, HttpParams>({
      params: () => new HttpParams().set('id', user()?.usrTipoUsuario ?? ''),
      stream: (option) => {
        if(option.params.get('id') === null || option.params.get('id') === undefined || option.params.get('id') === ''){
          return of({} as TipoUsuarioDTO);
        }
        return this.http.get<TipoUsuarioDTO>(this.urlBase + '/' + option.params.get('id'));
      },
      defaultValue: {} as TipoUsuarioDTO,
      injector: injector
    });
  }
}
