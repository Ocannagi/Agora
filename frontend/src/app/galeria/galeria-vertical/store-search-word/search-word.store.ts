import { deepComputed, patchState, signalStore, withComputed, withHooks, withMethods, withProps, withState } from "@ngrx/signals";
import { SearchWordInitialState } from "./search-word.slice";
import { computed, effect, inject, Injector } from "@angular/core";
import { AntiguedadesVentaService } from "../../../antiguedades-venta/antiguedades-venta-service";
import { PaginadoRequestSearchDTO } from "../../../compartidos/modelo/PaginadoRequestDTO";
import { AntiguedadALaVentaDTO } from "../../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO";
import { PaginadoResponseDTO } from "../../../compartidos/modelo/PaginadoResponseDTO";
import { PageEvent } from "@angular/material/paginator";
import { HttpErrorResponse } from "@angular/common/http";
import { CarritoStore } from "../../../carrito/store-carrito/carrito.store";

export const SearchWordStore = signalStore(
    { providedIn: 'root' },
    withState(SearchWordInitialState),
    withProps(() => ({
        _storeCarrito: inject(CarritoStore),
    })),
    withComputed((store) => {

        const paginadoRequest = deepComputed(() => ({
            pagina: store.pagina(),
            registrosPorPagina: store.registrosPorPagina(),
            filtrarPorUsrId: store.filtrarPorUsrId(),
            searchWord: store.searchWord()
        } as PaginadoRequestSearchDTO));

        const paginadoResponse = deepComputed(() => ({
            totalRegistros: store.totalRegistros(),
            paginaActual: store.pagina(),
            registrosPorPagina: store.registrosPorPagina(),
            arrayEntidad: store.arrayEntidad()
        } as PaginadoResponseDTO<AntiguedadALaVentaDTO>));

        return {
            paginadoRequest,
            paginadoResponse
        };
    }),
    withProps((store) => {
        const _injector = inject(Injector);
        const _service = inject(AntiguedadesVentaService);
        const _resourcePaginadoSearch = _service.getPaginadoSearch(
            store.paginadoRequest,
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

        const setPagina = (pagina: number) => patchState(store, { pagina });
        const setRegistrosPorPagina = (registrosPorPagina: number) => patchState(store, { registrosPorPagina });
        const setSearchWord = (searchWord: string) => patchState(store, { searchWord });
        const setPageEvent = (event: PageEvent) => {
            setPagina(event.pageIndex + 1);
            setRegistrosPorPagina(event.pageSize);
        };
        const setFiltrarPorUsrId = (filtrarPorUsrId: boolean) => {
            patchState(store, { filtrarPorUsrId });
        }
        const setTotalRegistros = (totalRegistros: number) => patchState(store, { totalRegistros });
        const setArrayEntidad = (arrayEntidad: AntiguedadALaVentaDTO[]) => patchState(store, { arrayEntidad });
        const resetStore = () => patchState(store, SearchWordInitialState);
        const reloadResourcePaginadoSearch = () => {
            store._resourcePaginadoSearch.reload();
        };

        return {
            setPagina,
            setRegistrosPorPagina,
            setSearchWord,
            setPageEvent,
            setFiltrarPorUsrId,
            setTotalRegistros,
            setArrayEntidad,
            resetStore,
            reloadResourcePaginadoSearch
        };
    }),
    withComputed((store) => {

        const resourcePagSearch = store.resourcePaginadoSearch;

        const isLoading = computed(() => resourcePagSearch.isLoading());
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

        const hayValores = computed(() => {
            return store.arrayEntidad.length > 0;
        });

        return {
            isLoading,
            resourcePagSearchStatusResolved,
            hayResourcePagSearchError,
            hayError,
            errors,
            hayValores
        };
    }),
    withHooks(store => ({
        onInit: () => {

            effect(() => {
                const valueSearch = store.resourcePaginadoSearch.value();
                store.setTotalRegistros(valueSearch?.totalRegistros ?? 0);
                store.setArrayEntidad(valueSearch?.arrayEntidad ?? []);
                store.setPagina(valueSearch?.paginaActual ?? 1);
                store.setRegistrosPorPagina(valueSearch?.registrosPorPagina ?? 5);
            });

        },
    }))
);