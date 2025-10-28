<?php

class AntiguedadCreacionDTO implements ICreacionDTO
{
    public PeriodoDTO $periodo;
    public SubcategoriaDTO $subcategoria;
    public string $antNombre;
    public string $antDescripcion;
    public UsuarioDTO $usuario;
    public TipoEstadoEnum $tipoEstado = TipoEstadoEnum::RetiradoDisponible; // Por defecto, la antigüedad se crea como "Retirado disponible"

    /* No se necesita la propiedad $imagenes en la creación de una antigüedad porque no se envían imágenes en la creación
    *las imágenes se envían aparte en su propio endpoint
    */

    use TraitMapPeriodoDTO; // Trait para mapear PeriodoDTO
    use TraitMapSubcategoriaDTO; // Trait para mapear SubcategoriaDTO
    use TraitMapUsuarioDTO; // Trait para mapear UsuarioDTO

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
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

        if (array_key_exists('antNombre', $data)) {
            $this->antNombre = (string)$data['antNombre'];
        }

        if (array_key_exists('antDescripcion', $data)) {
            $this->antDescripcion = (string)$data['antDescripcion'];
        }

        if (array_key_exists('usuario', $data) && $data['usuario'] instanceof UsuarioDTO) {
            $this->usuario = $data['usuario'];
        } else {
            $usuarioDTO = $this->mapUsuarioDTO($data);
            if ($usuarioDTO !== null) {
                $this->usuario = $usuarioDTO;
            } 
        }
    }
}