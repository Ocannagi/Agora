import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { AutocompletarStore } from '../compartidos/componentes/autocompletar-retorna-id/store-autocompletar/autocompletar.store';
import { AutocompletarLocalidades } from "../localidades/autocompletar-localidades/autocompletar-localidades";


@Component({
  selector: 'app-landing-page',
  imports: [AutocompletarLocalidades],
  templateUrl: './landing-page.html',
  styleUrl: './landing-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class LandingPage {

  readonly provId1 = signal<number | null>(2);
  readonly provId2 = signal<number | null>(1);

  readonly pruebaSignal1 = signal<number | null>(null);
  readonly pruebaSignal2 = signal<number | null>(null);

  /**
   *
   */
  constructor() {

  }

}
