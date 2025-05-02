<?php

class PeriodoCreacionDTO implements ICreacionDTO
{
    public string $perDescripcion;

    public function __construct(array | stdClass $data)
    {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }
        
        if (array_key_exists('perDescripcion', $data)) {
            $this->perDescripcion = (string)$data['perDescripcion'];
        } 
    }
}