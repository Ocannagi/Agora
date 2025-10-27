import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable, Injector, ResourceRef } from '@angular/core';
import { environment } from '../environments/environment.development';
import { UsuarioDomiciliosDTO } from './modelo/UsuarioDomiciliosDTO';
import { rxResource } from '@angular/core/rxjs-interop';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class UsuariosDomiciliosService {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/UsuariosDomicilios';

  public getByUsrIdResource(usrId: () => number | null, injector: Injector = inject(Injector)): ResourceRef<UsuarioDomiciliosDTO> {
    return rxResource<UsuarioDomiciliosDTO, HttpParams>({
      params: () => {
        return buildQueryParams({ usrId: usrId?.() ?? '' });
      },
      stream: ({ params }) => {
        const usr = params.get('params[usrId]');
        if (!usr) {
          return of<UsuarioDomiciliosDTO>({} as UsuarioDomiciliosDTO);
        }
        return this.http.get<UsuarioDomiciliosDTO>(this.urlBase, { params })
      },
      defaultValue: {} as UsuarioDomiciliosDTO,
      injector
    });
  }
  
}
