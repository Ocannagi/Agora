import { inject, Injectable, signal } from '@angular/core';
import { ImagenAntiguedadCreacionDTO, ImagenAntiguedadDTO } from './modelo/ImagenAntiguedadDTO';
import { IServiceCrudImagenes } from '../compartidos/interfaces/IServiceCrudImagenes';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { environment } from '../environments/environment.development';
import { catchError, Observable, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ImagenesAntiguedadService implements IServiceCrudImagenes<ImagenAntiguedadDTO, ImagenAntiguedadCreacionDTO> {
  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/ImagenesAntiguedad';

  readonly postError = signal<string | null>(null);


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
      catchError((err : HttpErrorResponse)  => {
        const msg = String(err.error ?? 'Error desconocido al guardar las imágenes.');
        this.postError.set(msg);
        return throwError(() => err);
      })
    );
  }
}
