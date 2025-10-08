import { ChangeDetectionStrategy, Component, input } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { RouterLink } from '@angular/router';
import { ValidaForm } from '../../compartidos/servicios/valida-form';

@Component({
  selector: 'app-formulario-usuario',
  imports: [MatButtonModule, RouterLink, MatFormFieldModule, ReactiveFormsModule, MatInputModule],
  providers: [ValidaForm],
  templateUrl: './formulario-usuario.html',
  styleUrl: './formulario-usuario.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class FormularioUsuario {

  readonly #modelo = input

}
