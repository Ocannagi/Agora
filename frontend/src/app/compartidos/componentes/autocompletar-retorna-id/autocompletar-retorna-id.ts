import { ChangeDetectionStrategy, Component, inject, input } from '@angular/core';
import { ValidaControlForm } from '../../servicios/valida-control-form';
import { FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { IAutocompletarDTO } from '../../interfaces/IAutocompletarDTO';
import { STORE_AUTOCOMPLETAR_TOKEN } from '../../proveedores/tokens';
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
  
  private validaForm = inject(ValidaControlForm);
  protected control = new FormControl<string>('', [Validators.required]);
  protected store = inject(AutocompletarStore);


  constructor() {
    this.store.setFormControlSignal(formControlSignal(this.control));
    
  }


  protected obtenerErrorCampo(campo: string): string | null {
    return this.validaForm.obtenerErrorControl(this.control, campo);
  }

  protected displayText = (loc: T | null): string =>
    loc ? loc.descripcion : '';


}
