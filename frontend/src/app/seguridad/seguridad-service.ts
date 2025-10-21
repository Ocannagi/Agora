import { HttpClient, HttpResponse } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../environments/environment.development';
import { tap } from 'rxjs/internal/operators/tap';
import { Observable } from 'rxjs';
import { CredencialesUsuarioDTO, KeysClaimDTO, RespuestaAutenticacionDTO } from './modelo/seguridadDTO';

@Injectable({
  providedIn: 'root'
})
export class SeguridadService {

  private http: HttpClient = inject(HttpClient);
  private urlBase: string = environment.apiURL;
  public readonly keyToken: string = 'jwt';

  public login(credenciales: CredencialesUsuarioDTO): Observable<HttpResponse<RespuestaAutenticacionDTO>> {
    return this.http.post<RespuestaAutenticacionDTO>(`${this.urlBase}/Login`, credenciales, { observe: 'response' })
      .pipe(
        tap(rtaAutenticacion => rtaAutenticacion.body ? this.saveToken(rtaAutenticacion.body) : '')
      );
  }

  public saveToken(rtaAutenticacion: RespuestaAutenticacionDTO): void {
    localStorage.setItem(this.keyToken, rtaAutenticacion.jwt);
  }

  public getFieldJWT(field: KeysClaimDTO): string | number | null {
    const token = localStorage.getItem(this.keyToken);
    if (!token)
      return null;

    let dataToken = JSON.parse(atob(token.split('.')[1]))
    return dataToken[field];
  }

  public logout(): Observable<HttpResponse<RespuestaAutenticacionDTO>> {
    return this.http.delete<RespuestaAutenticacionDTO>(`${this.urlBase}/Login`, { observe: 'response' });
  }

}
