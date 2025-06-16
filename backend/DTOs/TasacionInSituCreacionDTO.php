<?php

class TasacionInSituCreacionDTO implements ICreacionDTO
{
    use TraitMapDomicilioDTO; // Trait para mapear el domicilio desde el array o stdClass.
    use TraitMapTasacionDigitalDTO; // Trait para mapear TasacionDigitalDTO.
    
    public TasacionDigitalDTO $tasacionDigital; // Tasación digital asociada a la tasación in situ.
    public DomicilioDTO $domicilio; // Identificador del domicilio de la tasación in situ.
    public string $tisFechaTasInSituProvisoria; // Fecha provisoria para la tasación in situ.

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('tasacionDigital', $data) && $data['tasacionDigital'] instanceof TasacionDigitalDTO) {
            $this->tasacionDigital = $data['tasacionDigital'];
        } else {
            $tasacionDigital = $this->mapTasacionDigitalDTO($data);
            if ($tasacionDigital !== null) {
                $this->tasacionDigital = $tasacionDigital;
            }
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