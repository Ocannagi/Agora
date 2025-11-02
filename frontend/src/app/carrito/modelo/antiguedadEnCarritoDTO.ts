import { AntiguedadALaVentaDTO } from "../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO";
import { UsuarioDTO } from "../../usuarios/modelo/usuarioDTO";

export interface AntiguedadEnCarritoDTO{
    hayStock: boolean;
    cambioPrecio: boolean;
    antiguedadAlaVenta: AntiguedadALaVentaDTO;
}

export interface GrupoVendedor {
  vendedor: UsuarioDTO;
  items: AntiguedadEnCarritoDTO[];
};
