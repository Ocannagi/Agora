<?php

class HabilidadCreacionDTO implements ICreacionDTO
{
    public UsuarioDTO $usuario; // ID del usuario al que pertenece la habilidad.
    public PeriodoDTO $periodo; // Periodo de la habilidad.
    public SubcategoriaDTO $subcategoria; // Subcategoría de la habilidad.

    use TraitMapUsuarioDTO;
    use TraitMapPeriodoDTO;
    use TraitMapSubcategoriaDTO;

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('usuario', $data) && $data['usuario'] instanceof UsuarioDTO) {
            $this->usuario = $data['usuario'];
        } else {
            $usuarioDTO = $this->mapUsuarioDTO($data);
            if ($usuarioDTO !== null) {
                $this->usuario = $usuarioDTO;
            }
        }
        
        if (array_key_exists('periodo', $data) && $data['periodo'] instanceof PeriodoDTO) {
            $this->periodo = $data['periodo'];
        } else {
            $periodoDTO = $this->mapPeriodoDTO($data);
            if ($periodoDTO !== null) {
                $this->periodo = $periodoDTO;
            }
        }

        if (array_key_exists('subcategoria', $data) && $data['subcategoria'] instanceof SubcategoriaDTO) {
            $this->subcategoria = $data['subcategoria'];
        } else {
            $subcategoriaDTO = $this->mapSubcategoriaDTO($data);
            if ($subcategoriaDTO !== null) {
                $this->subcategoria = $subcategoriaDTO;
            }
        }

    }
}