<?php

namespace Utilidades;
use mysqli;
use ReflectionClass;

class Input
{
    public static function escaparDatos(object $instance, mysqli $linkExterno)
    {
        $refClass = new ReflectionClass($instance);
        $propiedades = $refClass->getProperties();
        foreach ($propiedades as $propiedad) {
            if ($propiedad->getType()->getName() === 'string') {
                $valor = $propiedad->getValue($instance);
                if (is_string($valor) && self::esNotNullVacioBlanco($valor)) {
                    $valorEscapado = $linkExterno->real_escape_string($valor);
                    $propiedad->setValue($instance,$valorEscapado);
                }
            } 
        }
    }

    public static function convertNULLtoString(object $instance)
    {
        $refClass = new ReflectionClass($instance);
        $propiedades = $refClass->getProperties();
        foreach ($propiedades as $propiedad) {
            if ($propiedad->getType()->getName() === 'string') {
                $valor = $propiedad->getValue($instance);
                if (!self::esNotNullVacioBlanco($valor)) {
                    $propiedad->setValue($instance, 'NULL');
                }
            } 
        }
    }

    public static function esNotNullVacioBlanco(?string $str): bool
    {
        return isset($str) && trim($str) !== "";
    }


}