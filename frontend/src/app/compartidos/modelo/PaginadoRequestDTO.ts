export interface PaginadoRequestDTO {
  pagina: number;
  registrosPorPagina: number;
  filtrarPorUsrId: boolean; // Nuevo campo agregado - Lo pensé para indicar en el controller de PHP si se debe filtrar por usrId del usuario autenticado
}

export interface PaginadoRequestSearchDTO extends PaginadoRequestDTO { // ya iba a tener que agregar un montón de cosas de todas formas...
  searchWord: string;
}