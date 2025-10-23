import { inject, Injectable, Injector, ResourceRef } from '@angular/core';
import { IServiceAutocompletar } from '../compartidos/interfaces/IServiceAutocompletar';
import { CategoriaAutocompletarDTO, CategoriaDTO } from './modelo/CategoriaDTO';
import { map } from 'rxjs/internal/operators/map';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { HttpClient, HttpParams } from '@angular/common/http';
import { rxResource } from '@angular/core/rxjs-interop';
import { environment } from '../environments/environment.development';

@Injectable({
  providedIn: 'root'
})
export class CategoriasService implements IServiceAutocompletar<CategoriaAutocompletarDTO> {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Categorias';


  public autocompletarResource(
    catDescripcion: () => string | null,
    injector: Injector = inject(Injector),
    dependenciaId?: () => number | null,
  ): ResourceRef<CategoriaAutocompletarDTO[]> {
    return rxResource<CategoriaAutocompletarDTO[], HttpParams>({
      params: () => buildQueryParams({
        catDescripcion: catDescripcion() ?? '',
      }),
      stream: (options) => {
        return this.http.get<CategoriaDTO[]>(this.urlBase, { params: options.params }).pipe(
          map(categorias =>
            categorias.map(cat => ({
              id: cat.catId,
              descripcion: cat.catDescripcion,
              dependenciaId: null
            })) as CategoriaAutocompletarDTO[]
          )
        );
      },
      defaultValue: [],
      injector: injector
    });
  };
  
}
