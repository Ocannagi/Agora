import { HttpClient, HttpParams, httpResource, HttpResourceFn, HttpResourceRef, HttpResponse } from '@angular/common/http';
import { inject, Injectable, Injector, Resource, ResourceRef } from '@angular/core';
import { environment } from '../environments/environment.development';
import { LocalidadDTO } from './modelo/localidadDTO';
import { Observable } from 'rxjs/internal/Observable';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { rxResource } from '@angular/core/rxjs-interop';
import { of } from 'rxjs';
import { IServiceAutocompletar } from '../compartidos/interfaces/IServiceAutocompletar';

@Injectable({
  providedIn: 'root'
})
export class LocalidadesService implements IServiceAutocompletar<LocalidadDTO> {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Localidades';

  public getLocalidadesByAutocompletar(provinciaId: number, localidadDescripcion: string): Observable<HttpResponse<LocalidadDTO[]>> {
    const params = {
      provId: provinciaId,
      locDescripcion: localidadDescripcion
    };
    return this.http.get<LocalidadDTO[]>(this.urlBase, { observe: 'response', params: buildQueryParams(params) });
  }

  public autocompletarResource(
    locDescripcion: () => string | null,
    injector: Injector = inject(Injector),
    provinciaId?: () => number | null
  ): Resource<LocalidadDTO[]> {
    return rxResource<LocalidadDTO[],HttpParams>({
      params: () => buildQueryParams({
        provId: provinciaId?.() ?? null,
        locDescripcion: locDescripcion() ?? ''
      }),
      stream: (options) => {if(/* options.params.get('params[locDescripcion]')!.length < 2 ||  */options.params.get('params[provId]') === '0') return of([]) as Observable<LocalidadDTO[]>;
        return this.http.get<LocalidadDTO[]>(this.urlBase, { params: options.params })},
      defaultValue: [],
      injector: injector
      }).asReadonly();
    };

}
