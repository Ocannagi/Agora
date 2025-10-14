import { ChangeDetectionStrategy, Component, computed, DestroyRef, effect, inject, Injector, input, numberAttribute, signal, untracked } from '@angular/core';
import { FormBuilder, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { ValidaControlForm } from '../../compartidos/servicios/valida-control-form';
import { TipoUsuarioDTO } from '../../seguridad/seguridadDTO';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { Router, RouterLink } from '@angular/router';
import { UsuarioCreacionDTO, UsuarioDTO } from '../modelo/usuarioDTO';
import { Cargando } from "../../compartidos/componentes/cargando/cargando";
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { formControlSignal } from '../../compartidos/funciones/formToSignal';
import { MatSelectModule } from '@angular/material/select';
import { TiposUsuarioService } from '../../seguridad/tipos-usuario-service';
import { HttpErrorResponse } from '@angular/common/http';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { AutocompletarLocalidades } from "../../localidades/autocompletar-localidades/autocompletar-localidades";
import { AutocompletarProvincias } from "../../provincias/autocompletar-provincias/autocompletar-provincias";
import { TextFieldModule } from '@angular/cdk/text-field';
import { DomicilioCreacionDTO } from '../../domicilios/modelo/domicilioDTO';
import { DomiciliosService } from '../../domicilios/domicilios-service';
import { UsuariosService } from '../usuarios-service';
import { switchMap } from 'rxjs/operators';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';

@Component({
  selector: 'app-crear-editar-usuario',
  imports: [MatButtonModule, RouterLink, MatFormFieldModule, ReactiveFormsModule, MatInputModule, Cargando, MostrarErrores, MatSelectModule, MatTooltipModule, MatDatepickerModule, AutocompletarLocalidades, AutocompletarProvincias, TextFieldModule],
  templateUrl: './crear-editar-usuario.html',
  styleUrl: './crear-editar-usuario.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CrearEditarUsuario {

  //INPUTS
  readonly id = input(null, { transform: numberAttribute, alias: 'idPath' });

  protected user?: UsuarioDTO;

  //SERVICES
  #fb = inject(FormBuilder);
  #vcf = inject(ValidaControlForm);
  #destroyRef = inject(DestroyRef);
  #domService = inject(DomiciliosService);
  #usrService = inject(UsuariosService);
  #router = inject(Router);

  protected tipoUsuarioResource = inject(TiposUsuarioService).tiposUsuarioResource();

  
  //COMPUTED & SIGNALS
  readonly esEdicion = computed(() => Number.isInteger(this.id()));
  readonly titulo = computed(() => this.esEdicion() ? 'Editar Perfil' : 'Registrar nuevo Usuario');
  readonly esCargando = computed(() => this.tipoUsuarioResource === null || this.tipoUsuarioResource.isLoading());
  readonly hayErrores = computed(() => this.tipoUsuarioResource === null ||
    this.tipoUsuarioResource.status() === 'error' || this.#domService.postError() !== null
    || this.#usrService.postError() !== null
  );

  readonly errores = computed(() => {
    if (this.hayErrores()) {
      const lista: string[] = [];
      if (this.tipoUsuarioResource?.status() === 'error')
        lista.push((this.tipoUsuarioResource.error() as HttpErrorResponse)?.error as string ?? 'Error desconocido');
      if (this.#domService.postError() !== null)
        lista.push(this.#domService.postError()!);
      if (this.#usrService.postError() !== null)
        lista.push(this.#usrService.postError()!);
      return lista;
    } else
      return [];
  });

  protected readonly maxCaracteresDescripcion = signal(500);



  //FORMULARIOS

  protected formDomicilio = this.#fb.group({
    domCPA: ['', { validators: [Validators.required, Validators.pattern(/^[A-Z]\d{4}[A-Z]{3}$/)], updateOn: 'change' }],
    domCalleRuta: ['', { validators: [Validators.required, Validators.maxLength(50)], updateOn: 'change' }],
    domNroKm: this.#fb.control<number | null>(0, { validators: [Validators.required, Validators.min(0), Validators.max(12000), this.#vcf.entero()], updateOn: 'change' }),
    domPiso: ['', { validators: [Validators.maxLength(10), Validators.pattern(/^[a-zA-Z0-9ñÑ]+$/)], updateOn: 'change' }],
    domDepto: ['', { validators: [Validators.maxLength(10), Validators.pattern(/^[a-zA-Z0-9ñÑ]+$/)], updateOn: 'change' }],
    locId: this.#fb.control<number | null>(null, { validators: [Validators.required, Validators.min(1)], updateOn: 'change' }),
    provId: this.#fb.control<number | null>(null, { validators: [Validators.required, Validators.min(1)], updateOn: 'change' }),
  });

  protected formUsuario = this.#fb.group({
    usrApellido: ['', { validators: [Validators.required, Validators.maxLength(50), this.#vcf.cadaPrimeraLetraMayuscula(), this.#vcf.apellidoNombreValido()], updateOn: 'change' }],
    usrNombre: ['', { validators: [Validators.required, Validators.maxLength(50), this.#vcf.cadaPrimeraLetraMayuscula(), this.#vcf.apellidoNombreValido()], updateOn: 'change' }],
    usrDni: ['', { validators: [Validators.required, Validators.pattern(/^\d{8}$/)], updateOn: 'change' }],
    usrCuitCuil: ['', { validators: [this.#vcf.cuitCuilValido()], updateOn: 'change' }],
    usrRazonSocialFantasia: ['', { validators: [Validators.maxLength(100)], updateOn: 'change' }],
    usrTipoUsuario: this.#fb.control<TipoUsuarioDTO | null>(null, { validators: [Validators.required], updateOn: 'change' }),
    usrMatricula: ['', { validators: [Validators.maxLength(20)], updateOn: 'change' }],
    usrEmail: ['', { validators: [Validators.required, Validators.email, Validators.minLength(6), Validators.maxLength(100)], updateOn: 'change' }],
    usrFechaNacimiento: this.#fb.control<Date | null>(null, { validators: [Validators.required, this.#vcf.fechaNoPuedeSerFutura()], updateOn: 'change' }),
    usrDescripcion: ['', { validators: [Validators.maxLength(this.maxCaracteresDescripcion())], updateOn: 'change' }],
    usrPassword: ['', { validators: [Validators.required, Validators.minLength(8), Validators.maxLength(25), Validators.pattern(/^(?=.*?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ])(?=.*?[a-zñäëïöüáéíóúâêîôûàèìòù])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/)], updateOn: 'change' }],
    domicilio: this.formDomicilio,
  }/*, { validators: [this.#vgf.razonSocialRequiredIfCuitValid] }*/);


  // CONTROLES TO SIGNAL Y VALIDACIONES ESPECIALES
  readonly ctrlTipoUsuario = formControlSignal(this.formUsuario.get('usrTipoUsuario') as FormControl<TipoUsuarioDTO | null>);
  readonly ctrlProvinciaSignal = formControlSignal(this.formUsuario.get('domicilio')?.get('provId') as FormControl<number | null>);
  readonly ctrlLocalidadSignal = formControlSignal(this.formUsuario.get('domicilio')?.get('locId') as FormControl<number | null>);

  readonly requiereMatricula = computed(() => {
    const tipo = this.ctrlTipoUsuario.value();
    if (!tipo) return false;
    return tipo.ttuRequiereMatricula;
  });
  readonly ctrlMatriculaSignal = formControlSignal(this.formUsuario.get('usrMatricula') as FormControl<string>);

  readonly ctrlCuitCuilSignal = formControlSignal(this.formUsuario.get('usrCuitCuil') as FormControl<string>);
  readonly requiereRazonSocial = computed(() => !!this.ctrlCuitCuilSignal.value());

  protected msgPassword = [
    'Debe tener al menos 8 caracteres y máximo 25.',
    'Debe tener al menos una mayúscula.',
    'Debe tener al menos una minúscula.',
    'Debe tener al menos un número.',
    'Debe tener al menos un carácter especial #?!@$%^&*- .'
  ];

  get passwordTooltip(): string {
    return this.msgPassword.map(m => `• ${m}`).join('\r\n');
  }



  constructor() {
    this.#vcf.requeridoIfEffect(this.requiereMatricula, this.formUsuario.get('usrMatricula')! as FormControl);
    this.#vcf.requeridoIfEffect(this.requiereRazonSocial, this.formUsuario.get('usrRazonSocialFantasia')! as FormControl);

    effect(() => {
      const reqMatricula = this.requiereMatricula();
      if (!reqMatricula && (this.ctrlMatriculaSignal.value() ?? '') !== '') {
        untracked(() => this.ctrlMatriculaSignal.value.set(''));
      }
      console.log('status matricula', this.ctrlMatriculaSignal.status());
    });

    this.#destroyRef.onDestroy(() => {
      this.#domService.postError.set(null);
      this.#usrService.postError.set(null);
    });

  }

  // MÉTODOS Y EVENTOS

  obtenerErrorCampo(arg0: string, esDomicilio: boolean = false, msgExtra?: string): string | null {

    if (esDomicilio) {
      return this.#vcf.obtenerErrorCampoGroup(this.formDomicilio.controls, arg0, true) + (msgExtra ? ' ' + msgExtra : '');
    }

    return this.#vcf.obtenerErrorCampoGroup(this.formUsuario.controls, arg0, true) + (msgExtra ? ' ' + msgExtra : '');
  }

  protected onSubmit() {
    if (this.formUsuario.invalid) {
      this.formUsuario.markAllAsTouched();
      return;
    }

    if (this.esEdicion()) {
      // editar
    } else {
      //crear
      const domCreacion = this.formDomicilio.value as DomicilioCreacionDTO;
      this.#domService.create(domCreacion).pipe(
        switchMap((domId: number) => {
          console.log('domId tras crear domicilio: ', domId);
          // Luego de crear el domicilio, crear el usuario
          const usrCreacion = this.mapearFormularioAUsuarioCreacion(domId);
          console.log('Creando usuario: ', usrCreacion);
          return this.#usrService.create(usrCreacion);

        }),
        takeUntilDestroyed(this.#destroyRef)).subscribe({
          next: () => {
            this.#router.navigate(['/login']);
          },
          error: (err: HttpErrorResponse) => {
            console.error('Error en la creación del usuario: ', err);
            this.#usrService.postError.set(String(err.error ?? 'Error desconocido al crear usuario.'));
          }
        });
    }
  }

  private mapearFormularioAUsuarioCreacion(domId: number): UsuarioCreacionDTO {
    return {
      usrApellido: this.formUsuario.get('usrApellido')?.value!,
      usrNombre: this.formUsuario.get('usrNombre')?.value!,
      usrTipoUsuario: this.ctrlTipoUsuario.value()!.ttuTipoUsuario,
      usrFechaNacimiento: (this.formUsuario.get('usrFechaNacimiento')?.value as Date)
        .toISOString().split('T')[0],
      usrDni: this.formUsuario.get('usrDni')?.value!,
      usrCuitCuil: this.formUsuario.get('usrCuitCuil')?.value ?? null,
      usrRazonSocialFantasia: this.formUsuario.get('usrRazonSocialFantasia')?.value ?? null,
      usrMatricula: this.formUsuario.get('usrMatricula')?.value ?? null,
      usrEmail: this.formUsuario.get('usrEmail')?.value!,
      usrDescripcion: this.formUsuario.get('usrDescripcion')?.value ?? null,
      usrPassword: this.formUsuario.get('usrPassword')?.value!,
      domId,
      usrScoring: 0, // Valor por defecto al crear un usuario
    };
  }

  ConsoleValidFormulario() {
    console.log({
      usrApellido: this.formUsuario.get('usrApellido')?.valid,
      usrNombre: this.formUsuario.get('usrNombre')?.valid,
      usrDni: this.formUsuario.get('usrDni')?.valid,
      usrCuitCuil: this.formUsuario.get('usrCuitCuil')?.valid,
      usrRazonSocialFantasia: this.formUsuario.get('usrRazonSocialFantasia')?.valid,
      usrTipoUsuario: this.formUsuario.get('usrTipoUsuario')?.valid,
      usrMatricula: this.formUsuario.get('usrMatricula')?.valid,
      usrEmail: this.formUsuario.get('usrEmail')?.valid,
      usrFechaNacimiento: this.formUsuario.get('usrFechaNacimiento')?.valid,
      usrDescripcion: this.formUsuario.get('usrDescripcion')?.valid,
      usrPassword: this.formUsuario.get('usrPassword')?.valid,
      domCPA: this.formDomicilio.get('domCPA')?.valid,
      domCalleRuta: this.formDomicilio.get('domCalleRuta')?.valid,
      domNroKm: this.formDomicilio.get('domNroKm')?.valid,
      domPiso: this.formDomicilio.get('domPiso')?.valid,
      domDepto: this.formDomicilio.get('domDepto')?.valid,
      locId: this.formDomicilio.get('locId')?.valid,
      provId: this.formDomicilio.get('provId')?.valid,
      formUsuarioValid: this.formUsuario.valid,
      formDomicilioValid: this.formDomicilio.valid,
      formUsuarioValue: this.formUsuario.value,
      formDomicilioValue: this.formDomicilio.value,
    });
  }


}
