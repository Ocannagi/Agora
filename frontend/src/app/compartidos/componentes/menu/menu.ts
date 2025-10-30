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
import { MatBadgeModule } from '@angular/material/badge';
import { SearchWordStore } from '../../../galeria/galeria-vertical/store-search-word/search-word.store';
import { CarritoStore } from '../../../carrito/store-carrito/carrito.store';


@Component({
  selector: 'app-menu',
  imports: [MatToolbarModule, MatIconModule, MatButtonModule, RouterLink, Autorizado, SwalDirective, ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatBadgeModule],
  templateUrl: './menu.html',
  styleUrl: './menu.scss'
})
export class Menu {
  readonly storeAuth = inject(AutenticacionStore);
  private router = inject(Router);

  #storeSearch = inject(SearchWordStore);
  readonly storeCarrito = inject(CarritoStore);

  // control del buscador
  protected searchCtrl = new FormControl<string>('', { nonNullable: true });

  protected onSearch(): void {
    const keyWord = this.searchCtrl.value.trim();
    if(!keyWord || keyWord.length === 0) {
      return;
    }

    if (keyWord !== this.#storeSearch.paginadoRequest.searchWord()) {
      this.#storeSearch.setSearchWord(keyWord);
    }
    this.router.navigate(['/galeriaVertical']);
  }
}
