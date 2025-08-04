<?php

class AntiguedadAlaVentaCreacionDTO implements ICreacionDTO
{
    public AntiguedadDTO $antiguedad;
    public DomicilioDTO $domicilio;
    public float $aavPrecioVenta;
    public TasacionDigitalDTO $tasacion;


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

        if (array_key_exists('domicilio', $data)) {
            if ($data['domicilio'] instanceof DomicilioDTO) {
                $this->domicilio = $data['domicilio'];
            } else {
                $domicilioDTO = $this->mapDomicilioDTO($data);
                if ($domicilioDTO !== null) {
                    $this->domicilio = $domicilioDTO;
                }
            }
        } else if (array_key_exists('aavDomOrigen', $data)) {
            $this->domicilio = $this->mapDomicilioDTO(['domId' => $data['aavDomOrigen']]);
        }

        if (array_key_exists('aavPrecioVenta', $data)) {
            $this->aavPrecioVenta = (float)$data['aavPrecioVenta'];
        }

        if (array_key_exists('tasacion', $data)) {
            if ($data['tasacion'] instanceof TasacionDigitalDTO) {
                $this->tasacion = $data['tasacion'];
            } else {
                $tasacionDTO = $this->mapTasacionDigitalDTO($data);
                if ($tasacionDTO !== null) {
                    $this->tasacion = $tasacionDTO;
                }
            }
        } else if (array_key_exists('aavTadId', $data)) {
            $this->tasacion = $this->mapTasacionDigitalDTO(['tadId' => (int)$data['aavTadId']]);
        } else if (array_key_exists('tadId', $data)) {
            $this->tasacion = $this->mapTasacionDigitalDTO(['tadId' => (int)$data['tadId']]);
        }

        
    }
}
