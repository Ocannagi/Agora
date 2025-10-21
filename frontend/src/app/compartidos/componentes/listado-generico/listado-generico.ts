import { ChangeDetectionStrategy, Component, input } from '@angular/core';
import { Cargando } from "../cargando/cargando";

@Component({
  selector: 'app-listado-generico',
  imports: [Cargando],
  templateUrl: './listado-generico.html',
  styleUrl: './listado-generico.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ListadoGenerico {
  readonly listado = input.required<any>();
}
