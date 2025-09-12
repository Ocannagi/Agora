<?php

namespace Utilidades;

use mysqli;
use ReflectionClass;
use DTOs\PHP_FileDTO;
use InvalidArgumentException;
use DateTime;

class Input
{
    public static function getArrayBody(string $msgEntidad = "la entidad"): array
    {        
        $array = json_decode(file_get_contents('php://input'), true);
        if (empty($array)) {
            throw new InvalidArgumentException(message: "No se recibieron datos para crear $msgEntidad");
        }
        if (json_last_error()) {
            throw new InvalidArgumentException(message: 'El formato de datos es incorrecto');
        }
        
        return $array;
    }

    public static function contieneSoloArraysAsociativos(array $array): bool //Por ahora no la uso, pero la dejo por si acaso
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

    /**
     * Guarda un archivo en el sistema de archivos. Lanza una excepción si hay un error al mover el archivo o si la carpeta de destino no existe.
     * @param PHP_FileDTO $fileDTO El objeto que contiene la información del archivo.
     * @param string $subcarpetaEnStorage La subcarpeta dentro de la carpeta de almacenamiento donde se guardará el archivo.
     * @param string $id Un identificador único para el archivo, que se usará en el nombre del archivo.
     * @return string La ruta relativa del archivo guardado.
     * @throws \Exception Si hay un error al mover el archivo o si la carpeta de destino no existe.
     */
    public static function saveFile(PHP_FileDTO $fileDTO, string $subcarpetaEnStorage, string $id): string
    {
        $dirBase = dirname(__DIR__, 2) . '/storage';
        $path = $dirBase . '/' . $subcarpetaEnStorage;
        if (!is_dir($path)) {
            throw new \Model\CustomException("La carpeta de destino no existe: $path", 500);
        }

        $nombreArchivo = $id . '_' . time() . '_' . pathinfo($fileDTO->name, PATHINFO_FILENAME) . '.' . explode('/', $fileDTO->type)[1];

        if (!move_uploaded_file($fileDTO->tmp_name, $path . '/' . $nombreArchivo)) {
            throw new \Model\CustomException("Error al mover el archivo a la ubicación deseada: $path/$nombreArchivo", 500);
        }

        // Verificar si el archivo se movió correctamente
        if (!file_exists($path . '/' . $nombreArchivo)) {
            throw new \Model\CustomException("El archivo no se movió correctamente a la ubicación: $path/$nombreArchivo", 500);
        }

        // Retornar la URL relativa
        return '/storage/' . $subcarpetaEnStorage . '/' . $nombreArchivo;
    }
    

