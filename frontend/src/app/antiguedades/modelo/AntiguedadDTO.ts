import { IIndiceEntidadDTO } from "../../compartidos/modelo/IIndiceEntidadDTO";
import { ImagenAntiguedadDTO } from "../../imagenes-antiguedad/modelo/ImagenAntiguedadDTO";
import { PeriodoDTO } from "../../periodos/modelo/PeriodoDTO";
import { SubcategoriaDTO } from "../../subcategorias/modelo/subcategoriaDTO";
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

  obtenerKeyPorValor: <T extends Record<string, string | number>>(
    enumObj: T ,
    value: T[keyof T]
  ): keyof T | undefined => {

    const separarCamelCaseComoTexto = (texto: string): string =>(texto.match(/([A-Z][a-z]*)/g) || []).join(" ");
    const result = (Object.keys(enumObj) as Array<keyof T>).find(key => enumObj[key] === value);
    return result ? separarCamelCaseComoTexto(String(result)) as keyof T : undefined;
  },

  convertStringToEnum: (value: string): TipoEstadoEnum | null => {
    const valores = Object.values(TipoEstadoEnum);
    return valores.includes(value as TipoEstadoEnum) ? (value as TipoEstadoEnum) : null;
  },

  isRetiradoDisponible: (t: TipoEstadoEnum): boolean => t === TipoEstadoEnum.RetiradoDisponible,
  isTasadoDigital: (t: TipoEstadoEnum): boolean => t === TipoEstadoEnum.TasadoDigital,
  isHabilitadoParaVenta: (t: TipoEstadoEnum): boolean =>
    t === TipoEstadoEnum.RetiradoDisponible ||
    t === TipoEstadoEnum.TasadoDigital ||
    t === TipoEstadoEnum.TasadoInSitu,
  isAlaVenta: (t: TipoEstadoEnum): boolean => t === TipoEstadoEnum.ALaVenta,
} as const;

// Equivalente a AntiguedadDTO.php
export interface AntiguedadDTO {
  antId: number;
  periodo: PeriodoDTO;
  subcategoria: SubcategoriaDTO;
  antNombre: string;
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
  antNombre: string;
  antDescripcion: string;
  usrId: number;
  // En PHP tiene valor por defecto (RetiradoDisponible). En el frontend puede omitirse.
  //tipoEstado?: TipoEstadoEnum;
}

export interface AntiguedadIndiceDTO extends AntiguedadDTO, IIndiceEntidadDTO {
}