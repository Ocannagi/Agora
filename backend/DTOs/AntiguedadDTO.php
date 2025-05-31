<?php

use Utilidades\Output;

class AntiguedadDTO implements IDTO
{
    public int $antId;
    public PeriodoDTO $periodo;
    public SubcategoriaDTO $subcategoria;
    public string $antDescripcion;
    /**
     * @var ?ImagenAntiguedadDTO[]
     */
    public ?array $imagenes = null;
    public UsuarioDTO $usuario;
    public TipoEstadoEnum $tipoEstado;
    public string $antFechaEstado;

    use TraitMapPeriodoDTO; // Trait para mapear PeriodoDTO
    use TraitMapSubcategoriaDTO; // Trait para mapear SubcategoriaDTO
    use TraitMapUsuarioDTO; // Trait para mapear UsuarioDTO

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('antId', $data)) {
            $this->antId = (int)$data['antId'];
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

        if (array_key_exists('antDescripcion', $data)) {
            $this->antDescripcion = (string)$data['antDescripcion'];
        }

        if (array_key_exists('imagenes', $data) && is_array($data['imagenes'])) {
            $this->imagenes = [];
            foreach ($data['imagenes'] as $imagen) {
                if ($imagen instanceof ImagenAntiguedadDTO) {
                    $this->imagenes[] = $imagen;
                } else {
                    $imagenDTO = new ImagenAntiguedadDTO($imagen);
                    $this->imagenes[] = $imagenDTO;
                }
            }
        }

        if (array_key_exists('usuario', $data) && $data['usuario'] instanceof UsuarioDTO) {
            $this->usuario = $data['usuario'];
        } else {
            $usuarioDTO = $this->mapUsuarioDTO($data);
            if ($usuarioDTO !== null) {
                $this->usuario = $usuarioDTO;
            }
        }

        if (array_key_exists('tipoEstado', $data) && $data['tipoEstado'] instanceof TipoEstadoEnum) {
            $this->tipoEstado = $data['tipoEstado'];
        } else {
            try {
                if (array_key_exists('antTipoEstado', $data)) {
                    $this->tipoEstado = TipoEstadoEnum::from($data['antTipoEstado']);
                } elseif (array_key_exists('tipoEstado', $data)) {
                    $this->tipoEstado = TipoEstadoEnum::from($data['tipoEstado']);
                }
            } catch (ValueError $th) {
                Output::outputError(400, 'El tipo de estado no es válido.');
            }
        }

        if (array_key_exists('antFechaEstado', $data)) {
            $this->antFechaEstado = (string)$data['antFechaEstado'];
        }
    }
}
