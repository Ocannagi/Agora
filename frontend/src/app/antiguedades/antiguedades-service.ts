import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { IServicePaginado } from '../compartidos/interfaces/IServicePaginado';
import { AntiguedadCreacionDTO, AntiguedadDTO, AntiguedadIndiceDTO } from './modelo/AntiguedadDTO';
import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { environment } from '../environments/environment.development';
import { rxResource } from '@angular/core/rxjs-interop';
import { PaginadoResponseDTO } from '../compartidos/modelo/PaginadoResponseDTO';
import { PaginadoRequestDTO } from '../compartidos/modelo/PaginadoRequestDTO';
import { map } from 'rxjs/internal/operators/map';
import { buildQueryPaginado } from '../compartidos/funciones/queryPaginado';
import { catchError, Observable, of, tap, throwError } from 'rxjs';
import { RetornaId } from '../compartidos/modelo/RetornaId';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { normalizarUrlImagen } from '../compartidos/funciones/normalizarUrlImagen';

@Injectable({
  providedIn: 'root'
})
export class AntiguedadesService implements IServicePaginado<AntiguedadIndiceDTO> {
  protected http = inject(HttpClient);
  protected urlBase = environment.apiURL + '/Antiguedades';

  readonly postError = signal<string | null>(null);
  readonly patchError = signal<string | null>(null);
  readonly deleteError = signal<string | null>(null);

  public getAllResource(injector: Injector = inject(Injector)): ResourceRef<AntiguedadDTO[]> {
    return rxResource<AntiguedadDTO[], null>({
      params: () => null,
      stream: (options) => {
        return this.http.get<AntiguedadDTO[]>(this.urlBase);
      },
      defaultValue: [],
      injector: injector
    });
  }

  public getAllPaginado(paginado: () => PaginadoRequestDTO, injector: Injector = inject(Injector)): ResourceRef<PaginadoResponseDTO<AntiguedadIndiceDTO>> {
    return rxResource<PaginadoResponseDTO<AntiguedadIndiceDTO>, PaginadoRequestDTO>({
      params: () => paginado(),
      stream: (options) => {
        const params = buildQueryPaginado(options.params);
        return this.http.get<PaginadoResponseDTO<AntiguedadDTO>>(this.urlBase, { params }).pipe(
          map(response => {
            const indiceResponse: PaginadoResponseDTO<AntiguedadIndiceDTO> = {
              totalRegistros: response.totalRegistros,
              paginaActual: response.paginaActual,
              registrosPorPagina: response.registrosPorPagina,
              arrayEntidad: response.arrayEntidad.map(antiguedad => ({
                ...antiguedad,
                id: antiguedad.antId,
                nombre: `${antiguedad.antNombre} - ${antiguedad.antDescripcion.substring(0, 30)}${antiguedad.antDescripcion.length > 30 ? '...' : ''}`,
                acciones: {
                  editar: `/antiguedades/editar/${antiguedad.antId}`,
                  borrar: true,
                }
              } as AntiguedadIndiceDTO))
            };
            return indiceResponse;
          }),
        );
      },
      defaultValue: {} as PaginadoResponseDTO<AntiguedadIndiceDTO>,
      injector: injector
    });
  }

  public getByIdResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<AntiguedadDTO> {
    return rxResource<AntiguedadDTO, number | null>({
      params: () => id(),
      stream: (options) => {
        if (options.params === null) {
          return of({} as AntiguedadDTO);
        }
        return this.http.get<AntiguedadDTO>(this.urlBase + '/' + options.params!);
      },
      defaultValue: {} as AntiguedadDTO,
      injector: injector
    });
  }

  public getByUsrIdHabilitadoVtaResource(usrId: () => number | null, injector: Injector = inject(Injector)): ResourceRef<AntiguedadDTO[]> {
    return rxResource<AntiguedadDTO[], HttpParams>({
      params: () => {
        return buildQueryParams({ usrId: usrId?.() ?? '',
                                  arrayAntTipoEstado: ['RD','TD','TI']
         });
      },
      stream: ({ params }) => {
        const usr = params.get('params[usrId]');
        if (!usr) {
          return of<AntiguedadDTO[]>([]);
        }

        return this.http.get<AntiguedadDTO[]>(this.urlBase, { params }).pipe(
          map((antiguedades) =>
            antiguedades.map(ant => ({
              ...ant,
              imagenes: ant.imagenes?.map(img => ({
                ...img,
                imaUrl: normalizarUrlImagen(img.imaUrl),
              })),
            }))
          )
        );
      },
      defaultValue: [],
      injector
    });
  }

  public create(data: AntiguedadCreacionDTO): Observable<number> {
    return this.http.post<RetornaId>(this.urlBase, data).pipe(
      map(response => {
        this.postError.set(null);
        return response.id;
      }),
      catchError((err: HttpErrorResponse) => {
        this.postError.set(String(err.error ?? 'Error desconocido al crear antigüedad.'));
        return throwError(() => err);
      })
    ) as Observable<number>;
  }

  public update(id: number, antiguedad: AntiguedadCreacionDTO): Observable<void> {
    return this.http.patch<void>(`${this.urlBase}/${id}`, antiguedad).pipe(
      tap(() => this.patchError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.patchError.set(String(err.error ?? 'Error desconocido al editar antigüedad.'));
        return throwError(() => err);
      })
    )
  }

  public delete(id: number): Observable<[]> {
    return this.http.delete<[]>(`${this.urlBase}/${id}`).pipe(
      tap(() => this.deleteError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.deleteError.set(String(err.error ?? 'Error desconocido al eliminar la antigüedad.'));
        return throwError(() => err);
      })
    );
  }




}
