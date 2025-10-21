import { IIndiceEntidadDTO } from "../../compartidos/modelo/IIndiceEntidadDTO";
import { ImagenAntiguedadDTO } from "../../ImagenesAntiguedad/modelo/ImagenAntiguedadDTO";
import { PeriodoDTO } from "../../periodos/modelo/PeriodoDTO";
import { SubcategoriaDTO } from "../../subcategoria/modelo/subcategoriaDTO";
import { UsuarioDTO } from "../../usuarios/modelo/usuarioDTO";

// Equivalente TS de TipoEstadoEnum.php
export enum TipoEstadoEnum {
  ALaVenta = 'VE',
  TasadoDigital = 'TD',
  TasadoInSitu = 'TI',
  Comprado = 'CO',
  RetiradoDisponible = 'RD',
  RetiradoNoDisponible = 'RN',
}

export const TipoEstado = {
  ALaVenta: (): TipoEstadoEnum => TipoEstadoEnum.ALaVenta,
  TasadoDigital: (): TipoEstadoEnum => TipoEstadoEnum.TasadoDigital,
  TasadoInSitu: (): TipoEstadoEnum => TipoEstadoEnum.TasadoInSitu,
  Comprado: (): TipoEstadoEnum => TipoEstadoEnum.Comprado,
  RetiradoDisponible: (): TipoEstadoEnum => TipoEstadoEnum.RetiradoDisponible,
  RetiradoNoDisponible: (): TipoEstadoEnum => TipoEstadoEnum.RetiradoNoDisponible,

  isRetiradoDisponible: (t: TipoEstadoEnum): boolean => t === TipoEstadoEnum.RetiradoDisponible,
  isTasadoDigital: (t: TipoEstadoEnum): boolean => t === TipoEstadoEnum.TasadoDigital,
  isHabilitadoParaVenta: (t: TipoEstadoEnum): boolean =>
    t === TipoEstadoEnum.RetiradoDisponible ||
    t === TipoEstadoEnum.TasadoDigital ||
    t === TipoEstadoEnum.TasadoInSitu,
} as const;

// Equivalente a AntiguedadDTO.php
export interface AntiguedadDTO {
  antId: number;
  periodo: PeriodoDTO;
  subcategoria: SubcategoriaDTO;
  antDescripcion: string;
  imagenes?: ImagenAntiguedadDTO[] | null;
  usuario: UsuarioDTO;
  tipoEstado: TipoEstadoEnum;
  antFechaEstado?: string | null;
}

// Equivalente a AntiguedadCreacionDTO.php
export interface AntiguedadCreacionDTO {
  perId: number;
  scatId: number;
  antDescripcion: string;
  usrId: number;
  // En PHP tiene valor por defecto (RetiradoDisponible). En el frontend puede omitirse.
  //tipoEstado?: TipoEstadoEnum;
}

export interface AntiguedadIndiceDTO  extends AntiguedadDTO, IIndiceEntidadDTO {
}