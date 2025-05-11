<?php

class UsuarioDTO implements IDTO
{
    public int $usrId; // Identificador único del usuario.
    public string $usrDni; // DNI del usuario.
    public string $usrApellido; // Apellido del usuario.
    public string $usrNombre; // Nombre del usuario.
    public ?string $usrRazonSocialFantasia = null; // Razón social o nombre de fantasía (nullable).
    public ?string $usrCuitCuil = null; // CUIT/CUIL del usuario (nullable).
    public string $usrTipoUsuario; // Tipo de usuario.
    public ?string $usrMatricula = null; // Matrícula (nullable).
    public DomicilioDTO $domicilio; // Domicilio del usuario (referencia a otra tabla).
    public string $usrFechaNacimiento; // Fecha de nacimiento del usuario.
    public ?string $usrDescripcion = null; // Descripción del usuario (nullable).
    public int $usrScoring = 0; // Puntuación del usuario (default 0).
    public string $usrEmail; // Email del usuario.
    public string $usrPassword;

    use TraitMapDomicilioDTO; // Trait para mapear el domicilio desde el array o stdClass.

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('usrId', $data)) {
            $this->usrId = (int)$data['usrId'];
        }
        if (array_key_exists('usrDni', $data)) {
            $this->usrDni = (string)$data['usrDni'];
        }
        if (array_key_exists('usrApellido', $data)) {
            $this->usrApellido = (string)$data['usrApellido'];
        }
        if (array_key_exists('usrNombre', $data)) {
            $this->usrNombre = (string)$data['usrNombre'];
        }
        if (array_key_exists('usrRazonSocialFantasia', $data)) {
            $this->usrRazonSocialFantasia = (string)$data['usrRazonSocialFantasia'];
        }
        if (array_key_exists('usrCuitCuil', $data)) {
            $this->usrCuitCuil = (string)$data['usrCuitCuil'];
        }
        if (array_key_exists('usrTipoUsuario', $data)) {
            $this->usrTipoUsuario = (string)$data['usrTipoUsuario'];
        }
        if (array_key_exists('usrMatricula', $data)) {
            $this->usrMatricula = (string)$data['usrMatricula'];
        }

        $this->domicilio = $this->mapDomicilioDTO($data);

        if (array_key_exists('usrFechaNacimiento', $data)) {
            $this->usrFechaNacimiento = (string)$data['usrFechaNacimiento'];
        }
        if (array_key_exists('usrDescripcion', $data)) {
            $this->usrDescripcion = (string)$data['usrDescripcion'];
        }
        if (array_key_exists('usrScoring', $data)) {
            $this->usrScoring = (int)$data['usrScoring'];
        }
        if (array_key_exists('usrEmail', $data)) {
            $this->usrEmail = (string)$data['usrEmail'];
        }
        if (array_key_exists('usrPassword', $data)) {
            $this->usrPassword = (string)$data['usrPassword'];
        }

    }
}
