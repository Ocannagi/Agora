import { ChangeDetectionStrategy, Component, computed, DestroyRef, inject, Injector, input, signal } from '@angular/core';
import { numberAttributeOrNull } from '../../compartidos/funciones/transform';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ValidaControlForm } from '../../compartidos/servicios/valida-control-form';
import { Router, RouterLink } from '@angular/router';
import { AutenticacionStore } from '../../seguridad/store/autenticacion.store';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { Cargando } from "../../compartidos/componentes/cargando/cargando";
import { formControlSignal } from '../../compartidos/funciones/formToSignal';
import { PeriodosService } from '../../periodos/periodos-service';
import { CategoriasService } from '../../categorias/categorias-service';
import { AntiguedadesService } from '../antiguedades-service';
import { ImagenesAntiguedadService } from '../../imagenes-antiguedad/imagenes-antiguedad-service';
import { HttpErrorResponse } from '@angular/common/http';
import { AutocompletarPeriodos } from "../../periodos/autocompletar-periodos/autocompletar-periodos";
import { AutocompletarCategorias } from "../../categorias/autocompletar-categorias/autocompletar-categorias";
import { AutocompletarSubcategorias } from "../../subcategorias/autocompletar-subcategorias/autocompletar-subcategorias";
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatButtonModule } from '@angular/material/button';

@Component({
  selector: 'app-crear-editar-antiguedad',
  standalone: true,
  imports: [MostrarErrores, Cargando, AutocompletarPeriodos, AutocompletarCategorias, AutocompletarSubcategorias, MatButtonModule, RouterLink, MatFormFieldModule, ReactiveFormsModule, MatInputModule],
  templateUrl: './crear-editar-antiguedad.component.html',
  styleUrl: './crear-editar-antiguedad.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CrearEditarAntiguedadComponent {
  
  //INPUTS
  readonly id = input(null, { transform: numberAttributeOrNull });

  //COMPUTED
  readonly esEdicion = computed(() => this.id() !== null && this.id() !== undefined);
  readonly esNuevo = computed(() => !this.esEdicion());
  readonly titulo = computed(() => this.esEdicion() ? 'Editar Antigüedad' : 'Registrar nueva Antigüedad');

  readonly esCargando = computed(() => {
    if (this.esEdicion()) {
      const antiguedadRes = this.antiguedadByIdResource;
      if (antiguedadRes?.isLoading())
        return true;
    }

    return false;
  });

  readonly errores = computed(() => {
    const lista: string[] = [];

    if (this.#antService.postError() !== null)
      lista.push(this.#antService.postError()!);
    if (this.esEdicion() && this.antiguedadByIdResource?.status() === 'error') {
      const wrapped = this.antiguedadByIdResource?.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }
    if (this.esEdicion() && this.#antService.patchError() !== null) {
      lista.push(this.#antService.patchError()!);
    }

    return lista;

  });

  readonly tieneErrores = computed(() => this.errores().length > 0);

  //SERVICES & INYECCIONES
  #fb = inject(FormBuilder);
  #vcf = inject(ValidaControlForm);
  #destroyRef = inject(DestroyRef);
  #injector = inject(Injector);
  #router = inject(Router);
  #authStore = inject(AutenticacionStore);
  #antService = inject(AntiguedadesService);
  #imgService = inject(ImagenesAntiguedadService);


  //FORMULARIO
  protected antDescripcion = this.#fb.control<string>('', { validators: [Validators.required, Validators.minLength(1), Validators.maxLength(500)], updateOn: 'change' })
  readonly antDescripcionFormControlSignal = formControlSignal(this.antDescripcion, this.#injector);

  //SIGNALS


  readonly perId = signal<number | null>(null);
  readonly scatId = signal<number | null>(null);
  readonly usrId = signal<number | null>(this.#authStore.usrId());
  readonly imagenes = signal<File[]>([]);

  readonly maxCaracteresDescripcion = signal(500);
  readonly periodoEditDescripcion = signal<string>('');
  readonly categoriaEditDescripcion = signal<string>('');
  readonly subcategoriaEditDescripcion = signal<string>('');
  
  readonly isAllValid = computed(() => {
    return this.antDescripcionFormControlSignal.status() === 'VALID'
        && this.antDescripcionFormControlSignal.value() !== null && this.antDescripcionFormControlSignal.value()!.trim().length > 0
        && this.perId() !== null
        && this.scatId() !== null
        && this.usrId() !== null
        && this.imagenes().length > 0;
  });

  //RESOURCES

  protected antiguedadByIdResource = this.#antService.getByIdResource(this.id, this.#injector);


  constructor() {


  }

  // MÉTODOS Y EVENTOS

  obtenerErrorAntDescripcion(): string | null {
    return this.#vcf.obtenerErrorControl(this.antDescripcion, 'descripción de la antigüedad', true);
  }


}