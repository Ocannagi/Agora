<?php

use Model\CustomException;
use Utilidades\Input;

class ComprasVentasValidacionService extends ValidacionServiceBase
{
    private static $instancia = null;

    private function __construct()
    {
        // Constructor privado para evitar instanciación directa
    }

    public static function getInstancia(): ComprasVentasValidacionService
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone()
    {
        // Previene la clonación de la instancia
    }

    public function validarInput(mysqli $linkExterno, ICreacionDTO|IDTO $compraVenta, mixed $extraParams = null): void
    {
        if (!($compraVenta instanceof CompraVentaCreacionDTO) && !($compraVenta instanceof CompraVentaDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: el DTO proporcionado no es del tipo correcto.');
        }

        if ($extraParams === null || !($extraParams instanceof ClaimDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: los parámetros extra son obligatorios y deben ser del tipo ClaimDTO.');
        }

        $this->validarDatosObligatorios(classModelName: CompraVenta::class, datos: get_object_vars($compraVenta));
        Input::trimStringDatos($compraVenta);

        $this->validarUsuario($compraVenta->usuarioComprador, $extraParams);
        $this->validarDomicilio($compraVenta->usuarioComprador, $compraVenta->domicilioDestino, $linkExterno);

        foreach ($compraVenta->detalles as $detalle) {
            if (!($detalle instanceof CompraVentaDetalleCreacionDTO || $detalle instanceof CompraVentaDetalleDTO)) {
                throw new CustomException(code: 500, message: 'Error interno: los detalles deben ser del tipo CompraVentaDetalleCreacionDTO o CompraVentaDetalleDTO.');
            }
            $this->validarDatosObligatorios(classModelName: CompraVentaDetalle::class, datos: get_object_vars($detalle));
            Input::trimStringDatos($detalle);
            // Validar que la antigüedad a la venta exista y esté activa
            $this->validarSiExisteAntiguedadAlaVentaActiva($detalle->antiguedadAlaVenta, $linkExterno);
            $this->validarUsuarioVendedor($detalle, $compraVenta);
            $this->validarOrigenDestinoDiferentes($detalle->antiguedadAlaVenta->domicilioOrigen, $compraVenta->domicilioDestino);
            Input::esFechaValidaYNoPasada($detalle->cvdFechaEntregaPrevista);

            if ($detalle instanceof CompraVentaDetalleDTO) {
                // Si es un DTO de detalle existente, validar que pertenezca a la compra/venta
                if ($compraVenta instanceof CompraVentaDTO) {
                    if ($detalle->covId !== $compraVenta->covId) {
                        throw new CustomException(code: 400, message: 'El detalle no pertenece a la compra/venta especificada.');
                    }

                    Input::esFechaValidaYNoPasada($detalle->cvdFechaEntregaReal);
                    Input::esFechaMayorOIgual($detalle->cvdFechaEntregaPrevista, $detalle->cvdFechaEntregaReal);

                } else {
                    throw new CustomException(code: 500, message: 'Error interno: no se puede validar el detalle existente sin un DTO de compra/venta existente.');
                }
            }
        }
    }

    private function validarUsuario(UsuarioDTO $usuario, ClaimDTO $claimDTO): void
    {
        if (!TipoUsuarioEnum::from($claimDTO->usrTipoUsuario)->isSoporteTecnico() && $usuario->usrId !== $claimDTO->usrId) {
            throw new CustomException(code: 403, message: 'No tienes permiso para registrar esta compra/venta.');
        }

        if (!TipoUsuarioEnum::from($usuario->usrTipoUsuario)->isCompradorVendedor()) {
            throw new CustomException(code: 400, message: 'El usuario comprador debe ser Usuario General o Anticuario.');
        }
    }

    private function validarDomicilio(UsuarioDTO $usuario, DomicilioDTO $domicilio, mysqli $linkExterno): void
    {
        $query = "SELECT 1 from usuariodomicilio WHERE udomUsr = {$usuario->usrId} AND udomDom = {$domicilio->domId} AND udomFechaBaja IS NULL";
        if ($this->_existeEnBD($linkExterno, $query, "verificar si el domicilio de destino pertenece al usuario comprador") === false) {
            throw new CustomException(code: 403, message: 'El domicilio no pertenece al usuario comprador.');
        }
    }

    private function validarSiExisteAntiguedadAlaVentaActiva(AntiguedadAlaVentaDTO $antiguedadAlaVenta, mysqli $linkExterno): void
    {
        $query = "SELECT 1 FROM antiguedadalaventa WHERE aavId = {$antiguedadAlaVenta->aavId} AND aavFechaRetiro IS NULL AND aavHayVenta = FALSE";
        if (!$this->_existeEnBD($linkExterno, $query, "verificar si la antigüedad a la venta existe y está activa")) {
            throw new CustomException(code: 400, message: 'La antigüedad a la venta no existe o no está activa.');
        }
    }

    private function validarUsuarioVendedor(CompraVentaDetalleCreacionDTO $detalle, CompraVentaCreacionDTO $compraVenta): void
    {
        if ($detalle->antiguedadAlaVenta->vendedor->usrId === $compraVenta->usuarioComprador->usrId) {
            throw new CustomException(code: 400, message: 'El usuario comprador no puede ser el mismo que el usuario vendedor de la antigüedad.');
        }
    }

    private function validarOrigenDestinoDiferentes(DomicilioDTO $origen, DomicilioDTO $destino): void
    {
        if ($origen->domId === $destino->domId) {
            throw new CustomException(code: 400, message: 'El domicilio de origen y destino no pueden ser el mismo.');
        }
    }
}
