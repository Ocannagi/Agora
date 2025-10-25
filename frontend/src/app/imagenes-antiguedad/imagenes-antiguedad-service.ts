import { inject, Injectable, Injector, ResourceRef, signal } from '@angular/core';
import { ImagenAntiguedadCreacionDTO, ImagenAntiguedadDTO } from './modelo/ImagenAntiguedadDTO';
import { IServiceCrudImagenes } from '../compartidos/interfaces/IServiceCrudImagenes';
import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { environment } from '../environments/environment.development';
import { catchError, map, Observable, of, tap, throwError } from 'rxjs';
import { buildQueryParams } from '../compartidos/funciones/queryParams';
import { rxResource } from '@angular/core/rxjs-interop';

@Injectable({
  providedIn: 'root'
})
export class ImagenesAntiguedadService implements IServiceCrudImagenes<ImagenAntiguedadCreacionDTO, ImagenAntiguedadDTO> {
  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/ImagenesAntiguedad';

  readonly postError = signal<string | null>(null);
  readonly deleteError = signal<string | null>(null);


  public create(files: File[], antId: number): Observable<number[]> {
    this.postError.set(null);

    if (!Array.isArray(files) || files.length === 0) {
      this.postError.set('No se recibieron archivos para subir.');
      return throwError(() => new Error('No se recibieron archivos para subir.'));
    }

    const form = new FormData();
    form.append('antId', String(antId));

    files.forEach((file, idx) => {
      if (file instanceof File) {
        form.append('imagenesAntiguedad[]', file, file.name ?? `imagen_${idx}`);
      }
    });

    return this.http.post<number[]>(this.urlBase, form).pipe(
      catchError((err: HttpErrorResponse) => {
        const msg = String(err.error ?? 'Error desconocido al guardar las imágenes.');
        this.postError.set(msg);
        return throwError(() => err);
      })
    );
  }

  public getByDependenciaIdResource(id: () => number | null, injector: Injector = inject(Injector)): ResourceRef<ImagenAntiguedadDTO[]> {
    return rxResource<ImagenAntiguedadDTO[], HttpParams>({
      params: () => buildQueryParams({antId: id?.() ?? ''}),
      stream: (options) => {
        const antId = options.params.get('params[antId]');
        if (antId === null || antId === '') {
          return of([] as ImagenAntiguedadDTO[]);
        }
        return this.http.get<ImagenAntiguedadDTO[]>(this.urlBase, { params: options.params }).pipe(
          map(arrayImg => arrayImg.map((img) => ({
            ...img,
            imaUrl: this.normalizarUrlImagen(img.imaUrl),
          })))
        );
      },
      defaultValue: [] as ImagenAntiguedadDTO[],
      injector: injector
    });
  }

  private normalizarUrlImagen(url: string): string {
    // Ya absoluta
    if (/^https?:\/\//i.test(url)) return url;

    // Limpieza de prefijos './' o '/' redundantes
    const path = url.replace(/^[.\/]+/, '');

    // Construir URL absoluta usando storageURL del entorno
    const base = environment.storageURL;
    if (base && base !== '...') {
      const baseWithSlash = base.endsWith('/') ? base : base + '/';
      return new URL(path, baseWithSlash).toString();
    }

    // Fallback: relativa al origen actual (puede fallar en :4200 si no hay proxy)
    return '/' + path;
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
