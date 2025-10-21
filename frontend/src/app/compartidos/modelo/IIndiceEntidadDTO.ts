export interface IIndiceEntidadDTO {
   id: number;
   nombre: string;
   acciones: {
        editar: string;
   };
}

export type KeysIIndiceEntidadDTO = keyof IIndiceEntidadDTO;