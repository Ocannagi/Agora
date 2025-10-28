import { KeysIIndiceEntidadDTO } from "../../../modelo/IIndiceEntidadDTO";
import { PaginadoRequestDTO } from "../../../modelo/PaginadoRequestDTO";

export interface IndiceEntidadSlice{
    readonly titulo: string;
    readonly pathCrear: string;
    readonly columnasDefault: KeysIIndiceEntidadDTO[];
    readonly columnasExtras: string[];
    readonly paginado: PaginadoRequestDTO;
    readonly busy: boolean;
}

export const IndiceEntidadInitialState: IndiceEntidadSlice = {
    titulo: '',
    pathCrear: '',
    columnasDefault: ['id', 'nombre', 'acciones'],
    columnasExtras: [],
    paginado: {
        pagina: 1,
        registrosPorPagina: 5,
        filtrarPorUsrId: true
    },
    busy: false
};
