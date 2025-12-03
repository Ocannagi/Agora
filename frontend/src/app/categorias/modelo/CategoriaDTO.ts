import { IAutocompletarDTO } from "../../compartidos/modelo/IAutocompletarDTO";
import { IIndiceEntidadDTO } from "../../compartidos/modelo/IIndiceEntidadDTO";

export interface CategoriaDTO {
  catId: number;
  catDescripcion: string;
}

export interface CategoriaCreacionDTO {
  catDescripcion: string;
}

export interface CategoriaAutocompletarDTO extends IAutocompletarDTO {}

export interface CategoriaMinDTO {
  catId: number;
  catDescripcion: string;
}

export interface CategoriaIndiceDTO extends IIndiceEntidadDTO, CategoriaMinDTO {}