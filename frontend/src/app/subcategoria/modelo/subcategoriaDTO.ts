import { CategoriaDTO } from "../../categoria/modelo/CategoriaDTO";

export interface SubcategoriaDTO {
  scatId: number;
  categoria: CategoriaDTO;
  scatDescripcion: string;
}

export interface SubcategoriaCreacionDTO {
  scatDescripcion: string;
  categoria: CategoriaDTO;
}