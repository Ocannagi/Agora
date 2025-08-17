<?php

enum TipoEstadoEnum: string
{
    case ALaVenta = 'VE';
    case TasadoDigital = 'TD';
    case TasadoInSitu = 'TI';
    case Comprado = 'CO';
    case RetiradoDisponible = 'RD';
    case RetiradoNoDisponible = 'RN';


    public static function ALaVenta(): TipoEstadoEnum
    {
        return self::ALaVenta;
    }

    public static function TasadoDigital(): TipoEstadoEnum
    {
        return self::TasadoDigital;
    }

    public static function TasadoInSitu(): TipoEstadoEnum
    {
        return self::TasadoInSitu;
    }

    public static function Comprado(): TipoEstadoEnum
    {
        return self::Comprado;
    }

    public static function RetiradoDisponible(): TipoEstadoEnum
    {
        return self::RetiradoDisponible;
    }

    public static function RetiradoNoDisponible(): TipoEstadoEnum
    {
        return self::RetiradoNoDisponible;
    }

    public function isRetiradoDisponible(): bool
    {
        return $this === self::RetiradoDisponible;
    }

    public function isTasadoDigital(): bool
    {
        return $this === self::TasadoDigital;
    }

    public function isHabilitadoParaVenta(): bool
    {
        return $this === self::RetiradoDisponible ||
               $this === self::TasadoDigital ||
               $this === self::TasadoInSitu;
    }
}
