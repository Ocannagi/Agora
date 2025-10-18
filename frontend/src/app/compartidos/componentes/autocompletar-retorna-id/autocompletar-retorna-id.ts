import { afterNextRender, ChangeDetectionStrategy, Component, effect, ElementRef, inject, Injector, input, untracked, viewChild } from '@angular/core';
import { ValidaControlForm } from '../../servicios/valida-control-form';
import { FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { IAutocompletarDTO } from '../../interfaces/IAutocompletarDTO';
import { formControlSignal } from '../../funciones/formToSignal';
import { MostrarErrores } from "../mostrar-errores/mostrar-errores";
import { MatInputModule } from "@angular/material/input";
import { MatAutocomplete, MatAutocompleteModule, MatAutocompleteTrigger } from "@angular/material/autocomplete";
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
  protected control = new FormControl<IAutocompletarDTO | null>(null, [Validators.required]);
  protected store = inject(AutocompletarStore);

  readonly searchBox = viewChild<ElementRef<HTMLInputElement>>('SearchBox');
  readonly trigger = viewChild<MatAutocompleteTrigger>('autoTrigger');
  #injector = inject(Injector);


  constructor() {
    this.store.setFormControlSignal(formControlSignal(this.control));

    afterNextRender(() => {
      effect(() => {
        const hayKeywordExterno = this.store.hayKeywordExterno();
        const dependenciaPadreResuelta = this.store.dependenciaPadreResuelta();
        const inputElem = this.searchBox();

        if (hayKeywordExterno && dependenciaPadreResuelta) {
          if (inputElem) {
            inputElem.nativeElement.value = this.store.keywordExterno();
            inputElem.nativeElement.dispatchEvent(new Event('input', { bubbles: true }));
            this.trigger()?.openPanel();
          }
        }
      }, { injector: this.#injector });
    });

    effect(() => {
      const hayKeywordExterno = this.store.hayKeywordExterno();
      const dependenciaPadreResuelta = this.store.dependenciaPadreResuelta();
      const resourceAll = this.store.resourceAll;
      const resourceAllStatusResolved = this.store.resourceAllStatusResolved();

      if (hayKeywordExterno && dependenciaPadreResuelta) {
        if (resourceAllStatusResolved) {
          const opciones = resourceAll.value();
          if (opciones.length !== 1) return;

          const unico = opciones[0];
          untracked(() => {
            this.control.setValue(unico);
            this.store.setModelId(unico.id);
            this.trigger()?.closePanel();
            this.store.resetKeywordExterno();
          });
        }
      }

    }, { injector: this.#injector });


  }


  protected obtenerErrorCampo(campo: string): string | null {
    return this.validaForm.obtenerErrorControl(this.control, campo);
  }

  protected displayText = (loc: T | null): string =>
    loc ? loc.descripcion : '';


}
