<?php

use Utilidades\Obligatorio;


class AntiguedadAlaVenta extends ClassBase
{
    private int $aavId;
    #[Obligatorio]
    private Antiguedad $antiguedad;
    #[Obligatorio]
    private Usuario $vendedor; // Se agrega el vendedor
    #[Obligatorio]
    private Domicilio $domicilioOrigen;
    #[Obligatorio]
    private float $aavPrecioVenta;
    private ?TasacionDigital $tasacionDigital = null;
    #[Obligatorio]
    private DateTime $aavFechaPublicacion;
    private ?DateTime $aavFechaRetiro = null;
    private bool $aavHayVenta = false;

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof AntiguedadAlaVentaCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->antiguedad = Antiguedad::fromArray(['antId' => $dto->antiguedad->antId]); // Convertir el DTO de antigüedad a objeto
        $instance->vendedor = Usuario::fromArray(['usrId' => $dto->vendedor->usrId]); // Convertir el DTO de usuario a objeto
        $instance->domicilioOrigen = Domicilio::fromArray(['domId' => $dto->domicilioOrigen->domId]); // Convertir el DTO de domicilio a objeto
        $instance->aavPrecioVenta = $dto->aavPrecioVenta;
        if (isset($dto->tasacion)) {
            $instance->tasacionDigital = TasacionDigital::fromArray(['tadId' => $dto->tasacion->tadId]);
        }
        $instance->aavFechaPublicacion = new DateTime('now'); // Asignar la fecha actual como fecha de publicación

        return $instance;
    }
}
