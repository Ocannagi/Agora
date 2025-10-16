import { Injector, Resource, ResourceRef } from "@angular/core";

export interface IServiceAutocompletar<IAutocompletarDTO> {
  autocompletarResource: (
    keyword: () => string | null,
    injector: Injector,
    idDependenciaPadre?: () => number | null,
    selectedId?: () => number | null
  ) => ResourceRef<IAutocompletarDTO[]>;

  //getByIdAutocompletarResource: (id: () => number | null, injector: Injector) => ResourceRef<IAutocompletarDTO>;
}