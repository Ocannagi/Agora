<?php

use Model\CustomException;

class VentaDetalleDTO implements IDTO
{
    use TraitMapAntiguedadAlaVentaDTO; // Trait para mapear AntiguedadAlaVentaDTO
    use TraitMapDomicilioDTO; // Trait para mapear DomicilioDTO
    use TraitMapUsuarioDTO; // Trait para mapear UsuarioDTO

    public int $cvdId; // CompraVentaDetalle ID
    public int $covId; // CompraVenta ID
    public UsuarioDTO $usuarioComprador;// Usuario comprador
    public DomicilioDTO $domicilioDestino; // Domicilio de destino
    public string $covFechaCompra;
    public AntiguedadAlaVentaDTO $antiguedadAlaVenta; // AntiguedadAlaVenta
    public TipoMedioPagoEnum $covTipoMedioPago;
    public string $cvdFechaEntregaPrevista; // Fecha de entrega prevista en formato 'Y-m-d H:i:s'
    public ?string $cvdFechaEntregaReal = null; // Fecha de entrega real en formato 'Y-m-d H:i:s'

    public function __construct(array | stdClass $data)
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }

        if (array_key_exists('cvdId', $data)) {
            $this->cvdId = (int)$data['cvdId'];
        }

        if (array_key_exists('covId', $data)) {
            $this->covId = (int)$data['covId'];
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

        if (array_key_exists('antiguedadAlaVenta', $data)) {
            if ($data['antiguedadAlaVenta'] instanceof AntiguedadAlaVentaDTO) {
                $this->antiguedadAlaVenta = $data['antiguedadAlaVenta'];
            } else {
                $aavDTO = $this->mapAntiguedadAlaVentaDTO($data['antiguedadAlaVenta']);
                if ($aavDTO !== null) {
                    $this->antiguedadAlaVenta = $aavDTO;
                }
            }
        } else if (array_key_exists('aavId', $data)) {
            $this->antiguedadAlaVenta = new AntiguedadAlaVentaDTO(['aavId' => (int)$data['aavId']]);
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

        if (array_key_exists('cvdFechaEntregaPrevista', $data)) {
            $this->cvdFechaEntregaPrevista = (string)$data['cvdFechaEntregaPrevista'];
        }

        if (array_key_exists('cvdFechaEntregaReal', $data)) {
            $this->cvdFechaEntregaReal = $data['cvdFechaEntregaReal'] !== null ? (string)$data['cvdFechaEntregaReal'] : null;
        }
    }
}