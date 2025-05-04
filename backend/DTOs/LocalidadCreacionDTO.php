<?php

class LocalidadCreacionDTO implements ICreacionDTO
{
    public string $locDescripcion;
    public int $locProvId;

    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('locDescripcion', $data)) {
            $this->locDescripcion = (string)$data['locDescripcion'];
        }
        if (array_key_exists('locProvId', $data)) {
            $this->locProvId = (int)$data['locProvId'];
        }
    }
}