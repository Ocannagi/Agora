import { IAutocompletarDTO } from "../../compartidos/interfaces/IAutocompletarDTO";

export interface ProvinciaDTO {
  provId: number;
  provDescripcion: string;
}

export interface ProvinciaAutocompletarDTO extends IAutocompletarDTO {}