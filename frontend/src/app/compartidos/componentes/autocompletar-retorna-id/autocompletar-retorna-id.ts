import { ChangeDetectionStrategy, Component, effect, inject, input, untracked } from '@angular/core';
import { ValidaControlForm } from '../../servicios/valida-control-form';
import { FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { IAutocompletarDTO } from '../../interfaces/IAutocompletarDTO';
import { formControlSignal } from '../../funciones/formToSignal';
import { MostrarErrores } from "../mostrar-errores/mostrar-errores";
import { MatInputModule } from "@angular/material/input";
import { MatAutocompleteModule } from "@angular/material/autocomplete";
import { AutocompletarStore } from './store-autocompletar/autocompletar.store';

@Component({
  selector: 'app-autocompletar-retorna-id',
  imports: [MostrarErrores, MatInputModule, MatAutocompleteModule, ReactiveFormsModule],
  templateUrl: './autocompletar-retorna-id.html',
  styleUrl: './autocompletar-retorna-id.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AutocompletarRetornaId<T extends IAutocompletarDTO> {

  readonly label = input.required<string>();
  readonly placeholder = input.required<string>();
  readonly selectedId = input<number | null>(null);
  
  private validaForm = inject(ValidaControlForm);
  protected control = new FormControl<IAutocompletarDTO | null>(null, [Validators.required]);
  protected store = inject(AutocompletarStore);




  constructor() {
    this.store.setFormControlSignal(formControlSignal(this.control));

    effect(() => {
      const selectedId = this.selectedId();
      console.log('AutocompletarRetornaId - selectedId cambiado:', selectedId);
      untracked(() => this.store.setSelectedId(selectedId));
    });
  }


  protected obtenerErrorCampo(campo: string): string | null {
    return this.validaForm.obtenerErrorControl(this.control, campo);
  }

  protected displayText = (loc: T | null): string =>
    loc ? loc.descripcion : '';


}
