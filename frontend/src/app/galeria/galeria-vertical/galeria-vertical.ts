import { ChangeDetectionStrategy, Component, computed, inject } from '@angular/core';
import { CurrencyPipe, NgOptimizedImage } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { Router } from '@angular/router';
import { AntiguedadALaVentaDTO } from '../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { Cargando } from "../../compartidos/componentes/cargando/cargando";
import { SearchWordStore } from './store-search-word/search-word.store';
import { ListadoGenerico } from "../../compartidos/componentes/listado-generico/listado-generico";

@Component({
  selector: 'app-galeria',
  imports: [MatCardModule, MatPaginatorModule, NgOptimizedImage, CurrencyPipe, MostrarErrores, Cargando, ListadoGenerico],
  templateUrl: './galeria-vertical.html',
  styleUrl: './galeria-vertical.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class GaleriaVertical {
  readonly storeSearch = inject(SearchWordStore);
  readonly router = inject(Router);

  /**
   *
   */
  constructor() {
    this.storeSearch.reloadResourcePaginadoSearch();
    
  }


  trackById = (_: number, aav: AntiguedadALaVentaDTO) => aav.aavId;

  onPage(ev: PageEvent): void {
    this.storeSearch.setPageEvent(ev);
  }

  portadaUrl(aav: AntiguedadALaVentaDTO): string | null {
    return aav.antiguedad.imagenes?.find( img => img.imaOrden === 1)?.imaUrl ?? null;
  }

  openDetalle(it: AntiguedadALaVentaDTO): void {
    this.router.navigate(['/galeriaVertical', it.aavId], {
      state: {
        returnTo: this.router.url,
      }
    });
  }
}
