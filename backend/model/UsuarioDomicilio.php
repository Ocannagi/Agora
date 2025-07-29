<?php

use Utilidades\Obligatorio;

class UsuarioDomicilio extends ClassBase
{
    private int $udomId; // Identificador único del registro de usuario-domicilio.
    #[Obligatorio]
    private Usuario $usuario; // Identificador del usuario al que pertenece el domicilio.
    #[Obligatorio]
    private Domicilio $domicilio; // Identificador del domicilio asociado al usuario.
    private DateTime $udomFechaInsert; // Fecha de inserción.
    private ?DateTime $udomFechaBaja; // Fecha de baja (nullable).

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof UsuarioDomicilioCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->usuario = Usuario::fromArray(['usrId' => $dto->usuario->usrId]);
        $instance->domicilio = Domicilio::fromArray(['domId' => $dto->domicilio->domId]);

        return $instance;
        
    }
}
