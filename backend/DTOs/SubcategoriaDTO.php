<?php

class SubcategoriaDTO implements IDTO
{
    public int $scatId;
    public int $scatCatId;
    public string $scatDescripcion;


    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('scatId', $data)) {
            $this->scatId = (int)$data['scatId'];
        }
        if (array_key_exists('scatCatId', $data)) {
            $this->scatCatId = (int)$data['scatCatId'];
        }
        if (array_key_exists('scatDescripcion', $data)) {
            $this->scatDescripcion = (string)$data['scatDescripcion'];
        }
    }
}
