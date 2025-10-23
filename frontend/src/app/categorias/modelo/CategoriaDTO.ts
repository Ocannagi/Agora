import { IAutocompletarDTO } from "../../compartidos/modelo/IAutocompletarDTO";

export interface CategoriaDTO {
  catId: number;
  catDescripcion: string;
}

export interface CategoriaCreacionDTO {
  catDescripcion: string;
}

export interface CategoriaAutocompletarDTO extends IAutocompletarDTO {}