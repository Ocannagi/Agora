import { Resource, ResourceStatus, signal } from "@angular/core";
import { FormControlSignals } from "../../../funciones/formToSignal";
import { FormControl } from "@angular/forms";


export interface AutocompletarSlice {
    readonly keyword: string;
    readonly modelId: number | null;
    readonly errors: string[];
    readonly formControlSignal: FormControlSignals<string | null>;
}

export const autocompletarInitialState: AutocompletarSlice = {
    keyword: '',
    modelId: null,
    errors: [],
    formControlSignal: {
        value: signal<string | null>(null),
        status: signal<string>('INVALID'),
        disabled: signal<boolean>(false),
        control: new FormControl<string | null>(null)
    }
};