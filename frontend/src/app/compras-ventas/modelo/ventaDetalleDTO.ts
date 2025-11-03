import { AntiguedadALaVentaDTO } from '../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO';
import { IIndiceEntidadDTO } from '../../compartidos/modelo/IIndiceEntidadDTO';
import { DomicilioDTO } from '../../domicilios/modelo/domicilioDTO';
import { UsuarioDTO } from '../../usuarios/modelo/usuarioDTO';
import { TipoMedioPagoEnum } from './compraVentaDTO';

// Equivalente TypeScript de VentaDetalleDTO.php
export interface VentaDetalleDTO {
  cvdId: number; // CompraVentaDetalle ID
  covId: number; // CompraVenta ID
  usuarioComprador: UsuarioDTO; // Usuario comprador
  domicilioDestino: DomicilioDTO; // Domicilio de destino
  covFechaCompra: string; // 'YYYY-MM-DD HH:mm:ss'
  antiguedadAlaVenta: AntiguedadALaVentaDTO; // Antigüedad a la venta
  covTipoMedioPago: TipoMedioPagoEnum;
  cvdFechaEntregaPrevista: string; // 'YYYY-MM-DD HH:mm:ss'
  cvdFechaEntregaReal: string | null; // 'YYYY-MM-DD HH:mm:ss' o null
}

export interface VentaDetalleIndiceDTO extends VentaDetalleDTO, IIndiceEntidadDTO {}