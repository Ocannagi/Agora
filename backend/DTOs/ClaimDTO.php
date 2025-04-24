<?php

class ClaimDTO
{
    public int $usrId; // Identificador único del usuario.
    public string $usrNombre; // Nombre del usuario.
    public string $usrTipoUsuario; // Tipo de usuario.
    public int $exp; // Fecha de expiración del token (timestamp).

    public function __construct(array | stdClass $data)
    {
        if (is_array($data)) {
            $this->usrId = (int)$data['usrId'];
            $this->usrNombre = $data['usrNombre'];
            $this->usrTipoUsuario = $data['usrTipoUsuario'];

            if(array_key_exists('exp', $data)) {
                $this->exp = (int)$data['exp'];
            } else {
                $this->exp = time() + JWT_EXP; // Esto no es propio de un DTO, pero me da pereza crear una clase Claim - Expiración del token (actual + tiempo de expiración definido en JWT_EXP).
            }
        }
        elseif ($data instanceof stdClass) {
            $this->usrId = (int)$data->usrId;
            $this->usrNombre = $data->usrNombre;
            $this->usrTipoUsuario = $data->usrTipoUsuario;
            if(isset($data->exp)) {
                $this->exp = (int)$data->exp;
            } else {
                $this->exp = time() + JWT_EXP; // Esto no es propio de un DTO, pero me da pereza crear una clase Claim - Expiración del token (actual + tiempo de expiración definido en JWT_EXP).
            }
        } else {
            throw new Exception("Error: El formato de los datos no es válido.");
        }
    }
}
