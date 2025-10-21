export interface PaginadoResponseDTO<T> {
  totalRegistros: number;
  paginaActual: number;
  registrosPorPagina: number;
  arrayEntidad: T[];
}