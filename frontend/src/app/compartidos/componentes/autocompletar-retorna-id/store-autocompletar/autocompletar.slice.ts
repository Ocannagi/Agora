import { signal } from "@angular/core";
import { FormControlSignal } from "../../../funciones/formToSignal";
import { FormControl } from "@angular/forms";
import { IAutocompletarDTO } from "../../../interfaces/IAutocompletarDTO";


export interface AutocompletarSlice {
    readonly keyword: string;
    readonly idDependenciaPadre: number | null;
    readonly modelId: number | null; // output model ID
    readonly formControlSignal: FormControlSignal<IAutocompletarDTO | null>;
}

export const autocompletarInitialState: AutocompletarSlice = {
    keyword: '',
    idDependenciaPadre: null,
    modelId: null,
    formControlSignal: {
        value: signal<IAutocompletarDTO | null>(null),
        status: signal<string>('INVALID'),
        disabled: signal<boolean>(false),
        control: new FormControl<IAutocompletarDTO | null>(null)
    }
};