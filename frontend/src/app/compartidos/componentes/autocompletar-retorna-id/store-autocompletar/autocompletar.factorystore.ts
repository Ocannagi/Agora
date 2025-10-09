import { signalStore, withProps, withState } from "@ngrx/signals";
import { autocompletarInitialState } from "./autocompletar.slice";
import { inject, InjectionToken, Injector } from "@angular/core";
import { IServiceAutocompletar } from "../../../interfaces/IServiceAutocompletar";

export function createAutocompletarStore<T>(
  token: InjectionToken<IServiceAutocompletar<T>>
) {
  return signalStore(
    withState(autocompletarInitialState),
    withProps(() => {
      const _injector = inject(Injector);
      const _service = inject(token).;
      return { _injector, _service };
    })
    // withMethods(...) usando _service
  );
}