import { effect, inject, Injectable, Injector } from '@angular/core';
import { AbstractControl, FormControl, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';

@Injectable({
    providedIn: 'root'
})
/**
 * @description Retorna funciones custom de validación para usar en los formularios
 */
export class ValidaControlForm {
    /**
      * @description Valida que el valor que recibe el control empiece con letra mayúscula en cada palabra
      * @returns Un error map de tipo ValidationErrors con key "cadaPrimeraLetraMayuscula" y value {msg: "La primera letra debe ser mayúscula en cada palabra"} si falla la validación}; si no, retorna null.
      */
    cadaPrimeraLetraMayuscula(): ValidatorFn {
        return (control: AbstractControl): ValidationErrors | null => {
            const valor = String(control.value ?? '')
                .trim()
                .replace(/\s{2,}/g, ' ');

            if (!valor) return null;
            else if (valor.length === 0) return null;

            const arrayPalabras = valor.split(' ');

            for (let palabra of arrayPalabras) {

                const primeraLetra = palabra[0];

                if (primeraLetra !== primeraLetra.toUpperCase()) {
                    /*             const unValidationError : ValidationErrors  = {  // ValidationErrors es un type (un alias para un tipo u objeto simple; type es palabra reservada propia de TypeScript)
                                    primeraLetraMayuscula : {
                                        mensaje: 'La primera letra debe ser mayúscula'
                                    }
                                }
                    
                                return  unValidationError; */

                    return {
                        cadaPrimeraLetraMayuscula: {
                            msg: 'La primera letra debe ser mayúscula en cada palabra.'
                        }
                    }
                }
            }

            return null;

        }
    }
    /**
     * @description Valida que la fecha que recibe el control no sea mayor a hoy
     * @returns Un error map de tipo ValidationErrors con key "futuro" y value {msg: "La fecha no puede ser mayor a hoy}; si no, retorna null.
     */

    fechaNoPuedeSerFutura(): ValidatorFn {
        return (control: AbstractControl): ValidationErrors | null => {
            const fechaControl = new Date(control.value);
            const hoy = new Date();
            if (fechaControl > hoy) {
                return {
                    futuro: {
                        msg: 'La fecha no puede ser mayor a hoy'
                    }
                }
            } else {
                return null;
            }
        }
    }



    apellidoNombreValido(): ValidatorFn {
        const regex = /^[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ][a-zñäëïöüáéíóúâêîôûàèìòù'-]*(?:[a-zñäëïöüáéíóúâêîôûàèìòù']\s?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ][a-zñäëïöüáéíóúâêîôûàèìòù'-]*)*$/u;

        return (control: AbstractControl): ValidationErrors | null => {
            const valor = String(control.value ?? '')
                .trim()
                .replace(/\s{2,}/g, ' ');

            if (valor === '') return null; // Validators.required se encarga de vacío
            return regex.test(valor) ? null : { apellidoNombre: { msg: 'El formato del apellido o nombre no es válido' } };
        };
    }

    entero(): ValidatorFn {
        return (control: AbstractControl): ValidationErrors | null => {
            const v = control.value;
            if (v === null || v === undefined || v === '') return null; // que required se encargue del vacío
            const n = typeof v === 'string' ? Number(v) : v;
            return Number.isInteger(n) ? null : { entero: { msg: 'El valor debe ser un número entero' } };
        };
    }

    /** Validador para CUIT/CUIL equivalente al de PHP (_esCuitCuilValido) */
    cuitCuilValido(): ValidatorFn {
        return (control: AbstractControl): ValidationErrors | null => {
            const valor = String(control.value ?? '').trim();
            if (valor === '') return null; // Validators.required maneja vacío
            return this._esCuitCuilValido(valor)
                ? null
                : { cuitCuil: { msg: 'El CUIT/CUIL no es válido' } };
        };
    }

    /** Lógica de validación equivalente a _esCuitCuilValido (PHP) */
    private _esCuitCuilValido(cuilCuit: string): boolean {
        if (!/^\d{11}$/.test(cuilCuit)) return false; // exactamente 11 dígitos

        const prefijo = Number(cuilCuit.slice(0, 2));
        const prefijosValidos = new Set([20, 23, 24, 25, 26, 27, 30, 33, 34]);
        if (!prefijosValidos.has(prefijo)) return false;

        const pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        let sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += Number(cuilCuit[i]) * pesos[i];
        }
        const resto = sum % 11;
        const dv = Number(cuilCuit[10]);

        if (resto === 0) {
            return dv === 0;
        } else {
            return dv === 11 - resto;
        }
    }


    /**
     * 
     * @param controles Diccionario de AbstractControl: debe ser el FormGroup.controls
     * @param campo nombre del control
     * @returns todos los mensajes de error agrupados en un único string o null si no hay errores
     */
    obtenerErrorCampoGroup(controles: { [key: string]: AbstractControl<any, any> }, campo: string): string | null {
        let control = controles[campo];
        if (control === undefined || control === null)
            throw Error("Error interno: no existe el campo evaluado en el formulario");

        if (control.errors) {
            return this.obtenerErrorControl(control, campo);
        } else {
            return null;
        }
    }

    obtenerErrorControl<T extends AbstractControl>(control: T, nombreCampo: string): string | null {
        if (!control || !control.errors) return null;

        let msg: string = Object.keys(control.errors).reduce((acumulador: string, valorActual: string) => {
            if (valorActual === 'required') {
                return acumulador + `El campo ${nombreCampo} es requerido.` + ' ';
            } else if (valorActual === 'maxlength') {
                return acumulador + `El campo ${nombreCampo} no puede tener más de ${control.getError(valorActual).requiredLength} caracteres.` + ' ';
            } else if (valorActual === 'email') {
                return acumulador + 'El email ingresado no es válido' + ' ';
            } else if (valorActual === 'minlength') {
                return acumulador + `El campo ${nombreCampo} debe tener al menos ${control.getError(valorActual).requiredLength} caracteres.` + ' ';
            } else if (valorActual === 'pattern') {
                return acumulador + `El formato del campo ${nombreCampo} no es válido.` + ' ';
            } else if (valorActual === 'min') {
                return acumulador + `El valor mínimo permitido para ${nombreCampo} es ${control.getError(valorActual).min}.` + ' ';
            } else if (valorActual === 'max') {
                return acumulador + `El valor máximo permitido para ${nombreCampo} es ${control.getError(valorActual).max}.` + ' ';
            } else if (control.getError(valorActual).msg) {
                return acumulador + control.getError(valorActual).msg + ' ';
            } else if (valorActual === 'matDatepickerParse') {
                return acumulador + `La fecha o el formato del campo ${nombreCampo} no son válidos.` + ' ';
            } else {
                return acumulador + `El campo ${nombreCampo} tuvo un error no definido.` + ' ';
            }
        }, '')

        if (msg)
            return msg.trim().replaceAll(/ {2,}/g, ' ');
        else
            return `El campo ${nombreCampo} tuvo un error`;
    }

    requeridoIfEffect(requerido: () => boolean, control: FormControl, injector = inject(Injector)) {
        effect(() => {
            const req = requerido();
            if (req) control.addValidators(Validators.required);
            else control.removeValidators(Validators.required);
            control.updateValueAndValidity({ onlySelf: true, emitEvent: false });
        }, { injector: injector });
    }

}