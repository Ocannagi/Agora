import { signal, effect, WritableSignal, DestroyRef, inject, Inject, Injector } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { FormControl, FormGroup } from '@angular/forms';
import { startWith } from 'rxjs';

export interface FormControlSignal<T> {
  value: WritableSignal<T>;
  status: () => string;
  disabled: WritableSignal<boolean>;
  control: FormControl<T>;
}

export interface FormGroupSignal<T> {
  value: WritableSignal<T>;
  status: () => string;
  disabled: WritableSignal<boolean>;
  group: FormGroup;
}

export function formControlSignal<T>(control: FormControl<T>, injector: Injector = inject(Injector)): FormControlSignal<T> {

  // Value (incluye arranque)
  const value = signal<T>(control.value as T);
  const valueChangesControl = control.valueChanges
    .pipe(startWith(control.value))
    .subscribe(v => { if (value() !== v) value.set(v as T); });

  // Status
  const status = toSignal(control.statusChanges.pipe(startWith(control.status)), {
    initialValue: control.status,
    injector: injector
  });

  // Disabled state signal (writable)
  const disabled = signal<boolean>(control.disabled);

  // Propagar signal -> control (value)
  effect(() => {
    const v = value();
    if (control.value !== v) control.setValue(v, { emitEvent: false });
  }, { injector });

  // Propagar disabled -> control
  effect(() => {
    const d = disabled();
    if (d && control.enabled) control.disable({ emitEvent: false });
    else if (!d && control.disabled) control.enable({ emitEvent: false });
  }, { injector });

  injector.get(DestroyRef).onDestroy(() => valueChangesControl.unsubscribe());

  return { value, status, disabled, control };
}

export function formGroupSignal<T extends Record<string, any>>(
  formGroup: FormGroup,
  injector: Injector = inject(Injector)
): FormGroupSignal<T> {

  // Value usando getRawValue para incluir disabled
  const valueFormGroupSignal = signal<T>(formGroup.getRawValue() as T, { equal: deepEqual });

  const valueChangesForm = formGroup.valueChanges
    .pipe(startWith(formGroup.getRawValue()))
    .subscribe(v => {
      if (!deepEqual(valueFormGroupSignal(), v)) valueFormGroupSignal.set(v as T);
    });

  // Status
  const status = toSignal(formGroup.statusChanges.pipe(startWith(formGroup.status)), {
    initialValue: formGroup.status,
    injector: injector
  });

  // Disabled state writable (si cualquiera cambia externamente no lo reflejará salvo enable/disable manual)
  const disabled = signal<boolean>(formGroup.disabled);

  // value signal -> formGroup
  effect(() => {
    const v = valueFormGroupSignal();
    const raw = formGroup.getRawValue();
    if (!deepEqual(raw, v)) {
      formGroup.patchValue(v, { emitEvent: false });
    }
  }, { injector });

  // disabled signal -> formGroup
  effect(() => {
    const d = disabled();
    if (d && formGroup.enabled) formGroup.disable({ emitEvent: false });
    else if (!d && formGroup.disabled) formGroup.enable({ emitEvent: false });
  }, { injector });

  injector.get(DestroyRef).onDestroy(() => valueChangesForm.unsubscribe());

  return { value: valueFormGroupSignal, status, disabled, group: formGroup };
}

// Comparación profunda
function deepEqual(a: any, b: any): boolean {
  if (a === b) return true;
  if (a == null || b == null) return false;

  if (a instanceof Date && b instanceof Date) return a.getTime() === b.getTime();

  if (Array.isArray(a) && Array.isArray(b)) {
    if (a.length !== b.length) return false;
    for (let i = 0; i < a.length; i++) if (!deepEqual(a[i], b[i])) return false;
    return true;
  }

  if (typeof a === 'object' && typeof b === 'object') {
    if (a.constructor !== b.constructor) return false;
    const ka = Object.keys(a);
    const kb = Object.keys(b);
    if (ka.length !== kb.length) return false;
    for (const k of ka) {
      if (!Object.prototype.hasOwnProperty.call(b, k)) return false;
      if (!deepEqual(a[k], b[k])) return false;
    }
    return true;
  }

  // Trata NaN === NaN como iguales
  return Number.isNaN(a) && Number.isNaN(b);
}

export function formGroupStatusSignal(formGroup: FormGroup, injector: Injector = inject(Injector)) {
  // Status
  const status = toSignal(formGroup.statusChanges.pipe(startWith(formGroup.status)), {
    initialValue: formGroup.status,
    injector: injector
  });
  return  status;
}

export function formControlStatusSignal(control: FormControl, injector: Injector = inject(Injector)) {
  // Status
  const status = toSignal(control.statusChanges.pipe(startWith(control.status)), {
    initialValue: control.status,
    injector: injector
  });
  return  status;
}