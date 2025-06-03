<?php

use Utilidades\Output;
use Utilidades\Input;

class ImagenesAntiguedadController extends BaseController
{
    private ValidacionServiceBase $imagenesAntiguedadValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $imagenesAntiguedadValidacionService)
    {
        parent::__construct($dbConnection);
        $this->imagenesAntiguedadValidacionService = $imagenesAntiguedadValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $imagenesAntiguedadValidacionService): ImagenesAntiguedadController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $imagenesAntiguedadValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}


    public function postImagenesAntiguedad()
    {
        $this->securityService->requireLogin(['ST', 'UG', 'UA']);
        $mysqli = $this->dbConnection->conectarBD();

        $imagenes = Input::getArrayFiles("imagenesAntiguedad");

        $this->imagenesAntiguedadValidacionService->validarFiles(
            files: $imagenes,
            tipoArchivo: ['image/jpeg', 'image/png', 'image/gif'],
            maxSize: 200000, // 200 KB
            maxFiles: 5
        );

        $data = Input::getArrayBody(msgEntidad:'las imágenes de antigüedad');

        if (!Input::contieneSoloArraysAsociativos($data)) {
            Output::outputError(400, "Los datos recibidos no son válidos. Se esperaba un array asociativo.");
        }

        ///HASTA ACÁ, SIGO MAÑANA

        // Convertir los archivos a DTOs
        $imagenesAntiguedadDTOs = [];



        $imagenesAntiguedadDTO = Input::getJsonBody("ImagenesAntiguedadDTO");

        $this->imagenesAntiguedadValidacionService->validar($imagenesAntiguedadDTO);

        $query = "INSERT INTO imagenes_antiguedad (imaAntFecha, imaAntDescripcion, imaAntUrl)
                  VALUES (:imaAntFecha, :imaAntDescripcion, :imaAntUrl)";

        $params = [
            ':imaAntFecha' => $imagenesAntiguedadDTO->getImaAntFecha(),
            ':imaAntDescripcion' => $imagenesAntiguedadDTO->getImaAntDescripcion(),
            ':imaAntUrl' => $imagenesAntiguedadDTO->getImaAntUrl()
        ];

        return parent::post(query: $query, params: $params);
    }
}