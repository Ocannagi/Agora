import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { environment } from '../environments/environment.development';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { AntiguedadALaVentaCreacionDTO, AntiguedadALaVentaDTO, AntiguedadALaVentaIndiceDTO } from './modelo/AntiguedadAlaVentaDTO';
import { rxResource } from '@angular/core/rxjs-interop';
import { PaginadoRequestDTO, PaginadoRequestSearchDTO } from '../compartidos/modelo/PaginadoRequestDTO';
import { PaginadoResponseDTO } from '../compartidos/modelo/PaginadoResponseDTO';
import { buildQueryPaginado, buildQueryPaginadoSearch } from '../compartidos/funciones/queryPaginado';
import { catchError, map, Observable, of, tap, throwError } from 'rxjs';
import { RetornaId } from '../compartidos/modelo/RetornaId';
import { IServicePaginado } from '../compartidos/interfaces/IServicePaginado';
import { formatFechaDDMMYYYY } from '../compartidos/funciones/formatFechaDDMMYYYY';
import { normalizarUrlImagen } from '../compartidos/funciones/normalizarUrlImagen';

@Injectable({
  providedIn: 'root'
})
export class AntiguedadesVentaService implements IServicePaginado<AntiguedadALaVentaIndiceDTO> {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/AntiguedadesAlaVenta';

  readonly postError = signal<string | null>(null);
  readonly patchError = signal<string | null>(null);
  readonly deleteError = signal<string | null>(null);

  public getAllResource(injector: Injector = inject(Injector)): ResourceRef<AntiguedadALaVentaDTO[]> {
    return rxResource<AntiguedadALaVentaDTO[], null>({
      params: () => null,
      stream: (options) => {
        return this.http.get<AntiguedadALaVentaDTO[]>(this.urlBase);
      },
      defaultValue: [],
      injector: injector
    });
  }

  public getAllPaginado(paginado: () => PaginadoRequestDTO, injector: Injector = inject(Injector)): ResourceRef<PaginadoResponseDTO<AntiguedadALaVentaIndiceDTO>> {
    return rxResource<PaginadoResponseDTO<AntiguedadALaVentaIndiceDTO>, PaginadoRequestDTO>({
      params: () => paginado(),
      stream: (options) => {
        const params = buildQueryPaginado(options.params);
        return this.http.get<PaginadoResponseDTO<AntiguedadALaVentaDTO>>(this.urlBase, { params }).pipe(
          map(response => {
            const indiceResponse: PaginadoResponseDTO<AntiguedadALaVentaIndiceDTO> = {
              totalRegistros: response.totalRegistros,
              paginaActual: response.paginaActual,
              registrosPorPagina: response.registrosPorPagina,
              arrayEntidad: response.arrayEntidad.map(antiguedadVenta => ({
                ...antiguedadVenta,
                aavFechaPublicacion : formatFechaDDMMYYYY(antiguedadVenta.aavFechaPublicacion),
                id: antiguedadVenta.aavId,
                nombre: antiguedadVenta.antiguedad.antDescripcion,
                acciones: {
                  editar: `/antiguedadesAlaVenta/editar/${antiguedadVenta.aavId}`
                }
              } as AntiguedadALaVentaIndiceDTO))
            };
            return indiceResponse;
          }),
        );
      },
      defaultValue: {} as PaginadoResponseDTO<AntiguedadALaVentaIndiceDTO>,
      injector: injector
    });
  }

  public getPaginadoSearch(paginado: () => PaginadoRequestSearchDTO, injector: Injector = inject(Injector)): ResourceRef<PaginadoResponseDTO<AntiguedadALaVentaDTO>> {
    return rxResource<PaginadoResponseDTO<AntiguedadALaVentaDTO>, PaginadoRequestSearchDTO>({
      params: () => paginado(),
      stream: (options) => {
        const params = buildQueryPaginadoSearch(options.params);
        return this.http.get<PaginadoResponseDTO<AntiguedadALaVentaDTO>>(this.urlBase, { params }).pipe(
          map(response => {
            const indiceResponse: PaginadoResponseDTO<AntiguedadALaVentaDTO> = {
              totalRegistros: response.totalRegistros,
              paginaActual: response.paginaActual,
              registrosPorPagina: response.registrosPorPagina,
              arrayEntidad: response.arrayEntidad.map(antiguedadVenta => ({
                ...antiguedadVenta,
                aavFechaPublicacion : formatFechaDDMMYYYY(antiguedadVenta.aavFechaPublicacion),
              } as AntiguedadALaVentaDTO))
            };
            return indiceResponse;
          }),
        );
      },
      defaultValue: {} as PaginadoResponseDTO<AntiguedadALaVentaIndiceDTO>,
      injector: injector
    });
  }

  public getByIdResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<AntiguedadALaVentaDTO> {
    return rxResource<AntiguedadALaVentaDTO, number | null>({
      params: () => id(),
      stream: (options) => {
        if (options.params === null) {
          return of({} as AntiguedadALaVentaDTO);
        }
        return this.http.get<AntiguedadALaVentaDTO>(this.urlBase + '/' + options.params!).pipe(
          map(antiguedadVenta => ({
            ...antiguedadVenta,
            aavFechaPublicacion : formatFechaDDMMYYYY(antiguedadVenta.aavFechaPublicacion),
            antiguedad: {
              ...antiguedadVenta.antiguedad,
              imagenes: antiguedadVenta.antiguedad.imagenes?.map(img => ({
                              ...img,
                              imaUrl: normalizarUrlImagen(img.imaUrl),
                            })) 
            }
          } as AntiguedadALaVentaDTO))
        );
      },
      defaultValue: {} as AntiguedadALaVentaDTO,
      injector: injector
    });
  }

  public create(data: AntiguedadALaVentaCreacionDTO): Observable<number> {
    return this.http.post<RetornaId>(this.urlBase, data).pipe(
      map(response => {
        this.postError.set(null);
        return response.id;
      }),
      catchError((err: HttpErrorResponse) => {
        this.postError.set(String(err.error ?? 'Error desconocido al crear antigüedad a la venta.'));
        return throwError(() => err);
      })
    ) as Observable<number>;
  }

  public update(id: number, antiguedadAlaVenta: AntiguedadALaVentaCreacionDTO): Observable<void> {
    return this.http.patch<void>(`${this.urlBase}/${id}`, antiguedadAlaVenta).pipe(
      tap(() => this.patchError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.patchError.set(String(err.error ?? 'Error desconocido al editar antigüedad a la venta.'));
        return throwError(() => err);
      })
    )
  }

  public delete(id: number): Observable<[]> {
    return this.http.delete<[]>(`${this.urlBase}/${id}`).pipe(
      tap(() => this.deleteError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.deleteError.set(String(err.error ?? 'Error desconocido al eliminar la antigüedad a la venta.'));
        return throwError(() => err);
      })
    );
  }
  
}
