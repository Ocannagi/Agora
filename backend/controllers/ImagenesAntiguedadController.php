<?php

use Utilidades\Output;
use Utilidades\Input;
use Utilidades\Querys;

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

    /** SECCION DE MÉTODOS CON getAntiguedadesByParams */


    public function getImagenesAntiguedadByParams(array $params)
    {
        if (is_array($params)) {
            if (array_key_exists('antId', $params) && is_numeric($params['antId'])) {
                $antId = (int)$params['antId'];
                return $this->getImagenesAntiguedadByAntId($antId);
            } else {
                Output::outputError(400, 'El parámetro antId es obligatorio y debe ser un número entero.');
            }
        } else {
            Output::outputError(400, 'Parámetros inválidos. Se esperaba un array con el parámetro "antId".');
        }
    }


    private function getImagenesAntiguedadByAntId(int $antId)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: null);

            $query = "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaAntId = $antId ORDER BY imaOrden";

            return parent::get($query, ImagenAntiguedadDTO::class);
        } catch (\Throwable $th) {
            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
            if ($th instanceof Model\CustomException) {
                Output::outputError($th->getHttpStatusCode(), "Error al obtener las imágenes de antigüedad: " . $th->getMessage() . " - " . $th->getFile() . ":" . $th->getLine());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            }
        }
    }

    /** FIN DE SECCION */


    public function getImagenesAntiguedadById($id)
    {
        settype($id, 'int');
        $this->securityService->requireLogin(tipoUsurio: null);

        $query = "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaId = $id ORDER BY imaOrden";
        return parent::getById($query, ImagenAntiguedadDTO::class);
    }

    public function postImagenesAntiguedad()
    {
        $mysqli = $this->dbConnection->conectarBD();
        $imagenesAntiguedadDTOs = [];
        try {
            $claimDTO = $this->securityService->requireLogin(['ST', 'UG', 'UA']);


            $antId = (int)$_POST['antId'] ?? null;
            $imagenes = Input::getArrayFiles("imagenesAntiguedad", $mysqli);

            $this->imagenesAntiguedadValidacionService->validarFiles(
                files: $imagenes,
                FKid: $antId,
                linkExterno: $mysqli,
                extraParams: $claimDTO // Pasar el ClaimDTO para validar la antigüedad
            );

            $countImagenesBD = Querys::obtenerCount(
                link: $mysqli,
                base: 'imagenantiguedad',
                where: "imaAntId = $antId",
                msg: 'obtener el número de imágenes de antigüedad'
            );

            for ($i = 0; $i < count($imagenes); $i++) {
                $imagenesAntiguedadDTO = new ImagenAntiguedadCreacionDTO([
                    'imaUrl' => Input::saveFile(
                        fileDTO: $imagenes[$i],
                        subcarpetaEnStorage: 'imagenesAntiguedad',
                        id: 'antId' . (string)$antId
                    ),
                    'antId' => $antId,
                    'imaOrden' => $countImagenesBD + $i + 1, // Asignar orden basado en el índice del archivo
                    'imaNombreArchivo' => $imagenes[$i]->name // Guardar el nombre original del archivo
                ]);
                $imagenesAntiguedadDTOs[] = $imagenesAntiguedadDTO;
            }

            $ids = [];
            foreach ($imagenesAntiguedadDTOs as $imagenAntiguedadDTO) {
                $query = "INSERT INTO imagenantiguedad (imaUrl, imaAntId, imaOrden, imaNombreArchivo)
                          VALUES ('{$imagenAntiguedadDTO->imaUrl}', {$imagenAntiguedadDTO->antId}, {$imagenAntiguedadDTO->imaOrden}
                                 ,'{$imagenAntiguedadDTO->imaNombreArchivo}')";
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

    public function patchImagenesAntiguedad()
    { //Solo se permite modificar el orden de las imágenes
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(['ST', 'UG', 'UA']);
            $data = Input::getArrayBody("el DTO ImagenesAntiguedadReordenarDTO");

            $imagenesAntiguedadReordenarDTO = new ImagenesAntiguedadReordenarDTO($data);

            $this->imagenesAntiguedadValidacionService->validarInputDTO(
                linkExterno: $mysqli,
                entidadDTO: $imagenesAntiguedadReordenarDTO,
                claimDTO: $claimDTO
            );

            $query = "UPDATE imagenantiguedad SET imaOrden = CASE imaId ";
            foreach ($imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden as $imagen) {
                $query .= "WHEN {$imagen->imaId} THEN {$imagen->imaOrden} ";
            }

            $query .= "END WHERE imaId IN (" . implode(", ", array_map(fn($img) => $img->imaId, $imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden)) . ")";
            $query .= " AND imaAntId = {$imagenesAntiguedadReordenarDTO->antId}";

            if (!$mysqli->query($query)) {
                throw new mysqli_sql_exception("Error al actualizar el orden de las imágenes de antigüedad: " . $mysqli->error);
            }

            return parent::patch($query, $mysqli);

        } catch (\Throwable $th) {
            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }
}
            
