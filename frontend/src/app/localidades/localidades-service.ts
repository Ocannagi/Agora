import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { rxResource } from '@angular/core/rxjs-interop';
import { environment } from '../environments/environment.development';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { buildQueryPaginado } from '../compartidos/funciones/queryPaginado';
import { IServiceAutocompletar } from '../compartidos/interfaces/IServiceAutocompletar';
import { PaginadoRequestDTO } from '../compartidos/modelo/PaginadoRequestDTO';
import { PaginadoResponseDTO } from '../compartidos/modelo/PaginadoResponseDTO';
import { LocalidadAutocompletarDTO, LocalidadDTO, LocalidadIndiceDTO, LocalidadCreacionDTO, LocalidadMinDTO } from './modelo/localidadDTO';
import { Observable } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { map, of, throwError, tap } from 'rxjs';


@Injectable({
  providedIn: 'root'
})
export class LocalidadesService implements IServiceAutocompletar<LocalidadAutocompletarDTO> {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Localidades';

  readonly postError = signal<string | null>(null);
  readonly patchError = signal<string | null>(null);
  readonly deleteError = signal<string | null>(null);

  public autocompletarResource(
    locDescripcion: () => string | null,
    injector: Injector = inject(Injector),
    provinciaId?: () => number | null,
  ): ResourceRef<LocalidadAutocompletarDTO[]> {
    return rxResource<LocalidadAutocompletarDTO[], HttpParams>({
      params: () => buildQueryParams({
        provId: provinciaId?.() ?? '',
        locDescripcion: locDescripcion() ?? '',
      }),
      stream: (options) => {
        const prov = options.params.get('params[provId]');

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

  public getAllResource(injector: Injector = inject(Injector)): ResourceRef<LocalidadDTO[]> {
    return rxResource<LocalidadDTO[], null>({
      params: () => null,
      stream: (options) => {
        return this.http.get<LocalidadDTO[]>(this.urlBase);
      },
      defaultValue: [],
      injector: injector
    });
  }

 public getAllPaginado(
     paginado: () => PaginadoRequestDTO,
     injector: Injector = inject(Injector)
   ): ResourceRef<PaginadoResponseDTO<LocalidadIndiceDTO>> {
     return rxResource<PaginadoResponseDTO<LocalidadIndiceDTO>, PaginadoRequestDTO>({
       params: () => paginado(),
       stream: (options) => {
         const params = buildQueryPaginado(options.params);
         return this.http.get<PaginadoResponseDTO<LocalidadMinDTO>>(this.urlBase, { params }).pipe(
           map(response => {
             const indiceResponse: PaginadoResponseDTO<LocalidadIndiceDTO> = {
               totalRegistros: response.totalRegistros,
               paginaActual: response.paginaActual,
               registrosPorPagina: response.registrosPorPagina,
               arrayEntidad: response.arrayEntidad.map(loc => ({
                 ...loc,
                 id: loc.locId,
                 nombre: `${loc.provincia.provDescripcion} - ${loc.locDescripcion}`,
                 acciones: { editar: `/localidades/editar/${loc.locId}`, borrar: true},
                 
               }) as LocalidadIndiceDTO)
             };
             return indiceResponse;
           })
         );
       },
       defaultValue: {} as PaginadoResponseDTO<LocalidadIndiceDTO>,
       injector: injector
     });
 }
  // ...existing



  public getByIdAutocompletarResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<LocalidadAutocompletarDTO> {
    return rxResource<LocalidadAutocompletarDTO, number | null>({
      params: () => id(),
      stream: (options) => {
        if (options.params === null) {
          return of({} as LocalidadAutocompletarDTO);
        }
        return this.http.get<LocalidadDTO>(`${this.urlBase}/${options.params}`).pipe(
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
  }


  public getById(id: number): Observable<LocalidadDTO> {
    return this.http.get<LocalidadDTO>(`${this.urlBase}/${id}`);
  }

  public create(localidadCreacionDTO: LocalidadCreacionDTO): Observable<number> {
    // devuelve Observable<number> (id creado)
    return this.http.post<number>(this.urlBase, localidadCreacionDTO).pipe(
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




    public update(id: number, localidadCreacionDTO: LocalidadCreacionDTO): Observable<void> {
      return this.http.patch<void>(`${this.urlBase}/${id}`, localidadCreacionDTO).pipe(
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
