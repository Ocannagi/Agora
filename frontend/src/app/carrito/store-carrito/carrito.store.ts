import { patchState, signalStore, withComputed, withHooks, withMethods, withProps, withState } from "@ngrx/signals";
import { CarritoInitialState, PersistenciaCarritoSlice, stringPersistencia } from "./carrito.slice";
import { computed, effect, inject, Injector, Signal } from "@angular/core";
import { AntiguedadALaVentaDTO } from "../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO";
import { AntiguedadesVentaService } from "../../antiguedades-venta/antiguedades-venta-service";
import { rxMethod } from "@ngrx/signals/rxjs-interop";
import { concatMap, mergeMap, pipe, tap, finalize, from } from 'rxjs';
import { tapResponse } from "@ngrx/operators";
import { HttpErrorResponse } from "@angular/common/http";


export const CarritoStore = signalStore(
    { providedIn: 'root' },
    withState(CarritoInitialState),
    withComputed((store) => {

        const totalItems = computed(() => store.carrito().length);
        const totalItemsValidos = computed(() => store.carrito().filter(item => item.hayStock && !item.cambioPrecio).length);
        const hayItems = computed(() => store.carrito().length > 0);
        const noHayItems = computed(() => !hayItems());
        const totalPrecio = computed(() => store.carrito().reduce((acc, item) => acc + (!item.cambioPrecio && item.hayStock ? item.antiguedadAlaVenta.aavPrecioVenta : 0), 0));
        const algunoSinStock = computed(() => store.carrito().some(item => !item.hayStock));
        const algunoConCambioPrecio = computed(() => store.carrito().some(item => item.cambioPrecio));
        
        return {
            totalItems,
            totalItemsValidos,
            hayItems,
            noHayItems,
            totalPrecio,
            algunoSinStock,
            algunoConCambioPrecio
        };
    }),
    withProps(() => {

        const _injector = inject(Injector);
        const _service = inject(AntiguedadesVentaService);

        return {
            _injector,
            _service
        };

    }),
    withMethods((store) => {
        const addCarrito = (item: AntiguedadALaVentaDTO) => {
            const carritoActual = store.carrito();
            if (carritoActual.some(ci => ci.antiguedadAlaVenta.aavId === item.aavId)) {
                return;
            }
            patchState(store, { carrito: [...carritoActual, { antiguedadAlaVenta: item, hayStock: true, cambioPrecio: false }] });
        };
        const removeCarrito = (itemId: number) => {
            const carritoActual = store.carrito();
            if (!carritoActual.some(ci => ci.antiguedadAlaVenta.aavId === itemId)) {
                return;
            }
            patchState(store, { carrito: carritoActual.filter(item => item.antiguedadAlaVenta.aavId !== itemId) });
        };
        const clearCarrito = () => {
            patchState(store, { carrito: [] });
        };
        const resetStore = () => patchState(store, CarritoInitialState);
        const isInCarrito = (itemId: number) => {
            return store.carrito().some(item => item.antiguedadAlaVenta.aavId === itemId);
        };
        const setHayStock = (itemId: number, hayStock: boolean) => {
            const carritoActual = store.carrito();
            const itemIndex = carritoActual.findIndex(item => item.antiguedadAlaVenta.aavId === itemId);
            if (itemIndex !== -1) {
                carritoActual[itemIndex].hayStock = hayStock;
                patchState(store, { carrito: carritoActual });
            }
        };
        const setTrueSiHuboCambioPrecio = (itemId: number, precio: number) => {
            const carritoActual = store.carrito();
            const itemIndex = carritoActual.findIndex(item => item.antiguedadAlaVenta.aavId === itemId);
            if (itemIndex !== -1) {
                const item = carritoActual[itemIndex];
                if (item.antiguedadAlaVenta.aavPrecioVenta !== precio) {
                    item.cambioPrecio = true;
                    patchState(store, { carrito: carritoActual });
                }
            }
        };
        const setCambioPrecio = (itemId: number, cambioPrecio: boolean) => {
            const carritoActual = store.carrito();
            const itemIndex = carritoActual.findIndex(item => item.antiguedadAlaVenta.aavId === itemId);
            if (itemIndex !== -1) {
                carritoActual[itemIndex].cambioPrecio = cambioPrecio;
                patchState(store, { carrito: carritoActual });
            }
        };
        const setBusy = (busy: boolean) => {
            patchState(store, { busy });
        };
        const setOneError = (error: string) => {
            const erroresActuales = store.errors();
            patchState(store, { errors: [...erroresActuales, error] });
        };
        const resetErrors = () => {
            patchState(store, { errors: [] });
        }
        const pullingTrigger = () => patchState(store, { triggerComprobacion: !store.triggerComprobacion() });
        const setFlagComprobacion = (flagComprobacion: boolean) => { patchState(store, { flagComprobacion }) };

        // Procesa un lote de IDs
        const comprStockPreAav = rxMethod<readonly number[]>(pipe(
            tap(() => {
                        resetErrors();
                        setBusy(true);
                        console.log('es busy true', store.busy());
                    }),
            // cada llamada dispara un lote; se encola si hay otro en curso
            concatMap((ids) =>
              from(ids).pipe(
                // opcional: limitar concurrencia por llamada
                mergeMap((aavId) =>
                  store._service.getById(aavId).pipe(
                    tapResponse({
                      next: (response) => {
                        console.log('Respuesta de getById para aavId', aavId, ':', response);
                        if (!response) setHayStock(aavId, false);
                        else setTrueSiHuboCambioPrecio(aavId, response.aavPrecioVenta);
                      },
                      error: (error: HttpErrorResponse) => {
                        setOneError(store._service.getByIdError() ?? 'Error desconocido al comprobar la antigüedad en el carrito.');
                        console.error(error);
                      },
                      finalize: () => {
                        // per-item: no tocar busy aquí
                      }
                    })
                  )
                  // , 4  // habilitá esta línea para concurrencia limitada
                ),
                finalize(() => {
                  setBusy(false);
                  console.log('es busy false', store.busy());
                  store._service.getByIdError.set(null);
                })
              )
            )
          ), { injector: store._injector });

        const _comprobarStockPrecioAav = () => {
            const ids = store.carrito().map(ci => ci.antiguedadAlaVenta.aavId);
            if (ids.length === 0) return;
            comprStockPreAav(ids);
        };

        const removeItemsInvalidos = () => {
            const carritoActual = store.carrito();
            const nuevosItems = carritoActual.filter(item => item.hayStock && !item.cambioPrecio);
            patchState(store, { carrito: nuevosItems });
        };

        return {
            addCarrito,
            removeCarrito,
            clearCarrito,
            resetStore,
            isInCarrito,
            setHayStock,
            setCambioPrecio,
            _comprobarStockPrecioAav,
            setOneError,
            resetErrors,
            setBusy,
            setTrueSiHuboCambioPrecio,
            pullingTrigger,
            setFlagComprobacion,
            removeItemsInvalidos
        };
    }),
    withHooks(store => ({
        onInit: () => {

            const persistido: Signal<PersistenciaCarritoSlice> = computed(() => ({ carrito: store.carrito() }));

            const persistidoLocalStorage = localStorage.getItem(stringPersistencia);
            if (persistidoLocalStorage) {
                const parseado: PersistenciaCarritoSlice = JSON.parse(persistidoLocalStorage);
                patchState(store, parseado);
            }

            effect(() => {
                const valuePersistencia = persistido();
                localStorage.setItem(stringPersistencia, JSON.stringify(valuePersistencia));
            });

            effect(() => {
                const trigger = store.triggerComprobacion();
                const flag = store.flagComprobacion();

                if (trigger !== flag) {
                    store.setFlagComprobacion(trigger);
                    store._comprobarStockPrecioAav();
                }
            });

        },
    }))
);