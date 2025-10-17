<?php

class PaginadoResponseDTO implements IDTO
{
    public int $totalRegistros;
    public int $paginaActual;
    public int $registrosPorPagina;
    /** @var array<IDTO> */
    public array $arrayEntidad;

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('totalRegistros', $data)) {
            $this->totalRegistros = (int)$data['totalRegistros'];
        }
        if (array_key_exists('paginaActual', $data)) {
            $this->paginaActual = (int)$data['paginaActual'];
        }
        if (array_key_exists('registrosPorPagina', $data)) {
            $this->registrosPorPagina = (int)$data['registrosPorPagina'];
        }
        if (array_key_exists('arrayEntidad', $data) && is_array($data['arrayEntidad'])) {
            $this->arrayEntidad = [];
            foreach ($data['datos'] as $item) {
                if ($item instanceof IDTO) {
                    $this->arrayEntidad[] = $item;
                }
            }
        }
    }
}