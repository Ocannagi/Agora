import { ChangeDetectionStrategy, Component, computed, DestroyRef, inject, Injector, signal } from '@angular/core';
import { Router } from '@angular/router';
import { CurrencyPipe, NgOptimizedImage } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';

import { CarritoStore } from '../store-carrito/carrito.store';
import { AntiguedadEnCarritoDTO } from '../modelo/antiguedadEnCarritoDTO';
import { AntiguedadALaVentaDTO } from '../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO';
import { UsuarioDTO } from '../../usuarios/modelo/usuarioDTO';
import { UsuariosDomiciliosService } from '../../usuarios-domicilios/usuario-domicilios-service';
import { AutenticacionStore } from '../../seguridad/store/autenticacion.store';
import { DomicilioDTO } from '../../domicilios/modelo/domicilioDTO';
import { ComprasVentasService } from '../../compras-ventas/compras-ventas-service';
import { CompraVentaCreacionDTO, CompraVentaDetalleCreacionDTO, TipoMedioPagoEnum } from '../../compras-ventas/modelo/compraVentaDTO';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { Cargando } from '../../compartidos/componentes/cargando/cargando';
import { stringPersistencia } from '../store-carrito/carrito.slice';
import { formatFechaDDMMYYYY, formatFechaYYYYMMDD } from '../../compartidos/funciones/formatFecha';
import { lastValueFrom } from 'rxjs';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { MatDialog } from '@angular/material/dialog';
import { DialogAgregarDomicilio } from '../../usuarios-domicilios/dialog-agregar-domicilio/dialog-agregar-domicilio';
import { MatIcon } from "@angular/material/icon";

type GrupoVendedorConSubtotal = {
  vendedor: UsuarioDTO;
  items: AntiguedadEnCarritoDTO[];
  subtotal: number;
};

