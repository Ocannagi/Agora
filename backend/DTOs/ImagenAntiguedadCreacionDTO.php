<?php

class ImagenAntiguedadCreacionDTO implements ICreacionDTO
{
    public string $imaUrl;
    public string $imaNombreArchivo; // Este campo es necesario para guardar el nombre del archivo
    public int $antId;
    public int $imaOrden;
    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('imaUrl', $data)) {
            $this->imaUrl = (string)$data['imaUrl'];
        }

        if (array_key_exists('imaNombreArchivo', $data)) {
            $this->imaNombreArchivo = (string)$data['imaNombreArchivo'];
        }

        if (array_key_exists('antId', $data)) {
            $this->antId = (int)$data['antId'];
        }

        if (array_key_exists('imaOrden', $data)) {
            $this->imaOrden = (int)$data['imaOrden'];
        }
    }
}