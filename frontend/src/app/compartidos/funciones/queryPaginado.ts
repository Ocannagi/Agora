import { HttpParams } from "@angular/common/http";
import { PaginadoRequestDTO } from "../modelo/PaginadoRequestDTO";

export function buildQueryPaginado(paginado: PaginadoRequestDTO): HttpParams {
  let httpParams = new HttpParams();
  Object.keys(paginado).forEach(key => {
    const value = paginado[key as keyof PaginadoRequestDTO];
    httpParams = httpParams.append(`paginado[${key}]`, value);
  });
  return httpParams;
}
