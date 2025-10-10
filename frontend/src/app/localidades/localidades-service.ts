import { HttpClient, HttpParams, HttpResponse } from '@angular/common/http';
import { inject, Injectable, Injector, Resource } from '@angular/core';
import { environment } from '../environments/environment.development';
import { LocalidadAutocompletarDTO, LocalidadDTO } from './modelo/localidadDTO';
import { Observable } from 'rxjs/internal/Observable';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { rxResource } from '@angular/core/rxjs-interop';
import { of, map } from 'rxjs';
import { IServiceAutocompletar } from '../compartidos/interfaces/IServiceAutocompletar';

@Injectable({
  providedIn: 'root'
})
export class LocalidadesService implements IServiceAutocompletar<LocalidadAutocompletarDTO> {

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
  ): Resource<LocalidadAutocompletarDTO[]> {
    return rxResource<LocalidadAutocompletarDTO[], HttpParams>({
      params: () => buildQueryParams({
        provId: provinciaId?.() ?? null,
        locDescripcion: locDescripcion() ?? ''
      }),
      stream: (options) => {
        const prov = options.params.get('params[provId]');
        const desc = (options.params.get('params[locDescripcion]') ?? '').trim();
        if (/* desc === '' ||  */prov === null) {
          return of([] as LocalidadAutocompletarDTO[]);
        }
        return this.http.get<LocalidadDTO[]>(this.urlBase, { params: options.params }).pipe(
          map(localidades =>
            localidades.map(loc => ({
              id: loc.locId,
              descripcion: loc.locDescripcion,
              dependenciaId: loc.provincia.provId
            })) as LocalidadAutocompletarDTO[]
          )
        );
      },
      defaultValue: [],
      injector: injector
    }).asReadonly();
  };

}
