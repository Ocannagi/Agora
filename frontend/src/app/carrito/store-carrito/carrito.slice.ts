import { AntiguedadEnCarritoDTO } from "../modelo/antiguedadEnCarritoDTO";

export interface CarritoSlice{
    readonly busy: boolean;
    readonly errors: string[];
    readonly usrId: number | null;
    readonly carrito: AntiguedadEnCarritoDTO[];
}


export const CarritoInitialState: CarritoSlice = {
    busy: false,
    errors: [],
    usrId: null,
    carrito: [] as AntiguedadEnCarritoDTO[],
};

export type PersistenciaCarritoSlice = Pick<CarritoSlice, 'usrId' | 'carrito'>;
export const stringPersistencia = 'carrito';