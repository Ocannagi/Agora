import { ChangeDetectionStrategy, Component, input } from '@angular/core';

@Component({
  selector: 'app-autorizado',
  imports: [],
  templateUrl: './autorizado.html',
  styleUrl: './autorizado.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class Autorizado {
  public esAutorizado = input.required<boolean>();
}
