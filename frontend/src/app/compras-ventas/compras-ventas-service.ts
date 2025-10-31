import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { rxResource } from '@angular/core/rxjs-interop';
import { catchError, map, Observable, of, tap, throwError } from 'rxjs';

import { environment } from '../environments/environment.development';
import { IServicePaginado } from '../compartidos/interfaces/IServicePaginado';
import { PaginadoRequestDTO } from '../compartidos/modelo/PaginadoRequestDTO';
import { PaginadoResponseDTO } from '../compartidos/modelo/PaginadoResponseDTO';
import { buildQueryPaginado } from '../compartidos/funciones/queryPaginado';
import { RetornaId } from '../compartidos/modelo/RetornaId';

import {
  CompraIndiceDTO,
  CompraVentaCreacionDTO,
  CompraVentaDTO,

} from './modelo/compraVentaDTO';

@Injectable({
  providedIn: 'root'
})
export class ComprasVentasService implements IServicePaginado<CompraIndiceDTO> {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/ComprasVentas';

  readonly postError = signal<string | null>(null);
  readonly patchError = signal<string | null>(null);
  readonly deleteError = signal<string | null>(null);

  public getAllResource(injector: Injector = inject(Injector)): ResourceRef<CompraVentaDTO[]> {
    return rxResource<CompraVentaDTO[], null>({
      params: () => null,
      stream: () => this.http.get<CompraVentaDTO[]>(this.urlBase),
      defaultValue: [],
      injector
    });
  }

  public getAllPaginado(
    paginado: () => PaginadoRequestDTO,
    injector: Injector = inject(Injector)
  ): ResourceRef<PaginadoResponseDTO<CompraIndiceDTO>> {
    return rxResource<PaginadoResponseDTO<CompraIndiceDTO>, PaginadoRequestDTO>({
      params: () => paginado(),
      stream: (options) => {
        const params = buildQueryPaginado(options.params);
        return this.http.get<PaginadoResponseDTO<CompraVentaDTO>>(this.urlBase, { params }).pipe(
          map(response => {
            const indiceResponse: PaginadoResponseDTO<CompraIndiceDTO> = {
              totalRegistros: response.totalRegistros,
              paginaActual: response.paginaActual,
              registrosPorPagina: response.registrosPorPagina,
              arrayEntidad: response.arrayEntidad.map(c => {
                const nombreComprador = `${c.usuarioComprador.usrNombre ?? ''} ${c.usuarioComprador.usrApellido ?? ''}`.trim();
                const nombre = nombreComprador ? `Compra #${c.covId} · ${nombreComprador}` : `Compra #${c.covId}`;
                // CompraVentaIndiceDTO extiende CompraVentaDTO + IIndiceEntidadDTO
                return {
                  ...c,
                  id: c.covId,
                  nombre,
                  acciones: {
                    ver: `/compras-ventas/${c.covId}` // placeholder de acción
                  }
                } as CompraIndiceDTO;
              })
            };
            return indiceResponse;
          })
        );
      },
      defaultValue: {} as PaginadoResponseDTO<CompraIndiceDTO>,
      injector
    });
  }

  public getByIdResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<CompraVentaDTO> {
    return rxResource<CompraVentaDTO, number | null>({
      params: () => id(),
      stream: (options) => {
        if (options.params === null) {
          return of({} as CompraVentaDTO);
        }
        return this.http.get<CompraVentaDTO>(`${this.urlBase}/${options.params}`);
      },
      defaultValue: {} as CompraVentaDTO,
      injector
    });
  }

  public create(data: CompraVentaCreacionDTO): Observable<number> {
    return this.http.post<RetornaId>(this.urlBase, data).pipe(
      map(res => {
        this.postError.set(null);
        return res.id;
      }),
      catchError((err: HttpErrorResponse) => {
        this.postError.set(String(err.error ?? 'Error desconocido al crear compra/venta.'));
        return throwError(() => err);
      })
    ) as Observable<number>;
  }

  public update(id: number, data: CompraVentaCreacionDTO): Observable<void> {
    return this.http.patch<void>(`${this.urlBase}/${id}`, data).pipe(
      tap(() => this.patchError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.patchError.set(String(err.error ?? 'Error desconocido al editar compra/venta.'));
        return throwError(() => err);
      })
    );
  }

  public delete(id: number): Observable<[]> {
    return this.http.delete<[]>(`${this.urlBase}/${id}`).pipe(
      tap(() => this.deleteError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.deleteError.set(String(err.error ?? 'Error desconocido al eliminar compra/venta.'));
        return throwError(() => err);
      })
    );
  }
}
