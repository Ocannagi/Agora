<?php

class UsuarioDTO
{
    public int $usrId; // Identificador único del usuario.
    public string $usrDni; // DNI del usuario.
    public string $usrApellido; // Apellido del usuario.
    public string $usrNombre; // Nombre del usuario.
    public ?string $usrRazonSocialFantasia; // Razón social o nombre de fantasía (nullable).
    public ?string $usrCuitCuil; // CUIT/CUIL del usuario (nullable).
    public string $usrTipoUsuario; // Tipo de usuario.
    public ?string $usrMatricula; // Matrícula (nullable).
    public int $usrDomicilio; // Domicilio del usuario (referencia a otra tabla).
    public string $usrFechaNacimiento; // Fecha de nacimiento del usuario.
    public ?string $usrDescripcion; // Descripción del usuario (nullable).
    public int $usrScoring; // Puntuación del usuario (default 0).
    public string $usrEmail; // Email del usuario.

    public function __construct(array $data)
    {
        $this->usrId = (int)$data['usrId'];
        $this->usrDni = $data['usrDni'];
        $this->usrApellido = $data['usrApellido'];
        $this->usrNombre = $data['usrNombre'];
        $this->usrRazonSocialFantasia = $data['usrRazonSocialFantasia'] ?? null;
        $this->usrCuitCuil = $data['usrCuitCuil'] ?? null;
        $this->usrTipoUsuario = $data['usrTipoUsuario'];
        $this->usrMatricula = $data['usrMatricula'] ?? null;
        $this->usrDomicilio = (int)$data['usrDomicilio'];
        $this->usrFechaNacimiento = $data['usrFechaNacimiento'];
        $this->usrDescripcion = $data['usrDescripcion'] ?? null;
        $this->usrScoring = (int)$data['usrScoring'] ?? 0;
        $this->usrEmail = $data['usrEmail'];
    }
}