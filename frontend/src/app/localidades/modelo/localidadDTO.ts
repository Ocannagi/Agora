import { ProvinciaDTO } from '../../provincias/modelo/provinciaDTO';

export interface LocalidadDTO {
  locId: number;
  locDescripcion: string;
  provincia: ProvinciaDTO;
}