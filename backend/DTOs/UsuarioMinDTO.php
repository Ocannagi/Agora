<?php

class UsuarioMinDTO implements IDTO
{
    public int $usrId;
    public string $usrApellido;
    public string $usrNombre;
    public string $usrEmail;
    public string $usrTipoUsuario;

    public function __construct(array | stdClass $data)
    {
         if ($data instanceof stdClass) {
            $data = (array)$data;
        }
        
        if (array_key_exists('usrId', $data)) {
            $this->usrId = (int)$data['usrId'];
        }
        if (array_key_exists('usrApellido', $data)) {
            $this->usrApellido = (string)$data['usrApellido'];
        }
        if (array_key_exists('usrNombre', $data)) {
            $this->usrNombre = (string)$data['usrNombre'];
        }
        if (array_key_exists('usrEmail', $data)) {
            $this->usrEmail = (string)$data['usrEmail'];
        }
        if (array_key_exists('usrTipoUsuario', $data)) {
            $this->usrTipoUsuario = (string)$data['usrTipoUsuario'];
        }

    }
}

