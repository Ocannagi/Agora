import { CategoriaDTO } from "../../categorias/modelo/CategoriaDTO";
import { IAutocompletarDTO } from "../../compartidos/modelo/IAutocompletarDTO";
import { IIndiceEntidadDTO } from "../../compartidos/modelo/IIndiceEntidadDTO";

export interface SubcategoriaDTO {
  scatId: number;
  categoria: CategoriaDTO;
  scatDescripcion: string;
}

export interface SubcategoriaCreacionDTO {
  scatDescripcion: string;
  categoria: CategoriaDTO;
}

export interface SubcategoriaAutocompletarDTO extends IAutocompletarDTO {}

export interface SubcategoriaMinDTO {
  scatId: number;
  categoria: CategoriaDTO;
  scatDescripcion: string;
}

export interface SubcategoriaIndiceDTO extends IIndiceEntidadDTO, SubcategoriaMinDTO {}