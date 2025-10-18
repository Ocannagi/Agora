import { ChangeDetectionStrategy, Component, effect, inject, input, model, output, untracked } from '@angular/core';
import { MatInputModule } from "@angular/material/input";
import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { ReactiveFormsModule } from '@angular/forms';
import { LocalidadesService } from '../localidades-service';
import { AutocompletarRetornaId } from "../../compartidos/componentes/autocompletar-retorna-id/autocompletar-retorna-id";
import { SERVICIO_AUTOCOMPLETAR_TOKEN } from '../../compartidos/proveedores/tokens';
import { AutocompletarStore } from '../../compartidos/componentes/autocompletar-retorna-id/store-autocompletar/autocompletar.store';

@Component({
  selector: 'app-autocompletar-localidades',
  imports: [MatInputModule, MatAutocompleteModule, ReactiveFormsModule, AutocompletarRetornaId],
  templateUrl: './autocompletar-localidades.html',
  styleUrl: './autocompletar-localidades.scss',
  providers: [
    { provide: SERVICIO_AUTOCOMPLETAR_TOKEN, useClass: LocalidadesService },
    AutocompletarStore,
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AutocompletarLocalidades {
  readonly store = inject(AutocompletarStore);
  readonly idProv = input<number | null>(null);
  readonly idLoc = output<number | null>();
  readonly keywordExterno = input<string>('');



  constructor() {
    this.store.setHayDependenciaPadre(true);

    effect(() => {
      const idProv = this.idProv();
      const idLoc = this.store.modelId();

      this.idLoc.emit(idLoc);
      untracked(() => this.store.setIdDependenciaPadre(idProv));
    });

    effect(() => {
      const keywordExterno = this.keywordExterno();
      untracked(() => this.store.setKeywordExterno(keywordExterno));
    });

  }


}
