import { inject, Injectable, signal } from '@angular/core';
import { environment } from '../environments/environment.development';
import { HttpClient, HttpErrorResponse, HttpResponse } from '@angular/common/http';
import { Observable } from 'rxjs/internal/Observable';
import { RetornaId } from '../compartidos/modelo/RetornaId';
import { DomicilioCreacionDTO } from './modelo/domicilioDTO';
import { catchError, throwError, tap, map, of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class DomiciliosService {
  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Domicilios';

  readonly postError = signal<string | null>(null);

  public create(domicilio: DomicilioCreacionDTO): Observable<number> {
    return this.http.post<RetornaId>(this.urlBase, domicilio).pipe(
      map((response) => {
        this.postError.set(null);
        return response.id;
      }),
      catchError((err: HttpErrorResponse) => {
        const txt = String(err.error ?? '');
        if (err.status === 409) {
          const m = txt.match(/ID_(\d+)/);
          if (m) {
            return of(Number(m[1])); //Convierte en Observable el número y lo lanza como si fuera un next
          } else {
            this.postError.set(txt || 'Error desconocido al crear domicilio.');
            return throwError(() => err);
          }
        }
        this.postError.set(txt || 'Error desconocido al crear domicilio.');
        return throwError(() => err);
      })
    );
  }
}
