import { ChangeDetectionStrategy, Component, inject, input, model, output } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { CredencialesUsuarioDTO } from '../seguridadDTO';
import { ValidaForm } from '../../compartidos/servicios/valida-form';

@Component({
  selector: 'app-formulario-autenticacion',
  imports: [MatFormFieldModule, MatButtonModule, MatInputModule, ReactiveFormsModule, MostrarErrores],
  templateUrl: './formulario-autenticacion.html',
  styleUrl: './formulario-autenticacion.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class FormularioAutenticacion {

  readonly titulo = input.required<string>();
  readonly errores = input.required<string[]>();
  readonly posteoFormulario = output<CredencialesUsuarioDTO>();

  private formBuilder = inject(FormBuilder);
  private validaForm = inject(ValidaForm);

  protected frmAutenticacion = this.formBuilder.group({
    usrEmail: ['', { validators: [Validators.required, Validators.email] }],
    usrPassword: ['', { validators: [Validators.required] }]
  })

  protected obtenerErrorCampo(campo: string): string | null {
    return this.validaForm.obtenerErrorCampo(this.frmAutenticacion.controls, campo);
  }

  saveChanges(): void {
    if (this.frmAutenticacion.invalid)
      return;
    this.posteoFormulario.emit(this.frmAutenticacion.value as CredencialesUsuarioDTO);
  }

}
