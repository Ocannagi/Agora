<?php

use Utilidades\Obligatorio;

class CompraVenta extends ClassBase
{
    private int $covId; // CompraVenta ID
    #[Obligatorio]
    private Usuario $usuarioComprador; // Usuario Comprador
    #[Obligatorio]
    private Domicilio $domicilioDestino; // Domicilio Destino
    #[Obligatorio]
    private DateTime $covFechaCompra; // Fecha de la compra/venta
    #[Obligatorio]
    private TipoMedioPagoEnum $covTipoMedioPago; // Medio de pago (usar TipoMedioPagoEnum)
    #[Obligatorio]
    /** @var CompraVentaDetalle[] */
    private array $detalles = []; // Detalles de la compra/venta
    private ?DateTime $covFechaBaja = null; // Fecha de baja

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof CompraVentaCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->usuarioComprador = Usuario::fromArray(['usrId' => $dto->usuarioComprador->usrId]);
        $instance->domicilioDestino = Domicilio::fromArray(['domId' => $dto->domicilioDestino->domId]);
        $instance->covFechaCompra = new DateTime($dto->covFechaCompra);
        $instance->covTipoMedioPago = $dto->covTipoMedioPago;
        return $instance;
    }

}