import { ChangeDetectionStrategy, Component, signal } from '@angular/core';
import { AutocompletarLocalidades } from "../localidades/autocompletar-localidades/autocompletar-localidades";

@Component({
  selector: 'app-landing-page',
  imports: [AutocompletarLocalidades],
  templateUrl: './landing-page.html',
  styleUrl: './landing-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class LandingPage {

  readonly provId = signal<number>(2);
  readonly pruebaSignal = signal<number>(0);

}