@Component({
  selector: 'app-checkout',
  imports: [
    MatCardModule, MatButtonModule, MatFormFieldModule, MatSelectModule,
    CurrencyPipe, NgOptimizedImage, MostrarErrores, Cargando,
    MatIcon
],
  templateUrl: './checkout.html',
  styleUrl: './checkout.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class Checkout {

  // Stores y servicios
  readonly storeCarrito = inject(CarritoStore);
  readonly router = inject(Router);
  readonly auth = inject(AutenticacionStore);
  #usrDomService = inject(UsuariosDomiciliosService);
  #comprasService = inject(ComprasVentasService);
  #injector = inject(Injector);
  #destroyRef = inject(DestroyRef);
  #dialog = inject(MatDialog); // Inyectar el servicio de diálogo (Modal)

  // Estado local
  readonly medioPago = signal<TipoMedioPagoEnum | null>(null);
  readonly selectedDomicilioId = signal<number | null>(null);
  readonly busy = signal(false);
  readonly success = signal(false);

  // Recursos
  readonly usrId = computed(() => this.auth.usrId() ?? null);
  readonly usuariosDomiciliosResource = this.#usrDomService.getByUsrIdResource(this.usrId, this.#injector);

  // Items válidos a comprar
  readonly itemsValidos = computed(() =>
    this.storeCarrito.carrito().filter(ci => ci.hayStock && !ci.cambioPrecio)
  );

  // Grupo por vendedor
  readonly grupos = computed<GrupoVendedorConSubtotal[]>(() => {
    const mapa = new Map<number, GrupoVendedorConSubtotal>();
    for (const antiguedad of this.itemsValidos()) {
      const vendedor = antiguedad.antiguedadAlaVenta.vendedor;
      const id = vendedor.usrId;
      const yaExistente = mapa.get(id);
      if (yaExistente) {
        yaExistente.items.push(antiguedad);
        yaExistente.subtotal += antiguedad.antiguedadAlaVenta.aavPrecioVenta ?? 0;
      } else {
        mapa.set(id, { vendedor: vendedor, items: [antiguedad], subtotal: antiguedad.antiguedadAlaVenta.aavPrecioVenta ?? 0 });
      }
    }
    return Array.from(mapa.values());
  });

  readonly total = computed(() => this.grupos().reduce((acc, g) => acc + g.subtotal, 0));

  // Domicilio seleccionado completo
  readonly selectedDomicilio = computed<DomicilioDTO | null>(() => {
    const id = this.selectedDomicilioId();
    if (!id) return null;
    const col = this.usuariosDomiciliosResource.value()?.domicilios ?? [];
    return col.find(d => d.domId === id) ?? null;
  });

  // Entregas previstas por AAV
  readonly entregaPrevistaPorAav = computed<Map<number, string>>(() => {
    const destino = this.selectedDomicilio();
    const map = new Map<number, string>();
    if (!destino) return map;

    for (const antiguedadCarrito of this.itemsValidos()) {
      const aav = antiguedadCarrito.antiguedadAlaVenta;
      const when = this.calcularFechaEntregaPrevista(aav.domicilioOrigen, destino);
      map.set(aav.aavId, when);
    }
    return map;
  });

  // Pantalla de éxito: conservar items comprados para mostrar
  readonly itemsComprados = signal<AntiguedadALaVentaDTO[]>([]);


  constructor() {
    this.#destroyRef.onDestroy(() => {
      this.storeCarrito.removeItemsInvalidos();
      this.#comprasService.postError.set(null);
    });
  }


  // UI helpers
  vendedorTitulo(v: UsuarioDTO): string {
    const rs = v.usrRazonSocialFantasia?.trim();
    return rs && rs.length > 0 ? 'Antigüedades del Anticuario' : 'Antigüedades de';
  }
  vendedorNombre(v: UsuarioDTO): string {
    const rs = v.usrRazonSocialFantasia?.trim();
    if (rs && rs.length > 0) return rs;
    return `${v.usrNombre ?? ''} ${v.usrApellido ?? ''}`.trim();
  }
  portadaUrl(aav: AntiguedadALaVentaDTO): string | null {
    const arr = aav.antiguedad.imagenes ?? [];
    if (!arr || arr.length === 0) return null;
    const byOrden = [...arr].sort((a, b) => (a.imaOrden ?? 0) - (b.imaOrden ?? 0));
    const portada = byOrden.find(i => i.imaOrden === 1) ?? byOrden[0];
    return portada?.imaUrl ?? null;
  }

  entregaPrevistaFor(aavId: number): string {
    return this.entregaPrevistaPorAav().get(aavId) ?? '';
  }

  // Reglas de entrega
  private calcularFechaEntregaPrevista(origen: DomicilioDTO, destino: DomicilioDTO): string {
    const ambosCABA = origen.localidad.provincia.provId === 1 && destino.localidad.provincia.provId === 1;
    const mismoLoc = origen.localidad.locId === destino.localidad.locId;
    const mismoProv = origen.localidad.provincia.provId === destino.localidad.provincia.provId;

    const dias = mismoLoc || ambosCABA ? 1 : (mismoProv ? 4 : 10);
    const fecha = this.addDays(new Date(), dias);
    return formatFechaDDMMYYYY(fecha);
  }
  private addDays(base: Date, days: number): Date {
    const d = new Date(base);
    d.setDate(d.getDate() + days);
    return d;
  }

  // Acciones
  cancelar(): void {
    this.router.navigate(['/carrito']);
  }

  async comprar(): Promise<void> {
    await lastValueFrom(this.storeCarrito.comprobarStockPrecioAav().pipe(takeUntilDestroyed(this.#destroyRef)));


    const usrId = this.usrId();
    const destId = this.selectedDomicilioId();
    const mp = this.medioPago();
    const impedir = this.storeCarrito.impedirContinuarCompra();

    if (!destId || !mp || this.itemsValidos().length === 0 || impedir) {
      this.storeCarrito.setOneError('No se cumplen las condiciones para realizar la compra.');
      return;
    }

    const itemsAComprar = this.itemsValidos();

    const detalles: CompraVentaDetalleCreacionDTO[] = itemsAComprar.map(ci => ({
      antiguedadAlaVenta:
      {
        aavId: ci.antiguedadAlaVenta.aavId,
        aavPrecioVenta: ci.antiguedadAlaVenta.aavPrecioVenta

      },
      cvdFechaEntregaPrevista: formatFechaYYYYMMDD(this.entregaPrevistaFor(ci.antiguedadAlaVenta.aavId))
    }));

    const dto: CompraVentaCreacionDTO = {
      usuarioComprador: { usrId: usrId! },
      domicilioDestino: { domId: destId },
      covTipoMedioPago: mp,
      detalles
    };

    this.busy.set(true);
    this.#comprasService.create(dto).subscribe({
      next: () => {
        // éxito: limpiar carrito, limpiar persistencia y mostrar confirmación
        const comprados = itemsAComprar.map(ci => ci.antiguedadAlaVenta);
        this.itemsComprados.set(comprados);
        try { localStorage.removeItem(stringPersistencia); } catch { }
        this.storeCarrito.clearCarrito();
        this.success.set(true);
        this.#comprasService.postError.set(null);
      },
      error: () => {
        // error ya seteado en postError
      },
      complete: () => this.busy.set(false)
    });
  }

  // Habilitación del botón comprar
  readonly puedeComprar = computed(() =>
    !!this.usrId() &&
    !!this.selectedDomicilioId() &&
    !!this.medioPago() &&
    this.itemsValidos().length > 0 &&
    !this.busy() &&
    !this.storeCarrito.impedirContinuarCompra()
  );

  // Errores a mostrar
  readonly errores = computed(() => {
    const arr: string[] = [];
    if (this.#comprasService.postError()) arr.push(this.#comprasService.postError()!);
    if (this.storeCarrito.errors().length) arr.push(...this.storeCarrito.errors());
    return arr;
  });

  // Exponer enum al template
  protected readonly TMP = TipoMedioPagoEnum;

  protected openDialog(): void {
    const dialogRef = this.#dialog.open(DialogAgregarDomicilio, {
      disableClose: true,
    });

    const subscription = dialogRef.afterClosed().subscribe(result => {
      console.log('The dialog was closed');
      if (result === true) {
        this.usuariosDomiciliosResource.reload();
      }
    });

    this.#destroyRef.onDestroy(() => {
      subscription.unsubscribe();
    });
  }
}
