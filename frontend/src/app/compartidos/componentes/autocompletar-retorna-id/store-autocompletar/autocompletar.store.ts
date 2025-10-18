import { patchState, signalStore, withComputed, withHooks, withMethods, withProps, withState } from "@ngrx/signals";
import { autocompletarInitialState } from "./autocompletar.slice";
import { computed, effect, inject, Injector, untracked } from "@angular/core";
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
    const _resourceAll = _service.autocompletarResource(store.keyword, _injector, store.idDependenciaPadre);
    // const _resourceById = _service.getByIdAutocompletarResource(store.modelId, _injector);
    // const resourceById = _resourceById.asReadonly();
    const resourceAll = _resourceAll.asReadonly();

    return {
      _injector,
      _resourceAll,
      resourceAll,
      // _resourceById,
      // resourceById
    };
  }),
  withMethods((store) => {
    const setKeyword = (keyword: string) => patchState(store, { keyword });
    const setKeywordExterno = (keyword: string) => patchState(store, { keywordExterno: keyword });
    const setHayDependenciaPadre = (hay: boolean) => patchState(store, { hayDependenciaPadre: hay });
    const setIdDependenciaPadre = (id: number | null) => patchState(store, { idDependenciaPadre: id });
    const setModelId = (id: number | null) => patchState(store, { modelId: id });
    const setFormControlSignal = (formControlSignal: FormControlSignal<IAutocompletarDTO | null>) => patchState(store, { formControlSignal });
    const resetStore = () => patchState(store, autocompletarInitialState);
    const resetKeyword = () => patchState(store, { keyword: '' });
    const resetKeywordExterno = () => patchState(store, { keywordExterno: '' });
    return {
      setKeyword,
      setKeywordExterno,
      setHayDependenciaPadre,
      setIdDependenciaPadre,
      setModelId,
      setFormControlSignal,
      resetStore,
      resetKeyword,
      resetKeywordExterno
    };
  }),
  withComputed((store) => {

    const resourceAll = store.resourceAll;
    //const resourceById = store.resourceById;

    const resourceAllStatusResolved = computed(() => resourceAll.status() === 'resolved');

    const hayResourceAllError = computed(() => resourceAll === null || resourceAll.status() === 'error');
    //const hayResourceByIdError = computed(() => haySelectedId() && resourceById.status() === 'error');

    const hayError = computed(() => {
      // if (hayResourceByIdError()) {
      //   return true;
      // } 
      if (hayResourceAllError()) {
        return true;
      }
      return false;
    });

    const errors = computed(() => {
      if (hayError()) {
        const listaErrores: string[] = [];

        // if (hayResourceByIdError()) {
        //   listaErrores.push((resourceById.error()?.cause as HttpErrorResponse)?.error as string ?? 'Error desconocido');
        // }
        if (hayResourceAllError()) {
          listaErrores.push((resourceAll.error()?.cause as HttpErrorResponse)?.error as string ?? 'Error desconocido');
        }
        return listaErrores;
      } else
        return [];
    });
    const cantidadRegistros = computed(() => store.resourceAll.value().length);
    const noHayRegistros = computed(() => cantidadRegistros() === 0);
    const hayRegistros = computed(() => cantidadRegistros() > 0);
    const statusNoValido = computed(() => store.formControlSignal.status()() === 'INVALID');


    const statusValido = computed(() => store.formControlSignal.status()() !== 'INVALID');
    const hayKeyword = computed(() => store.keyword().length > 0);
    const hayKeywordExterno = computed(() => store.keywordExterno().length > 0);

  
    const dependenciaPadreResuelta = computed(() => {
      if (store.hayDependenciaPadre()) {
        return store.idDependenciaPadre() !== null;
      }
      return true;
    });

    const dependenciaPadreNoResuelta = computed(() => !dependenciaPadreResuelta());

    const hayQueReseterar = computed(() => (statusNoValido() || noHayRegistros()
      || store.formControlSignal.value()() === null)
      && store.keyword() !== '' && store.modelId() !== null);

    return {
      resourceAllStatusResolved,
      hayError,
      errors,
      cantidadRegistros,
      noHayRegistros,
      hayRegistros,
      hayQueReseterar,
      hayResourceAllError,
      //hayResourceByIdError,
      statusValido,
      statusNoValido,
      hayKeyword,
      hayKeywordExterno,
      dependenciaPadreResuelta,
      dependenciaPadreNoResuelta
    }
  }),
  withHooks(store => ({
    onInit: () => {



      effect(() => {


        const reset = store.hayQueReseterar();
        if (reset) {

          untracked(() => {
            store.resetKeyword();
            store.setModelId(null);
          });
        }


      });

      effect(() => {
        const idDependenciaPadre = store.idDependenciaPadre();

        if (idDependenciaPadre !== null && store.formControlSignal.value()()?.dependenciaId !== idDependenciaPadre) {
          untracked(() => {
            store.resetKeyword();
            store.setModelId(null);
            store.formControlSignal.value().set(null);
          });
        }
      });

      effect(() => {
        const dependenciaPadreNoResuelta = store.dependenciaPadreNoResuelta();

        if (dependenciaPadreNoResuelta) {
          untracked(() => {
            store.resetKeyword();
            store.setModelId(null);
            store.formControlSignal.value().set(null);
          });
        }
      });

    },
    onDestroy: () => {
      //console.log('Autocompletar store destroy');
    }
  }))
);