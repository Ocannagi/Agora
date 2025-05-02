<?php

class CategoriaDTO implements IDTO
{
    public int $catId;
    public string $catDescripcion;

    public function __construct(array | stdClass $data)
    {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }
        
        if (array_key_exists('catId', $data)) {
            $this->catId = (int)$data['catId'];
        }

        if (array_key_exists('catDescripcion', $data)) {
            $this->catDescripcion = (string)$data['catDescripcion'];
        }
    }
}