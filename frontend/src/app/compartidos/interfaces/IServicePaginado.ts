import { Injector, ResourceRef } from "@angular/core";
import { PaginadoResponseDTO } from "../modelo/PaginadoResponseDTO";
import { PaginadoRequestDTO } from "../modelo/PaginadoRequestDTO";
import { IServiceCrud } from "./IServiceCrud";

export interface IServicePaginado<IIndiceEntidadDTO> extends IServiceCrud<any, any> {
  getAllPaginado(
    paginado: () => PaginadoRequestDTO,
    injector?: Injector
  ): ResourceRef<PaginadoResponseDTO<IIndiceEntidadDTO>>;
  
}