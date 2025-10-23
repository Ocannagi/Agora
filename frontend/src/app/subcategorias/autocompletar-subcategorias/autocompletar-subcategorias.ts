import { ChangeDetectionStrategy, Component, effect, inject, input, output, untracked } from '@angular/core';
import { AutocompletarRetornaId } from "../../compartidos/componentes/autocompletar-retorna-id/autocompletar-retorna-id";
import { SubcategoriasService } from '../subcategorias-service';
import { AutocompletarStore } from '../../compartidos/componentes/autocompletar-retorna-id/store-autocompletar/autocompletar.store';
import { SERVICIO_AUTOCOMPLETAR_TOKEN } from '../../compartidos/proveedores/tokens';

@Component({
  selector: 'app-autocompletar-subcategorias',
  imports: [AutocompletarRetornaId],
  templateUrl: './autocompletar-subcategorias.html',
  styleUrl: './autocompletar-subcategorias.scss',
  providers: [
      { provide: SERVICIO_AUTOCOMPLETAR_TOKEN, useClass: SubcategoriasService },
      AutocompletarStore,
    ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AutocompletarSubcategorias {
  readonly store = inject(AutocompletarStore);
  readonly idCat = input<number | null>(null);
  readonly idScat = output<number | null>();
  readonly keywordExterno = input<string>('');



  constructor() {
    this.store.setHayDependenciaPadre(true);

    effect(() => {
      const idCat = this.idCat();
      const idScat = this.store.modelId();

      this.idScat.emit(idScat);
      untracked(() => this.store.setIdDependenciaPadre(idCat));
    });

    effect(() => {
      const keywordExterno = this.keywordExterno();
      untracked(() => this.store.setKeywordExterno(keywordExterno));
    });

  }

}
