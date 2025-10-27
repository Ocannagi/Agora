import { HttpParams } from "@angular/common/http";

export function buildQueryParams(params: Record<string, unknown>): HttpParams {
  let httpParams = new HttpParams();

  for (const key in params) {
    if (!Object.hasOwn(params, key)) continue;

    const element = params[key];
    if (element === undefined || element === null) continue;

    if (Array.isArray(element)) {
      // params[key][]=v1&params[key][]=v2  -> PHP: $_GET['params']['key'] es array
      for (const v of element) {
        httpParams = httpParams.append(`params[${key}][]`, String(v));
      }
    } else {
      httpParams = httpParams.append(`params[${key}]`, String(element));
    }
  }

  return httpParams;
}
