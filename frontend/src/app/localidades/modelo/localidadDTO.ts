import { IAutocompletarDTO } from '../../compartidos/interfaces/IAutocompletarDTO';
import { ProvinciaDTO } from '../../provincias/modelo/provinciaDTO';

export interface LocalidadDTO {
  locId: number;
  locDescripcion: string;
  provincia: ProvinciaDTO;
}

export interface LocalidadAutocompletarDTO extends IAutocompletarDTO {}
