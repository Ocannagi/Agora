<?php

use Utilidades\Output;
use Utilidades\Input;

class ImagenesAntiguedadController extends BaseController
{
    private ValidacionFileServiceBase $imagenesAntiguedadValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionFileServiceBase $imagenesAntiguedadValidacionService)
    {
        parent::__construct($dbConnection);
        $this->imagenesAntiguedadValidacionService = $imagenesAntiguedadValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionFileServiceBase $imagenesAntiguedadValidacionService): ImagenesAntiguedadController
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
        $mysqli = $this->dbConnection->conectarBD();
        $imagenesAntiguedadDTOs = [];
        try {
            $this->securityService->requireLogin(['ST', 'UG', 'UA']);


            $antId = (int)$_POST['antId'] ?? null;
            $imagenes = Input::getArrayFiles("imagenesAntiguedad", $mysqli);

            $this->imagenesAntiguedadValidacionService->validarFiles(
                files: $imagenes,
                FKid: $antId,
                linkExterno: $mysqli
            );

            for ($i = 0; $i < count($imagenes); $i++) {
                $imagenesAntiguedadDTO = new ImagenAntiguedadCreacionDTO([
                    'imaUrl' => Input::saveFile(
                        fileDTO: $imagenes[$i],
                        subcarpetaEnStorage: 'imagenesAntiguedad',
                        id: (string)$antId
                    ),
                    'antId' => $antId,
                    'imaOrden' => $i + 1 // Asignar orden basado en el índice del archivo
                ]);
                $imagenesAntiguedadDTOs[] = $imagenesAntiguedadDTO;
            }

            $ids = [];
            foreach ($imagenesAntiguedadDTOs as $imagenAntiguedadDTO) {
                $query = "INSERT INTO imagenantiguedad (imaUrl, imaAntId, imaOrden) VALUES ('{$imagenAntiguedadDTO->imaUrl}', {$imagenAntiguedadDTO->antId}, {$imagenAntiguedadDTO->imaOrden})";
                if (!$mysqli->query($query)) {
                    throw new mysqli_sql_exception("Error al insertar la imagen de antigüedad: " . $mysqli->error);
                }
                $ids[] = $mysqli->insert_id; // Guardar el ID de la imagen insertada
            }
            $mysqli->close(); // Cerrar la conexión a la base de datos
            Output::outputJson(['ids' => $ids], 201); // Retornar los IDs de las imágenes insertadas

        } catch (\Throwable $th) {

            if (!empty($imagenesAntiguedadDTOs) && count($imagenesAntiguedadDTOs) > 0) {
                foreach ($imagenesAntiguedadDTOs as $imagen) {
                    if (file_exists($imagen->imaUrl)) {
                        unlink($imagen->imaUrl); // Eliminar el archivo si existe
                    }
                }
            }

            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }

            if ($th instanceof Model\CustomException) {
                Output::outputError($th->getHttpStatusCode(), "Error al guardar las imágenes de antigüedad: " . $th->getMessage() . " - " . $th->getFile() . ":" . $th->getLine());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }
}
