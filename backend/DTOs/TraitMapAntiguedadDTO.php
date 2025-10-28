<?php
use Model\CustomException;

trait TraitMapAntiguedadDTO
{
    use TraitMapPeriodoDTO; // Trait para mapear PeriodoDTO
    use TraitMapSubcategoriaDTO; // Trait para mapear SubcategoriaDTO
    use TraitMapUsuarioDTO; // Trait para mapear UsuarioDTO

    
    
    private function mapAntiguedadDTO(array | stdClass $data, bool $returnArray = false): AntiguedadDTO | array | null
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arrayAnt = [];

        if (array_key_exists('antiguedad', $data)) {
            return $returnArray ? get_object_vars(new AntiguedadDTO($data['antiguedad'])) : new AntiguedadDTO($data['antiguedad']);
        }

        if (array_key_exists('antId', $data)) {
            $arrayAnt['antId'] = (int)$data['antId'];
        }  
        else if (array_key_exists('tasAntId', $data)) {
            $arrayAnt['antId'] = (int)$data['tasAntId'];
        } else if (array_key_exists('aavAntId', $data)) {
            $arrayAnt['antId'] = (int)$data['aavAntId'];
        } else {
            return null; // No se puede mapear sin antId
        }

        if (array_key_exists('periodo', $data) && $data['periodo'] instanceof PeriodoDTO) {
           $arrayAnt['periodo'] = $data['periodo'];
        } else {
            $periodoDTO = $this->mapPeriodoDTO($data, $returnArray);
            if ($periodoDTO !== null) {
                $arrayAnt['periodo'] = $periodoDTO;
            }
        }

        if (array_key_exists('subcategoria', $data) && $data['subcategoria'] instanceof SubcategoriaDTO) {
            $arrayAnt['subcategoria'] = $data['subcategoria'];
        } else {
            $subcategoriaDTO = $this->mapSubcategoriaDTO($data, $returnArray);
            if ($subcategoriaDTO !== null) {
                $arrayAnt['subcategoria'] = $subcategoriaDTO;
            }
        }

        if (array_key_exists('antNombre', $data)) {
            $this->antNombre = (string)$data['antNombre'];
        }

        if (array_key_exists('antDescripcion', $data)) {
            $arrayAnt['antDescripcion'] = (string)$data['antDescripcion'];
        }

        if (array_key_exists('imagenes', $data) && is_array($data['imagenes'])) {
            $arrayAnt['imagenes'] = [];
            foreach ($data['imagenes'] as $imagen) {
                if ($imagen instanceof ImagenAntiguedadDTO) {
                    $arrayAnt['imagenes'][] = $imagen;
                } else {
                    $imagenDTO = new ImagenAntiguedadDTO($imagen);
                    $arrayAnt['imagenes'][] = $imagenDTO;
                }
            }
        }

        if (array_key_exists('usuario', $data) && $data['usuario'] instanceof UsuarioDTO) {
            $arrayAnt['usuario'] = $data['usuario'];
        } else {
            $usuarioDTO = $this->mapUsuarioDTO($data, $returnArray);
            if ($usuarioDTO !== null) {
                $arrayAnt['usuario'] = $usuarioDTO;
            }
        }

        if (array_key_exists('tipoEstado', $data) && $data['tipoEstado'] instanceof TipoEstadoEnum) {
            $arrayAnt['tipoEstado'] = $data['tipoEstado'];
        } else {
            try {
                if (array_key_exists('antTipoEstado', $data)) {
                    $arrayAnt['tipoEstado'] = TipoEstadoEnum::from($data['antTipoEstado']);
                } elseif (array_key_exists('tipoEstado', $data)) {
                    $arrayAnt['tipoEstado'] = TipoEstadoEnum::from($data['tipoEstado']);
                }
            } catch (ValueError $th) {
                throw new CustomException(code:400, message:'El tipo de estado no es válido.');
            }
        }

        if (array_key_exists('antFechaEstado', $data)) {
            $arrayAnt['antFechaEstado'] = (string)$data['antFechaEstado'];
        }

        return $returnArray ? $arrayAnt : new AntiguedadDTO($arrayAnt);
    }
}