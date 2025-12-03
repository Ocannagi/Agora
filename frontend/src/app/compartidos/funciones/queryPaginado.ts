import { HttpParams } from "@angular/common/http";
import { PaginadoRequestDTO, PaginadoRequestSearchDTO } from "../modelo/PaginadoRequestDTO";

export function buildQueryPaginado(paginado: PaginadoRequestDTO): HttpParams {
  let httpParams = new HttpParams();
  Object.keys(paginado).forEach(key => {
    const value = paginado[key as keyof PaginadoRequestDTO];
    httpParams = httpParams.append(`paginado[${key}]`, value);
  });
  return httpParams;
}


export function buildQueryPaginadoSearch(paginado: PaginadoRequestSearchDTO): HttpParams {
  let httpParams = new HttpParams();
  Object.keys(paginado).forEach(key => {
    const value = paginado[key as keyof PaginadoRequestSearchDTO];
    httpParams = httpParams.append(`paginadoSearch[${key}]`, value);
  });
  return httpParams;
}

export function buildQueryPaginadoExtra(paginado: PaginadoRequestDTO, extraParams: {[key: string]: any}): HttpParams {
  let httpParams = new HttpParams();
  Object.keys(paginado).forEach(key => {
    const value = paginado[key as keyof PaginadoRequestDTO];
    httpParams = httpParams.append(`paginado[${key}]`, value);
  });
  Object.keys(extraParams).forEach(key => {
    const value = extraParams[key];
    httpParams = httpParams.append(`paginado[${key}]`, value);
  });
  return httpParams;
}