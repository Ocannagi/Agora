<?php

class LocalidadDTO implements IDTO
{
    public int $locId;
    public string $locDescripcion;
    public ProvinciaDTO $provincia; // Relación con la provincia

    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('locId', $data)) {
            $this->locId = (int)$data['locId'];
        }
        if (array_key_exists('locDescripcion', $data)) {
            $this->locDescripcion = (string)$data['locDescripcion'];
        }
        if (array_key_exists('provincia', $data)) {
            $this->provincia = new ProvinciaDTO($data['provincia']);
        } else if (array_key_exists('provId', $data)) {
            $arrayProv = ['provId' => (int)$data['provId']];
            if (array_key_exists('provDescripcion', $data))
                $arrayProv['provDescripcion'] = (string)$data['provDescripcion'];

            $this->provincia = new ProvinciaDTO($arrayProv);
        } else if (array_key_exists('locProvId', $data)) {
            $arrayProv = ['provId' => (int)$data['locProvId']];

            $this->provincia = new ProvinciaDTO($arrayProv);
        }
    }
}