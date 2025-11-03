import { ChangeDetectionStrategy, Component, computed, inject, Injector, input, signal } from '@angular/core';
import { NgOptimizedImage, CurrencyPipe } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { Router } from '@angular/router';

import { numberAttributeOrNull } from '../../compartidos/funciones/transform';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { Cargando } from '../../compartidos/componentes/cargando/cargando';

import { VentasDetalleService } from '../ventas-detalle-service';
import { VentaDetalleDTO } from '../modelo/ventaDetalleDTO';
import { AntiguedadALaVentaDTO } from '../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO';
import { UsuarioDTO } from '../../usuarios/modelo/usuarioDTO';
import { TipoMedioPagoEnum } from '../modelo/compraVentaDTO';
import { HttpErrorResponse } from '@angular/common/http';
import { formatFechaDDMMYYYY, formatFechaYYYYMMDD } from '../../compartidos/funciones/formatFecha';
import { DomicilioDTO } from '../../domicilios/modelo/domicilioDTO';

@Component({
  selector: 'app-ver-ventas',
  imports: [MostrarErrores, Cargando, MatCardModule, MatButtonModule, MatIconModule, MatFormFieldModule, MatInputModule, MatDatepickerModule, CurrencyPipe, NgOptimizedImage],
  templateUrl: './ver-ventas.html',
  styleUrl: './ver-ventas.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class VerVentas {
  readonly id = input(null, { transform: numberAttributeOrNull });

  // Inyecciones
  #ventaDetService = inject(VentasDetalleService);
  #injector = inject(Injector);
  #router = inject(Router);

  // Recurso
  protected ventaResource = this.#ventaDetService.getByIdResource(this.id, this.#injector);

  // Estado
  readonly guardando = signal(false);
  readonly fechaEntregaRealInput = signal<Date | null>(null);

  // Derivados
  readonly esCargando = computed(() => this.ventaResource.isLoading());
  readonly venta = computed<VentaDetalleDTO | null>(() => {
    if (this.ventaResource.status() === 'resolved') return this.ventaResource.value();
    return null;
  });

  readonly errores = computed(() => {
    const lista: string[] = [];
    if (this.#ventaDetService.patchError()) lista.push(this.#ventaDetService.patchError()!);
    if (this.ventaResource.status() === 'error') {
      const wrapped = this.ventaResource.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }
    return lista;
  });

  // Textos
  compradorNombre(usrComprador: UsuarioDTO): string {
    const rs = usrComprador.usrRazonSocialFantasia as string | undefined | null;
    if (rs && rs.trim().length > 0) return rs.trim();
    const nom = usrComprador.usrNombre ?? '';
    const ape = usrComprador.usrApellido ?? '';
    return `${nom} ${ape}`.trim();
  }

  fechaCompra(): string {
    const vd = this.venta();
    return vd ? formatFechaDDMMYYYY(vd.covFechaCompra) : '';
  }

  // Fecha prevista formateada
  fechaPrevista(vd: VentaDetalleDTO): string {
    return formatFechaDDMMYYYY(vd.cvdFechaEntregaPrevista);
  }

  // Domicilio destino en una sola línea
  domicilioDestinoLinea(dom: DomicilioDTO): string {
    const calle = dom.domCalleRuta ?? '';
    const nro = dom.domNroKm ?? ''; // puede ser string | number | ''
    const loc = dom.localidad?.locDescripcion ?? '';
    const prov = dom.localidad?.provincia?.provDescripcion ?? '';

    const notEmpty = (s: string): s is string => s.trim().length > 0;

    const calleNum = [calle, nro].map(v => String(v)).filter(notEmpty).join(' ');
    const zona = [loc, prov].filter(notEmpty).join(' - ');
    return [calleNum, zona].filter(notEmpty).join(' · ');
  }

  medioPagoTexto(): string {
    const vd = this.venta();
    if (!vd) return '';
    const map: Record<TipoMedioPagoEnum, string> = {
      [TipoMedioPagoEnum.TarjetaCredito]: 'Tarjeta de crédito',
      [TipoMedioPagoEnum.TransferenciaBancaria]: 'Transferencia bancaria',
      [TipoMedioPagoEnum.MercadoPago]: 'Mercado Pago',
    };
    return map[vd.covTipoMedioPago] ?? String(vd.covTipoMedioPago);
  }

  portadaUrl(aav: AntiguedadALaVentaDTO): string | null {
    const imgs = aav.antiguedad.imagenes ?? [];
    if (!imgs || imgs.length === 0) return null;
    const byOrden = [...imgs].sort((a, b) => (a.imaOrden ?? 0) - (b.imaOrden ?? 0));
    const portada = byOrden.find(i => i.imaOrden === 1) ?? byOrden[0];
    return portada?.imaUrl ?? null;
  }

  // Guardar fecha de entrega real
  guardarFechaEntregaReal(): void {
    const vd = this.venta();
    const fecha = this.fechaEntregaRealInput();
    const ymd = formatFechaYYYYMMDD(fecha);
    if (!vd || !ymd) return;

    // Backend espera string, enviamos 'YYYY-MM-DD'
    const payload: Partial<VentaDetalleDTO> = {
      cvdFechaEntregaReal: ymd
    };

    this.guardando.set(true);
    this.#ventaDetService.update(vd.cvdId, payload).subscribe({
      next: () => {
        this.ventaResource.reload();
      },
      error: () => {},
      complete: () => this.guardando.set(false)
    });
  }

  volver(): void {
    this.#router.navigate(['/ventas']);
  }
}
