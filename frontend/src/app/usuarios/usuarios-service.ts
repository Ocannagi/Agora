import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { environment } from '../environments/environment.development';
import { UsuarioCreacionDTO, UsuarioDTO, UsuarioIndiceDTO, UsuarioMinDTO } from './modelo/usuarioDTO';
import { catchError, map, Observable, of, tap, throwError } from 'rxjs';
import { RetornaId } from '../compartidos/modelo/RetornaId';
import { rxResource } from '@angular/core/rxjs-interop';
import { PaginadoRequestDTO } from '../compartidos/modelo/PaginadoRequestDTO';
import { PaginadoResponseDTO } from '../compartidos/modelo/PaginadoResponseDTO';
import { buildQueryPaginado } from '../compartidos/funciones/queryPaginado';
import { IServicePaginado } from '../compartidos/interfaces/IServicePaginado';

@Injectable({
  providedIn: 'root'
})
export class UsuariosService implements IServicePaginado<UsuarioIndiceDTO> {
  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Usuarios';

  readonly postError = signal<string | null>(null);
  readonly patchError = signal<string | null>(null);
  readonly deleteError = signal<string | null>(null);


  public getAllResource(injector: Injector = inject(Injector)): ResourceRef<UsuarioDTO[]> {
    return rxResource<UsuarioDTO[], null>({
      params: () => null,
      stream: (options) => {
        return this.http.get<UsuarioDTO[]>(this.urlBase);
      },
      defaultValue: [],
      injector: injector
    });
  }

  public getAllPaginado(paginado: () => PaginadoRequestDTO, injector: Injector = inject(Injector)): ResourceRef<PaginadoResponseDTO<UsuarioIndiceDTO>> {
    return rxResource<PaginadoResponseDTO<UsuarioIndiceDTO>, PaginadoRequestDTO>({
      params: () => paginado(),
      stream: (options) => {
        const params = buildQueryPaginado(options.params);
        return this.http.get<PaginadoResponseDTO<UsuarioMinDTO>>(this.urlBase, { params }).pipe(
          map(response => {
            const indiceResponse: PaginadoResponseDTO<UsuarioIndiceDTO> = {
              totalRegistros: response.totalRegistros,
              paginaActual: response.paginaActual,
              registrosPorPagina: response.registrosPorPagina,
              arrayEntidad: response.arrayEntidad.map(usuario => ({
                ...usuario,
                id: usuario.usrId,
                nombre: `${usuario.usrNombre} ${usuario.usrApellido}`,
                acciones: {
                  editar: `/usuarios/editar/${usuario.usrId}`},
                  borrar: true,
              }) as UsuarioIndiceDTO)
            };
            return indiceResponse;
          }),
        )
      },
      defaultValue: {} as PaginadoResponseDTO<UsuarioIndiceDTO>,
      injector: injector
    });
  }

  public getByIdResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<UsuarioDTO> {
    return rxResource<UsuarioDTO, number | null>({
      params: () => id(),
      stream: (options) => {
        if (options.params === null) {
          return of({} as UsuarioDTO);
        }
        return this.http.get<UsuarioDTO>(this.urlBase + '/' + options.params!);
      },
      defaultValue: {} as UsuarioDTO,
      injector: injector
    });
  }

  public create(data: UsuarioCreacionDTO): Observable<number> {
    return this.http.post<RetornaId>(this.urlBase, data).pipe(
      map(response => {
        this.postError.set(null);
        return response.id;
      }),
      catchError((err: HttpErrorResponse) => {
        this.postError.set(String(err.error ?? 'Error desconocido al crear usuario.'));
        return throwError(() => err);
      })
    ) as Observable<number>;
  }



  public update(id: number, usuario: UsuarioCreacionDTO): Observable<void> {
    return this.http.patch<void>(`${this.urlBase}/${id}`, usuario).pipe(
      tap(() => this.patchError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.patchError.set(String(err.error ?? 'Error desconocido al editar usuario.'));
        return throwError(() => err);
      })
    )
  }

  public delete(id: number): Observable<[]> {
    return this.http.delete<[]>(`${this.urlBase}/${id}`).pipe(
      tap(() => this.deleteError.set(null)),
      catchError((err: HttpErrorResponse) => {
        this.deleteError.set(String(err.error ?? 'Error desconocido al eliminar usuario.'));
        return throwError(() => err);
      })
    );
  }

}