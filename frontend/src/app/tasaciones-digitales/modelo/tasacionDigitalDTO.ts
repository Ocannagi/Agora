import { AntiguedadDTO } from "../../antiguedades/modelo/AntiguedadDTO";
import { TasacionInSituDTO } from "../../tasaciones-inSitu/tasacionInSitu";
import { UsuarioDTO } from "../../usuarios/modelo/usuarioDTO";

/**
 * Equivalente TS de TasacionDigitalCreacionDTO (PHP).
 * Solo referencias a entidades.
 */
export interface TasacionDigitalCreacionDTO {
  tasador: UsuarioDTO;
  propietario: UsuarioDTO;
  antiguedad: AntiguedadDTO;
}

/**
 * Equivalente TS de TasacionDigitalDTO (PHP).
 */
export interface TasacionDigitalDTO {
  tadId: number;
  tasador: UsuarioDTO;
  propietario: UsuarioDTO;
  antiguedad: AntiguedadDTO;
  tadFechaSolicitud: string;
  tadFechaTasDigitalRealizada?: string | null;
  tadFechaTasDigitalRechazada?: string | null;
  tadObservacionesDigital?: string | null;
  tadPrecioDigital?: number | null;
  tasacionInSitu?: TasacionInSituDTO | null;
}
