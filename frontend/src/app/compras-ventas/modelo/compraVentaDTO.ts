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


export interface CompraIndiceDTO extends CompraVentaDTO, IIndiceEntidadDTO {}


