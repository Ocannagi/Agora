import { Injectable } from '@angular/core';
import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

@Injectable({
  providedIn: 'root'
})
/**
 * @description Retorna funciones custom de validación para usar en los formularios
 */
export class ValidaForm {
   /**
     * @description Valida que el valor que recibe el control empiece con letra mayúscula
     * @returns Un error map de tipo ValidationErrors con key "primeraLetraMayuscula" y value {msg: "La primera letra debe ser mayúscula" si falla la validación}; si no, retorna null.
     */
    primeraLetraMayuscula(): ValidatorFn {
        return (control : AbstractControl) : ValidationErrors | null => {
            const valor = <string>control.value;
    
            if (!valor) return null;
            else if (valor.length === 0) return null;
    
    
            const primeraLetra = valor[0];
    
            if (primeraLetra !== primeraLetra.toUpperCase()){    
    /*             const unValidationError : ValidationErrors  = {  // ValidationErrors es un type (un alias para un tipo u objeto simple; type es palabra reservada propia de TypeScript)
                    primeraLetraMayuscula : {
                        mensaje: 'La primera letra debe ser mayúscula'
                    }
                }
    
                return  unValidationError; */
    
                return {  
                    primeraLetraMayuscula : {
                        msg: 'La primera letra debe ser mayúscula.'
                    }
                }
            }
            else return null;
        }

    }

    /**
     * @description Valida que la fecha que recibe el control no sea mayor a hoy
     * @returns Un error map de tipo ValidationErrors con key "futuro" y value {msg: "La fecha no puede ser mayor a hoy}; si no, retorna null.
     */

    fechaNoPuedeSerFutura():ValidatorFn{
        return (control : AbstractControl) : ValidationErrors | null => {
            const fechaControl = new Date(control.value);
            const hoy = new Date();
            if(fechaControl>hoy){
                return {
                    futuro:{
                        msg:'La fecha no puede ser mayor a hoy'
                    }
                }
            } else{
                return null;
            }
        }
    }


    /**
     * 
     * @param controles Diccionario de AbstractControl: debe ser el FormGroup.controls
     * @param campo nombre del control
     * @returns todos los mensajes de error agrupados en un único string o null si no hay errores
     */
    obtenerErrorCampo(controles : {[key: string]: AbstractControl<any, any>} ,campo : string) : string | null {
        let control = controles[campo];
        if(control === undefined || control === null)
          throw Error("Error interno: no existe el campo evaluado en el formulario");
    
        if(control.errors)
        {
          let msg : string = Object.keys(control.errors).reduce((acumulador : string, valorActual : string) =>{
            if(valorActual === 'required')
            {
               return acumulador + `El campo ${campo} es requerido.` + ' ';
            } else if (valorActual === 'maxlength') {
                return acumulador + `El campo ${campo} no puede tener más de ${control.getError(valorActual).requiredLength} caracteres.` + ' ';
            } else if (valorActual === 'email'){
                return acumulador + 'El email ingresado no es válido' + ' ';
            } else if (control.getError(valorActual).msg){
                return acumulador + control.getError(valorActual).msg + ' ';
            } else {
                return acumulador + `El campo ${campo} tuvo un error no definido.` + ' ';
            }
          },'')
    
          if(msg)
            return msg.trim().replaceAll(/ {2,}/g,' ');
          else
            return `El campo ${campo} tuvo un error`;
        } else{
          return null;
        }
    }
}
