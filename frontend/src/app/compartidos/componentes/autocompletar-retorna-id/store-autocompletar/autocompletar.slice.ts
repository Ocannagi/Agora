import { Resource, ResourceStatus, signal } from "@angular/core";
import { FormControlSignal } from "../../../funciones/formToSignal";
import { FormControl } from "@angular/forms";
import { IAutocompletarDTO } from "../../../interfaces/IAutocompletarDTO";


export interface AutocompletarSlice {
    readonly keyword: string;
    readonly idDependenciaPadre: number | null;
    readonly modelId: number | null;
    readonly errors: string[];
    readonly formControlSignal: FormControlSignal<string | null>;
}

export const autocompletarInitialState: AutocompletarSlice = {
    keyword: '',
    idDependenciaPadre: null,
    modelId: null,
    errors: [],
    formControlSignal: {
        value: signal<string | null>(null),
        status: signal<string>('INVALID'),
        disabled: signal<boolean>(false),
        control: new FormControl<string | null>(null)
    },
};