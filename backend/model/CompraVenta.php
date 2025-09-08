<?php

use Utilidades\Obligatorio;

class CompraVenta extends ClassBase
{
    private int $covId; // CompraVenta ID
    #[Obligatorio]
    private Usuario $usuarioVendedor; // Usuario Vendedor
    #[Obligatorio]
    private Usuario $usuarioComprador; // Usuario Comprador
    #[Obligatorio]
    private DateTime $covFechaCompraVenta; // Fecha de la compra/venta
    #[Obligatorio]
    private TipoMedioPagoEnum $covTipoMedioPago; // Medio de pago (usar TipoMedioPagoEnum)
    #[Obligatorio]
    /** @var ?CompraVentaDetalle[] */
    private ?array $detalles = null; // Detalles de la compra/venta
    private ?DateTime $covFechaBaja = null; // Fecha de baja

    public static function fromCreacionDTO(ICreacionDTO $dto): self
    {
        if (!$dto instanceof CompraVentaCreacionDTO) {
            throw new InvalidArgumentException("El DTO proporcionado no es del tipo correcto.");
        }

        $instance = new self();
        $instance->usuarioVendedor = Usuario::fromArray(['usrId' => $dto->usuarioVendedor->usrId]);
        $instance->usuarioComprador = Usuario::fromArray(['usrId' => $dto->usuarioComprador->usrId]);
        $instance->covFechaCompraVenta = new DateTime($dto->covFechaCompraVenta);
        $instance->covTipoMedioPago = $dto->covTipoMedioPago;
        return $instance;
    }

}