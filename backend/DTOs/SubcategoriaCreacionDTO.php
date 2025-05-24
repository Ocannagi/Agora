<?php

class SubcategoriaCreacionDTO implements ICreacionDTO
{
    public string $scatDescripcion;
    public CategoriaDTO $categoria; // Relación con la categoría

    use TraitMapCategoriaDTO; // Trait para mapear CategoriaDTO

    public function __construct(array | stdClass $data)
    {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('scatDescripcion', $data)) {
            $this->scatDescripcion = (string)$data['scatDescripcion'];
        }

        if (array_key_exists('categoria', $data) && $data['categoria'] instanceof CategoriaDTO) {
            $this->categoria = $data['categoria'];
        } else {
            $categoriaDTO = $this->mapCategoriaDTO($data);
            if ($categoriaDTO !== null) {
                $this->categoria = $categoriaDTO;
            }
        }
        
    }
}