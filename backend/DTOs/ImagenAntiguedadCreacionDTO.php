<?php

class ImagenAntiguedadCreacionDTO implements ICreacionDTO
{
    public string $imaUrl;
    public ?string $imaDescripcion;
    public int $antId;

    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('imaUrl', $data)) {
            $this->imaUrl = (string)$data['imaUrl'];
        }
        
        if (array_key_exists('imaDescripcion', $data)) {
            $this->imaDescripcion = (string)$data['imaDescripcion'];
        } else {
            $this->imaDescripcion = null;
        }

        if (array_key_exists('antId', $data)) {
            $this->antId = (int)$data['antId'];
        }
    }
}