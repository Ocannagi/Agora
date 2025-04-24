<?php

class UsuarioCreacionDTO
{
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
    public string $usrEmail; // Email del usuario.
    public string $usrPassword; // Contraseña del usuario.

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function reSetear(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}