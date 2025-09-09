<?php

class AntiguedadAlaVentaCreacionDTO implements ICreacionDTO
{
    public AntiguedadDTO $antiguedad;
    public DomicilioDTO $domicilioOrigen;
    public float $aavPrecioVenta;
    public ?TasacionDigitalDTO $tasacion = null;


    use TraitMapAntiguedadDTO; // Trait para mapear AntiguedadDTO
    use TraitMapTasacionDigitalDTO; // Trait para mapear TasacionDigitalDTO.

   public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('antiguedad', $data)) {
            if ($data['antiguedad'] instanceof AntiguedadDTO) {
                $this->antiguedad = $data['antiguedad'];
            } else {
                $antiguedadDTO = $this->mapAntiguedadDTO($data);
                if ($antiguedadDTO !== null) {
                    $this->antiguedad = $antiguedadDTO;
                }
            }
        } else if (array_key_exists('antId', $data)) {
            $this->antiguedad = $this->mapAntiguedadDTO(['antId' => (int)$data['antId']]);
        } else if (array_key_exists('aavAntId', $data)) {
            $this->antiguedad = $this->mapAntiguedadDTO(['antId' => (int)$data['aavAntId']]);
        }

        if (array_key_exists('domicilioOrigen', $data)) {
            if ($data['domicilioOrigen'] instanceof DomicilioDTO) {
                $this->domicilioOrigen = $data['domicilioOrigen'];
            } else {
                $domicilioDTO = $this->mapDomicilioDTO($data['domicilioOrigen']);
                if ($domicilioDTO !== null) {
                    $this->domicilioOrigen = $domicilioDTO;
                }
            }
        } else if (array_key_exists('aavDomOrigen', $data)) {
            $this->domicilioOrigen = $this->mapDomicilioDTO(['domId' => $data['aavDomOrigen']]);
        }

        if (array_key_exists('aavPrecioVenta', $data)) {
            $this->aavPrecioVenta = (float)$data['aavPrecioVenta'];
        }

        if (array_key_exists('tasacion', $data)) {
            if ($data['tasacion'] instanceof TasacionDigitalDTO) {
                $this->tasacion = $data['tasacion'];
            } else {
                $tasacionDTO = $this->mapTasacionDigitalDTO($data['tasacion']);
                if ($tasacionDTO !== null) {
                    $this->tasacion = $tasacionDTO;
                }
            }
        } else if (array_key_exists('aavTadId', $data)) {
            $this->tasacion = $this->mapTasacionDigitalDTO(['tadId' => (int)$data['aavTadId']]);
        } else if (array_key_exists('tadId', $data)) {
            $this->tasacion = $this->mapTasacionDigitalDTO(['tadId' => (int)$data['tadId']]);
        }

        if ($this->tasacion !== null){
            if (array_key_exists('tisId', $data)) {
                $this->tasacion->tasacionInSitu = new TasacionInSituDTO(['tisId' => (int)$data['tisId']]);
            }
        }

        
    }
}
