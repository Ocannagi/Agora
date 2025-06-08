<?php

class ImagenAntiguedadOrdenDTO implements IDTO
{
    public int $imaId;
    public int $imaOrden;

    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('imaId', $data)) {
            $this->imaId = (int)$data['imaId'];
        }

        if (array_key_exists('imaOrden', $data)) {
            $this->imaOrden = (int)$data['imaOrden'];
        }
    }
}