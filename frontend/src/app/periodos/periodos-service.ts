import { inject, Injectable, Injector, ResourceRef } from '@angular/core';
import { environment } from '../environments/environment.development';
import { IServiceAutocompletar } from '../compartidos/interfaces/IServiceAutocompletar';
import { PeriodoAutocompletarDTO, PeriodoDTO } from './modelo/PeriodoDTO';
import { HttpClient, HttpParams } from '@angular/common/http';
import { rxResource } from '@angular/core/rxjs-interop';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class PeriodosService implements IServiceAutocompletar<PeriodoAutocompletarDTO> {


  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Periodos';


  public autocompletarResource(
    perDescripcion: () => string | null,
    injector: Injector = inject(Injector),
    dependenciaId?: () => number | null,
  ): ResourceRef<PeriodoAutocompletarDTO[]> {
    return rxResource<PeriodoAutocompletarDTO[], HttpParams>({
      params: () => buildQueryParams({
        perDescripcion: perDescripcion() ?? '',
      }),
      stream: (options) => {
        return this.http.get<PeriodoDTO[]>(this.urlBase, { params: options.params }).pipe(
          map(periodos =>
            periodos.map(per => ({
              id: per.perId,
              descripcion: per.perDescripcion,
              dependenciaId: null
            })) as PeriodoAutocompletarDTO[]
          )
        );
      },
      defaultValue: [],
      injector: injector
    });
  };
  
}
