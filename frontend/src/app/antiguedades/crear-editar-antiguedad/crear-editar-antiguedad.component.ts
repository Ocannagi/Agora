import { ChangeDetectionStrategy, Component, computed, DestroyRef, inject, Injector, input } from '@angular/core';
import { numberAttributeOrNull } from '../../compartidos/funciones/transform';
import { FormBuilder, Validators } from '@angular/forms';
import { ValidaControlForm } from '../../compartidos/servicios/valida-control-form';
import { Router } from '@angular/router';
import { AutenticacionStore } from '../../seguridad/store/autenticacion.store';

@Component({
  selector: 'app-crear-editar-antiguedad',
  standalone: true,
  imports: [],
  templateUrl: './crear-editar-antiguedad.component.html',
  styleUrl: './crear-editar-antiguedad.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CrearEditarAntiguedadComponent {
  
  //INPUTS
  readonly id = input(null, { transform: numberAttributeOrNull });

  //COMPUTED & SIGNALS
  readonly esEdicion = computed(() => this.id() !== null && this.id() !== undefined);
  readonly esNuevo = computed(() => !this.esEdicion());
  readonly titulo = computed(() => this.esEdicion() ? 'Editar Antigüedad' : 'Registrar nuevo Antigüedad');

  //SERVICES & INYECCIONES
  #fb = inject(FormBuilder);
  #vcf = inject(ValidaControlForm);
  #destroyRef = inject(DestroyRef);
  #injector = inject(Injector);
  #router = inject(Router);
  #authStore = inject(AutenticacionStore);

  //FORMULARIOS

  protected formImgAntiguedad = this.#fb.group({
    // Definir controles del formulario aquí
  });
  
  protected formAntiguedad = this.#fb.group({
    perId: this.#fb.control<number | null>(null, { validators: [Validators.required, Validators.min(1)], updateOn: 'change' }),
    scatId: this.#fb.control<number | null>(null, { validators: [Validators.required, Validators.min(1)], updateOn: 'change' }),
    antDescripcion: this.#fb.control<string>('', { validators: [Validators.required, Validators.minLength(1), Validators.maxLength(500)], updateOn: 'change' }),
    usrId: this.#fb.control<number | null>(this.#authStore.usrId(), { validators: [Validators.required, Validators.min(1)], updateOn: 'change' }),
    imagenes: this.formImgAntiguedad
  });



}
