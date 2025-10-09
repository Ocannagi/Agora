import { Injector, Resource } from "@angular/core";

export interface IServiceAutocompletar<T> {
  autocompletarResource: (
    keyword: () => string | null,
    injector: Injector,
    idDependenciaPadre?: () => number | null
  ) => Resource<T[]>;
}