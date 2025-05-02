<?php

class PeriodoDTO implements IDTO
{
    public int $perId;
    public string $perDescripcion;

    public function __construct(array | stdClass $data)
    {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }
        
        if (array_key_exists('perId', $data)) {
            $this->perId = (int)$data['perId'];
        } 
        
        if (array_key_exists('perDescripcion', $data)) {
            $this->perDescripcion = (string)$data['perDescripcion'];
        } 
        
    }
}