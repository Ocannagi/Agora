<?php

class SubcategoriaCreacionDTO implements ICreacionDTO
{
    public string $scatDescripcion;
    public CategoriaDTO $categoria; // Relación con la categoría

    public function __construct(array | stdClass $data)
    {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('scatDescripcion', $data)) {
            $this->scatDescripcion = (string)$data['scatDescripcion'];
        }
        if (array_key_exists('categoria', $data)) {
            $this->categoria = new CategoriaDTO($data['categoria']);
        } else if (array_key_exists('catId', $data)) {
            $arrayCat = ['catId' => (int)$data['catId']];
            if (array_key_exists('catDescripcion', $data))
                $arrayCat['catDescripcion'] = (string)$data['catDescripcion'];

            $this->categoria = new CategoriaDTO($arrayCat);
        } else if (array_key_exists('scatCatId', $data)) {
            $arrayCat = ['catId' => (int)$data['scatCatId']];

            $this->categoria = new CategoriaDTO($arrayCat);
        }
    }
}