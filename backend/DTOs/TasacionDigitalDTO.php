<?php

use Utilidades\Input;

class TasacionDigitalDTO implements IDTO
{
    public int $tadId; // Identificador de la tasación.
    public UsuarioDTO $tasador; // Identificador del usuario tasador.
    public UsuarioDTO $propietario; // Identificador del usuario propietario.
    public AntiguedadDTO $antiguedad; // Identificador del inmueble a tasar.
    public string $tadFechaSolicitud; // Fecha de solicitud de la tasación.
    public ?string $tadFechaTasDigitalRealizada = null; // Fecha de la tasación.
    public ?string $tadFechaTasDigitalRechazada = null; // Fecha de la tasación rechazada.
    public ?string $tadObservacionesDigital = null; // Observaciones de la tasación.
    public ?float $tadPrecioDigital = null; // Precio de la tasación digital.
    public ?string $tadFechaBaja = null; // Fecha de baja de la tasación digital.


    use TraitMapUsuarioDTO; // Trait para mapear UsuarioDTO
    use TraitMapAntiguedadDTO; // Trait para mapear AntiguedadDTO


    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('tadId', $data)) {
            $this->tadId = (int)$data['tadId'];
        }

        if (array_key_exists('tasador', $data)) {
            if ($data['tasador'] instanceof UsuarioDTO) {
                $this->tasador = $data['tasador'];
            } else {
                $usuarioDTO = $this->mapUsuarioDTO($data['tasador']);
                if ($usuarioDTO !== null) {
                    $this->tasador = $usuarioDTO;
                }
            }
        } else if (array_key_exists('tadUsrTasId', $data)) {
            $this->tasador = $this->mapUsuarioDTO(['usrId' => (int)$data['tadUsrTasId']]);
        }

        if (array_key_exists('propietario', $data)) {
            if ($data['propietario'] instanceof UsuarioDTO) {
                $this->propietario = $data['propietario'];
            } else {
                $usuarioDTO = $this->mapUsuarioDTO($data['propietario']);
                if ($usuarioDTO !== null) {
                    $this->propietario = $usuarioDTO;
                }
            }
        } else if (array_key_exists('tadUsrPropId', $data)) {
            $this->propietario = $this->mapUsuarioDTO(['usrId' => (int)$data['tadUsrPropId']]);
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
        } else if (array_key_exists('tadAntId', $data)) {
            $this->antiguedad = $this->mapAntiguedadDTO(['antId' => (int)$data['tadAntId']]);
        }

        if (array_key_exists('tadFechaSolicitud', $data)) {
            $this->tadFechaSolicitud = (string)$data['tadFechaSolicitud'];
        }

        if (array_key_exists('tadFechaTasDigitalRealizada', $data)) {
            $this->tadFechaTasDigitalRealizada = (string)$data['tadFechaTasDigitalRealizada'];
        }

        if (array_key_exists('tadFechaTasDigitalRechazada', $data)) {
            $this->tadFechaTasDigitalRechazada = (string)$data['tadFechaTasDigitalRechazada'];
        }

        if (array_key_exists('tadObservacionesDigital', $data)) {
            $this->tadObservacionesDigital = (string)$data['tadObservacionesDigital'];
        }

        if (array_key_exists('tadPrecioDigital', $data)) {
            $this->tadPrecioDigital = Input::esNotNullVacioBlanco($data['tadPrecioDigital']) ? (float)$data['tadPrecioDigital'] : null;
        }

        if (array_key_exists('tadFechaBaja', $data)) {
            $this->tadFechaBaja = Input::esNotNullVacioBlanco($data['tadFechaBaja']) ? (string)$data['tadFechaBaja'] : null;
        }
    }
}
