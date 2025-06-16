<?php

class TasacionInSituCreacionDTO implements ICreacionDTO
{
    use TraitMapDomicilioDTO; // Trait para mapear el domicilio desde el array o stdClass.
    //use TraitMapTasacionDigitalDTO; // Trait para mapear TasacionDigitalDTO.
    
    //public TasacionDigitalDTO $tasacionDigital; // Tasación digital asociada a la tasación in situ.
    public int $tadId; // Identificador de la tasación digital asociada.
    public DomicilioDTO $domicilio; // Identificador del domicilio de la tasación in situ.
    public string $tisFechaTasInSituProvisoria; // Fecha provisoria para la tasación in situ.

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('tadId', $data)) {
            $this->tadId = (int)$data['tadId'];
        } else if (array_key_exists('tisTadId', $data)) {
            $this->tadId = (int)$data['tisTadId'];
        }

        if (array_key_exists('domicilio', $data) && $data['domicilio'] instanceof DomicilioDTO) {
            $this->domicilio = $data['domicilio'];
        } else {
            $domicilioDTO = $this->mapDomicilioDTO($data);
            if ($domicilioDTO !== null) {
                $this->domicilio = $domicilioDTO;
            }
        }

        if (array_key_exists('tisFechaTasInSituProvisoria', $data)) {
            $this->tisFechaTasInSituProvisoria = $data['tisFechaTasInSituProvisoria'];
        }
    }
}