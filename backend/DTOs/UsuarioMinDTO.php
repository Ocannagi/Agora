<?php

class UsuarioMinDTO
{
    public int $usrId;
    public string $usrApellido;
    public string $usrNombre;
    public string $usrEmail;
    public string $usrTipoUsuario;

    public function __construct(array $data)
    {
        $this->usrId = (int)$data['usrId'];
        $this->usrApellido = $data['usrApellido'];
        $this->usrNombre = $data['usrNombre'];
        $this->usrEmail = $data['usrEmail'];
        $this->usrTipoUsuario = $data['usrTipoUsuario'];
    }
}

