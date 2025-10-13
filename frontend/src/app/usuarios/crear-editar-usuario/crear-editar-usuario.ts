import { ChangeDetectionStrategy, Component, computed, effect, inject, input, numberAttribute, signal, untracked } from '@angular/core';
import { FormBuilder, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { ValidaControlForm } from '../../compartidos/servicios/valida-control-form';
import { ValidaGroupForm } from '../../compartidos/servicios/valida-group-form';
import { TipoUsuarioDTO, TipoUsuarioEnum } from '../../seguridad/seguridadDTO';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { RouterLink } from '@angular/router';
import { UsuarioDTO } from '../modelo/usuarioDTO';
import { Cargando } from "../../compartidos/componentes/cargando/cargando";
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { formControlSignal, formGroupSignal } from '../../compartidos/funciones/formToSignal';
import { MatSelectModule } from '@angular/material/select';
import { TiposUsuarioService } from '../../seguridad/tipos-usuario-service';
import { HttpErrorResponse } from '@angular/common/http';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { AutocompletarLocalidades } from "../../localidades/autocompletar-localidades/autocompletar-localidades";
import { AutocompletarProvincias } from "../../provincias/autocompletar-provincias/autocompletar-provincias";

@Component({
  selector: 'app-crear-editar-usuario',
  imports: [MatButtonModule, RouterLink, MatFormFieldModule, ReactiveFormsModule, MatInputModule, Cargando, MostrarErrores, MatSelectModule, MatTooltipModule, MatDatepickerModule, AutocompletarLocalidades, AutocompletarProvincias],
  templateUrl: './crear-editar-usuario.html',
  styleUrl: './crear-editar-usuario.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CrearEditarUsuario {

  readonly id = input(null, { transform: numberAttribute, alias: 'idPath' });

  protected user?: UsuarioDTO;



  readonly esEdicion = computed(() => Number.isInteger(this.id()));
  readonly titulo = computed(() => this.esEdicion() ? 'Editar Perfil' : 'Registrar nuevo Usuario');

  readonly esCargando = computed(() => this.tipoUsuarioResource === null || this.tipoUsuarioResource.isLoading());







  #fb = inject(FormBuilder);
  #vcf = inject(ValidaControlForm);
  protected tipoUsuarioResource = inject(TiposUsuarioService).tiposUsuarioResource();

  readonly hayErrores = computed(() => this.tipoUsuarioResource === null || this.tipoUsuarioResource.status() === 'error');

  readonly errores = computed(() => {
    return this.hayErrores() ? [(this.tipoUsuarioResource.error() as HttpErrorResponse)?.error as string ?? 'Error desconocido'] : [];
  });

  protected readonly maxCaracteresDescripcion = signal(500);

  protected formDomicilio = this.#fb.group({
    CPA: ['', { validators: [Validators.required, Validators.pattern(/^[A-Z]\d{4}[A-Z]{3}$/)], updateOn: 'change' }],
    calleRuta: ['', { validators: [Validators.required, Validators.maxLength(50)], updateOn: 'change' }],
    nroKm: [0, { validators: [Validators.required, Validators.min(0), Validators.max(12000), this.#vcf.entero()], updateOn: 'change' }],
    piso: ['', { validators: [Validators.maxLength(10), Validators.pattern(/^[a-zA-Z0-9ñÑ]+$/)], updateOn: 'change' }],
    departamento: ['', { validators: [Validators.maxLength(10), Validators.pattern(/^[a-zA-Z0-9ñÑ]+$/)], updateOn: 'change' }],
    idLocalidad: this.#fb.control<number | null>(null, { validators: [Validators.required, Validators.min(1)], updateOn: 'change' }),
    idProvincia: this.#fb.control<number | null>(null, { validators: [Validators.required, Validators.min(1)], updateOn: 'change' }),
  });

  protected formUsuario = this.#fb.group({
    apellido: ['', { validators: [Validators.required, Validators.maxLength(50), this.#vcf.cadaPrimeraLetraMayuscula(), this.#vcf.apellidoNombreValido()], updateOn: 'change' }],
    nombre: ['', { validators: [Validators.required, Validators.maxLength(50), this.#vcf.cadaPrimeraLetraMayuscula(), this.#vcf.apellidoNombreValido()], updateOn: 'change' }],
    dni: ['', { validators: [Validators.required, Validators.pattern(/^\d{8}$/)], updateOn: 'change' }],
    cuitCuil: ['', { validators: [this.#vcf.cuitCuilValido()], updateOn: 'change' }],
    razonSocial: ['', { validators: [Validators.maxLength(100)], updateOn: 'change' }],
    tipoUsuario: this.#fb.control<TipoUsuarioDTO | null>(null, { validators: [Validators.required], updateOn: 'change' }),
    matricula: ['', { validators: [Validators.maxLength(20)], updateOn: 'change' }],
    email: ['', { validators: [Validators.required, Validators.email, Validators.minLength(6), Validators.maxLength(100)], updateOn: 'change' }],
    fechaNacimiento: this.#fb.control<Date | null>(null, { validators: [Validators.required, this.#vcf.fechaNoPuedeSerFutura()], updateOn: 'change' }),
    descripcion: ['', { validators: [Validators.maxLength(this.maxCaracteresDescripcion())], updateOn: 'change' }],
    password: ['', { validators: [Validators.required, Validators.minLength(8), Validators.maxLength(25), Validators.pattern(/^(?=.*?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ])(?=.*?[a-zñäëïöüáéíóúâêîôûàèìòù])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/)], updateOn: 'change' }],
    domicilio: this.formDomicilio,
  }/*, { validators: [this.#vgf.razonSocialRequiredIfCuitValid] }*/);




  readonly ctrlTipoUsuario = formControlSignal(this.formUsuario.get('tipoUsuario') as FormControl<TipoUsuarioDTO | null>);
  readonly ctrlProvinciaSignal = formControlSignal(this.formUsuario.get('domicilio')?.get('idProvincia') as FormControl<number | null>);
  readonly ctrlLocalidadSignal = formControlSignal(this.formUsuario.get('domicilio')?.get('idLocalidad') as FormControl<number | null>);

  readonly requiereMatricula = computed(() => {
    const tipo = this.ctrlTipoUsuario.value();
    if (!tipo) return false;
    return tipo.ttuRequiereMatricula;
  });
  readonly ctrlMatriculaSignal = formControlSignal(this.formUsuario.get('matricula') as FormControl<string>);

  readonly ctrlCuitCuilSignal = formControlSignal(this.formUsuario.get('cuitCuil') as FormControl<string>);
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
    this.#vcf.requeridoIfEffect(this.requiereMatricula, this.formUsuario.get('matricula')! as FormControl);
    this.#vcf.requeridoIfEffect(this.requiereRazonSocial, this.formUsuario.get('razonSocial')! as FormControl);

    effect(() => {
      const reqMatricula = this.requiereMatricula();
      if (!reqMatricula && (this.ctrlMatriculaSignal.value() ?? '') !== '') {
        untracked(() => this.ctrlMatriculaSignal.value.set(''));
      }
      console.log('status matricula', this.ctrlMatriculaSignal.status());
    });

  }

  obtenerErrorCampo(arg0: string, esDomicilio: boolean = false): string | null {
    
    if (esDomicilio) {
      return this.#vcf.obtenerErrorCampoGroup(this.formDomicilio.controls, arg0);
    }

    return this.#vcf.obtenerErrorCampoGroup(this.formUsuario.controls, arg0);
  }


}
