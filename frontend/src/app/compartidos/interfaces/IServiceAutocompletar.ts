import { Injector, Resource } from "@angular/core";

export interface IServiceAutocompletar<IAutocompletarDTO> {
  autocompletarResource: (
    keyword: () => string | null,
    injector: Injector,
    idDependenciaPadre?: () => number | null
  ) => Resource<IAutocompletarDTO[]>;
}