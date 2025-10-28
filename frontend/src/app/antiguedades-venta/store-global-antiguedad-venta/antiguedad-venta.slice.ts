import { PaginadoRequestSearchDTO } from "../../compartidos/modelo/PaginadoRequestDTO";
import { PaginadoResponseDTO } from "../../compartidos/modelo/PaginadoResponseDTO";
import { AntiguedadALaVentaDTO } from "../modelo/AntiguedadAlaVentaDTO";

export interface AntiguedadVentaSlice{
    readonly paginadoRequest: PaginadoRequestSearchDTO;
    readonly paginadoResponse: PaginadoResponseDTO<AntiguedadALaVentaDTO>;
    readonly busy: boolean;

}


export const AntiguedadVentaInitialState: AntiguedadVentaSlice = {
    paginadoRequest: {
        pagina: 1,
        registrosPorPagina: 5,
        filtrarPorUsrId: false,
        searchWord: ''
    },
    paginadoResponse: {
        totalRegistros: 0,
        paginaActual: 1,
        registrosPorPagina: 5,
        arrayEntidad: [] as AntiguedadALaVentaDTO[]
    },
    busy: false
};