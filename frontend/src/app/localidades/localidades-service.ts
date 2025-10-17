import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable, Injector, Resource, ResourceRef } from '@angular/core';
import { environment } from '../environments/environment.development';
import { LocalidadAutocompletarDTO, LocalidadDTO } from './modelo/localidadDTO';
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


  public autocompletarResource(
    locDescripcion: () => string | null,
    injector: Injector = inject(Injector),
    provinciaId?: () => number | null,
    selectedId?: () => number | null
  ): ResourceRef<LocalidadAutocompletarDTO[]> {
    return rxResource<LocalidadAutocompletarDTO[], HttpParams>({
      params: () => buildQueryParams({
        provId: provinciaId?.() ?? '',
        locDescripcion: locDescripcion() ?? '',
      }),
      stream: (options) => {
        const prov = options.params.get('params[provId]');
        const desc = (options.params.get('params[locDescripcion]') ?? '').trim();
        const selId = selectedId?.() ?? null;

        if (selId !== null) {
          return this.http.get<LocalidadDTO>(`${this.urlBase}/${selId}`).pipe(
            map(loc => ([{
            id: loc.locId,
            descripcion: loc.locDescripcion,
            dependenciaId: loc.provincia.provId
          }] as LocalidadAutocompletarDTO[])));
        }

        if (/* desc === '' ||  */prov === '') {
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
    });
  };

/*   public getByIdAutocompletarResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<LocalidadAutocompletarDTO> {
    return rxResource<LocalidadAutocompletarDTO, number | null>({
      stream: () => {
        if(id() === null){
          return of({} as LocalidadAutocompletarDTO);
        }
        return this.http.get<LocalidadDTO>(`${this.urlBase}/${id()}`).pipe(
          map(loc => ({
            id: loc.locId,
            descripcion: loc.locDescripcion,
            dependenciaId: loc.provincia.provId
          } as LocalidadAutocompletarDTO))
        );
      },
      defaultValue: {} as LocalidadAutocompletarDTO,
      injector: injector
    });
  } */
}
