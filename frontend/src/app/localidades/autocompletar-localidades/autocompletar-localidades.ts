import { ChangeDetectionStrategy, Component, computed, effect, ElementRef, inject, Injector, input, model, output, signal, viewChild } from '@angular/core';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { MatInputModule } from "@angular/material/input";
import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { ValidaForm } from '../../compartidos/servicios/valida-form';
import { LocalidadesService } from '../localidades-service';
import { LocalidadDTO } from '../modelo/localidadDTO';
import { formControlSignal } from '../../compartidos/funciones/formToSignal';

@Component({
  selector: 'app-autocompletar-localidades',
  imports: [MostrarErrores, MatInputModule, MatAutocompleteModule, ReactiveFormsModule],
  templateUrl: './autocompletar-localidades.html',
  styleUrl: './autocompletar-localidades.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AutocompletarLocalidades {

  readonly provinciaId = input<number>(0);
  readonly keyword = model<string>('');
  readonly modelId = model.required<number>();
  readonly errores = signal<string[]>([]);




  private validaForm = inject(ValidaForm);
  private service = inject(LocalidadesService);
  private injector = inject(Injector);

  protected localidadControl = new FormControl<string>('', [Validators.required]);
  readonly localidadControlSignal = formControlSignal(this.localidadControl);


  readonly localidadesResource = this.service.autocompletarLocalidadesResource(
    () => this.provinciaId(),
    () => this.keyword(),
    this.injector
  );

  readonly hayError = computed(() => this.errores().length > 0);
  readonly hayQueResetear = computed(() => (this.localidadControlSignal.status() !== 'VALID' ||
    this.localidadesResource.value().length === 0) && this.modelId() !== 0);
  readonly hayResourceError = computed(() => this.localidadesResource.status() === 'error');

  protected obtenerErrorCampo(campo: string): string | null {
    return this.validaForm.obtenerErrorControl(this.localidadControl, campo);
  }

  protected displayLocalidad = (loc: LocalidadDTO | null): string =>
    loc ? loc.locDescripcion : '';


  constructor() {

    effect(() => {
      console.log('Me ejecuto');

      if (this.hayQueResetear()){
        this.keyword.set('');
        this.modelId.set(0);
      }
        

      if (this.hayResourceError())
        this.errores.update(errs => [...errs, this.localidadesResource.error()!.message])
      else
        this.hayError() ? this.errores.set([]) : '';
    });
  }

}
