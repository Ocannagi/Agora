import { ChangeDetectionStrategy, Component, effect, inject, output, untracked } from '@angular/core';
import { AutocompletarRetornaId } from "../../compartidos/componentes/autocompletar-retorna-id/autocompletar-retorna-id";
import { AutocompletarStore } from '../../compartidos/componentes/autocompletar-retorna-id/store-autocompletar/autocompletar.store';
import { SERVICIO_AUTOCOMPLETAR_TOKEN } from '../../compartidos/proveedores/tokens';
import { ProvinciasService } from '../provincias-service';

@Component({
  selector: 'app-autocompletar-provincias',
  imports: [AutocompletarRetornaId],
  templateUrl: './autocompletar-provincias.html',
  styleUrl: './autocompletar-provincias.scss',
  providers: [
    { provide: SERVICIO_AUTOCOMPLETAR_TOKEN, useClass: ProvinciasService },
    AutocompletarStore,
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AutocompletarProvincias {
  readonly store = inject(AutocompletarStore);
  readonly idProv = output<number | null>();

  constructor() {
    effect(() => {
      const idModel = this.store.modelId();

      this.idProv.emit(idModel);
    });
  }

}
