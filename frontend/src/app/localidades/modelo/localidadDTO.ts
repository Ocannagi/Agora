import { IAutocompletarDTO } from '../../compartidos/modelo/IAutocompletarDTO';
import { ProvinciaDTO } from '../../provincias/modelo/provinciaDTO';
import { IIndiceEntidadDTO } from "../../compartidos/modelo/IIndiceEntidadDTO";

export interface LocalidadDTO {
  locId: number;
  locDescripcion: string;
  provincia: ProvinciaDTO;
}

export interface LocalidadAutocompletarDTO extends IAutocompletarDTO {}

export interface LocalidadCreacionDTO {
  locDescripcion: string;
  provincia: ProvinciaDTO;
}

export interface LocalidadMinDTO {
  locId: number;
  locDescripcion: string;
  provincia: ProvinciaDTO;
}

export interface LocalidadIndiceDTO extends IIndiceEntidadDTO, LocalidadMinDTO {}