import { ChangeDetectionStrategy, Component, effect, inject, input, output, untracked } from '@angular/core';
import { AutocompletarRetornaId } from "../../compartidos/componentes/autocompletar-retorna-id/autocompletar-retorna-id";
import { AutocompletarStore } from '../../compartidos/componentes/autocompletar-retorna-id/store-autocompletar/autocompletar.store';
import { CategoriasService } from '../categorias-service';
import { SERVICIO_AUTOCOMPLETAR_TOKEN } from '../../compartidos/proveedores/tokens';

@Component({
  selector: 'app-autocompletar-categorias',
  imports: [AutocompletarRetornaId],
  templateUrl: './autocompletar-categorias.html',
  styleUrl: './autocompletar-categorias.scss',
  providers: [
        { provide: SERVICIO_AUTOCOMPLETAR_TOKEN, useClass: CategoriasService },
        AutocompletarStore,
      ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AutocompletarCategorias {
  readonly store = inject(AutocompletarStore);
  readonly idCat = output<number | null>();
  readonly keywordExterno = input<string>('');
  readonly disabled = input<boolean>(false);

  constructor() {
    effect(() => {
      const idModel = this.store.modelId();
      console.log('AutocompletarCategorias - idModel:', idModel);
      this.idCat.emit(idModel);
    });

    effect(() => {
      const keywordExterno = this.keywordExterno();
      untracked(() => this.store.setKeywordExterno(keywordExterno));
    });
  }

}
