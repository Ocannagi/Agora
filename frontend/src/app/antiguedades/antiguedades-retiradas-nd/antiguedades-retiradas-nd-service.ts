import { inject, Injectable, Injector, ResourceRef } from '@angular/core';
import { AntiguedadesService } from '../antiguedades-service';
import { AntiguedadCreacionDTO, AntiguedadDTO, AntiguedadIndiceDTO } from '../modelo/AntiguedadDTO';
import { rxResource } from '@angular/core/rxjs-interop';
import { buildQueryParams } from '../../compartidos/funciones/queryParams';
import { PaginadoRequestDTO } from '../../compartidos/modelo/PaginadoRequestDTO';
import { PaginadoResponseDTO } from '../../compartidos/modelo/PaginadoResponseDTO';
import { buildQueryPaginadoExtra } from '../../compartidos/funciones/queryPaginado';
import { map } from 'rxjs/internal/operators/map';
import { Observable } from 'rxjs/internal/Observable';

@Injectable({
  providedIn: 'root'
})
export class AntiguedadesRetiradasNdService extends AntiguedadesService {
  constructor() {
    super();
  }

  override getAllResource(injector: Injector = inject(Injector)): ResourceRef<AntiguedadDTO[]> {
    return rxResource<AntiguedadDTO[], { antTipoEstado: 'RN' }>({
      params: () => ({ antTipoEstado: 'RN' }),
      stream: (options) => {
        const params = buildQueryParams(options.params);
        return this.http.get<AntiguedadDTO[]>(this.urlBase, { params });
      },
      defaultValue: [],
      injector: injector
    });
  }

  override getAllPaginado(paginado: () => PaginadoRequestDTO, injector: Injector = inject(Injector)): ResourceRef<PaginadoResponseDTO<AntiguedadIndiceDTO>> {
    return rxResource<PaginadoResponseDTO<AntiguedadIndiceDTO>, PaginadoRequestDTO>({
      params: () => paginado(),
      stream: (options) => {
        const params = buildQueryPaginadoExtra({ ...options.params }, { antTipoEstado: 'RN' });
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
                  editar: `/antiguedadesRetiradasNd/${antiguedad.antId}`,
                  borrar: false,
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

  override  getByUsrIdHabilitadoVtaResource(usrId: () => number | null, injector: Injector = inject(Injector)): ResourceRef<AntiguedadDTO[]> {
    throw new Error('Method not implemented.');
  }

  override create(data: AntiguedadCreacionDTO): Observable<number> {
    throw new Error('Method not implemented.');
  }

/*   override update(id: number, antiguedad: AntiguedadCreacionDTO): Observable<void> {
    throw new Error('Method not implemented.');
  } */

  override delete(id: number): Observable<[]> {
    throw new Error('Method not implemented.');
  }


}
