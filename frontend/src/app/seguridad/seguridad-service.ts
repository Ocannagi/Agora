import { HttpClient, HttpResponse } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../environments/environment.development';
import { tap } from 'rxjs/internal/operators/tap';
import { Observable } from 'rxjs';
import { CredencialesUsuarioDTO, RespuestaAutenticacionDTO } from './seguridadDTO';

@Injectable({
  providedIn: 'root'
})
export class SeguridadService {

  private http: HttpClient = inject(HttpClient);
  private urlBase: string = environment.apiURL;
  private readonly keyToken: string = 'jwt';

  public login(credenciales: CredencialesUsuarioDTO): Observable<HttpResponse<RespuestaAutenticacionDTO>> {
    return this.http.post<RespuestaAutenticacionDTO>(`${this.urlBase}/Login`, credenciales, { observe: 'response' })
      .pipe(
        tap(rtaAutenticacion => rtaAutenticacion.body ? this.saveToken(rtaAutenticacion.body) : '')
      );
  }

  private saveToken(rtaAutenticacion: RespuestaAutenticacionDTO): void {
    localStorage.setItem(this.keyToken, rtaAutenticacion.jwt);
  }

}
