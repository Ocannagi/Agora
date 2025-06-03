<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;
use DTOs\PHP_FileDTO;

abstract class ValidacionServiceBase
{

    abstract public function validarInput(mysqli $linkExterno, ICreacionDTO | IDTO $entidadDTO);

    
    /**
     * Valida que los archivos recibidos sean válidos según el tipo y tamaño especificados.
     * Si algún archivo no es válido, lanza un error.
     * @param array $files Array de archivos de clase PHP_FileDTO a validar.
     * @param array $tipoArchivo Array de tipos de archivo permitidos.
     * @param int $maxSize Tamaño máximo permitido para los archivos (en bytes).
     */
    public function validarFiles(array $files, array $tipoArchivo, int $maxSize = 0, int $maxFiles = 0)
    {
        if (empty($files)) {
            Output::outputError(400, "No se recibieron archivos para subir.");
        }

        foreach ($files as $file) {
            if (!$file instanceof PHP_FileDTO) {
                Output::outputError(400, "El archivo no es válido.");
            }

            if ($file->size <= 0) {
                Output::outputError(400, "El archivo {$file->name} está vacío.");
            }

            if ($maxSize > 0 && $file->size > $maxSize) {
                Output::outputError(400, "El archivo {$file->name} excede el tamaño máximo permitido de {$maxSize} bytes.");
            }

            if (!in_array($file->type, $tipoArchivo)) {
                Output::outputError(400, "El tipo de archivo {$file->name} no es válido. Debe ser uno de los siguientes: " . implode(", ", $tipoArchivo));
            }

            if ($maxFiles > 0 && count($files) > $maxFiles) {
                Output::outputError(400, "Se ha superado el número máximo de archivos permitidos: $maxFiles.");
            }
        }
    }


    /**
     * Valida que los tipos de datos de las propiedades de la clase coincidan con los tipos de los datos del array.
     * Si no coinciden, lanza un error.
     * @param string $className Nombre de la clase a validar.
     * @param array $datos Array de datos a validar.
     */
    public function validarType(string $className, array $datos)
    {
        $refClass = new ReflectionClass($className);
        $propiedades = $refClass->getProperties();
        foreach ($propiedades as $propiedad) {
            $tipo = $propiedad->getType()->getName();
            if (array_key_exists($propiedad->getName(), $datos)) {

                if (!$propiedad->getType()->isBuiltin()) {
                    continue; // Si no es un tipo primitivo, no se valida
                }

                $estandarizarType = function (string $tipo): string {
                    return match ($tipo) {
                        'integer' => 'int',
                        'boolean' => 'bool',
                        'double' => 'float',
                        default => $tipo,
                    };
                };


                if ($estandarizarType(gettype($datos[$propiedad->getName()])) !== $tipo && gettype($datos[$propiedad->getName()]) !== 'NULL') {

                    /*   var_dump($datos[$propiedad->getName()]);
                    var_dump(gettype($datos[$propiedad->getName()]));
                    var_dump($tipo); */



                    Output::outputError(400, "El campo " . $propiedad->getName() . " debe ser de tipo $tipo.");
                }
            }
        }
    }

    protected function validarDatosObligatorios(string $classModelName, array $datos)
    {
        if (is_subclass_of($classModelName, "ClassBase")) {
            $msg = $this->_existenDatos($classModelName::getObligatorios(), $datos);
            if ($msg !== true)
                Output::outputError(400, 'Los siguientes datos deben estar completos: ' . implode(", ", $msg) . ".");
        } else {
            Output::outputError(500, "Error interno: la clase $classModelName no hereda de ClassBase");
        }
    }


    /****************** Funciones Privadas sin Conectar a BD ******************/

    /**
     * Devuelve true si las key pasadas en el primer parámetro tienen asignado un valor en el segundo parámetro, o bien,
     * devuelve un array de strings con las key sin valor.
     */
    protected function _existenDatos(array $arrayKeys, array $arrayAsociativo): bool|array
    {
        if (!is_array($arrayKeys) || !is_array($arrayAsociativo))
            Output::outputError(400, 'No se enviaron los datos necesarios para la operación.');

        $faltantes = [];

        for ($i = 0; $i < count($arrayKeys); $i++) {
            if (!isset($arrayAsociativo[$arrayKeys[$i]]))
                $faltantes[] = $arrayKeys[$i];
            else if (is_string($arrayAsociativo[$arrayKeys[$i]]) && !Input::esNotNullVacioBlanco($arrayAsociativo[$arrayKeys[$i]]))
                $faltantes[] = $arrayKeys[$i];
            else if (is_array($arrayAsociativo[$arrayKeys[$i]]) && count($arrayAsociativo[$arrayKeys[$i]]) === 0)
                $faltantes[] = $arrayKeys[$i];
        }

        if (count($faltantes) === 0)
            return true;
        else
            return $faltantes;
    }

