<?php

namespace Utilidades;

use mysqli;
use ReflectionClass;

class Input
{
    public static function getArrayBody(string $msgEntidad = "la entidad"): array
    {
        $array = json_decode(file_get_contents('php://input'), true);
        if (json_last_error()) {
            Output::outputError(400, 'El formato de datos es incorrecto');
        }
        if (empty($data)) {
            Output::outputError(400, "No se recibieron datos para crear $msgEntidad");
        }

        return $array;
    }

    public static function escaparDatos(object $instance, mysqli $linkExterno)
    {
        $refClass = new ReflectionClass($instance);
        $propiedades = $refClass->getProperties();
        foreach ($propiedades as $propiedad) {
            if ($propiedad->getType()->getName() === 'string') {
                $valor = $propiedad->getValue($instance);
                if (is_string($valor) && self::esNotNullVacioBlanco($valor)) {
                    $valorEscapado = $linkExterno->real_escape_string($valor);
                    $propiedad->setValue($instance, $valorEscapado);
                }
            }
        }
    }

    public static function trimStringDatos(object $instance)
    {
        $refClass = new ReflectionClass($instance);
        $propiedades = $refClass->getProperties();
        foreach ($propiedades as $propiedad) {
            if ($propiedad->getType()->getName() === 'string') {
                $valor = $propiedad->getValue($instance);
                if (self::esNotNullVacioBlanco($valor)) {
                    $valorTrimeado = trim($valor);
                    $propiedad->setValue($instance, $valorTrimeado);
                }
            }
        }
    }


    public static function esNotNullVacioBlanco(?string $str): bool
    {
        return isset($str) && trim($str) !== "";
    }

    public static function agregarComillas_ConvertNULLtoString(object $instance)
    {
        $refClass = new ReflectionClass($instance);
        $propiedades = $refClass->getProperties();
        foreach ($propiedades as $propiedad) {
            if ($propiedad->getType()->getName() === 'string') {
                $valor = $propiedad->getValue($instance);
                if (self::esNotNullVacioBlanco($valor)) {
                    $valorConComillas = "'$valor'";
                    $propiedad->setValue($instance, $valorConComillas);
                } else {
                    $propiedad->setValue($instance, 'NULL');
                }
            }
        }
    }

    public static function cadaPalabraMayuscula(string $str): string
    {
        return ucwords(strtolower($str));
    }
}