    /**
     * Obtiene un array de archivos subidos a través de un formulario HTML.
     * @param string $name El nombre del campo de archivo en el formulario.
     * @param mysqli $linkExterno Conexión a la base de datos para escapar los nombres de los archivos.
     * @return PHP_FileDTO[] Un array de objetos PHP_FileDTO que representan los archivos subidos.
     */
    public static function getArrayFiles(string $name, mysqli $linkExterno) : array
    {

        if (!isset($_FILES[$name]) || !is_array($_FILES[$name]["error"]) || count($_FILES[$name]["error"]) === 0) {
            throw new InvalidArgumentException(message: "No se recibieron archivos para subir.");
        }

        $arrayFiles = [];
        foreach ($_FILES[$name]["error"] as $key => $error) {

            if ($error === UPLOAD_ERR_OK) {
                $tmpName = $_FILES[$name]["tmp_name"][$key];
                $nameArchivo = $linkExterno->real_escape_string($_FILES[$name]["name"][$key]);
                $type = $_FILES[$name]["type"][$key];
                $size = $_FILES[$name]["size"][$key];
                $arrayFiles[] = new PHP_FileDTO([
                    'tmp_name' => $tmpName,
                    'name' => $nameArchivo,
                    'type' => $type,
                    'size' => $size
                ]);
            } else {
                throw new InvalidArgumentException(message: "Error al subir el archivo: " . $error);
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

    /**Evalúa si es string y si está dentro del min/max, ambos incluidos */
    public static function esStringLongitud($val, int $min, int $max): bool
    {
        $bool = false;
        if (is_string($val)) {
            $len = strlen($val);
            if ($len === strlen(trim($val))) // Si no tiene espacios en blanco al principio o al final
                $bool = ($len >= $min) && ($len <= $max);
        }

        return $bool;
    }

    /**
     *  Verifica si una fecha es válida en el formato AAAA-MM-DD.
     *  Lanza una excepción si el formato es incorrecto o si la fecha no es válida.
     *  @param string $stringFecha La fecha en formato AAAA-MM-DD.
     *  @throws InvalidArgumentException Si el formato de la fecha es incorrecto o si la fecha no es válida.
     */
    public static function esFechaValida(string $stringFecha) : void
    {
        if (!self::_esFormatoFecha($stringFecha))
            throw new InvalidArgumentException(message: 'El formato de la fecha debe ser AAAA-MM-DD. La fecha ingresada es: ' . $stringFecha);
        list($anio, $mes, $dia) = explode('-', $stringFecha); //Revisar
        if (!checkdate($mes, $dia, $anio)) {
            throw new InvalidArgumentException(message: 'La fecha no es válida. La fecha ingresada es: ' . $stringFecha);
        }
    }

    public static function esFechaValidaYNoFutura(string $stringFecha): void
    {
        self::esFechaValida($stringFecha);
        $fechaActual = new DateTime();
        $fechaActual = DateTime::createFromFormat('Y-m-d', $fechaActual->format('Y-m-d'));
        $fechaIngresada = DateTime::createFromFormat('Y-m-d', $stringFecha);
        if ($fechaIngresada > $fechaActual) {
            throw new InvalidArgumentException(message: 'La fecha no puede ser futura. La fecha ingresada es: ' . $stringFecha);
        }
    }

    public static function esFechaValidaYNoPasada(string $stringFecha): void
    {
        self::esFechaValida($stringFecha);
        $fechaActual = new DateTime();
        $fechaActual = DateTime::createFromFormat('Y-m-d', $fechaActual->format('Y-m-d'));
        $fechaIngresada = DateTime::createFromFormat('Y-m-d', $stringFecha);
        if ($fechaIngresada < $fechaActual) {
            throw new InvalidArgumentException(message: 'La fecha no puede ser pasada. La fecha ingresada es: ' . $stringFecha);
        }
    }

    public static function esFechaMayorOIgual(string $fechaInicio, string $fechaFin): void
    {
        self::esFechaValida($fechaInicio);
        self::esFechaValida($fechaFin);

        $fechaInicioDT = DateTime::createFromFormat('Y-m-d', $fechaInicio);
        $fechaFinDT = DateTime::createFromFormat('Y-m-d', $fechaFin);

        if ($fechaFinDT < $fechaInicioDT) {
            throw new InvalidArgumentException(message: 'La fecha de fin no puede ser anterior a la fecha de inicio. Fecha inicio: ' . $fechaInicio . ', Fecha fin: ' . $fechaFin);
        }
    }

    public static function agregarDiasAFecha(string $fecha, int $dias) : string
    {
        self::esFechaValida($fecha);
        $fechaDT = DateTime::createFromFormat('Y-m-d', $fecha);
        $fechaDT->modify("+$dias days");
        return $fechaDT->format('Y-m-d');
    }

    private static function _esFormatoFecha(string $fecha): bool
    {
        return  preg_match("/^\d{4}-{1}\d{2}-{1}\d{2}$/", $fecha) === 1;
    }

    public static function redondearNumero(float $numero, int $decimales = 2): float
    {
        return round($numero, $decimales);
    }

    public static function esPrecioValido(float $precio): void
    {

        if (!is_numeric($precio)) {
            throw new InvalidArgumentException(message: 'El precio debe ser un número.');
        }
        if ($precio <= 0) {
            throw new InvalidArgumentException(message: 'El precio no puede ser negativo ni cero.');
        }
        if ($precio > 9999999999999.99) {
            throw new InvalidArgumentException(message: 'El precio no puede ser mayor a 9999999999999.99');
            }
    }
}
