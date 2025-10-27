import { AntiguedadDTO } from "../../antiguedades/modelo/AntiguedadDTO";
import { IIndiceEntidadDTO } from "../../compartidos/modelo/IIndiceEntidadDTO";
import { DomicilioDTO } from "../../domicilios/modelo/domicilioDTO";
import { TasacionDigitalDTO } from "../../tasaciones-digitales/modelo/tasacionDigitalDTO";
import { UsuarioDTO } from "../../usuarios/modelo/usuarioDTO";

export interface AntiguedadALaVentaCreacionDTO {
  antiguedad: AntiguedadDTO;
  vendedor: UsuarioDTO;
  domicilioOrigen: DomicilioDTO;
  aavPrecioVenta: number;
  tasacion?: TasacionDigitalDTO | null;
}

export interface AntiguedadALaVentaDTO {
  aavId: number;
  antiguedad: AntiguedadDTO;
  vendedor: UsuarioDTO;
  domicilioOrigen: DomicilioDTO;
  aavPrecioVenta: number;
  tasacion?: TasacionDigitalDTO | null;
  aavFechaPublicacion: string;
  aavFechaRetiro?: string | null;
  aavHayVenta: boolean;
}

export interface AntiguedadALaVentaIndiceDTO extends AntiguedadALaVentaDTO, IIndiceEntidadDTO {
}