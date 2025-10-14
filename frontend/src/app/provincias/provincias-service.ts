import { inject, Injectable, Injector, ResourceRef } from '@angular/core';
import { environment } from '../environments/environment.development';
import { HttpClient, HttpParams } from '@angular/common/http';
import { ProvinciaAutocompletarDTO, ProvinciaDTO } from './modelo/provinciaDTO';
import { rxResource } from '@angular/core/rxjs-interop';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { map, of } from 'rxjs';
import { IServiceAutocompletar } from '../compartidos/interfaces/IServiceAutocompletar';

@Injectable({
  providedIn: 'root'
})
export class ProvinciasService implements IServiceAutocompletar<ProvinciaAutocompletarDTO> {
  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Provincias';


  public autocompletarResource(
    provDescripcion: () => string | null,
    injector: Injector = inject(Injector),
    dependenciaIdId?: () => number | null
  ): ResourceRef<ProvinciaAutocompletarDTO[]> {
    return rxResource<ProvinciaAutocompletarDTO[], HttpParams>({
      params: () => buildQueryParams({
        provDescripcion: provDescripcion() ?? ''
      }),
      stream: (options) => {
        return this.http.get<ProvinciaDTO[]>(this.urlBase, { params: options.params }).pipe(
          map(provincias =>
            provincias.map(prov => ({
              id: prov.provId,
              descripcion: prov.provDescripcion,
              dependenciaId: null
            })) as ProvinciaAutocompletarDTO[]
          )
        );
      },
      defaultValue: [],
      injector: injector
    });
  };

}
