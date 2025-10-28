import { PaginadoRequestSearchDTO } from "../../compartidos/modelo/PaginadoRequestDTO";
import { AntiguedadALaVentaDTO } from "../modelo/AntiguedadAlaVentaDTO";

export interface AntiguedadVentaSlice{

    readonly antiguedadesVenta: AntiguedadALaVentaDTO[];
    readonly paginado: PaginadoRequestSearchDTO;
    readonly busy: boolean;

}


export const AntiguedadVentaInitialState: AntiguedadVentaSlice = {
    antiguedadesVenta: [],
    paginado: {
        pagina: 1,
        registrosPorPagina: 10,
        filtrarPorUsrId: false,
        searchWord: ''
    },
    busy: false
};