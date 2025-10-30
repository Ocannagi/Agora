import { AntiguedadALaVentaDTO } from "../../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO";

export interface SearchWordSlice{
    readonly pagina: number;
    readonly registrosPorPagina: number;
    readonly totalRegistros: number;
    readonly filtrarPorUsrId: boolean;
    readonly searchWord: string;
    readonly arrayEntidad: AntiguedadALaVentaDTO[];
}

export const SearchWordInitialState: SearchWordSlice = {
    pagina: 1,
    registrosPorPagina: 5,
    totalRegistros: 0,
    filtrarPorUsrId: false,
    searchWord: '',
    arrayEntidad: [],
};
