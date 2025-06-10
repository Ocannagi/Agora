<?php

class TasacionDigitalCreacionDTO implements ICreacionDTO
{
    public UsuarioDTO $tasador; // Identificador del usuario tasador.
    public UsuarioDTO $propietario; // Identificador del usuario propietario.
    public AntiguedadDTO $antiguedad; // Identificador del inmueble a tasar.
    public string $tasFechaSolicitud; // Fecha de solicitud de la tasación.

    use TraitMapUsuarioDTO; // Trait para mapear UsuarioDTO
    use TraitMapAntiguedadDTO; // Trait para mapear AntiguedadDTO


    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
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
    }
}