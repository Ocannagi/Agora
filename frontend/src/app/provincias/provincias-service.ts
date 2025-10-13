import { inject, Injectable } from '@angular/core';
import { environment } from '../environments/environment.development';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class ProvinciasService {
  private http = inject(HttpClient);
  private urlBase = environment.apiURL + '/Provincias';
  
}
