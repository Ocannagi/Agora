import { CategoriaDTO } from "../../categorias/modelo/CategoriaDTO";
import { IAutocompletarDTO } from "../../compartidos/modelo/IAutocompletarDTO";

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