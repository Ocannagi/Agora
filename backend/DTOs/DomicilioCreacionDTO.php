<?php

class DomicilioCreacionDTO implements ICreacionDTO
{
    public string $domCPA;
    public string $domCalleRuta;
    public int $domNroKm;
    public ?string $domPiso = null;
    public ?string $domDepto = null;
    public LocalidadDTO $localidad; // Relación con la localidad

    public function __construct(array | stdClass $data)
    {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('domCPA', $data)) {
            $this->domCPA = (string)$data['domCPA'];
        }
        if (array_key_exists('domCalleRuta', $data)) {
            $this->domCalleRuta = (string)$data['domCalleRuta'];
        }
        if (array_key_exists('domNroKm', $data)) {
            $this->domNroKm = (int)$data['domNroKm'];
        }
        if (array_key_exists('domPiso', $data)) {
            $this->domPiso = (string)$data['domPiso'];
        }
        if (array_key_exists('domDepto', $data)) {
            $this->domDepto = (string)$data['domDepto'];
        }
        if (array_key_exists('localidad', $data)) {
            $this->localidad = new LocalidadDTO($data['localidad']);
        } else if (array_key_exists('locId', $data)) {
            $arrayLoc = ['locId' => (int)$data['locId']];
            if (array_key_exists('locDescripcion', $data))
                $arrayLoc['locDescripcion'] = (string)$data['locDescripcion'];

            if (array_key_exists('provincia', $data)) {
                $arrayLoc['provincia'] = new ProvinciaDTO($data['provincia']);
            } else if (array_key_exists('provId', $data)) {
                $arrayProv = ['provId' => (int)$data['provId']];
                if (array_key_exists('provDescripcion', $data))
                    $arrayProv['provDescripcion'] = (string)$data['provDescripcion'];

                $arrayLoc['provincia']
                    = new ProvinciaDTO($arrayProv);
            }
            $this->localidad = new LocalidadDTO($arrayLoc);
        }
    }
}