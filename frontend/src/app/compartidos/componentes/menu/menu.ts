import { Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatToolbarModule } from '@angular/material/toolbar';
import { RouterLink } from '@angular/router';
import { Autorizado } from '../../../seguridad/autorizado/autorizado';
import { AutenticacionStore } from '../../../seguridad/store/autenticacion.store';
import { SwalDirective } from '@sweetalert2/ngx-sweetalert2';


@Component({
  selector: 'app-menu',
  imports: [MatToolbarModule, MatIconModule, MatButtonModule, RouterLink, Autorizado, SwalDirective],
  templateUrl: './menu.html',
  styleUrl: './menu.scss'
})
export class Menu {
  readonly store = inject(AutenticacionStore);

}
