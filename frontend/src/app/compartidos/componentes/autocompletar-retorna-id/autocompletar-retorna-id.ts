import { afterNextRender, ChangeDetectionStrategy, Component, effect, ElementRef, inject, Injector, input, signal, untracked, viewChild } from '@angular/core';
import { ValidaControlForm } from '../../servicios/valida-control-form';
import { FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { IAutocompletarDTO } from '../../modelo/IAutocompletarDTO';
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
  readonly disabled = input<boolean>(false);

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
        const usuarioNoInteractuo = !this.store.usuarioInteractuo();

        if (hayKeywordExterno && dependenciaPadreResuelta && usuarioNoInteractuo) {
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
      const usuarioNoInteractuo = !this.store.usuarioInteractuo();

      if (hayKeywordExterno && dependenciaPadreResuelta && usuarioNoInteractuo) {
        if (resourceAllStatusResolved) {
          const opciones = resourceAll.value();

          if (opciones.length === 0) {
            return;
          }
          
          let unico : IAutocompletarDTO;
          if (opciones.length !== 1){
            unico = opciones.find(o => o.descripcion.toLowerCase() === this.store.keywordExterno().toLowerCase())!;
          }
          else{
            unico = opciones[0];
          }

          untracked(() => {
            this.control.setValue(unico);
            this.store.setModelId(unico.id);
            this.trigger()?.closePanel();
            this.store.resetKeywordExterno();
          });
        }
      }

    }, { injector: this.#injector });

    effect(() => {
      if (this.disabled()) {
        this.control.disable();
      } else {
        this.control.enable();
      }
    });

    effect(() => {
      console.log('usuario interactuo:', this.store.usuarioInteractuo());
    });

  }


  protected obtenerErrorCampo(campo: string): string | null {
    return this.validaForm.obtenerErrorControl(this.control, campo);
  }

  protected displayText = (loc: T | null): string =>
    loc ? loc.descripcion : '';

  protected reenlazarInput(value: IAutocompletarDTO | null): void {
    if (value) {
      this.store.setUsuarioInteractuo(true);
      // Seteamos el control y el modelo sin disparar eventos extra
      this.control.setValue(value);
      this.store.setModelId(value.id);
      this.store.resetKeyword();      // dejamos el keyword limpio
      this.trigger()?.closePanel();
    } else {
      this.control.setValue(null);
      this.store.setModelId(null);
      this.store.resetKeyword();
    }
  }

  protected onBlur(): void {
    // Evita resetear mientras el panel está abierto o si el control ya es válido
    if (this.trigger()?.panelOpen) return;
    if (this.control.valid) return;

    if (this.store.hayRegistros() && this.store.statusNoValido()) {
      this.store.resetKeyword();
      // Si necesitás, podés limpiar el modelId aquí también:
      // this.store.setModelId(null);
    }
  }
}
