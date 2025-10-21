import { Injector, ResourceRef, WritableSignal } from "@angular/core";
import { Observable } from "rxjs";

export interface IServiceCrud<TCreacionDTO, TDTO> {
    postError: WritableSignal<string | null>;
    patchError: WritableSignal<string | null>;
    deleteError: WritableSignal<string | null>;

    getAllResource: (injector: Injector) => ResourceRef<TDTO[]>;
    getByIdResource: (id: () => number | null, injector: Injector) => ResourceRef<TDTO>;
    create: (data: TCreacionDTO) => Observable<number>;
    update: (id: number, data: TCreacionDTO) => Observable<void>;
    delete: (id: number) => Observable<[]>;
}