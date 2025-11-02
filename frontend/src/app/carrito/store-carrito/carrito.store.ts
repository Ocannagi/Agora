import { patchState, signalStore, withComputed, withHooks, withMethods, withProps, withState } from "@ngrx/signals";
import { CarritoInitialState, PersistenciaCarritoSlice, stringPersistencia } from "./carrito.slice";
import { computed, effect, inject, Injector } from "@angular/core";
import { AntiguedadALaVentaDTO } from "../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO";
import { AntiguedadesVentaService } from "../../antiguedades-venta/antiguedades-venta-service";
import { mergeMap, tap, from, map, Observable, defer, catchError, EMPTY } from 'rxjs';
import { AutenticacionStore } from "../../seguridad/store/autenticacion.store";


export const CarritoStore = signalStore(
    { providedIn: 'root' },
    withState(CarritoInitialState),
    withProps(() => {

        const _injector = inject(Injector);
        const _service = inject(AntiguedadesVentaService);
        const _storeAuth = inject(AutenticacionStore);

        return {
            _injector,
            _service,
            _storeAuth,
        };

    }),
    withComputed((store) => {

        const totalItems = computed(() => store.carrito().length);
        const totalItemsValidos = computed(() => store.carrito().filter(item => item.hayStock && !item.cambioPrecio).length);
        const hayItems = computed(() => store.carrito().length > 0);
        const noHayItems = computed(() => !hayItems());
        const totalPrecio = computed(() => store.carrito().reduce((acc, item) => acc + (!item.cambioPrecio && item.hayStock ? item.antiguedadAlaVenta.aavPrecioVenta : 0), 0));
        const algunoSinStock = computed(() => store.carrito().some(item => !item.hayStock));
        const algunoConCambioPrecio = computed(() => store.carrito().some(item => item.cambioPrecio));
        const impedirContinuarCompra = computed(() => {
            return store.busy() || noHayUsrLogueado() || noHayItems() || algunoSinStock() || algunoConCambioPrecio();
        });
        const hayUsrLogueado = computed(() => store.usrId() !== null);
        const noHayUsrLogueado = computed(() => !hayUsrLogueado());
        const deshabilitarCarrito = computed(() => noHayUsrLogueado() || noHayItems());
        const _persistido = computed(() => ({ usrId: store.usrId(), carrito: store.carrito() } as PersistenciaCarritoSlice));

        return {
            totalItems,
            totalItemsValidos,
            hayItems,
            noHayItems,
            totalPrecio,
            algunoSinStock,
            algunoConCambioPrecio,
            hayUsrLogueado,
            noHayUsrLogueado,
            deshabilitarCarrito,
            impedirContinuarCompra,
            _persistido
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
        };

        const comprobarStockPrecioAav = (): Observable<void> => defer(() => {
            const ids = store.carrito().map(ci => ci.antiguedadAlaVenta.aavId);
            resetErrors();
            if (ids.length === 0) {
                setBusy(false);
                return EMPTY;
            }
            setBusy(true);
            return from(ids).pipe(
                mergeMap((aavId) =>
                    store._service.getById(aavId).pipe(
                        map((response) => {
                            if (!response) {
                                setHayStock(aavId, false);
                            } else {
                                setTrueSiHuboCambioPrecio(aavId, response.aavPrecioVenta);
                            }
                            return undefined;
                        }),
                        catchError((err) => {
                            setOneError(store._service.getByIdError() ?? 'Error desconocido al comprobar la antigüedad en el carrito.');
                            console.error(err);
                            // continuar el lote
                            return [undefined];
                        })
                    )
                ),
                // Asegurar busy=false ANTES de que el subscriber reciba complete
                tap({
                    complete: () => {
                        setBusy(false);
                        store._service.getByIdError.set(null);
                        console.log('Comprobación de stock/precio finalizada para lote de IDs del carrito.');
                    }
                })
            );
        });


        /*         // Procesa un lote de IDs
                const comprStockPreAav = rxMethod<readonly number[]>(pipe(
                    tap(() => {
                        resetErrors();
                        setBusy(true);
                    }),
                    // cada llamada dispara un lote; se encola si hay otro en curso
                    concatMap((ids) =>
                        from(ids).pipe(
                            // opcional: limitar concurrencia por llamada
                            mergeMap((aavId) =>
                                store._service.getById(aavId).pipe(
                                    tapResponse({
                                        next: (response) => {
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
                                console.log('Comprobación de stock/precio finalizada para lote de IDs del carrito.');
                                setBusy(false);
                                store._service.getByIdError.set(null);
                            })
                        )
                    )
                ), { injector: store._injector });
        
                const comprobarStockPrecioAav$ = (): void => {
                    const ids = store.carrito().map(ci => ci.antiguedadAlaVenta.aavId);
                    if (ids.length === 0) {
                        return;
                    }
                    comprStockPreAav(ids);
                }; */

        const removeItemsInvalidos = () => {
            const carritoActual = store.carrito();
            const nuevosItems = carritoActual.filter(item => item.hayStock && !item.cambioPrecio);
            patchState(store, { carrito: nuevosItems });
        };

        const _setUsrId = (usrId: number | null) => {
            patchState(store, { usrId });
        }

        return {
            addCarrito,
            removeCarrito,
            clearCarrito,
            resetStore,
            isInCarrito,
            setHayStock,
            setCambioPrecio,
            setOneError,
            resetErrors,
            setBusy,
            setTrueSiHuboCambioPrecio,
            removeItemsInvalidos,
            _setUsrId,
            comprobarStockPrecioAav
        };
    }),
    withHooks(store => ({
        onInit: () => {

            effect(() => {
                const valuePersistencia = store._persistido();
                if (store.noHayUsrLogueado()) {
                    return;
                }
                localStorage.setItem(stringPersistencia, JSON.stringify(valuePersistencia));
            });

            effect(() => {
                const usrId = store._storeAuth.usrId() ?? null;
                store._setUsrId(usrId);

                if (usrId === null) {
                    store.resetStore();
                }
            });

            effect(() => {
                const hayUsrLogueado = store.hayUsrLogueado();
                const noHayItems = store.noHayItems();

                if (hayUsrLogueado && noHayItems) {

                    const persistidoLocalStorage = localStorage.getItem(stringPersistencia);
                    if (persistidoLocalStorage) {
                        const parseado: PersistenciaCarritoSlice = JSON.parse(persistidoLocalStorage);
                        if (parseado.usrId !== store.usrId()) {
                            localStorage.removeItem(stringPersistencia);
                        } else {
                            patchState(store, parseado);
                        }
                    }
                }
            });
        },
    }))
);