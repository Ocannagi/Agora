import { ChangeDetectionStrategy, Component, input } from '@angular/core';

@Component({
  selector: 'app-mostrar-errores',
  imports: [],
  templateUrl: './mostrar-errores.html',
  styleUrl: './mostrar-errores.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class MostrarErrores {
  readonly errores = input<string[]>();
}
