import { NgOptimizedImage } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject, input } from '@angular/core';
import { AutenticacionStore } from '../../../seguridad/store/autenticacion.store';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';

@Component({
  selector: 'app-cargando',
  imports: [NgOptimizedImage, RouterLink, MatButtonModule],
  templateUrl: './cargando.html',
  styleUrl: './cargando.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  host: {
    // Overlay de pantalla completa
    '[class.cargando-overlay]': 'fullscreen()',
    role: 'status',
    'aria-busy': 'true',
    'aria-live': 'polite'
  }
})
export class Cargando {
  // Permite alternar entre overlay full-screen (default) o inline si alguna vez lo necesitás
  readonly fullscreen = input(true);
  readonly authStore = inject(AutenticacionStore);
}
