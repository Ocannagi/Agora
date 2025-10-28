import { ChangeDetectionStrategy, Component, computed, inject } from '@angular/core';
import { CurrencyPipe, NgOptimizedImage } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { Router } from '@angular/router';
import { AntiguedadVentaStore } from '../antiguedades-venta/store-global-antiguedad-venta/antiguedad-venta.store';
import { AntiguedadALaVentaDTO } from '../antiguedades-venta/modelo/AntiguedadAlaVentaDTO';
import { MostrarErrores } from "../compartidos/componentes/mostrar-errores/mostrar-errores";
import { Cargando } from "../compartidos/componentes/cargando/cargando";

@Component({
  selector: 'app-galeria',
  imports: [MatCardModule, MatPaginatorModule, NgOptimizedImage, CurrencyPipe, MostrarErrores, Cargando],
  templateUrl: './galeria.html',
  styleUrl: './galeria.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class Galeria {
  readonly storeVenta = inject(AntiguedadVentaStore);
  readonly router = inject(Router);

  readonly items = computed(() => this.storeVenta.paginadoResponse().arrayEntidad);
  readonly total = computed(() => this.storeVenta.paginadoResponse().totalRegistros);
  readonly pageIndex = computed(() => Math.max(0, this.storeVenta.paginadoResponse().paginaActual - 1));
  readonly pageSize = computed(() => this.storeVenta.paginadoResponse().registrosPorPagina);
  readonly isLoading = computed(() => this.storeVenta.isCargando());

  trackById = (_: number, it: AntiguedadALaVentaDTO) => it.aavId;

  onPage(ev: PageEvent): void {
    this.storeVenta.setPageEvent(ev);
  }

  portadaUrl(it: AntiguedadALaVentaDTO): string | null {
    return it.antiguedad.imagenes?.[0]?.imaUrl ?? null;
  }

  openDetalle(it: AntiguedadALaVentaDTO): void {
    this.router.navigate(['/antiguedadesAlaVenta', 'ver', it.aavId]);
  }
}
