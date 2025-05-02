<?php

class CategoriaCreacionDTO implements ICreacionDTO
{
    public string $catDescripcion;

    public function __construct(array | stdClass $data)
    {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }
        
        if (array_key_exists('catDescripcion', $data)) {
            $this->catDescripcion = (string)$data['catDescripcion'];
        } 
    }
}