import { Component, inject } from '@angular/core';
import { ReactiveFormsModule, FormControl } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatToolbarModule } from '@angular/material/toolbar';
import { RouterLink, Router } from '@angular/router';
import { Autorizado } from '../../../seguridad/autorizado/autorizado';
import { AutenticacionStore } from '../../../seguridad/store/autenticacion.store';
import { SwalDirective } from '@sweetalert2/ngx-sweetalert2';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { AntiguedadVentaStore } from '../../../antiguedades-venta/store-global-antiguedad-venta/antiguedad-venta.store';


@Component({
  selector: 'app-menu',
  imports: [MatToolbarModule, MatIconModule, MatButtonModule, RouterLink, Autorizado, SwalDirective, ReactiveFormsModule, MatFormFieldModule, MatInputModule],
  templateUrl: './menu.html',
  styleUrl: './menu.scss'
})
export class Menu {
  readonly store = inject(AutenticacionStore);
  private router = inject(Router);

  #storeVenta = inject(AntiguedadVentaStore);

  // control del buscador
  protected searchCtrl = new FormControl<string>('', { nonNullable: true });

  protected onSearch(): void {
    const q = this.searchCtrl.value.trim();
    this.#storeVenta.resetStore();
    this.#storeVenta.setSearchWord(q);
    this.router.navigate(['/'], { queryParams: q ? { q } : {} });
  }
}
