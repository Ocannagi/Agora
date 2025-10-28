import { patchState, signalStore, withComputed, withMethods, withProps, withState } from "@ngrx/signals";
import { AntiguedadVentaInitialState } from "./antiguedad-venta.slice";
import { computed, inject, Injector } from "@angular/core";
import { AntiguedadesVentaService } from "../antiguedades-venta-service";
import { PaginadoRequestSearchDTO } from "../../compartidos/modelo/PaginadoRequestDTO";
import { PageEvent } from "@angular/material/paginator";
import { HttpErrorResponse } from "@angular/common/http";


export const AntiguedadVentaStore = signalStore(
    {providedIn: 'root'},
    withState(AntiguedadVentaInitialState),
    withProps((store) => {
        const _injector = inject(Injector);
        const _service = inject(AntiguedadesVentaService);
        const _resourcePaginadoSearch = _service.getPaginadoSearch(
            store.paginado,
            _injector
        );
        const resourcePaginadoSearch = _resourcePaginadoSearch.asReadonly();

        return {
            resourcePaginadoSearch,
            _injector,
            _service,
            _resourcePaginadoSearch
        };
    }),
    withMethods((store) => {

        const setPaginado = (paginado: PaginadoRequestSearchDTO) => patchState(store, { paginado });
        const setPagina = (pagina: number) => patchState(store, { paginado: { ...store.paginado(), pagina } });
        const setRegistrosPorPagina = (registrosPorPagina: number) => patchState(store, { paginado: { ...store.paginado(), registrosPorPagina } });
        const setSearchWord = (searchWord: string) => patchState(store, { paginado: { ...store.paginado(), searchWord } });
        const setPageEvent = (event: PageEvent) => {
            setPagina(event.pageIndex + 1);
            setRegistrosPorPagina(event.pageSize);
        };
        const setFiltrarPorUsrId = (filtrarPorUsrId: boolean) => {
            patchState(store, { paginado: { ...store.paginado(), filtrarPorUsrId } });
        }
        const onBusy = () => patchState(store, { busy: true });
        const offBusy = () => patchState(store, { busy: false });
        const resetStore = () => patchState(store, AntiguedadVentaInitialState);

        return {
            setPaginado,
            setPagina,
            setRegistrosPorPagina,
            setSearchWord,
            setPageEvent,
            setFiltrarPorUsrId,
            onBusy,
            offBusy,
            resetStore
        };
    }),
    withComputed((store) => {
        const resourcePagSearch = store.resourcePaginadoSearch;

        const isCargando = computed(() => resourcePagSearch.isLoading() || store.busy());
        const resourcePagSearchStatusResolved = computed(() => resourcePagSearch.status() === 'resolved');
        const hayResourcePagSearchError = computed(() => resourcePagSearch === null || resourcePagSearch.status() === 'error');
        const hayError = computed(() => {
            if (hayResourcePagSearchError()) {
                return true;
            }
            return false;
        });
        const errors = computed(() => {
            const errores: string[] = [];
            if (hayResourcePagSearchError()) {
                errores.push((resourcePagSearch.error()?.cause as HttpErrorResponse)?.error as string ?? 'Error desconocido');
            }
            return errores;
        });

        return {
            isCargando,
            resourcePagSearchStatusResolved,
            hayResourcePagSearchError,
            hayError,
            errors
        };
    })
);