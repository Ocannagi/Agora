import { IAutocompletarDTO } from "../../compartidos/modelo/IAutocompletarDTO";

export interface ProvinciaDTO {
  provId: number;
  provDescripcion: string;
}

export interface ProvinciaAutocompletarDTO extends IAutocompletarDTO {}