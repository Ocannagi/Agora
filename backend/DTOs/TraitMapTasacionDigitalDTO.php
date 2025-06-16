<?php

use Utilidades\Input;

trait TraitMapTasacionDigitalDTO
{
    use TraitMapUsuarioDTO; // Trait para mapear UsuarioDTO
    use TraitMapAntiguedadDTO; // Trait para mapear AntiguedadDTO
    
    private function mapTasacionDigitalDTO(array | stdClass $data, bool $returnArray = false): TasacionDigitalDTO | array | null
    {
       if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        $arrayTasDigital = [];

        if (array_key_exists('tasacionDigital', $data)) {
            return $returnArray ? get_object_vars(new TasacionDigitalDTO($data['tasacionDigital'])) : new TasacionDigitalDTO($data['tasacionDigital']);
        }

        if (array_key_exists('tadId', $data)) {
            $arrayTasDigital['tadId'] = (int)$data['tadId'];
        } elseif(array_key_exists('tisTadId', $data)) {
            $arrayTasDigital['tadId'] = (int)$data['tisTadId'];
        } else {
            return null; // No se puede mapear sin tadId
        }

        if (array_key_exists('tasador', $data)) {
            if ($data['tasador'] instanceof UsuarioDTO) {
                $arrayTasDigital['tasador'] = $data['tasador'];
            } else {
                $usuarioDTO = $this->mapUsuarioDTO($data['tasador']);
                if ($usuarioDTO !== null) {
                    $arrayTasDigital['tasador'] = $usuarioDTO;
                }
            }
        } else if (array_key_exists('tadUsrTasId', $data)) {
            $arrayTasDigital['tasador'] = $this->mapUsuarioDTO(['usrId' => (int)$data['tadUsrTasId']]);
        }

        if (array_key_exists('propietario', $data)) {
            if ($data['propietario'] instanceof UsuarioDTO) {
                $arrayTasDigital['propietario'] = $data['propietario'];
            } else {
                $usuarioDTO = $this->mapUsuarioDTO($data['propietario']);
                if ($usuarioDTO !== null) {
                    $arrayTasDigital['propietario'] = $usuarioDTO;
                }
            }
        } else if (array_key_exists('tadUsrPropId', $data)) {
            $arrayTasDigital['propietario'] = $this->mapUsuarioDTO(['usrId' => (int)$data['tadUsrPropId']]);
        }

        if (array_key_exists('antiguedad', $data)) {
            if ($data['antiguedad'] instanceof AntiguedadDTO) {
                $arrayTasDigital['antiguedad'] = $data['antiguedad'];
            } else {
                $antiguedadDTO = $this->mapAntiguedadDTO($data);
                if ($antiguedadDTO !== null) {
                    $arrayTasDigital['antiguedad'] = $antiguedadDTO;
                }
            }
        } else if (array_key_exists('tadAntId', $data)) {
            $arrayTasDigital['antiguedad'] = $this->mapAntiguedadDTO(['antId' => (int)$data['tadAntId']]);
        }

        if (array_key_exists('tadFechaSolicitud', $data)) {
            $arrayTasDigital['tadFechaSolicitud'] = (string)$data['tadFechaSolicitud'];
        }

        if (array_key_exists('tadFechaTasDigitalRealizada', $data)) {
            $arrayTasDigital['tadFechaTasDigitalRealizada'] = (string)$data['tadFechaTasDigitalRealizada'];
        }

        if (array_key_exists('tadFechaTasDigitalRechazada', $data)) {
            $arrayTasDigital['tadFechaTasDigitalRechazada'] = (string)$data['tadFechaTasDigitalRechazada'];
        }

        if (array_key_exists('tadObservacionesDigital', $data)) {
            $arrayTasDigital['tadObservacionesDigital'] = (string)$data['tadObservacionesDigital'];
        }

        if (array_key_exists('tadPrecioDigital', $data)) {
            $arrayTasDigital['tadPrecioDigital'] = Input::esNotNullVacioBlanco($data['tadPrecioDigital']) ? (float)$data['tadPrecioDigital'] : null;
        }

        return $returnArray ? $arrayTasDigital : new TasacionDigitalDTO($arrayTasDigital);
    }
}