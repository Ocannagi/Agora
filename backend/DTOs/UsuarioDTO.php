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
    public int $usrDomicilio; // Domicilio del usuario (referencia a otra tabla).
    public string $usrFechaNacimiento; // Fecha de nacimiento del usuario.
    public ?string $usrDescripcion = null; // Descripción del usuario (nullable).
    public int $usrScoring; // Puntuación del usuario (default 0).
    public string $usrEmail; // Email del usuario.
    public string $usrPassword;

    public function __construct(array $data)
    {
        $refClass = new ReflectionClass(__CLASS__);
        $properties = $refClass->getProperties();

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if (array_key_exists($propertyName, $data)) {
                $value = $data[$propertyName];
                settype($value, $property->getType()->getName());
                $this->$propertyName = $value;
            }
        }
    }
}
