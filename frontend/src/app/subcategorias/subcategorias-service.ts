import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { IServiceAutocompletar } from '../compartidos/interfaces/IServiceAutocompletar';
import { SubcategoriaAutocompletarDTO, SubcategoriaDTO, SubcategoriaCreacionDTO,SubcategoriaIndiceDTO,SubcategoriaMinDTO } from './modelo/subcategoriaDTO';
import { environment } from '../environments/environment.development';
import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { rxResource } from '@angular/core/rxjs-interop';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { PaginadoRequestDTO } from '../compartidos/modelo/PaginadoRequestDTO';
import { PaginadoResponseDTO } from '../compartidos/modelo/PaginadoResponseDTO';
import { buildQueryPaginado } from '../compartidos/funciones/queryPaginado';
import { Observable } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { map, of, throwError, tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SubcategoriasService implements IServiceAutocompletar<SubcategoriaAutocompletarDTO> {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Subcategorias';

  readonly postError = signal<string | null>(null);
  readonly patchError = signal<string | null>(null);
  readonly deleteError = signal<string | null>(null);

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


  public getAllResource(injector: Injector = inject(Injector)): ResourceRef<SubcategoriaDTO[]> {
    return rxResource<SubcategoriaDTO[], null>({
      params: () => null,
      stream: (options) => {
        return this.http.get<SubcategoriaDTO[]>(this.urlBase);
      },
      defaultValue: [],
      injector: injector
    });
  }

  public getAllPaginado(
    paginado: () => PaginadoRequestDTO,
    injector: Injector = inject(Injector)
  ): ResourceRef<PaginadoResponseDTO<SubcategoriaIndiceDTO>> {
    return rxResource<PaginadoResponseDTO<SubcategoriaIndiceDTO>, PaginadoRequestDTO>({
      params: () => paginado(),
      stream: (options) => {
        const params = buildQueryPaginado(options.params);
        return this.http.get<PaginadoResponseDTO<SubcategoriaMinDTO>>(this.urlBase, { params }).pipe(
          map(response => {
            const indiceResponse: PaginadoResponseDTO<SubcategoriaIndiceDTO> = {
              totalRegistros: response.totalRegistros,
              paginaActual: response.paginaActual,
              registrosPorPagina: response.registrosPorPagina,
              arrayEntidad: response.arrayEntidad.map(scat => ({
                ...scat,
                id: scat.scatId,
                nombre: `${scat.categoria.catDescripcion} - ${scat.scatDescripcion}`,
                acciones: { editar: `/subcategorias/editar/${scat.scatId}`, borrar: true},
                
              }) as SubcategoriaIndiceDTO)
            };
            return indiceResponse;
          })
        );
      },
      defaultValue: {} as PaginadoResponseDTO<SubcategoriaIndiceDTO>,
      injector: injector
    });
}
  // ...existing




  public getByIdResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<SubcategoriaDTO> {
    return rxResource<SubcategoriaDTO, number | null>({
      params: () => id(),
      stream: (options) => {
        if (options.params === null) {
          return of({} as SubcategoriaDTO);
        }
        return this.http.get<SubcategoriaDTO>(this.urlBase + '/' + options.params!);
      },
      defaultValue: {} as SubcategoriaDTO,
      injector: injector
    });
  }




  public getById(id: number): Observable<SubcategoriaDTO> {
    return this.http.get<SubcategoriaDTO>(`${this.urlBase}/${id}`);
  }

  public create(subcategoriaDescripcion: SubcategoriaCreacionDTO): Observable<number> {
    // devuelve Observable<number> (id creado)
    console.log("Valor >> ",subcategoriaDescripcion);
    return this.http.post<number>(this.urlBase, subcategoriaDescripcion).pipe(
      map((response) => {
        this.postError.set(null);
        return response;
      }),
      catchError((err: HttpErrorResponse) => {
        const txt = String(err.error ?? '');
        if (err.status === 409) {
          const m = txt.match(/ID_(\d+)/);
          if (m) {
            return of(Number(m[1])); //Convierte en Observable el número y lo lanza como si fuera un next
          } else {
            this.postError.set(txt || 'Error desconocido al crear la subcategoria');
            return throwError(() => err);
          }
        }
        this.postError.set(txt || 'Error desconocido al crear la subcategoria.');
        return throwError(() => err);
      })
    );
    ;
  }




    public update(id: number, usuario: SubcategoriaCreacionDTO): Observable<void> {
      return this.http.patch<void>(`${this.urlBase}/${id}`, usuario).pipe(
        tap(() => this.patchError.set(null)),
        catchError((err: HttpErrorResponse) => {
          this.patchError.set(String(err.error ?? 'Error desconocido al editar la subcategoria.'));
          return throwError(() => err);
        })
      )
    }

  public delete(id: number): Observable<[]> {
    return this.http.delete<[]>(`${this.urlBase}/${id}`).pipe(
      tap(() => this.deleteError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.deleteError.set(String(err.error ?? 'Error desconocido al eliminar la subcategoria.'));
        return throwError(() => err);
      })
    );
  }

}
