<?php


use Model\CustomException;
use Utilidades\Querys;
use DTOs\PHP_FileDTO;

define('MAX_FILE_SIZE', 200000); // 200 KB
// Define la constante para el tamaño máximo de archivo permitido
define('MAX_FILES', 5); // Máximo de 5 archivos permitidos

class ImagenesAntiguedadValidacionService extends ValidacionFileServiceBase
{
    private static $instancia = null;

    public const MAX_FILE_SIZE = MAX_FILE_SIZE;
    public const MAX_FILES = MAX_FILES;

    private function __construct() {}

    public static function getInstancia(): ImagenesAntiguedadValidacionService
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone() {}


    public function validarFiles(array $files, int $FKid, mysqli $linkExterno, mixed $extraParams = null): void
    {
        if(!isset($extraParams) || !$extraParams instanceof ClaimDTO) {
            throw new CustomException(code: 500, message: 'Error interno: se requiere ClaimDTO para validar la antigüedad.');
        }
        
        $this->validarFK(
            antId: $FKid,
            mysqli: $linkExterno,
            claimDTO: $extraParams
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
            maxSize: MAX_FILE_SIZE,
            maxFiles: MAX_FILES - $uploadedNumberFiles
        );

        // Validar que los nombres de los archivos no estén duplicados en la base de datos
        $this->validarNombreDuplicadoEnBD(
            files: $files,
            antId: $FKid,
            mysqli: $linkExterno
        );

    }


    /**
     * Valida que el ID de la antigüedad sea válido y exista en la base de datos.
     * @param int|null $antId ID de la antigüedad a validar.
     * @param mysqli $mysqli Conexión a la base de datos.
     */
    private function validarFK(?int $antId, mysqli $mysqli, ClaimDTO $claimDTO): void{

        if (!$antId || $antId <= 0) {
            throw new InvalidArgumentException(message: 'El ID de la antigüedad es obligatorio.');
        }

        $query = "SELECT antId FROM antiguedad WHERE antId = $antId";
        if ($claimDTO->usrTipoUsuario !== 'ST') {
            $query .= " AND antUsrId = {$claimDTO->usrId}";
        }

        if(!Querys::existeEnBD(
            link: $mysqli,
            query: $query,
            msg: 'validar el ID de la antigüedad'
        )) {
            $msgError = $claimDTO->usrTipoUsuario === 'ST' ? 
                "La antigüedad con el ID $antId no existe." : 
                "La antigüedad con el ID $antId no existe o no tienes permiso para acceder a ella.";
            throw new CustomException(code: 404, message: $msgError);
        }

    }

    private function validarNombreDuplicadoEnBD(array $files, int $antId ,mysqli $mysqli): void
    {
        foreach ($files as $file) {
            if (!$file instanceof PHP_FileDTO) {
                throw new CustomException(code: 500, message: "Error interno: El DTO de archivo no es válido.");
            }
            
            if(Querys::existeEnBD(
                link: $mysqli,
                query: "SELECT imaNombreArchivo FROM imagenantiguedad WHERE imaNombreArchivo = '{$file->name}' AND imaAntId = $antId",
                msg: "validar el nombre del archivo {$file->name}"
            )) {
                throw new InvalidArgumentException(message: "El archivo {$file->name} ya existe en la base de datos para la antigüedad con ID $antId.");
            }
        }
    }

    public function validarInputDTO(mysqli $linkExterno, IDTO $entidadDTO, ClaimDTO $claimDTO): void
    {
        if(!($entidadDTO instanceof ImagenesAntiguedadReordenarDTO)) {
            throw new CustomException(code: 500, message: 'Error interno: El DTO de imagen de antigüedad no es del tipo correcto.');
        }

        $this->validarFK(
            antId: $entidadDTO->antId,
            mysqli: $linkExterno,
            claimDTO: $claimDTO
        );

        $this->_validarTipoDato($entidadDTO);

        $this->_validarMinMaxImagenes($entidadDTO);

        $this->_validarUnicidadImagenes($entidadDTO);

        $this->_validarRangoOrden($entidadDTO);

        if(Querys::obtenerCount(
            link: $linkExterno,
            base: 'imagenantiguedad',
            where: "imaAntId = {$entidadDTO->antId}",
            msg: 'obtener el número de imágenes de antigüedad'
        ) !== count($entidadDTO->imagenesAntiguedadOrden)) {
            throw new InvalidArgumentException(message: 'El número de imágenes a reordenar no coincide con el número de imágenes en la base de datos.');
        }

        foreach ($entidadDTO->imagenesAntiguedadOrden as $imagenDTO) {
            if(Querys::existeEnBD(
                link: $linkExterno,
                query: "SELECT 1 FROM imagenantiguedad WHERE imaId = {$imagenDTO->imaId} AND imaAntId = {$entidadDTO->antId}",
                msg: 'validar la existencia de la imagen de antigüedad'
            ) === false) {
                throw new CustomException(code: 404, message: "La imagen con ID {$imagenDTO->imaId} no existe para la antigüedad con ID {$entidadDTO->antId}.");
            }
        }

    }

    private function _validarTipoDato(ImagenesAntiguedadReordenarDTO $imagenesAntiguedadReordenarDTO): void
    {
        if (!is_array($imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden) || empty($imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden)) {
            throw new InvalidArgumentException(message: 'El campo "imagenesAntiguedadOrden" debe ser un array no vacío.');
        }

        foreach ($imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden as $imagenDTO) {
            if (!($imagenDTO instanceof ImagenAntiguedadOrdenDTO)) {
                throw new CustomException(code: 500, message: 'Error interno: El DTO de imagen de antigüedad no es del tipo correcto.');
            }
        }
    }

    private function _validarMinMaxImagenes(ImagenesAntiguedadReordenarDTO $imagenesAntiguedadReordenarDTO): void
    {
       //Se comenta porque bloquea el método patch de AntiguedadesController que se da en simultáneo con el Controller de ImagenesAntiguedad
        /*  if (count($imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden) < 2) {
            throw new InvalidArgumentException(message: 'Se requiere al menos dos imágenes para reordenar.');
        } */

        if (count($imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden) > MAX_FILES) {
            throw new InvalidArgumentException(message: 'El número máximo de imágenes para reordenar es ' . MAX_FILES . '.');
        }
    }

    private function _validarUnicidadImagenes(ImagenesAntiguedadReordenarDTO $imagenesAntiguedadReordenarDTO): void
    {
        $ids = array_map(fn($img) => $img->imaId, $imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden);
        if (count($ids) !== count(array_unique($ids))) {
            throw new InvalidArgumentException(message: 'Los IDs de las imágenes deben ser únicos.');
        }

        $ordenes = array_map(fn($img) => $img->imaOrden, $imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden);
        if (count($ordenes) !== count(array_unique($ordenes))) {
            throw new InvalidArgumentException(message: 'Los órdenes de las imágenes deben ser únicos.');
        }
    }

    private function _validarRangoOrden(ImagenesAntiguedadReordenarDTO $imagenesAntiguedadReordenarDTO): void
    {
        $ordenes = array_map(fn($img) => $img->imaOrden, $imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden);
        $cantidadImagenes = count($imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden);
        foreach ($ordenes as $orden) {
            if ($orden < 1 || $orden > $cantidadImagenes) {
                throw new InvalidArgumentException(message: 'El orden de la imagen debe estar entre 1 y ' . $cantidadImagenes . '.');
            }
        }
    }

}