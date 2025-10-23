import { ChangeDetectionStrategy, Component, effect, inject, input, output, untracked } from '@angular/core';
import { AutocompletarRetornaId } from "../../compartidos/componentes/autocompletar-retorna-id/autocompletar-retorna-id";
import { PeriodosService } from '../periodos-service';
import { SERVICIO_AUTOCOMPLETAR_TOKEN } from '../../compartidos/proveedores/tokens';
import { AutocompletarStore } from '../../compartidos/componentes/autocompletar-retorna-id/store-autocompletar/autocompletar.store';

@Component({
  selector: 'app-autocompletar-periodos',
  imports: [AutocompletarRetornaId],
  templateUrl: './autocompletar-periodos.html',
  styleUrl: './autocompletar-periodos.scss',
  providers: [
      { provide: SERVICIO_AUTOCOMPLETAR_TOKEN, useClass: PeriodosService },
      AutocompletarStore,
    ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AutocompletarPeriodos {
  readonly store = inject(AutocompletarStore);
  readonly idPer = output<number | null>();
  readonly keywordExterno = input<string>('');

  constructor() {
    effect(() => {
      const idModel = this.store.modelId();

      this.idPer.emit(idModel);
    });

    effect(() => {
      const keywordExterno = this.keywordExterno();
      untracked(() => this.store.setKeywordExterno(keywordExterno));
    });

  }

}
