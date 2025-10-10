import { ChangeDetectionStrategy, Component, computed, inject, input, signal } from '@angular/core';
import { FormBuilder, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { RouterLink } from '@angular/router';
import { ValidaControlForm } from '../../compartidos/servicios/valida-control-form';
import { ValidaGroupForm } from '../../compartidos/servicios/valida-group-form';
import { TipoUsuarioEnum } from '../../seguridad/seguridadDTO';

@Component({
  selector: 'app-formulario-usuario',
  imports: [MatButtonModule, RouterLink, MatFormFieldModule, ReactiveFormsModule, MatInputModule],
  providers: [ValidaControlForm],
  templateUrl: './formulario-usuario.html',
  styleUrl: './formulario-usuario.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class FormularioUsuario {

  #fb = inject(FormBuilder);
  #vcf = inject(ValidaControlForm);
  #vgf = inject(ValidaGroupForm);

  protected formUsuario = this.#fb.group({
    apellido: ['', {validators:[Validators.required, Validators.maxLength(50), this.#vcf.apellidoNombreValido()], updateOn: 'blur'}],
    nombre: ['', {validators:[Validators.required, Validators.maxLength(50), this.#vcf.apellidoNombreValido()], updateOn: 'blur'}],
    dni: ['', {validators:[Validators.required, Validators.pattern(/^\d{8}$/)], updateOn: 'blur'}],
    cuitCuil: ['', {validators:[this.#vcf.cuitCuilValido()], updateOn: 'blur'}],
    razonSocial: ['', {validators:[Validators.maxLength(100)], updateOn: 'blur'}],
    matricula: ['', {validators:[Validators.maxLength(20)], updateOn: 'blur'}],
    email: ['', {validators:[Validators.required, Validators.email, Validators.minLength(6), Validators.maxLength(100)], updateOn: 'blur'}],
    fechaNacimiento: this.#fb.control<Date | null>(null,{validators:[Validators.required, this.#vcf.fechaNoPuedeSerFutura()], updateOn: 'blur'}),
    descripcion: ['', {validators:[Validators.maxLength(500)], updateOn: 'blur'}],
    password: ['', {validators:[Validators.required, Validators.minLength(8), Validators.maxLength(25), Validators.pattern(/^(?=.*?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ])(?=.*?[a-zñäëïöüáéíóúâêîôûàèìòù])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/)], updateOn: 'blur'}],
  }, {validators: [this.#vgf.razonSocialRequiredIfCuitValid]});

  protected formDomicilio = this.#fb.group({
    CPA: ['', {validators:[Validators.required, Validators.pattern(/^[A-Z]\d{4}[A-Z]{3}$/)], updateOn: 'blur'}],
    calleRuta: ['', {validators:[Validators.required, Validators.maxLength(50)], updateOn: 'blur'}],
    nroKm: [0, {validators:[Validators.required,Validators.min(0),Validators.max(12000),this.#vcf.entero()], updateOn: 'blur'}],
    piso: ['', {validators:[Validators.maxLength(10), Validators.pattern(/^[a-zA-Z0-9ñÑ]+$/)], updateOn: 'blur'}],
    departamento: ['', {validators:[Validators.maxLength(10), Validators.pattern(/^[a-zA-Z0-9ñÑ]+$/)], updateOn: 'blur'}],
  });

  readonly idProv = signal<number | null>(null);
  readonly idLoc = signal<number | null>(null);
  readonly tipoUsuario = signal<TipoUsuarioEnum>(TipoUsuarioEnum.UsuarioGeneral);

  //TODO: Traer desde el backend si el tipo de usuario requiere matrícula
  readonly requiereMatricula = computed(() => {
    const tipo = this.tipoUsuario();
    return tipo === TipoUsuarioEnum.UsuarioAnticuario || tipo === TipoUsuarioEnum.UsuarioTasador;
  });

  constructor() {
    //TODO: Traer desde el backend si el tipo de usuario requiere matrícula
    this.#vcf.requeridoIfEffect(this.requiereMatricula,this.formUsuario.get('matricula')! as FormControl);
  }

}
