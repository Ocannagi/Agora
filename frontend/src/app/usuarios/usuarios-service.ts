import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { environment } from '../environments/environment.development';
import { UsuarioCreacionDTO, UsuarioDTO } from './modelo/usuarioDTO';
import { catchError, map, Observable, of, tap, throwError } from 'rxjs';
import { RetornaId } from '../compartidos/modelo/RetornaId';
import { rxResource } from '@angular/core/rxjs-interop';

@Injectable({
  providedIn: 'root'
})
export class UsuariosService {
  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Usuarios';

  readonly postError = signal<string | null>(null);
  readonly patchError = signal<string | null>(null);

  public create(usuario: UsuarioCreacionDTO): Observable<number> {
    return this.http.post<RetornaId>(this.urlBase, usuario).pipe(
      map(response => { 
        this.postError.set(null);
        return response.id; }),
      catchError((err: HttpErrorResponse) => {
        this.postError.set(String(err.error ?? 'Error desconocido al crear usuario.'));
        return throwError(() => err);
      })
    ) as Observable<number>;
  }

  public getByIdResource(id : () => number | null, injector: Injector = inject(Injector)) : ResourceRef<UsuarioDTO> {
    return rxResource<UsuarioDTO, number | null>({
      stream: () => {
        if(id() === null){
          return of({} as UsuarioDTO);
        }
        return this.http.get<UsuarioDTO>(this.urlBase + '/' + id());
      },
      defaultValue: {} as UsuarioDTO,
      injector: injector
    });
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

}