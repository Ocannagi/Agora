import { inject, Injectable, Injector, ResourceRef } from '@angular/core';
import { IServiceAutocompletar } from '../compartidos/interfaces/IServiceAutocompletar';
import { SubcategoriaAutocompletarDTO, SubcategoriaDTO } from './modelo/subcategoriaDTO';
import { environment } from '../environments/environment.development';
import { HttpClient, HttpParams } from '@angular/common/http';
import { rxResource } from '@angular/core/rxjs-interop';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { map, of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SubcategoriasService implements IServiceAutocompletar<SubcategoriaAutocompletarDTO> {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Subcategorias';


  public autocompletarResource(
    scatDescripcion: () => string | null,
    injector: Injector = inject(Injector),
    catId?: () => number | null,
  ): ResourceRef<SubcategoriaAutocompletarDTO[]> {
    return rxResource<SubcategoriaAutocompletarDTO[], HttpParams>({
      params: () => buildQueryParams({
        catId: catId?.() ?? '',
        scatDescripcion: scatDescripcion() ?? '',
      }),
      stream: (options) => {
        const cat = options.params.get('params[catId]');

        if (/* desc === '' ||  */cat === '') {
          return of([] as SubcategoriaAutocompletarDTO[]);
        }

        return this.http.get<SubcategoriaDTO[]>(this.urlBase, { params: options.params }).pipe(
          map(subcategorias =>
            subcategorias.map(scat => ({
              id: scat.scatId,
              descripcion: scat.scatDescripcion,
              dependenciaId: scat.categoria.catId
            })) as SubcategoriaAutocompletarDTO[]
          )
        );
      },
      defaultValue: [],
      injector: injector
    });
  };
  
}
