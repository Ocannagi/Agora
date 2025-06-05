<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;
use DTOs\PHP_FileDTO;

abstract class ValidacionFileServiceBase
{

    /**
     * Valida los archivos y que el ID de la entidad relacionada exista en la base de datos.
     * @param array $files Array de archivos a validar.
     * @param int $FKid ID de la entidad relacionada.
     * @param mysqli $linkExterno Conexión a la base de datos.
     */
    public abstract function validarFiles(array $files, int $FKid, mysqli $linkExterno): void;
    
    /**
     * Valida que los archivos recibidos sean válidos según el tipo y tamaño especificados.
     * Si algún archivo no es válido, lanza un error.
     * @param array $files Array de archivos de clase PHP_FileDTO a validar.
     * @param array $tipoArchivo Array de tipos de archivo permitidos.
     * @param int $maxSize Tamaño máximo permitido para los archivos (en bytes).
     */
    protected function validarFilesProperties(array $files, array $tipoArchivo, int $maxSize = 0, int $maxFiles = 0)
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



}