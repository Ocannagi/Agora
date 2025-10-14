import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { inject, Injectable, signal } from '@angular/core';
import { environment } from '../environments/environment.development';
import { UsuarioCreacionDTO } from './modelo/usuarioDTO';
import { catchError, map, Observable, throwError } from 'rxjs';
import { RetornaId } from '../compartidos/modelo/RetornaId';

@Injectable({
  providedIn: 'root'
})
export class UsuariosService {
  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Usuarios';

  readonly postError = signal<string | null>(null);

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
}
