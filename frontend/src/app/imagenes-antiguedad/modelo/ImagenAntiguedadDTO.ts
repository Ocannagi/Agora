export interface ImagenAntiguedadDTO {
  imaId: number;
  antId: number;                 // en backend también puede venir como imaAntId
  imaUrl: string;
  imaNombreArchivo: string;      // nombre del archivo
  imaOrden: number;
}

export interface ImagenAntiguedadCreacionDTO {
  imaUrl: string;
  imaNombreArchivo: string;      // nombre del archivo
  antId: number;
  imaOrden: number;
}

export interface ImagenAntiguedadOrdenDTO {
  imaId: number;
  imaOrden: number;
}

export interface ImagenesAntiguedadReordenarDTO {
  antId: number;                               // en backend también puede venir como imaAntId
  imagenesAntiguedadOrden: ImagenAntiguedadOrdenDTO[];
}