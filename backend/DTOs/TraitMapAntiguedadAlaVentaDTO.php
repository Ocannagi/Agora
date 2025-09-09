<?php

trait TraitMapAntiguedadAlaVentaDTO
{
    use TraitMapAntiguedadDTO; // Trait para mapear AntiguedadDTO
    use TraitMapDomicilioDTO; // Trait para mapear DomicilioDTO
    use TraitMapTasacionDigitalDTO; // Trait para mapear TasacionDigitalDTO.

    private function mapAntiguedadAlaVentaDTO(array | stdClass $data, bool $returnArray = false): AntiguedadAlaVentaDTO | array | null
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arrayAntiguedadAlaVenta = [];

        if (array_key_exists('antiguedadAlaVenta', $data)) {
            return $returnArray ? get_object_vars(new AntiguedadAlaVentaDTO($data['antiguedadAlaVenta'])) : new AntiguedadAlaVentaDTO($data['antiguedadAlaVenta']);
        }

        if (array_key_exists('aavId', $data)) {
            $arrayAntiguedadAlaVenta['aavId'] = (int)$data['aavId'];
        } elseif (array_key_exists('cvdAavId', $data)) {
            $arrayAntiguedadAlaVenta['aavId'] = (int)$data['cvdAavId'];
        } else {
            return null; // No se puede mapear sin aavId
        }

        if (array_key_exists('antiguedad', $data) && $data['antiguedad'] instanceof AntiguedadDTO) {
          
            $arrayAntiguedadAlaVenta['antiguedad'] = $data['antiguedad'];
        } else {
            $antiguedadDTO = $this->mapAntiguedadDTO($data, $returnArray);
            if ($antiguedadDTO !== null) {
                $arrayAntiguedadAlaVenta['antiguedad'] = $antiguedadDTO;
            }
        }
        

        if (array_key_exists('domicilioOrigen', $data) && $data['domicilioOrigen'] instanceof DomicilioDTO) {
            $arrayAntiguedadAlaVenta['domicilioOrigen'] = $data['domicilioOrigen'];
        } else {
            $domicilioDTO = $this->mapDomicilioDTO($data, $returnArray);
            if ($domicilioDTO !== null) {
                $arrayAntiguedadAlaVenta['domicilioOrigen'] = $domicilioDTO;
            }
        }

        if (array_key_exists('aavPrecioVenta', $data)) {
            $arrayAntiguedadAlaVenta['aavPrecioVenta'] = (float)$data['aavPrecioVenta'];
        }

        if (array_key_exists('tasacion', $data)) {
            if ($data['tasacion'] instanceof TasacionDigitalDTO) {
                $arrayAntiguedadAlaVenta['tasacion'] = $data['tasacion'];
            } else {
                $tasacionDTO = $this->mapTasacionDigitalDTO($data['tasacion'], $returnArray);
                if ($tasacionDTO !== null) {
                    $arrayAntiguedadAlaVenta['tasacion'] = $tasacionDTO;
                }
            }
        } else if (array_key_exists('aavTadId', $data)) {
            $tasacionDTO = $this->mapTasacionDigitalDTO(['tadId' => (int)$data['aavTadId']], false);

            if ($tasacionDTO !== null){
                if(array_key_exists('tisId', $data)){
                    $tasacionDTO->tasacionInSitu = new TasacionInSituDTO(['tisId' => (int)$data['tisId']]);
                }
            }
            $arrayAntiguedadAlaVenta['tasacion'] = $returnArray ? get_object_vars($tasacionDTO) : $tasacionDTO;
        } else if (array_key_exists('tadId', $data)) {
            $tasacionDTO = $this->mapTasacionDigitalDTO(['tadId' => (int)$data['tadId']], false);

            if ($tasacionDTO !== null) {
                if (array_key_exists('tisId', $data)) {
                    $tasacionDTO->tasacionInSitu = new TasacionInSituDTO(['tisId' => (int)$data['tisId']]);
                }
            }
            $arrayAntiguedadAlaVenta['tasacion'] = $returnArray ? get_object_vars($tasacionDTO) : $tasacionDTO;
        }

        if (array_key_exists('aavFechaPublicacion', $data)) {
            $arrayAntiguedadAlaVenta['aavFechaPublicacion'] = (string)$data['aavFechaPublicacion'];
        }

        if (array_key_exists('aavFechaRetiro', $data)) {
            $arrayAntiguedadAlaVenta['aavFechaRetiro'] = (string)$data['aavFechaRetiro'];
        }

        if (array_key_exists('aavHayVenta', $data)) {
            $arrayAntiguedadAlaVenta['aavHayVenta'] = (bool)$data['aavHayVenta'];
        }

        return $returnArray ? $arrayAntiguedadAlaVenta : new AntiguedadAlaVentaDTO($arrayAntiguedadAlaVenta);
    }
}
