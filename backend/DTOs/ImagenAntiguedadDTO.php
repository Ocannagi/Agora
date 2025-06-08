<?php

class ImagenAntiguedadDTO implements IDTO
{
    public int $imaId;
    public int $antId;
    public string $imaUrl;
    public string $imaNombreArchivo; // Este campo es necesario para guardar el nombre del archivo
    public int $imaOrden;

    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('imaId', $data)) {
            $this->imaId = (int)$data['imaId'];
        }

        if (array_key_exists('antId', $data)) {
            $this->antId = (int)$data['antId'];
        } else if (array_key_exists('imaAntId', $data)) {
            $this->antId = (int)$data['imaAntId'];
        }

        if (array_key_exists('imaUrl', $data)) {
            $this->imaUrl = (string)$data['imaUrl'];
        }

        if (array_key_exists('imaNombreArchivo', $data)) {
            $this->imaNombreArchivo = (string)$data['imaNombreArchivo'];
        }

        if (array_key_exists('imaOrden', $data)) {
            $this->imaOrden = (int)$data['imaOrden'];
        }

    }
}