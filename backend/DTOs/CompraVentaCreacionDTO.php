<?php
use Model\CustomException;

class CompraVentaCreacionDTO implements ICreacionDTO
{
    public UsuarioDTO $usuarioComprador; // Usuario Comprador
    public DomicilioDTO $domicilioDestino; // Domicilio Destino
    public string $covFechaCompra; // Fecha de la compra/venta en formato 'Y-m-d H:i:s'
    public TipoMedioPagoEnum $covTipoMedioPago; // Medio de pago (usar TipoMedioPagoEnum)
    /**
     * @var CompraVentaDetalleCreacionDTO[]
     */
    public array $detalles = [];

    use TraitMapUsuarioDTO; // Trait para mapear UsuarioDTO
    use TraitMapDomicilioDTO; // Trait para mapear DomicilioDTO

    public function __construct(array | stdClass $data) {
        if($data instanceof stdClass) {
            $data = (array)$data;
        }

        if(array_key_exists('usuarioComprador', $data)) {
            if($data['usuarioComprador'] instanceof UsuarioDTO) {
                $this->usuarioComprador = $data['usuarioComprador'];
            } else {
                $usrDTO = $this->mapUsuarioDTO($data['usuarioComprador']);
                if($usrDTO !== null) {
                    $this->usuarioComprador = $usrDTO;
                }
            }
        } else if (array_key_exists('covUsrComprador', $data)) {
            $this->usuarioComprador = new UsuarioDTO(['usrId' => (int)$data['covUsrComprador']]);
        } else if (array_key_exists('usrComprador', $data)) {
            $this->usuarioComprador = new UsuarioDTO(['usrId' => (int)$data['usrComprador']]);
        } else if (array_key_exists('usrId', $data)) {
            $this->usuarioComprador = new UsuarioDTO(['usrId' => (int)$data['usrId']]);
        }

        if(array_key_exists('domicilioDestino', $data)) {
            if($data['domicilioDestino'] instanceof DomicilioDTO) {
                $this->domicilioDestino = $data['domicilioDestino'];
            } else {
                $domicilioDTO = $this->mapDomicilioDTO($data['domicilioDestino']);
                if($domicilioDTO !== null) {
                    $this->domicilioDestino = $domicilioDTO;
                }
            }
        } else if (array_key_exists('covDomDestino', $data)) {
            $this->domicilioDestino = new DomicilioDTO(['domId' => (int)$data['covDomDestino']]);
        } else if (array_key_exists('domId', $data)) {
            $this->domicilioDestino = new DomicilioDTO(['domId' => (int)$data['domId']]);
        }

        if (array_key_exists('covFechaCompra', $data)) {
            $this->covFechaCompra = (string)$data['covFechaCompra'];
        }
        
        if (array_key_exists('covTipoMedioPago', $data) && $data['covTipoMedioPago'] instanceof TipoMedioPagoEnum) {
            $this->covTipoMedioPago = $data['covTipoMedioPago'];
        } else {
            try {
                if (array_key_exists('covTipoMedioPago', $data)) {
                    $this->covTipoMedioPago = TipoMedioPagoEnum::from($data['covTipoMedioPago']);
                } elseif (array_key_exists('tipoMedioPago', $data)) {
                    $this->covTipoMedioPago = TipoMedioPagoEnum::from($data['tipoMedioPago']);
                }
            } catch (ValueError $th) {
                throw new CustomException(code:400, message:'El tipo de medio de pago no es válido.');
            }
        }

        if (array_key_exists('detalles', $data) && is_array($data['detalles'])) {
            $this->detalles = [];
            foreach ($data['detalles'] as $detalle) {
                if ($detalle instanceof CompraVentaDetalleCreacionDTO) {
                    $this->detalles[] = $detalle;
                } else {
                    $detalleDTO = new CompraVentaDetalleCreacionDTO($detalle);
                    $this->detalles[] = $detalleDTO;
                }
            }
        }
    }
}