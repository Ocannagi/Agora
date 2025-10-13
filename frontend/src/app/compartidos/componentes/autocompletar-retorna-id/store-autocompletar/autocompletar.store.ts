import { patchState, signalStore, withComputed, withHooks, withMethods, withProps, withState } from "@ngrx/signals";
import { autocompletarInitialState } from "./autocompletar.slice";
import { computed, effect, inject, InjectionToken, Injector, untracked } from "@angular/core";
import { IServiceAutocompletar } from "../../../interfaces/IServiceAutocompletar";
import { SERVICIO_AUTOCOMPLETAR_TOKEN } from "../../../proveedores/tokens";
import { FormControlSignal } from "../../../funciones/formToSignal";
import { IAutocompletarDTO } from "../../../interfaces/IAutocompletarDTO";
import { HttpErrorResponse } from "@angular/common/http";

export const AutocompletarStore = signalStore(
  withState(autocompletarInitialState),
  withProps((store) => {
    const _injector = inject(Injector);
    const _service = inject(SERVICIO_AUTOCOMPLETAR_TOKEN) as IServiceAutocompletar<IAutocompletarDTO>;
    const _resource = _service.autocompletarResource(store.keyword, _injector, store.idDependenciaPadre);
    const resource = _resource.asReadonly();

    return {
      _injector,
      _resource,
      resource
    };
  }),
  withMethods((store) => {
    const setKeyword = (keyword: string) => patchState(store, { keyword });
    const setIdDependenciaPadre = (id: number | null) => patchState(store, { idDependenciaPadre: id });
    const setModelId = (id: number | null) => patchState(store, { modelId: id });
    const setFormControlSignal = (formControlSignal: FormControlSignal<IAutocompletarDTO | null>) => patchState(store, { formControlSignal });
    const resetStore = () => patchState(store, autocompletarInitialState);
    const resetKeyword = () => patchState(store, { keyword: '' });

    return {
      setKeyword,
      setIdDependenciaPadre,
      setModelId,
      setFormControlSignal,
      resetStore,
      resetKeyword
    };
  }),
  withComputed((store) => {

    const hayError = computed(() => store.resource === null || store.resource.status() === 'error');
    const errors = computed(() => hayError() ? [(store.resource.error() as HttpErrorResponse)?.error as string ?? 'Error desconocido'] : []);
    const cantidadRegistros = computed(() => store.resource.value().length);
    const noHayRegistros = computed(() => cantidadRegistros() === 0);
    const hayRegistros = computed(() => cantidadRegistros() > 0);
    const statusNoValido = computed(() => store.formControlSignal.status()() !== 'VALID');
    const hayQueReseterar = computed(() => (statusNoValido() ||  noHayRegistros()
                                             || store.formControlSignal.value()() === null) 
                                                    && store.keyword() !== '' && store.modelId() !== null);
    const hayResourceError = computed(() => store.resource.status() === 'error');
    const statusValido = computed(() => store.formControlSignal.status()() === 'VALID');
    const hayKeyword = computed(() => store.keyword().length > 0);


    return {
      hayError,
      errors,
      cantidadRegistros,
      noHayRegistros,
      hayRegistros,
      hayQueReseterar,
      hayResourceError,
      statusValido,
      statusNoValido,
      hayKeyword
    }
  }),
  withHooks(store => ({
    onInit: () => {
      console.log('Autocompletar store init');
      

      effect(() => {

        /* console.log('keyword',store.keyword());
        console.log('status',store.formControlSignal.status()());
        console.log('statusValido',store.statusValido());
        console.log('controlValue',store.formControlSignal.value()());
        console.log('modelId',store.modelId());
        console.log('cantidadRegistros',store.cantidadRegistros());
        console.log('resourceHasValue',store.resource.hasValue());
 */
        const hayQueReseterar = store.hayQueReseterar();

        if (hayQueReseterar) {
          /* console.log('Reseteando autocompletar porque el form es inválido o no hay registros', {
            formStatus: store.formControlSignal.status()(),
            cantidadRegistros: store.cantidadRegistros(),
            modelId: store.modelId()
          }); */
          untracked(() => {
            store.resetKeyword();
            store.setModelId(null);
            store._resource.reload();
          });
        }


      });
    },
    onDestroy: () => {
      console.log('Autocompletar store destroy');
    }
  }))
);