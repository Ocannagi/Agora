import { Injectable } from '@angular/core';
import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

@Injectable({
  providedIn: 'root'
})
export class ValidaGroupForm {

  razonSocialRequiredIfCuitValid: ValidatorFn = (group: AbstractControl): ValidationErrors | null => {
    const cuit = group.get('cuitCuil');
    const razon = group.get('razonSocial');
    if (!cuit || !razon) return null;

    const cuitVal = String(cuit.value ?? '').trim();
    const necesitaRazon = cuitVal !== '' && cuit.valid;

    const razonVal = String(razon.value ?? '').trim();
    if (necesitaRazon && razonVal === '') {
      // agrega 'required' sin pisar otros errores
      razon.setErrors({ ...(razon.errors ?? {}), required: true });
    } else if (razon.errors?.['required']) {
      // quita solo 'required' si ya no aplica
      const { required, ...rest } = razon.errors;
      razon.setErrors(Object.keys(rest).length ? rest : null);
    }
    return null;
  };
  
}
