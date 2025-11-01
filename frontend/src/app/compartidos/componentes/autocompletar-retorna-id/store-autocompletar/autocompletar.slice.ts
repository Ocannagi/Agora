import { signal } from "@angular/core";
import { FormControlSignal } from "../../../funciones/formToSignal";
import { FormControl } from "@angular/forms";
import { IAutocompletarDTO } from "../../../modelo/IAutocompletarDTO";


export interface AutocompletarSlice {
    readonly keyword: string;
    readonly keywordExterno: string;
    readonly hayDependenciaPadre: boolean;
    readonly idDependenciaPadre: number | null;
    readonly modelId: number | null; // output model ID
    readonly formControlSignal: FormControlSignal<IAutocompletarDTO | null>;
    readonly usuarioInteractuo: boolean;
}

export const autocompletarInitialState: AutocompletarSlice = {
    keyword: '',
    keywordExterno: '',
    hayDependenciaPadre: false,
    idDependenciaPadre: null,
    modelId: null,
    formControlSignal: {
        value: signal<IAutocompletarDTO | null>(null),
        status: signal<string>('INVALID'),
        disabled: signal<boolean>(false),
        control: new FormControl<IAutocompletarDTO | null>(null)
    },
    usuarioInteractuo: false
};