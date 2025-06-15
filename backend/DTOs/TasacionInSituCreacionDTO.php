<?php

class TasacionInSituCreacionDTO implements ICreacionDTO
{
    use TraitMapDomicilioDTO; // Trait para mapear el domicilio desde el array o stdClass.
    
    public DomicilioDTO $domicilio; // Identificador del domicilio de la tasación in situ.
    public string $tisFechaTasInSituAcordada; // Fecha acordada para la tasación in situ.

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('domicilio', $data) && $data['domicilio'] instanceof DomicilioDTO) {
            $this->domicilio = $data['domicilio'];
        } else {
            $domicilioDTO = $this->mapDomicilioDTO($data);
            if ($domicilioDTO !== null) {
                $this->domicilio = $domicilioDTO;
            }
        }

        if (array_key_exists('tisFechaTasInSituAcordada', $data)) {
            $this->tisFechaTasInSituAcordada = $data['tisFechaTasInSituAcordada'];
        }
    }
}