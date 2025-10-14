import { httpResource } from '@angular/common/http';
import { inject, Injectable, Injector, Resource } from '@angular/core';

import { TipoUsuarioDTO } from './seguridadDTO';
import { environment } from '../environments/environment.development';

@Injectable({
  providedIn: 'root'
})
export class TiposUsuarioService {
  private urlBase = environment.apiURL + '/TiposUsuario';

  public tiposUsuarioResource(injector: Injector = inject(Injector)): Resource<TipoUsuarioDTO[]> {
    return httpResource<TipoUsuarioDTO[]>(
      () => ({
        url: `${this.urlBase}`
      }),
      { defaultValue: [], injector: injector }
    ).asReadonly();
  }
}
