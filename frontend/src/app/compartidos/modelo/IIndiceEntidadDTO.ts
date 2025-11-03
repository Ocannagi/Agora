export interface IIndiceEntidadDTO {
   id: number;
   nombre: string;
   extra?: string;
   acciones: {
     editar?: string;
     ver?: string;
     borrar?: boolean;
   };
}

export type KeysIIndiceEntidadDTO = keyof IIndiceEntidadDTO;

export const TituloExtraSeparador = '@@'