<?php

namespace Utilidades;

use mysqli;
use ReflectionClass;
use DTOs\PHP_FileDTO;

class Input
{
    public static function getArrayBody(string $msgEntidad = "la entidad"): array
    {        
        $array = json_decode(file_get_contents('php://input'), true);
        if (json_last_error()) {
            Output::outputError(400, 'El formato de datos es incorrecto');
        }
        if (empty($array)) {
            Output::outputError(400, "No se recibieron datos para crear $msgEntidad");
        }

        return $array;
    }

    public static function contieneSoloArraysAsociativos(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        $bool = true;

        foreach ($array as $elemento) {
            if (is_array($elemento)) {
                // Verificar si el array es asociativo
                if (array_keys($elemento) === range(0, count($elemento) - 1)) {
                    // Si las claves son numéricas y consecutivas, no es un array asociativo
                    $bool = false;
                    break;
                }
            } else {
                // Si el elemento no es un array, no es un array asociativo
                $bool = false;
                break;
            }
        }

        return $bool;
    }
    


    public static function getArrayFiles(string $name) : array
    {

        if (!isset($_FILES[$name]) || !is_array($_FILES[$name]["error"]) || count($_FILES[$name]["error"]) === 0) {
            Output::outputError(400, "No se recibieron archivos para subir.");
        }

        $arrayFiles = [];
        foreach ($_FILES[$name]["error"] as $key => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $tmpName = $_FILES[$name]["tmp_name"][$key];
                $name = $_FILES[$name]["name"][$key];
                $type = $_FILES[$name]["type"][$key];
                $size = $_FILES[$name]["size"][$key];
                $arrayFiles[] = new PHP_FileDTO([
                    'tmp_name' => $tmpName,
                    'name' => $name,
                    'type' => $type,
                    'size' => $size
                ]);
            } else {
                Output::outputError(400, "Error al subir el archivo: " . $error);
            }
        }
        return $arrayFiles;
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
