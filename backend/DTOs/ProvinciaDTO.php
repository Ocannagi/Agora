<?php

class ProvinciaDTO implements IDTO
{
    public int $provId;
    public string $provDescripcion;

    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('provId', $data)) {
            $this->provId = (int)$data['provId'];
        }
        if (array_key_exists('provDescripcion', $data)) {
            $this->provDescripcion = (string)$data['provDescripcion'];
        }
    }
}
