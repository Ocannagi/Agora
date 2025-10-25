import { NgOptimizedImage } from '@angular/common';
import { ChangeDetectionStrategy, Component } from '@angular/core';

@Component({
  selector: 'app-cargando',
  imports: [NgOptimizedImage],
  templateUrl: './cargando.html',
  styleUrl: './cargando.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class Cargando {

}
