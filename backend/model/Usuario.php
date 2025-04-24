<?php
use Utilidades\Obligatorio;

class Usuario {
    private int $usrId; // Identificador único del usuario.
    #[Obligatorio]
    private string $usrDni; // DNI del usuario.
    #[Obligatorio]
    private string $usrApellido; // Apellido del usuario.
    #[Obligatorio]
    private string $usrNombre; // Nombre del usuario.
    private ?string $usrRazonSocialFantasia; // Razón social o nombre de fantasía (nullable).
    private ?string $usrCuitCuil; // CUIT/CUIL del usuario (nullable).
    #[Obligatorio]
    private string $usrTipoUsuario; // Tipo de usuario.
    private ?string $usrMatricula; // Matrícula (nullable).
    #[Obligatorio]
    private int $usrDomicilio; // Domicilio del usuario (referencia a otra tabla).
    #[Obligatorio]
    private string $usrFechaNacimiento; // Fecha de nacimiento del usuario.
    private ?string $usrDescripcion; // Descripción del usuario (nullable).
    private int $usrScoring; // Puntuación del usuario (default 0).
    #[Obligatorio]
    private string $usrEmail; // Email del usuario.
    #[Obligatorio]
    private string $usrPassword; // Contraseña del usuario.
    private string $usrFechaInsert; // Fecha de inserción (timestamp automático).
    private ?string $usrFechaBaja; // Fecha de baja (nullable).

    public static function fromCreacionDTO(UsuarioCreacionDTO $dto): self
    {
        $instance = new self();
        $instance->usrDni = $dto->usrDni;
        $instance->usrApellido = $dto->usrApellido;
        $instance->usrNombre = $dto->usrNombre;
        $instance->usrRazonSocialFantasia = $dto->usrRazonSocialFantasia;
        $instance->usrCuitCuil = $dto->usrCuitCuil;
        $instance->usrTipoUsuario = $dto->usrTipoUsuario;
        $instance->usrMatricula = $dto->usrMatricula;
        $instance->usrDomicilio = $dto->usrDomicilio;
        $instance->usrFechaNacimiento = $dto->usrFechaNacimiento;
        $instance->usrDescripcion = $dto->usrDescripcion;
        $instance->usrEmail = $dto->usrEmail;
        $instance->usrPassword = password_hash($dto->usrPassword, PASSWORD_DEFAULT);
        return $instance;
    }
    
    public static function fromArray(array $data): self
    {
        $instance = new self();
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function getObligatorios(): array{
        $refClass = new ReflectionClass(__CLASS__);
        $propiedades = $refClass->getProperties();
        $obligatorios = [];
        foreach ($propiedades as $propiedad) {
            if ($propiedad->getAttributes(Obligatorio::class)) {
                $obligatorios[] = $propiedad->getName();
            }
        }
        return $obligatorios;
    }


    
}