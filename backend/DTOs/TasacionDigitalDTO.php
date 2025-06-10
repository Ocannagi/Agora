<?php

use Utilidades\Input;

class TasacionDigitalDTO implements IDTO
{
    public int $tasId; // Identificador de la tasación.
    public UsuarioDTO $tasador; // Identificador del usuario tasador.
    public UsuarioDTO $propietario; // Identificador del usuario propietario.
    public AntiguedadDTO $antiguedad; // Identificador del inmueble a tasar.
    public string $tasFechaSolicitud; // Fecha de solicitud de la tasación.
    public ?string $tasFechaTasProviRealizada = null; // Fecha de la tasación.
    public ?string $tasFechaTasProviRechazada = null; // Fecha de la tasación rechazada.
    public ?string $tasObservacionesProv = null; // Observaciones de la tasación.
    public ?float $tasPrecioProvisoria = null; // Precio de la tasación provisoria.
    public ?int $tasTisId = null;


    use TraitMapUsuarioDTO; // Trait para mapear UsuarioDTO
    use TraitMapAntiguedadDTO; // Trait para mapear AntiguedadDTO


    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('tasId', $data)) {
            $this->tasId = (int)$data['tasId'];
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
        }

        if (array_key_exists('tasFechaSolicitud', $data)) {
            $this->tasFechaSolicitud = (string)$data['tasFechaSolicitud'];
        }

        if (array_key_exists('tasFechaTasProviRealizada', $data)) {
            $this->tasFechaTasProviRealizada = (string)$data['tasFechaTasProviRealizada'];
        }

        if (array_key_exists('tasFechaTasProviRechazada', $data)) {
            $this->tasFechaTasProviRechazada = (string)$data['tasFechaTasProviRechazada'];
        }

        if (array_key_exists('tasObservacionesProv', $data)) {
            $this->tasObservacionesProv = (string)$data['tasObservacionesProv'];
        }

        if (array_key_exists('tasPrecioProvisoria', $data)) {
            $this->tasPrecioProvisoria = Input::esNotNullVacioBlanco($data['tasPrecioProvisoria']) ? (float)$data['tasPrecioProvisoria'] : null;
        }
    }
}
