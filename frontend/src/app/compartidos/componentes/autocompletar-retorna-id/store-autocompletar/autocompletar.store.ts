import { patchState, signalStore, withComputed, withHooks, withMethods, withProps, withState } from "@ngrx/signals";
import { autocompletarInitialState } from "./autocompletar.slice";
import { computed, effect, inject, InjectionToken, Injector, untracked } from "@angular/core";
import { IServiceAutocompletar } from "../../../interfaces/IServiceAutocompletar";
import { SERVICIO_AUTOCOMPLETAR_TOKEN } from "../../../proveedores/tokens";
import { FormControlSignal } from "../../../funciones/formToSignal";
import { IAutocompletarDTO } from "../../../interfaces/IAutocompletarDTO";

export const AutocompletarStore = signalStore(
  withState(autocompletarInitialState),
  withProps((store) => {
    const _injector = inject(Injector);
    const _service = inject(SERVICIO_AUTOCOMPLETAR_TOKEN) as IServiceAutocompletar<IAutocompletarDTO>;
    const resource = _service.autocompletarResource(store.keyword, _injector, store.idDependenciaPadre);

    return {
      _injector,
      resource
    };
  }),
  withMethods((store) => {
    const setKeyword = (keyword: string) => patchState(store, { keyword });
    const setIdDependenciaPadre = (id: number | null) => patchState(store, { idDependenciaPadre: id });
    const setModelId = (id: number | null) => patchState(store, { modelId: id });
    const setErrors = (errors: string[]) => patchState(store, { errors });
    const setUnError = (error: string) => patchState(store, { errors: [...store.errors(), error] });
    const setFormControlSignal = (formControlSignal: FormControlSignal<string | null>) => patchState(store, { formControlSignal });
    const resetStore = () => patchState(store, autocompletarInitialState);

    return {
      setKeyword,
      setIdDependenciaPadre,
      setModelId,
      setErrors,
      setUnError,
      setFormControlSignal,
      resetStore
    };
  }),
  withComputed((store) => {

    const hayError = computed(() => store.errors().length > 0);
    const cantidadRegistros = computed(() => store.resource.value().length);
    const noHayRegistros = computed(() => cantidadRegistros() === 0);
    const hayQueReseterar = computed(() => (store.formControlSignal.status()() !== 'VALID' ||
      noHayRegistros()) && store.modelId() !== null);
    const hayResourceError = computed(() => store.resource.status() === 'error');


    return {
      hayError,
      cantidadRegistros,
      noHayRegistros,
      hayQueReseterar,
      hayResourceError
    }
  }),
  withHooks(store => ({
    onInit: () => {
      console.log('Autocompletar store init');

      effect(() => {
        if (store.hayQueReseterar()) {
          untracked(() => {
            store.setKeyword('');
            store.setModelId(null);
          });
        }

        if (store.hayResourceError()) {
          untracked(
            () => store.setErrors([...store.errors(), store.resource.error()!.message])
          );
        }



      });
    },
    onDestroy: () => {
      console.log('Autocompletar store destroy');
    }
  }))
);