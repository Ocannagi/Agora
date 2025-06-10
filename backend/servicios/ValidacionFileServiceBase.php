<?php

use Model\CustomException;
use Utilidades\Input;
use Utilidades\Querys;
use DTOs\PHP_FileDTO;

define('MAX_LONG_NOMBRE_ARCHIVO', 50); // Longitud máxima del nombre del archivo

abstract class ValidacionFileServiceBase
{
    public const MAX_FILE_SIZE = 0;
    public const MAX_FILES = 0;

    /**
     * Valida los archivos y que el ID de la entidad relacionada exista en la base de datos.
     * @param array $files Array de archivos a validar.
     * @param int $FKid ID de la entidad relacionada.
     * @param mysqli $linkExterno Conexión a la base de datos.
     */
    public abstract function validarFiles(array $files, int $FKid, mysqli $linkExterno, mixed $extraParams = null): void;

    abstract public function validarInputDTO(mysqli $linkExterno, IDTO $entidadDTO, ClaimDTO $claimDTO): void;

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
            throw new InvalidArgumentException(message: "No se recibieron archivos para subir.");
        }

        // Verificar nombres duplicados
        $nombres = [];
        foreach ($files as $file) {
            if (!$file instanceof PHP_FileDTO) {
                throw new InvalidArgumentException(message: "El archivo no es válido.");
            }

            if (!Input::esNotNullVacioBlanco($file->name)) {
                throw new InvalidArgumentException(message: "El archivo no tiene un nombre válido.");
            }

            if (!Input::esStringLongitud($file->name, 1, MAX_LONG_NOMBRE_ARCHIVO)) {
                throw new InvalidArgumentException(message: "La longitud del nombre del archivo {$file->name} no es válida.");
            }

            if (in_array($file->name, $nombres)) {
                throw new InvalidArgumentException(message: "Hay archivos con el mismo nombre: {$file->name}");
            }
            $nombres[] = $file->name;

            if ($file->size <= 0) {
                throw new InvalidArgumentException(message: "El archivo {$file->name} está vacío.");
            }

            if ($maxSize > 0 && $file->size > $maxSize) {
                throw new InvalidArgumentException(message: "El archivo {$file->name} excede el tamaño máximo permitido de {$maxSize} bytes.");
            }

            if (!in_array($file->type, $tipoArchivo)) {
                throw new InvalidArgumentException(message: "El tipo de archivo {$file->name} no es válido. Debe ser uno de los siguientes: " . implode(", ", $tipoArchivo));
            }

            if ($maxFiles > 0 && count($files) > $maxFiles) {
                throw new InvalidArgumentException(message: "Se ha superado el número máximo de archivos permitidos restantes: $maxFiles.");
            }
        }
    }



}