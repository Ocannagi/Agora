import { IAutocompletarDTO } from "../../compartidos/modelo/IAutocompletarDTO";

export interface PeriodoDTO {
  perId: number;
  perDescripcion: string;
}

export interface PeriodoCreacionDTO {
  perDescripcion: string;
}

export interface PeriodoAutocompletarDTO extends IAutocompletarDTO {}