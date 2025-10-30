import { ChangeDetectionStrategy, Component, computed, DestroyRef, inject } from '@angular/core';
import { CurrencyPipe, NgOptimizedImage } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { CarritoStore } from '../store-carrito/carrito.store';
import { AntiguedadEnCarritoDTO } from '../modelo/antiguedadEnCarritoDTO';
import { AntiguedadALaVentaDTO } from '../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO';
import { UsuarioDTO } from '../../usuarios/modelo/usuarioDTO';
import { Router } from '@angular/router';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { MatError } from "@angular/material/form-field";

type GrupoVendedor = {
  vendedor: UsuarioDTO;
  items: AntiguedadEnCarritoDTO[];
};

@Component({
  selector: 'app-carrito',
  imports: [MatCardModule, MatButtonModule, CurrencyPipe, NgOptimizedImage, MostrarErrores, MatError],
  templateUrl: './carrito.html',
  styleUrl: './carrito.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class Carrito {
  readonly storeCarrito = inject(CarritoStore);
  readonly router = inject(Router);
  #destroyRef = inject(DestroyRef);

  readonly grupos = computed<GrupoVendedor[]>(() => {
    const mapa = new Map<number, GrupoVendedor>();
    for (const antEnCarrito of this.storeCarrito.carrito()) {
      const vendedor = antEnCarrito.antiguedadAlaVenta.vendedor;
      const usrId = vendedor.usrId;
      const existente = mapa.get(usrId);
      if (existente) existente.items.push(antEnCarrito);
      else mapa.set(usrId, { vendedor, items: [antEnCarrito] });
    }
    return Array.from(mapa.values());
  });


  constructor() {
    this.storeCarrito.pullingTrigger();

    this.#destroyRef.onDestroy(() => {
      // limpiar items inválidos al salir del carrito
      this.storeCarrito.removeItemsInvalidos();
    });
  }

  vendedorTitulo(vendedor: UsuarioDTO): string {
    const razonSocial = vendedor.usrRazonSocialFantasia as string | undefined | null;
    return razonSocial && razonSocial.trim().length > 0 ? 'Antigüedades del Anticuario' : 'Antigüedades de';
  }

  vendedorNombre(vendedor: UsuarioDTO): string {
    const razonSocial = vendedor.usrRazonSocialFantasia as string | undefined | null;
    if (razonSocial && razonSocial.trim().length > 0) return razonSocial.trim();
    const nom = vendedor.usrNombre ?? '';
    const ape = vendedor.usrApellido ?? '';
    return `${nom} ${ape}`.trim();
  }

  portadaUrl(aav: AntiguedadALaVentaDTO): string | null {
    return aav.antiguedad.imagenes?.find(img => img.imaOrden === 1)?.imaUrl ?? null;
  }

  trackGrupo = (_: number, g: GrupoVendedor) => g.vendedor.usrId as number;
  trackItem = (_: number, ci: AntiguedadEnCarritoDTO) => ci.antiguedadAlaVenta.aavId;

  openDetalle(aav: AntiguedadALaVentaDTO): void {
    this.router.navigate(['/galeriaVertical', aav.aavId], {
      state: { returnTo: this.router.url }
    });
  }

  eliminar(aavId: number): void {
    this.storeCarrito.removeCarrito(aavId);
  }

  continuarCompra(): void {
    this.storeCarrito.pullingTrigger();
    // TODO: navegación a flujo de checkout
    // this.router.navigate(['/checkout']);
  }

  isDisabled(ci: AntiguedadEnCarritoDTO): boolean {
    return !ci.hayStock || ci.cambioPrecio;
  }

  onCardClick(ci: AntiguedadEnCarritoDTO, ev: MouseEvent): void {
    if (this.isDisabled(ci)) {
      ev.preventDefault();
      ev.stopImmediatePropagation();
      return;
    }
    this.openDetalle(ci.antiguedadAlaVenta);
  }

  onCardKey(ci: AntiguedadEnCarritoDTO,ev: Event): void {
    const keyboardEvent = ev as KeyboardEvent;
    
    if (this.isDisabled(ci)) return;
    if (keyboardEvent.key === 'Enter' || keyboardEvent.key === ' ') {
      keyboardEvent.preventDefault();
      this.openDetalle(ci.antiguedadAlaVenta);
    }
  }
}
