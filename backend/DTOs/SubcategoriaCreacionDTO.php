<?php

class SubcategoriaCreacionDTO implements ICreacionDTO
{
    public string $scatDescripcion;
    public int $scatCatId;

    public function __construct(array | stdClass $data)
    {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('scatDescripcion', $data)) {
            $this->scatDescripcion = (string)$data['scatDescripcion'];
        }
        if (array_key_exists('scatCatId', $data)) {
            $this->scatCatId = (int)$data['scatCatId'];
        }
    }
}