import { ChangeDetectionStrategy, Component, computed, inject, Injector, input } from '@angular/core';
import { NgOptimizedImage, CurrencyPipe } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { Router } from '@angular/router';

import { numberAttributeOrNull } from '../../compartidos/funciones/transform';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { Cargando } from '../../compartidos/componentes/cargando/cargando';

import { ComprasVentasService } from '../compras-ventas-service';
import { CompraVentaDTO, CompraVentaDetalleDTO, TipoMedioPagoEnum } from '../modelo/compraVentaDTO';
import { UsuarioDTO } from '../../usuarios/modelo/usuarioDTO';
import { AntiguedadALaVentaDTO } from '../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO';
import { HttpErrorResponse } from '@angular/common/http';
import { formatFechaDDMMYYYY } from '../../compartidos/funciones/formatFecha';

type GrupoVendedor = {
  vendedor: UsuarioDTO;
  items: CompraVentaDetalleDTO[];
  subtotal: number;
};

@Component({
  selector: 'app-ver-compras-ventas',
  imports: [MostrarErrores, Cargando, MatCardModule, MatButtonModule, MatIconModule, NgOptimizedImage, CurrencyPipe],
  templateUrl: './ver-compras-ventas.html',
  styleUrl: './ver-compras-ventas.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class VerComprasVentas {
  readonly id = input(null, { transform: numberAttributeOrNull });

  // Inyecciones
  #compraVtaService = inject(ComprasVentasService);
  #injector = inject(Injector);
  #router = inject(Router);

  // Recurso por id
  protected compraResource = this.#compraVtaService.getByIdResource(this.id, this.#injector);

  // Estado derivado
  readonly esCargando = computed(() => this.compraResource.isLoading());
  readonly compra = computed<CompraVentaDTO | null>(() => {
    if (this.compraResource.status() === 'resolved') return this.compraResource.value();
    return null;
  });

  readonly errores = computed(() => {
    const lista: string[] = [];
    if (this.#compraVtaService.postError()) lista.push(this.#compraVtaService.postError()!);
    if (this.#compraVtaService.patchError()) lista.push(this.#compraVtaService.patchError()!);
    if (this.compraResource.status() === 'error') {
      const wrapped = this.compraResource.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }
    return lista;
  });

  // Datos de cabecera (sin ID)
  readonly fechaCompra = computed(() => {
    const cov = this.compra();
    return cov ? formatFechaDDMMYYYY(cov.covFechaCompra) : '';
  });

  readonly medioPagoTexto = computed(() => {
    const cov = this.compra();
    if (!cov) return '';
    const mp = cov.covTipoMedioPago;
    const map: Record<TipoMedioPagoEnum, string> = {
      [TipoMedioPagoEnum.TarjetaCredito]: 'Tarjeta de crédito',
      [TipoMedioPagoEnum.TransferenciaBancaria]: 'Transferencia bancaria',
      [TipoMedioPagoEnum.MercadoPago]: 'Mercado Pago',
    };
    return map[mp] ?? String(mp);
  });

  readonly domicilioDestinoTexto = computed(() => {
    const cov = this.compra();
    if (!cov) return '';
    const dom = cov.domicilioDestino as any;
    const calle = dom.domCalleRuta ?? '';
    const nro = dom.domNroKm ?? '';
    const loc = dom.localidad?.locDescripcion ?? dom.locDescripcion ?? '';
    const prov = dom.localidad?.provincia?.provDescripcion ?? dom.provDescripcion ?? '';
    const cpa = dom.domCPA ?? '';
    const linea1 = `${calle} ${nro}`.trim();
    const linea2 = [loc, prov].filter(Boolean).join(' - ');
    return [linea1, linea2, cpa].filter(Boolean).join(' | ');
  });

  // Agrupación por vendedor y totales
  readonly grupos = computed<GrupoVendedor[]>(() => {
    const cov = this.compra();
    if (!cov) return [];
    const mapa = new Map<number, GrupoVendedor>();
    for (const det of cov.detalles) {
      const vend = det.antiguedadAlaVenta.vendedor;
      const vendId = (vend as any).usrId as number;
      const existente = mapa.get(vendId);
      if (existente) {
        existente.items.push(det);
        existente.subtotal += det.antiguedadAlaVenta.aavPrecioVenta ?? 0;
      } else {
        mapa.set(vendId, {
          vendedor: vend,
          items: [det],
          subtotal: det.antiguedadAlaVenta.aavPrecioVenta ?? 0
        });
      }
    }
    return Array.from(mapa.values());
  });

  readonly total = computed(() => this.grupos().reduce((acc, g) => acc + g.subtotal, 0));

  // Helpers vendedor
  vendedorTitulo(v: UsuarioDTO): string {
    const rs = (v as any).usrRazonSocialFantasia as string | undefined | null;
    return rs && rs.trim().length > 0 ? 'Antigüedades del Anticuario' : 'Antigüedades de';
  }
  vendedorNombre(v: UsuarioDTO): string {
    const rs = (v as any).usrRazonSocialFantasia as string | undefined | null;
    if (rs && rs.trim().length > 0) return rs;
    const nom = (v as any).usrNombre ?? '';
    const ape = (v as any).usrApellido ?? '';
    return `${nom} ${ape}`.trim();
  }

  // Imagen portada (imaOrden 1 o primera por orden)
  portadaUrl(aav: AntiguedadALaVentaDTO): string | null {
    const imgs = aav.antiguedad.imagenes ?? [];
    if (!imgs || imgs.length === 0) return null;
    const byOrden = [...imgs].sort((a, b) => (a.imaOrden ?? 0) - (b.imaOrden ?? 0));
    const portada = byOrden.find(i => i.imaOrden === 1) ?? byOrden[0];
    return portada?.imaUrl ?? null;
  }

  // Navegar a editar/ver Antigüedad
  openVerAntiguedad(antId: number | null | undefined): void {
    if (!antId) return;
    this.#router.navigate(['/antiguedades/editar', antId], {
      state: { returnTo: ['/compras', this.id()] }
    });
  }

  // Fecha de entrega a mostrar: real si existe, si no la prevista
  fechaEntrega(det: CompraVentaDetalleDTO): string {
    const fecha = det.cvdFechaEntregaReal ?? det.cvdFechaEntregaPrevista ?? null;
    return formatFechaDDMMYYYY(fecha);
  }

  // trackBy
  trackGrupo = (_: number, g: GrupoVendedor) => (g.vendedor as any).usrId as number;
  trackItem = (_: number, d: CompraVentaDetalleDTO) => d.cvdId;
}
