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
    const _resourceAll = _service.autocompletarResource(store.keyword, _injector, store.idDependenciaPadre, store.selectedId);
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
    const setIdDependenciaPadre = (id: number | null) => patchState(store, { idDependenciaPadre: id });
    const setModelId = (id: number | null) => patchState(store, { modelId: id });
    const setFormControlSignal = (formControlSignal: FormControlSignal<IAutocompletarDTO | null>) => patchState(store, { formControlSignal });
    const setSelectedId = (id: number | null) => patchState(store, { selectedId: id });
    const resetStore = () => patchState(store, autocompletarInitialState);
    const resetKeyword = () => patchState(store, { keyword: '' });

    return {
      setKeyword,
      setIdDependenciaPadre,
      setModelId,
      setFormControlSignal,
      setSelectedId,
      resetStore,
      resetKeyword
    };
  }),
  withComputed((store) => {

    const resourceAll = store.resourceAll;
    //const resourceById = store.resourceById;

    const haySelectedId = computed(() => store.selectedId !== null && store.selectedId !== undefined && store.selectedId() !== null && store.selectedId() !== undefined);

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
    const statusNoValido = computed(() => store.formControlSignal.status()() !== 'VALID');

    
    const statusValido = computed(() => store.formControlSignal.status()() === 'VALID');
    const hayKeyword = computed(() => store.keyword().length > 0);

    const hayQueReseterar = computed(() => (statusNoValido() || noHayRegistros()
      || store.formControlSignal.value()() === null)
      && store.keyword() !== '' && store.modelId() !== null);

    const selectedItem = computed(() => {
      if (haySelectedId() && /*resourceById*/ resourceAll.status() === 'resolved' && resourceAll.hasValue() && cantidadRegistros() === 1) {
        return /*resourceById*/ resourceAll.value();
      }
      return null;
    });

    return {
      hayError,
      errors,
      cantidadRegistros,
      noHayRegistros,
      haySelectedId,
      hayRegistros,
      hayQueReseterar,
      hayResourceAllError,
      //hayResourceByIdError,
      statusValido,
      statusNoValido,
      hayKeyword,
      selectedItem
    }
  }),
  withHooks(store => ({
    onInit: () => {
      //console.log('Autocompletar store init');


      effect(() => {

        /* console.log('keyword',store.keyword());
        console.log('status',store.formControlSignal.status()());
        console.log('statusValido',store.statusValido());
        console.log('controlValue',store.formControlSignal.value()());
        console.log('modelId',store.modelId());
        console.log('cantidadRegistros',store.cantidadRegistros());
        console.log('resourceHasValue',store.resource.hasValue());
 */
        const reset = store.hayQueReseterar();
        if (reset) {
          //console.log('Reseteando autocompletar')
          /* console.log('Reseteando autocompletar porque el form es inválido o no hay registros', {
            formStatus: store.formControlSignal.status()(),
            cantidadRegistros: store.cantidadRegistros(),
            modelId: store.modelId()
          }); */
          untracked(() => {
            store.resetKeyword();
            store.setModelId(null);
            store._resourceAll.reload(); // Es necesario?
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
        const item = store.selectedItem();
        if (item !== null && item[0].id !== store.modelId()) {
          //console.log('Seteando modelId desde selectedItem', item);
          untracked(() => {
            store.formControlSignal.value().set(item[0]);
            store.setModelId(item[0].id);
            store.setKeyword(item[0].descripcion);
          });
        }
      });
      
      //PENSAR COMO HACER PARA QUE CUANDO SE VUELVE A ESCRIBIR LA KEYWORD, SE LIMPIE EL SELECTEDID Y EL MODELID
      

    },
    onDestroy: () => {
      //console.log('Autocompletar store destroy');
    }
  }))
);