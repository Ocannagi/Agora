import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { environment } from '../environments/environment.development';
import { HttpClient, HttpErrorResponse, HttpParams} from '@angular/common/http';
import { Observable } from 'rxjs/internal/Observable';
import { RetornaId } from '../compartidos/modelo/RetornaId';
import { DomicilioCreacionDTO, DomicilioDTO } from './modelo/domicilioDTO';
import { catchError, throwError, map, of } from 'rxjs';
import { rxResource } from '@angular/core/rxjs-interop';

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

  public getByIdResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<DomicilioDTO> {
    return rxResource<DomicilioDTO, number | null>({
      params: () => id(),
      stream: (options) => {
        if (options.params === null) {
          return of({} as DomicilioDTO);
        }
        return this.http.get<DomicilioDTO>(this.urlBase + '/' + options.params!);
      },
      defaultValue: {} as DomicilioDTO,
      injector: injector
    });
  }
}
