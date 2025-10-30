import { AntiguedadALaVentaDTO } from "../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO";

export interface AntiguedadEnCarritoDTO{
    hayStock: boolean;
    cambioPrecio: boolean;
    antiguedadAlaVenta: AntiguedadALaVentaDTO;
}