<?php

enum TipoEstadoEnum: string
{
    case ALaVenta = 'VE';
    case TasadoDigital = 'TD';
    case TasadoInSitu = 'TS';
    case Comprado = 'CO';
    case RetiradoDisponible = 'RD';
    case RetiradoNoDisponible = 'RN';
}
