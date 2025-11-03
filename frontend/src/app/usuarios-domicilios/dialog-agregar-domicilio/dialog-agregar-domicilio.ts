import { ChangeDetectionStrategy, Component, computed, DestroyRef, inject, Injector, signal } from '@angular/core';
import { AutenticacionStore } from '../../seguridad/store/autenticacion.store';
import { MatDialogActions, MatDialogContent, MatDialogRef, MatDialogTitle } from '@angular/material/dialog';
import { FormBuilder, FormControl, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { SwalDirective } from '@sweetalert2/ngx-sweetalert2';
import { ValidaControlForm } from '../../compartidos/servicios/valida-control-form';
import { DomiciliosService } from '../../domicilios/domicilios-service';
import { formControlSignal } from '../../compartidos/funciones/formToSignal';
import { AutocompletarProvincias } from "../../provincias/autocompletar-provincias/autocompletar-provincias";
import { AutocompletarLocalidades } from "../../localidades/autocompletar-localidades/autocompletar-localidades";
import { DomicilioCreacionDTO } from '../../domicilios/modelo/domicilioDTO';
import { switchMap, take } from 'rxjs';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { UsuariosDomiciliosService } from '../usuario-domicilios-service';

@Component({
  selector: 'app-dialog-agregar-domicilio',
  imports: [MatFormFieldModule,
    MatInputModule,
    FormsModule,
    MatButtonModule,
    MatDialogTitle,
    MatDialogContent,
    MatDialogActions,
    SwalDirective,
    AutocompletarProvincias,
    AutocompletarLocalidades,
    ReactiveFormsModule, MostrarErrores],
  templateUrl: './dialog-agregar-domicilio.html',
  styleUrl: './dialog-agregar-domicilio.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class DialogAgregarDomicilio {

  readonly authStore = inject(AutenticacionStore);
  #dialogRef = inject(MatDialogRef<DialogAgregarDomicilio>);
  readonly provinciaEditDescripcion = signal<string>('');
  readonly localidadEditDescripcion = signal<string>('');

  //SERVICES & INYECCIONES
  #fb = inject(FormBuilder);
  #vcf = inject(ValidaControlForm);
  #destroyRef = inject(DestroyRef);
  #injector = inject(Injector);
  #domService = inject(DomiciliosService);
  #authStore = inject(AutenticacionStore);
  #usrDomService = inject(UsuariosDomiciliosService);

  //FORMULARIOS

  protected formDomicilio = this.#fb.group({
    domCPA: ['', { validators: [Validators.required, Validators.pattern(/^[A-Z]\d{4}[A-Z]{3}$/i)], updateOn: 'change' }],
    domCalleRuta: ['', { validators: [Validators.required, Validators.maxLength(50)], updateOn: 'change' }],
    domNroKm: this.#fb.control<number | null>(0, { validators: [Validators.required, Validators.min(0), Validators.max(12000), this.#vcf.entero()], updateOn: 'change' }),
    domPiso: ['', { validators: [Validators.maxLength(10), Validators.pattern(/^[a-zA-Z0-9ñÑ]+$/)], updateOn: 'change' }],
    domDepto: ['', { validators: [Validators.maxLength(10), Validators.pattern(/^[a-zA-Z0-9ñÑ]+$/)], updateOn: 'change' }],
    locId: this.#fb.control<number | null>(null, { validators: [Validators.required, Validators.min(1)], updateOn: 'change' }),
    provId: this.#fb.control<number | null>(null, { validators: [Validators.required, Validators.min(1)], updateOn: 'change' }),
  });


  //FORM SIGNALS

  readonly ctrlProvinciaSignal = formControlSignal(this.formDomicilio.get('provId') as FormControl<number | null>);
  readonly ctrlLocalidadSignal = formControlSignal(this.formDomicilio.get('locId') as FormControl<number | null>);

  //COMPUTED
  readonly errors = computed(() => {
    const errores: string[] = [];
    const postError = this.#domService.postError();
    const postErrorUD = this.#usrDomService.postError();

    if (postError) {
      errores.push(postError);
    }
    if (postErrorUD) {
      errores.push(postErrorUD);
    }

    return errores;
  });



  constructor() {
    this.formDomicilio.get('domCPA')?.valueChanges.pipe(
      takeUntilDestroyed(this.#destroyRef)
    ).subscribe({
      next: (value) => {
        if (value) {
          this.formDomicilio.get('domCPA')?.setValue(value.toUpperCase(), { emitEvent: false });
        }
      }
    });

  }


  protected onConfirmSwal() {
    if (this.formDomicilio.invalid) {
      this.formDomicilio.markAllAsTouched();
      return;
    }

    const domCreacion = this.formDomicilio.value as DomicilioCreacionDTO;
    this.#domService.create(domCreacion).pipe(
      switchMap((domId: number) => {
        const usrId = this.#authStore.usrId();
        if (!usrId) {
          const msg = 'Usuario no autenticado';
          this.#usrDomService.postError.set(msg);
          throw new Error(msg);
        }

        return this.#usrDomService.create({
          udomDom: domId,
          udomUsr: usrId
        })
      }),
      takeUntilDestroyed(this.#destroyRef)
    ).subscribe({
      next: () => {
        this.#dialogRef.close(true);
      },
      error: (err) => {
        console.error('Error al crear domicilio:', err);
      }
    });


  }

  protected Cancelar() {
    this.#dialogRef.close(false);
  }

  obtenerErrorCampo(arg0: string, msgExtra?: string): string | null {
    return this.#vcf.obtenerErrorCampoGroup(this.formDomicilio.controls, arg0, true) + (msgExtra ? ' ' + msgExtra : '');
  }

  OnSubmit($event: Event) {
    $event.preventDefault();
  }

}
