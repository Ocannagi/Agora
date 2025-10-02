import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { AutenticacionStore } from '../store/autenticacion.store';
import { FormularioAutenticacion } from "../formulario-autenticacion/formulario-autenticacion";

@Component({
  selector: 'app-login',
  imports: [FormularioAutenticacion],
  templateUrl: './login.html',
  styleUrl: './login.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class Login {
  readonly store = inject(AutenticacionStore);
}
