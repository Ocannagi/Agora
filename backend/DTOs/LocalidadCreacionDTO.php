<?php

class LocalidadCreacionDTO implements ICreacionDTO
{
    public string $locDescripcion;
    public ProvinciaDTO $provincia;

    use TraitMapProvinciaDTO; // Trait para mapear ProvinciaDTO

    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('locDescripcion', $data)) {
            $this->locDescripcion = (string)$data['locDescripcion'];
        }
        
        $provinciaDTO = $this->mapProvinciaDTO($data);
        if ($provinciaDTO !== null) {
            $this->provincia = $provinciaDTO;
        }
        
    }
}