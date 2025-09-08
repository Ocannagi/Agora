<?php

enum TipoMedioPagoEnum: string
{
    case TarjetaCredito = 'TC';
    case TransferenciaBancaria = 'TB';
    case MercadoPago = 'MP';

    public static function TarjetaCredito(): TipoMedioPagoEnum
    {
        return self::TarjetaCredito;
    }

    public static function TransferenciaBancaria(): TipoMedioPagoEnum
    {
        return self::TransferenciaBancaria;
    }

    public static function MercadoPago(): TipoMedioPagoEnum
    {
        return self::MercadoPago;
    }
}