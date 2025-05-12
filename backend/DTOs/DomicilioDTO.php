<?php

class DomicilioDTO implements IDTO
{
    public int $domId;
    public string $domCPA;
    public string $domCalleRuta;
    public int $domNroKm;
    public ?string $domPiso = null;
    public ?string $domDepto = null;

    public LocalidadDTO $localidad; // Relación con la localidad

    use TraitMapLocalidadDTO; // Trait para mapear LocalidadDTO

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('domId', $data)) {
            $this->domId = (int)$data['domId'];
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
        if (array_key_exists('domCPA', $data)) {
            $this->domCPA = (string)$data['domCPA'];
        }

        if (array_key_exists('localidad', $data) && $data['localidad'] instanceof LocalidadDTO) {
            $this->localidad = $data['localidad'];
        } else {
            $localidadDTO = $this->mapLocalidadDTO($data);
            if ($localidadDTO !== null) {
                $this->localidad = $localidadDTO;
            }
        }
        
    }
}
