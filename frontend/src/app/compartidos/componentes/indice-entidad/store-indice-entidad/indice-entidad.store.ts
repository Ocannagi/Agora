import { patchState, signalStore, withComputed, withHooks, withMethods, withProps, withState } from "@ngrx/signals";
import { IndiceEntidadInitialState } from "./indice-entidad.slice";
import { Injector, computed, inject } from "@angular/core";
import { SERVICIO_PAGINADO_TOKEN } from "../../../proveedores/tokens";
import { IServicePaginado } from "../../../interfaces/IServicePaginado";
import { PaginadoRequestDTO } from "../../../modelo/PaginadoRequestDTO";
import { HttpErrorResponse } from "@angular/common/http";
import { IIndiceEntidadDTO } from "../../../modelo/IIndiceEntidadDTO";
import { rxMethod } from "@ngrx/signals/rxjs-interop";
import { pipe } from "rxjs/internal/util/pipe";
import { of, switchMap, tap } from "rxjs";
import { tapResponse } from "@ngrx/operators";
import { PageEvent } from "@angular/material/paginator";

export const IndiceEntidadStore = signalStore(
    withState(IndiceEntidadInitialState),
    withProps((store) => {

        const _injector = inject(Injector);
        const _service = inject(SERVICIO_PAGINADO_TOKEN) as IServicePaginado<IIndiceEntidadDTO>;
        const _resourcePaginado = _service.getAllPaginado(
            store.paginado,
            _injector
        );
        const resourcePaginado = _resourcePaginado.asReadonly();

        return {
            _injector,
            _service,
            _resourcePaginado,
            resourcePaginado
        };
    }),
    withMethods((store) => {
        const setTitulo = (titulo: string) => patchState(store, { titulo });
        const setPathCrear = (pathCrear: string) => patchState(store, { pathCrear });
        const setMsgBorrar = (msgBorrar: string) => patchState(store, { msgBorrar });
        const setColumnasExtras = (columnasExtras: string[]) => patchState(store, { columnasExtras });
        const setPaginado = (paginado: PaginadoRequestDTO) => patchState(store, { paginado });
        const setPagina = (pagina: number) => patchState(store, { paginado: { ...store.paginado(), pagina } });
        const setRegistrosPorPagina = (registrosPorPagina: number) => patchState(store, { paginado: { ...store.paginado(), registrosPorPagina } });
        const setPageEvent = (event: PageEvent) => {
            setPagina(event.pageIndex + 1);
            setRegistrosPorPagina(event.pageSize);
        };
        const setFiltrarPorUsrId = (filtrarPorUsrId: boolean) => {
            patchState(store, { paginado: { ...store.paginado(), filtrarPorUsrId } });
        }

        const onBusy = () => patchState(store, { busy: true });
        const offBusy = () => patchState(store, { busy: false });
        const resetStore = () => patchState(store, IndiceEntidadInitialState);
        const borrarEntidad = rxMethod<number>(pipe(
            tap(() => onBusy()),
            switchMap((idEntidad) => {
                return store._service.delete(idEntidad).pipe(
                    tapResponse({
                        next: () => store._resourcePaginado.reload(),
                        error: (error: HttpErrorResponse) => {
                            console.error('Error al borrar la entidad:', error);
                        },
                        finalize: () => offBusy()
                    })
                );
            })
        ), { injector: store._injector });



        return {
            setTitulo,
            setPathCrear,
            setMsgBorrar,
            setColumnasExtras,
            setPaginado,
            setPagina,
            setRegistrosPorPagina,
            setFiltrarPorUsrId,
            setPageEvent,
            onBusy,
            offBusy,
            resetStore,
            borrarEntidad
        };
    }),
    withComputed((store) => {

        const resourcePaginado = store.resourcePaginado;

        const columnasAMostrar = computed(() => {
            const cols = [...store.columnasDefault(), ...store.columnasExtras()];
            const tieneAcciones = cols.includes('acciones');
            const sinAcciones = cols.filter(c => c !== 'acciones');
            return tieneAcciones ? [...sinAcciones, 'acciones'] : sinAcciones;
        });

        const isCargando = computed(() => resourcePaginado.isLoading() || store.busy());

        const resourcePaginadoStatusResolved = computed(() => resourcePaginado.status() === 'resolved');
        const hayResourcePaginadoError = computed(() => resourcePaginado === null || resourcePaginado.status() === 'error');
        const hayDeleteError = computed(() => store._service.deleteError() !== null);
        const hayError = computed(() => {
            if (hayResourcePaginadoError()) {
                return true;
            }
            if (hayDeleteError()) {
                return true;
            }

            return false;
        });
        const errors = computed(() => {
            const errores: string[] = [];
            if (hayResourcePaginadoError()) {
                errores.push((resourcePaginado.error()?.cause as HttpErrorResponse)?.error as string ?? 'Error desconocido');
            }
            if (hayDeleteError()) {
                errores.push(store._service.deleteError()!);
            }
            return errores;
        });

        return {
            columnasAMostrar,
            isCargando,
            resourcePaginadoStatusResolved,
            hayResourcePaginadoError,
            hayError,
            errors
        };
    }),
    withHooks(store => ({
        onInit: () => {

        },
        onDestroy: () => {
            store._service.deleteError.set(null);
        }
    }))
);