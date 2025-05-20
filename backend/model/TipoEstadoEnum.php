<?php

enum TipoEstadoEnum: string
{
    case ALaVenta = 'VE';
    case TasadoProvisorio = 'TP';
    case TasadoInSitu = 'TS';
    case Comprado = 'CO';
    case RetiradoDisponible = 'RD';
    case RetiradoNoDisponible = 'RN';
}
