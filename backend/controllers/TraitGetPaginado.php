<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;

trait TraitGetPaginado
{
    use TraitGetInterno;

    public function getPaginado(mixed $paginado, mysqli $mysqli, string $baseCount, string $whereCount, string $msgCount, string $queryClassDTO, string $classDTO)
    {
        $paginadoResponseDTO = $this->getPaginadoResponseDTO($paginado, $mysqli, $baseCount, $whereCount, $msgCount, $queryClassDTO, $classDTO);
        Output::outputJson($paginadoResponseDTO);
    }

    public function getPaginadoResponseDTO(mixed $paginado, mysqli $mysqli, string $baseCount, string $whereCount, string $msgCount, string $queryClassDTO, string $classDTO) : PaginadoResponseDTO
    {
        if (is_array($paginado)) {
            if (array_key_exists('pagina', $paginado) && array_key_exists('registrosPorPagina', $paginado)) {
                if (!Input::esNotNullVacioBlanco($paginado['pagina']) || !Input::esNotNullVacioBlanco($paginado['registrosPorPagina'])) {
                    throw new InvalidArgumentException("Los parámetros 'pagina' y 'registrosPorPagina' no pueden estar vacíos.");
                }

                settype($paginado['pagina'], 'integer');
                settype($paginado['registrosPorPagina'], 'integer');

                $pagina = $paginado['pagina'];

                if($pagina < 1) {
                    $pagina = 1;
                }

                $registrosPorPagina = $paginado['registrosPorPagina'];



                $offset = ($pagina - 1) * $paginado['registrosPorPagina'];

                // Obtener total de registros

                $total = Querys::obtenerCount(link: $mysqli, base: $baseCount, where: $whereCount, msg: $msgCount);
                $query = $queryClassDTO  . " LIMIT $registrosPorPagina OFFSET $offset";



                $arrayEntidad = $this->getInterno(query: $query, classDTO: $classDTO, linkExterno: $mysqli);

                $paginadoResponseDTO = new PaginadoResponseDTO([
                    'totalRegistros' => $total,
                    'paginaActual' => $pagina,
                    'registrosPorPagina' => $registrosPorPagina,
                    'arrayEntidad' => $arrayEntidad
                ]);

                return $paginadoResponseDTO;
            } else {
                throw new InvalidArgumentException("Faltan los parámetros 'pagina' o 'registrosPorPagina'.");
            }
        } else {
            throw new InvalidArgumentException("El paginado debe ser un array asociativo.");
        }
    }

    public function isFiltrarPorUsrId(mixed $paginado) : bool
    {
        if (is_array($paginado) && array_key_exists('filtrarPorUsrId', $paginado)) {
            return filter_var($paginado['filtrarPorUsrId'], FILTER_VALIDATE_BOOLEAN);
        }
        return false; // Valor por defecto si no se proporciona
    }

    public function validatePaginadoSearch(mixed $paginado) : void
    {
        if (is_array($paginado)) {
            if (!array_key_exists('searchWord', $paginado) || !Input::esNotNullVacioBlanco($paginado['searchWord'])) {
                throw new InvalidArgumentException("El parámetro 'searchWord' es obligatorio y no puede estar vacío.");
            }
            if (!array_key_exists('pagina', $paginado) || !Input::esNotNullVacioBlanco($paginado['pagina'])) {
                throw new InvalidArgumentException("El parámetro 'pagina' es obligatorio y no puede estar vacío.");
            }
            if (!array_key_exists('registrosPorPagina', $paginado) || !Input::esNotNullVacioBlanco($paginado['registrosPorPagina'])) {
                throw new InvalidArgumentException("El parámetro 'registrosPorPagina' es obligatorio y no puede estar vacío.");
            }
            if (!array_key_exists('filtrarPorUsrId', $paginado) || !Input::esNotNullVacioBlanco($paginado['filtrarPorUsrId'])) {
                throw new InvalidArgumentException("El parámetro 'filtrarPorUsrId' es obligatorio y no puede estar vacío.");
            }
        } else {
            throw new InvalidArgumentException("El paginado debe ser un array asociativo.");
        }
    }


}