    protected function _esDigito(string $strNum): bool
    {
        return preg_match("/^\d+$/", $strNum) === 1;
    }

    protected function _esDigitoNegativoOPositivo(string $strNum): bool
    {
        return preg_match("/^-?\d+$/", $strNum) === 1;
    }

    protected function _esLetraSinTilde(string $str): bool
    {
        return preg_match("/^[a-zñA-ZÑ]+$/", $str) === 1;
    }

    protected function _esAlfaNumerico(string $str): bool
    {
        return preg_match("/^[a-zA-Z0-9ñÑ]+$/", $str) === 1;
    }



    /**Evalúa si es string y si está dentro del min/max, ambos incluidos */
    protected function _esStringLongitud($val, int $min, int $max): bool
    {
        $bool = false;
        if (is_string($val)) {
            $len = strlen($val);
            if ($len === strlen(trim($val))) // Si no tiene espacios en blanco al principio o al final
                $bool = ($len >= $min) && ($len <= $max);
        }

        return $bool;
    }

    protected function _esApellidoNombreValido(string $apellido): bool
    {
        return preg_match("/^[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ]{1}[a-zñäëïöüáéíóúâêîôûàèìòù'-]*(?:[a-zñäëïöüáéíóúâêîôûàèìòù']\s?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ]{1}[a-zñäëïöüáéíóúâêîôûàèìòù'-]*)*$/", $apellido) === 1;
    }

    protected function _esEmailValido(string $email): bool
    {
        return preg_match("/^[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*@[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,5}$/", $email) === 1;
    }

    /**
     * Verifica que tenga entre 8 y 25 caracteres, que tenga al menos una mayúscula, una minúscula, al menos un número y al menos un carácter especial #?!@$%^&*-"
     */
    protected function _esPasswordValido(string $password): bool
    {
        return preg_match("/^(?=.*?[A-ZÑÄËÏÖÜÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙ])(?=.*?[a-zñäëïöüáéíóúâêîôûàèìòù])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/", $password) === 1;
    }

    /**
     * Verifica que tenga 11 caracteres, que sean todos dígitos, que el prefijo sea 20, 23, 24, 25, 26, 27, 30, 33 o 34 y que el dígito verificador sea correcto.
     */
    protected function _esCuitCuilValido(string $cuilCuit): bool
    {
        $bool = false;
        if (is_string($cuilCuit) && strlen($cuilCuit) === 11 && preg_match_all("/[^\d]/", $cuilCuit) === 0) {
            if (in_array((int)substr($cuilCuit, 0, 2), [20, 23, 24, 25, 26, 27, 30, 33, 34])) {
                $sum = 0;
                for ($i = 0; $i < 10; $i++) {
                    switch ($i) {
                        case 0:
                            $sum += (int)$cuilCuit[$i] * 5;
                            break;
                        case 1:
                            $sum += (int)$cuilCuit[$i] * 4;
                            break;
                        case 2:
                            $sum += (int)$cuilCuit[$i] * 3;
                            break;
                        case 3:
                            $sum += (int)$cuilCuit[$i] * 2;
                            break;
                        case 4:
                            $sum += (int)$cuilCuit[$i] * 7;
                            break;
                        case 5:
                            $sum += (int)$cuilCuit[$i] * 6;
                            break;
                        case 6:
                            $sum += (int)$cuilCuit[$i] * 5;
                            break;
                        case 7:
                            $sum += (int)$cuilCuit[$i] * 4;
                            break;
                        case 8:
                            $sum += (int)$cuilCuit[$i] * 3;
                            break;
                        case 9:
                            $sum += (int)$cuilCuit[$i] * 2;
                            break;
                    }
                }

                $resto = $sum % 11;

                if ($resto === 0)
                    $bool = $resto === (int)$cuilCuit[10];
                else
                    $bool = (11 - $resto) === (int)$cuilCuit[10];
            }
        }

        return $bool;
    }

    protected function _esFormatoFecha(string $fecha): bool
    {
        return preg_match("/^\d{4}-{1}\d{2}-{1}\d{2}$/", $fecha) === 1;
    }

    /***************************** Funciones privadas que conectan a BD con link externo ********************************/

    /**
     * Verifica si existe un registro en la base de datos con el query pasado como parámetro.
     * Si no existe, devuelve false, si existe, devuelve true.
     * @param mysqli $link Conexión a la base de datos.
     * @param string $query Consulta SQL a ejecutar.
     * @param string $msg Mensaje descriptivo de la acción que se está realizando.
     * @return bool
     */
    protected function _existeEnBD(mysqli $link, string $query, string $msg): bool
    {
        return Querys::existeEnBD($link, $query, $msg);
    }
}
