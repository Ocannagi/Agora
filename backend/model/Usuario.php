<?php

use Utilidades\Obligatorio;

class Usuario extends ClassBase
{
    protected int $usrId; // Identificador único del usuario.
    #[Obligatorio]
    protected string $usrDni; // DNI del usuario.
    #[Obligatorio]
    protected string $usrApellido; // Apellido del usuario.
    #[Obligatorio]
    protected string $usrNombre; // Nombre del usuario.
    protected ?string $usrRazonSocialFantasia; // Razón social o nombre de fantasía (nullable).
    protected ?string $usrCuitCuil; // CUIT/CUIL del usuario (nullable).
    #[Obligatorio]
    protected string $usrTipoUsuario; // Tipo de usuario.
    protected ?string $usrMatricula; // Matrícula (nullable).
    #[Obligatorio]
    protected Domicilio $domicilio; // Domicilio del usuario (referencia a otra tabla).
    #[Obligatorio]
    protected DateTime $usrFechaNacimiento; // Fecha de nacimiento del usuario.
    protected ?string $usrDescripcion; // Descripción del usuario (nullable).
    protected int $usrScoring; // Puntuación del usuario (default 0).
    #[Obligatorio]
    protected string $usrEmail; // Email del usuario.
    #[Obligatorio]
    protected string $usrPassword; // Contraseña del usuario.
    protected DateTime $usrFechaInsert; // Fecha de inserción.
    protected ?DateTime $usrFechaBaja; // Fecha de baja (nullable).

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if(!$dto instanceof UsuarioCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }
        
        $instance = new self();
        $instance->usrDni = $dto->usrDni;
        $instance->usrApellido = $dto->usrApellido;
        $instance->usrNombre = $dto->usrNombre;
        $instance->usrRazonSocialFantasia = $dto->usrRazonSocialFantasia;
        $instance->usrCuitCuil = $dto->usrCuitCuil;
        $instance->usrTipoUsuario = $dto->usrTipoUsuario;
        $instance->usrMatricula = $dto->usrMatricula;
        $instance->domicilio = Domicilio::fromArray(['domId' => $dto->domicilio->domId]);
        $instance->usrFechaNacimiento = DateTime::createFromFormat('Y-m-d', $dto->usrFechaNacimiento);
        $instance->usrDescripcion = $dto->usrDescripcion;
        $instance->usrEmail = $dto->usrEmail;
        $instance->usrPassword = password_hash($dto->usrPassword, PASSWORD_DEFAULT);
        return $instance;
    }

}
