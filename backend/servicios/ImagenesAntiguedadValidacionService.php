<?php


use Utilidades\Output;
use Utilidades\Querys;

class ImagenesAntiguedadValidacionService extends ValidacionFileServiceBase
{
    private static $instancia = null;

    private function __construct() {}

    public static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone() {}


    public function validarFiles(array $files, int $FKid, mysqli $linkExterno): void
    {
        $this->validarFK(
            antId: $FKid,
            mysqli: $linkExterno
        );
        
        $uploadedNumberFiles = Querys::obtenerCount(
            link: $linkExterno,
            base: 'imagenantiguedad',
            where: "imaAntId = $FKid",
            msg: 'obtener el número de imágenes de antigüedad'
        );
        
        // Validar que los archivos sean imágenes y cumplan con las restricciones
        $this->validarFilesProperties(
            files: $files,
            tipoArchivo: ['image/jpeg', 'image/png', 'image/gif'],
            maxSize: 200000, // 200 KB
            maxFiles: 5 - $uploadedNumberFiles // Permitir un máximo de 5 imágenes en total
        );
    }


    /**
     * Valida que el ID de la antigüedad sea válido y exista en la base de datos.
     * @param int|null $antId ID de la antigüedad a validar.
     * @param mysqli $mysqli Conexión a la base de datos.
     */
    private function validarFK(?int $antId, mysqli $mysqli){

        if (!$antId || $antId <= 0) {
            Output::outputError(400, 'El ID de la antigüedad es obligatorio.');
        }

        if(!Querys::existeEnBD(
            link: $mysqli,
            query: "SELECT antId FROM antiguedad WHERE antId = $antId",
            msg: 'validar el ID de la antigüedad'
        )) {
            Output::outputError(404, 'La antigüedad con el ID proporcionado no existe.');
        }
    }

}