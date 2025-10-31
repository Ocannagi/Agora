// DTOs equivalentes a: CompraVentaCreacionDTO.php, CompraVentaDetalleCreacionDTO.php,
// CompraVentaDTO.php y CompraVentaDetalleDTO.php

import type { UsuarioDTO } from '../../usuarios/modelo/usuarioDTO';
import type { DomicilioDTO } from '../../domicilios/modelo/domicilioDTO';
import type { AntiguedadALaVentaDTO } from '../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO';
import { IIndiceEntidadDTO } from '../../compartidos/modelo/IIndiceEntidadDTO';


export enum TipoMedioPagoEnum {
  TarjetaCredito = 'TC',
  TransferenciaBancaria = 'TB',
  MercadoPago = 'MP',
}

// Referencias mínimas por ID para creaciones (el backend acepta objeto o solo ID)
export type UsuarioRef = Pick<UsuarioDTO, 'usrId'>;
export type DomicilioRef = Pick<DomicilioDTO, 'domId'>;
export type AntiguedadALaVentaRef = Pick<AntiguedadALaVentaDTO, 'aavId'>;

/**
 * CompraVentaDetalleCreacionDTO
 * - cvdFechaEntregaPrevista en formato 'Y-m-d H:i:s'
 */
export interface CompraVentaDetalleCreacionDTO {
  antiguedadAlaVenta: AntiguedadALaVentaRef | AntiguedadALaVentaDTO;
  cvdFechaEntregaPrevista: string;
}

/**
 * CompraVentaCreacionDTO
 */
export interface CompraVentaCreacionDTO {
  usuarioComprador: UsuarioRef | UsuarioDTO;
  domicilioDestino: DomicilioRef | DomicilioDTO;
  covTipoMedioPago: TipoMedioPagoEnum;
  detalles: CompraVentaDetalleCreacionDTO[];
}

/**
 * CompraVentaDetalleDTO
 * - cvdFechaEntregaPrevista/Real en formato 'Y-m-d H:i:s'
 */
export interface CompraVentaDetalleDTO {
  cvdId: number;
  covId: number;
  antiguedadAlaVenta: AntiguedadALaVentaDTO;
  cvdFechaEntregaPrevista: string;
  cvdFechaEntregaReal: string | null;
}

/**
 * CompraVentaDTO
 * - covFechaCompra en formato 'Y-m-d H:i:s'
 */
export interface CompraVentaDTO {
  covId: number;
  usuarioComprador: UsuarioDTO;
  domicilioDestino: DomicilioDTO;
  covFechaCompra: string;
  covTipoMedioPago: TipoMedioPagoEnum;
  detalles: CompraVentaDetalleDTO[];
}

export type CompraDatosDTO = Pick<CompraVentaDTO, 'covId' | 'covFechaCompra' | 'covTipoMedioPago' | 'domicilioDestino'>;
export type VentaDatosDTO = Pick<AntiguedadALaVentaDTO,'vendedor' | 'antiguedad' | 'aavPrecioVenta' >;

export interface CompraDTO extends VentaDatosDTO, CompraDatosDTO{}

export interface IndiceCompraDTO {
  
}


export interface CompraIndiceDTO extends CompraDTO, IIndiceEntidadDTO {}

export function convertToCompraIndiceDTO(compra: CompraVentaDTO): CompraIndiceDTO[] {
  if (!compra || !Array.isArray(compra.detalles) || compra.detalles.length === 0) {
    return [];
  }

  return compra.detalles.map((det) => {
    const aav = det.antiguedadAlaVenta;

    const item: CompraIndiceDTO = {
      // CompraDTO (CompraDatosDTO + VentaDatosDTO)
      covId: compra.covId,
      covFechaCompra: compra.covFechaCompra,
      covTipoMedioPago: compra.covTipoMedioPago,
      domicilioDestino: compra.domicilioDestino,
      vendedor: aav.vendedor,
      antiguedad: aav.antiguedad,
      aavPrecioVenta: aav.aavPrecioVenta,

      // IIndiceEntidadDTO
      id: compra.covId,
      nombre: `Vendedor: ${aav.vendedor.usrRazonSocialFantasia?.trim() ?? (aav.vendedor.usrNombre + ' ' + aav.vendedor.usrApellido).trim()}`,
      acciones: {
        editar: `/compras-ventas/editar/${compra.covId}`
      }
    };

    return item;
  });
}