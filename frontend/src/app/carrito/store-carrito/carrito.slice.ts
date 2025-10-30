import { PaginadoRequestSearchDTO } from "../../compartidos/modelo/PaginadoRequestDTO";
import { PaginadoResponseDTO } from "../../compartidos/modelo/PaginadoResponseDTO";
import { AntiguedadALaVentaDTO } from "../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO";
import { AntiguedadEnCarritoDTO } from "../modelo/antiguedadEnCarritoDTO";

export interface CarritoSlice{
    readonly busy: boolean;
    readonly errors: string[];
    readonly carrito: AntiguedadEnCarritoDTO[];
    readonly triggerComprobacion : boolean;
    readonly flagComprobacion : boolean;
}


export const CarritoInitialState: CarritoSlice = {
    busy: false,
    errors: [],
    carrito: [] as AntiguedadEnCarritoDTO[],
    triggerComprobacion: false, //Para forzar la comprobación de stock/precio
    flagComprobacion: true, //Flag interno para evitar bucles infinitos
};

export type PersistenciaCarritoSlice = Pick<CarritoSlice, 'carrito'>;
export const stringPersistencia = 'carrito';