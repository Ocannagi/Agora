import { HttpParams } from "@angular/common/http";

export function buildQueryParams(params: Record<string, any>): HttpParams {
  let httpParams = new HttpParams();
  Object.keys(params).forEach(key => {
    httpParams = httpParams.append(`params[${key}]`, params[key]);
  });
  return httpParams;
}
