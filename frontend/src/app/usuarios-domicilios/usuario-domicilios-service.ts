import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { environment } from '../environments/environment.development';
import { UsuarioDomiciliosCreacionDTO, UsuarioDomiciliosDTO } from './modelo/UsuarioDomiciliosDTO';
import { rxResource } from '@angular/core/rxjs-interop';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { catchError, map, Observable, of, throwError } from 'rxjs';
import { RetornaId } from '../compartidos/modelo/RetornaId';

@Injectable({
  providedIn: 'root'
})
export class UsuariosDomiciliosService {

  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/UsuariosDomicilios';

  readonly postError = signal<string | null>(null);

  public getByUsrIdResource(usrId: () => number | null, injector: Injector = inject(Injector)): ResourceRef<UsuarioDomiciliosDTO | null> {
    return rxResource<UsuarioDomiciliosDTO | null, HttpParams>({
      params: () => {
        return buildQueryParams({ usrId: usrId?.() ?? '' });
      },
      stream: ({ params }) => {
        const usr = params.get('params[usrId]');
        if (!usr) {
          return of<UsuarioDomiciliosDTO | null>(null);
        }
        return this.http.get<UsuarioDomiciliosDTO>(this.urlBase, { params })
      },
      defaultValue: null,
      injector
    });
  }

  public create(data: UsuarioDomiciliosCreacionDTO): Observable<number> {
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
  
}
