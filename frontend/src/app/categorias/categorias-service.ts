import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { IServiceAutocompletar } from '../compartidos/interfaces/IServiceAutocompletar';
import { CategoriaAutocompletarDTO, CategoriaCreacionDTO, CategoriaIndiceDTO, CategoriaMinDTO } from './modelo/CategoriaDTO';
import { CategoriaDTO } from './modelo/CategoriaDTO';
import { map } from 'rxjs/operators';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { buildQueryPaginado } from '../compartidos/funciones/queryPaginado';
import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { rxResource } from '@angular/core/rxjs-interop';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment.development';
import { catchError } from 'rxjs/operators';
import { of, throwError, tap } from 'rxjs';
import { RetornaId } from '../compartidos/modelo/RetornaId';
import { PaginadoRequestDTO } from '../compartidos/modelo/PaginadoRequestDTO';
import { PaginadoResponseDTO } from '../compartidos/modelo/PaginadoResponseDTO';
@Injectable({
  providedIn: 'root'
})
export class CategoriasService implements  IServiceAutocompletar<CategoriaAutocompletarDTO> {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Categorias';

  
  readonly postError = signal<string | null>(null);
  readonly patchError = signal<string | null>(null);
  readonly deleteError = signal<string | null>(null);

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

  public getAllResource(injector: Injector = inject(Injector)): ResourceRef<CategoriaDTO[]> {
    return rxResource<CategoriaDTO[], null>({
      params: () => null,
      stream: (options) => {
        return this.http.get<CategoriaDTO[]>(this.urlBase);
      },
      defaultValue: [],
      injector: injector
    });
  }

  public getAllPaginado(
    paginado: () => PaginadoRequestDTO,
    injector: Injector = inject(Injector)
  ): ResourceRef<PaginadoResponseDTO<CategoriaIndiceDTO>> {
    return rxResource<PaginadoResponseDTO<CategoriaIndiceDTO>, PaginadoRequestDTO>({
      params: () => paginado(),
      stream: (options) => {
        const params = buildQueryPaginado(options.params);
        return this.http.get<PaginadoResponseDTO<CategoriaMinDTO>>(this.urlBase, { params }).pipe(
          map(response => {
            const indiceResponse: PaginadoResponseDTO<CategoriaIndiceDTO> = {
              totalRegistros: response.totalRegistros,
              paginaActual: response.paginaActual,
              registrosPorPagina: response.registrosPorPagina,
              arrayEntidad: response.arrayEntidad.map(cat => ({
                ...cat,
                id: cat.catId,
                nombre: (cat as any).catNombre ?? cat.catDescripcion,
                acciones: { editar: `/categorias/editar/${cat.catId}`, borrar: true},
                
              }) as CategoriaIndiceDTO)
            };
            return indiceResponse;
          })
        );
      },
      defaultValue: {} as PaginadoResponseDTO<CategoriaIndiceDTO>,
      injector: injector
    });
}
  // ...existing




  public getByIdResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<CategoriaDTO> {
    return rxResource<CategoriaDTO, number | null>({
      params: () => id(),
      stream: (options) => {
        if (options.params === null) {
          return of({} as CategoriaDTO);
        }
        return this.http.get<CategoriaDTO>(this.urlBase + '/' + options.params!);
      },
      defaultValue: {} as CategoriaDTO,
      injector: injector
    });
  }




  public getById(id: number): Observable<CategoriaDTO> {
    return this.http.get<CategoriaDTO>(`${this.urlBase}/${id}`);
  }

  public create(categoriaDescripcion: CategoriaCreacionDTO): Observable<number> {
    // devuelve Observable<number> (id creado)
    console.log("Valor >> ",categoriaDescripcion);
    return this.http.post<number>(this.urlBase, categoriaDescripcion).pipe(
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
            this.postError.set(txt || 'Error desconocido al crear la categoria');
            return throwError(() => err);
          }
        }
        this.postError.set(txt || 'Error desconocido al crear la categoria.');
        return throwError(() => err);
      })
    );
    ;
  }




    public update(id: number, usuario: CategoriaCreacionDTO): Observable<void> {
      return this.http.patch<void>(`${this.urlBase}/${id}`, usuario).pipe(
        tap(() => this.patchError.set(null)),
        catchError((err: HttpErrorResponse) => {
          this.patchError.set(String(err.error ?? 'Error desconocido al editar la categoria.'));
          return throwError(() => err);
        })
      )
    }

  public delete(id: number): Observable<[]> {
    return this.http.delete<[]>(`${this.urlBase}/${id}`).pipe(
      tap(() => this.deleteError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.deleteError.set(String(err.error ?? 'Error desconocido al eliminar categoria.'));
        return throwError(() => err);
      })
    );
  }

}
