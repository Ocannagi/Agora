import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { VentaDetalleDTO, VentaDetalleIndiceDTO } from './modelo/ventaDetalleDTO';
import { IServicePaginado } from '../compartidos/interfaces/IServicePaginado';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { environment } from '../environments/environment.development';
import { PaginadoRequestDTO } from '../compartidos/modelo/PaginadoRequestDTO';
import { PaginadoResponseDTO } from '../compartidos/modelo/PaginadoResponseDTO';
import { rxResource } from '@angular/core/rxjs-interop';
import { buildQueryPaginado } from '../compartidos/funciones/queryPaginado';
import { map } from 'rxjs/internal/operators/map';
import { normalizarUrlImagen } from '../compartidos/funciones/normalizarUrlImagen';
import { formatFechaDDMMYYYY } from '../compartidos/funciones/formatFecha';
import { catchError, Observable, of, tap, throwError } from 'rxjs';
import { TituloExtraSeparador } from '../compartidos/modelo/IIndiceEntidadDTO';

@Injectable({
  providedIn: 'root'
})
export class VentasDetalleService implements IServicePaginado<VentaDetalleIndiceDTO> {
  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/VentasDetalle';

  readonly postError = signal<string | null>(null);
  readonly patchError = signal<string | null>(null);
  readonly deleteError = signal<string | null>(null);

  // Métodos de la interfaz IServicePaginado
  public getAllPaginado(
    paginado: () => PaginadoRequestDTO,
    injector: Injector = inject(Injector)
  ): ResourceRef<PaginadoResponseDTO<VentaDetalleIndiceDTO>> {
    return rxResource<PaginadoResponseDTO<VentaDetalleIndiceDTO>, PaginadoRequestDTO>({
      params: () => paginado(),
      stream: (options) => {
        const params = buildQueryPaginado(options.params);
        return this.http.get<PaginadoResponseDTO<VentaDetalleDTO>>(this.urlBase, { params }).pipe(
          map(response => {
            const indiceResponse: PaginadoResponseDTO<VentaDetalleIndiceDTO> = {
              totalRegistros: response.totalRegistros,
              paginaActual: response.paginaActual,
              registrosPorPagina: response.registrosPorPagina,
              arrayEntidad: response.arrayEntidad.map(v => {
                const vdNorm: VentaDetalleDTO = {
                  ...v,
                  antiguedadAlaVenta: {
                    ...v.antiguedadAlaVenta,
                    antiguedad: {
                      ...v.antiguedadAlaVenta.antiguedad,
                      imagenes: (v.antiguedadAlaVenta.antiguedad.imagenes ?? []).map(img => ({
                        ...img,
                        imaUrl: img.imaUrl ? normalizarUrlImagen(img.imaUrl) : img.imaUrl
                      }))
                    }
                  }
                };

                // Armar item de índice con datos normalizados
                const fechaVenta = formatFechaDDMMYYYY(vdNorm.covFechaCompra);
                const cvdFechaEntregaPrevista = formatFechaDDMMYYYY(vdNorm.cvdFechaEntregaPrevista);
                const cvdFechaEntregaReal = vdNorm.cvdFechaEntregaReal
                  ? formatFechaDDMMYYYY(vdNorm.cvdFechaEntregaReal)
                  : null;
                return {
                  ...vdNorm,
                  cvdFechaEntregaPrevista,
                  cvdFechaEntregaReal,
                  id: vdNorm.cvdId,
                  nombre: `Fecha Venta: ${fechaVenta} · ${vdNorm.antiguedadAlaVenta.antiguedad.antNombre}`,
                  extra: vdNorm.antiguedadAlaVenta.vendedor.usrRazonSocialFantasia ? `Vendedor${TituloExtraSeparador}${vdNorm.antiguedadAlaVenta.vendedor.usrRazonSocialFantasia}` : `Vendedor${TituloExtraSeparador}${vdNorm.antiguedadAlaVenta.vendedor.usrNombre} ${vdNorm.antiguedadAlaVenta.vendedor.usrApellido}`,
                  acciones: {
                    ver: `/ventas/${vdNorm.cvdId}`
                  }
                } as VentaDetalleIndiceDTO;
              })
            };
            return indiceResponse;
          })
        );
      },
      defaultValue: {} as PaginadoResponseDTO<VentaDetalleIndiceDTO>,
      injector
    });
  }

  public getByIdResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<VentaDetalleDTO | null> {
    return rxResource<VentaDetalleDTO | null, number | null>({
      params: () => id(),
      stream: (options) => {
        if (options.params === null) {
          return of(null);
        }
        return this.http.get<VentaDetalleDTO>(`${this.urlBase}/${options.params}`).pipe(
          map((vd) => {

            const fechaVenta = formatFechaDDMMYYYY(vd.covFechaCompra);
            const cvdFechaEntregaPrevista = formatFechaDDMMYYYY(vd.cvdFechaEntregaPrevista);
            const cvdFechaEntregaReal = vd.cvdFechaEntregaReal
              ? formatFechaDDMMYYYY(vd.cvdFechaEntregaReal)
              : null;

            return {
              ...vd,
              cvdFechaEntregaPrevista,
              cvdFechaEntregaReal,
              covFechaCompra: fechaVenta,
              antiguedadAlaVenta: {
                ...vd.antiguedadAlaVenta,
                antiguedad: {
                  ...vd.antiguedadAlaVenta.antiguedad,
                  imagenes: (vd.antiguedadAlaVenta.antiguedad.imagenes ?? []).map(img => ({
                    ...img,
                    imaUrl: img.imaUrl ? normalizarUrlImagen(img.imaUrl) : img.imaUrl
                  }))
                }
              }
            }
          }))
      },
      defaultValue: null,
      injector
    });
  }

  public getAllResource(injector: Injector = inject(Injector)): ResourceRef<VentaDetalleDTO[]> {
    return rxResource<VentaDetalleDTO[], null>({
      params: () => null,
      stream: () => this.http.get<VentaDetalleDTO[]>(this.urlBase),
      defaultValue: [],
      injector
    });
  }

  //No son necesarios, se crean para poder usar la interfaz completa
  
  public create(data: VentaDetalleDTO): Observable<number> {
    throw new Error('Not implemented');
  }

  public update(id: number, data: Partial<VentaDetalleDTO>): Observable<void> {
    return this.http.patch<void>(`${this.urlBase}/${id}`, data).pipe(
      tap(() => this.patchError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.patchError.set(String(err.error ?? 'Error desconocido al editar compra/venta.'));
        return throwError(() => err);
      })
    );
  }

  public delete(id: number): Observable<[]> {
    throw new Error('Not implemented');
  }

}
