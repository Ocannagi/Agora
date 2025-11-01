import { NgOptimizedImage } from '@angular/common';
import { ChangeDetectionStrategy, Component, input } from '@angular/core';

@Component({
  selector: 'app-cargando',
  imports: [NgOptimizedImage],
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
}
