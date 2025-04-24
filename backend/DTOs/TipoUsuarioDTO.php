<?php

class TipoUsuarioDTO
{
    public string $ttuTipoUsuario; // Tipo de usuario.
    public string $ttuDescripcion; // Descripción del tipo de usuario.
    public bool $ttuRequiereMatricula; // Indica si se requiere matrícula (booleano).

    public function __construct(array $data)
    {
        $this->ttuTipoUsuario = $data['ttuTipoUsuario'];
        $this->ttuDescripcion = $data['ttuDescripcion'];
        $this->ttuRequiereMatricula = (bool)$data['ttuRequiereMatricula'];
    }
}