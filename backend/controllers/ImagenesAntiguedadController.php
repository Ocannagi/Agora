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
        try {
            if (is_array($params)) {
                if (array_key_exists('antId', $params) && is_numeric($params['antId'])) {
                    $antId = (int)$params['antId'];
                    return $this->getImagenesAntiguedadByAntId($antId);
                } else {
                    throw new InvalidArgumentException(code: 400, message: 'El parámetro antId es obligatorio y debe ser un número entero.');
                }
            } else {
                throw new InvalidArgumentException(code: 400, message: 'Parámetros inválidos. Se esperaba un array con el parámetro "antId".');
            }
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof Model\CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    private function getImagenesAntiguedadByAntId(int $antId)
    {
        $this->securityService->requireLogin(tipoUsurio: null);
        $query = "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaAntId = $antId ORDER BY imaOrden";
        return parent::get($query, ImagenAntiguedadDTO::class);
    }

    /** FIN DE SECCION */


    public function getImagenesAntiguedadById($id)
    {
        try {
            settype($id, 'int');
            $this->securityService->requireLogin(tipoUsurio: null);

            $query = "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaId = $id ORDER BY imaOrden";
            return parent::getById($query, ImagenAntiguedadDTO::class);
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof Model\CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function postImagenesAntiguedad()
    {
        $mysqli = $this->dbConnection->conectarBD();
        $stmt = $mysqli->prepare("INSERT INTO imagenantiguedad (imaUrl, imaAntId, imaOrden, imaNombreArchivo) VALUES (?, ?, ?, ?)");
        $imagenesAntiguedadDTOs = [];
        try {
            $mysqli->begin_transaction(); // Iniciar transacción
            $claimDTO = $this->securityService->requireLogin(TipoUsuarioEnum::compradorVendedorToArray());


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

            /*
            foreach ($imagenesAntiguedadDTOs as $imagenAntiguedadDTO) {
                $query = "INSERT INTO imagenantiguedad (imaUrl, imaAntId, imaOrden, imaNombreArchivo)
                          VALUES ('{$imagenAntiguedadDTO->imaUrl}', {$imagenAntiguedadDTO->antId}, {$imagenAntiguedadDTO->imaOrden}
                                 ,'{$imagenAntiguedadDTO->imaNombreArchivo}')";
                if (!$mysqli->query($query)) {
                    throw new mysqli_sql_exception("Error al insertar la imagen de antigüedad: " . $mysqli->error);
                }
                $ids[] = $mysqli->insert_id; // Guardar el ID de la imagen insertada
            }
            */

            if (!$stmt) {
                throw new mysqli_sql_exception("Error al preparar la consulta: " . $mysqli->error);
            }
            foreach ($imagenesAntiguedadDTOs as $imagenAntiguedadDTO) {
                $stmt->bind_param(
                    'siis',
                    $imagenAntiguedadDTO->imaUrl,
                    $imagenAntiguedadDTO->antId,
                    $imagenAntiguedadDTO->imaOrden,
                    $imagenAntiguedadDTO->imaNombreArchivo
                );
                if (!$stmt->execute()) {
                    throw new mysqli_sql_exception("Error al insertar la imagen de antigüedad: " . $stmt->error);
                }
                $ids[] = $mysqli->insert_id; // Guardar el ID de la imagen insertada
            }

            $mysqli->commit(); // Confirmar la transacción
            Output::outputJson(['ids' => $ids], 201); // Retornar los IDs de las imágenes insertadas

        } catch (\Throwable $th) {

            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->rollback(); // Revertir transacción si hay error
            }

            if (!empty($imagenesAntiguedadDTOs) && count($imagenesAntiguedadDTOs) > 0) {
                foreach ($imagenesAntiguedadDTOs as $imagen) {
                    $absolutePath = dirname(__DIR__, 2) . $imagen->imaUrl;
                    if (file_exists($absolutePath)) {
                        unlink($absolutePath); // Eliminar el archivo si existe
                    }
                }
            }

            if ($th instanceof Model\CustomException) {
                Output::outputError($th->getCode(), "Error al guardar las imágenes de antigüedad: " . $th->getMessage() . " - " . $th->getFile() . ":" . $th->getLine());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close(); // Cerrar el statement
            }
            
            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
        }
    }

    public function patchImagenesAntiguedad()
    { //Solo se permite modificar el orden de las imágenes
        $mysqli = $this->dbConnection->conectarBD();
        $stmt = $mysqli->prepare("UPDATE imagenantiguedad SET imaOrden = ? WHERE imaId = ? AND imaAntId = ?");
        try {
            $claimDTO = $this->securityService->requireLogin(TipoUsuarioEnum::compradorVendedorToArray());
            $data = Input::getArrayBody("el DTO ImagenesAntiguedadReordenarDTO");

            $imagenesAntiguedadReordenarDTO = new ImagenesAntiguedadReordenarDTO($data);

            $this->imagenesAntiguedadValidacionService->validarInputDTO(
                linkExterno: $mysqli,
                entidadDTO: $imagenesAntiguedadReordenarDTO,
                claimDTO: $claimDTO
            );

            /* Actualizar el orden de las imágenes con una sola consulta (opción comentada)
            $query = "UPDATE imagenantiguedad SET imaOrden = CASE imaId ";
            foreach ($imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden as $imagen) {
                $query .= "WHEN {$imagen->imaId} THEN {$imagen->imaOrden} ";
            }

            $query .= "END WHERE imaId IN (" . implode(", ", array_map(fn($img) => $img->imaId, $imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden)) . ")";
            $query .= " AND imaAntId = {$imagenesAntiguedadReordenarDTO->antId}";

            if (!$mysqli->query($query)) {
                throw new mysqli_sql_exception("Error al actualizar el orden de las imágenes de antigüedad: " . $mysqli->error);
            }
            */

            // Actualizar el orden de cada imagen con consulta preparada
            if (!$stmt) {
                throw new mysqli_sql_exception("Error al preparar la consulta: " . $mysqli->error);
            }
            foreach ($imagenesAntiguedadReordenarDTO->imagenesAntiguedadOrden as $imagen) {
                $stmt->bind_param(
                    'iii',
                    $imagen->imaOrden,
                    $imagen->imaId,
                    $imagenesAntiguedadReordenarDTO->antId
                );
                if (!$stmt->execute()) {
                    throw new mysqli_sql_exception("Error al actualizar el orden de la imagen de antigüedad: " . $stmt->error);
                }
            }
            Output::outputJson([], 200);
            //return parent::patch($query, $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } elseif ($th instanceof Model\CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }

            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
        }
    }

    public function deleteImagenesAntiguedad($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $mysqli->begin_transaction(); // Iniciar transacción

            settype($id, 'int');
            $claimDTO = $this->securityService->requireLogin(TipoUsuarioEnum::compradorVendedorToArray());

            $imagenAntiguedadDTO = $this->_obtenerImagenAntiguedadDTO(imaId: $id, claimDTO: $claimDTO, mysqli: $mysqli);

            if (Querys::obtenerCount(
                link: $mysqli,
                base: 'imagenantiguedad',
                where: "imaAntId = {$imagenAntiguedadDTO->antId}",
                msg: 'obtener el número de imágenes de antigüedad'
            ) <= 1) {
                throw new Model\CustomException(code: 404, message: "No se puede eliminar la última imagen de antigüedad. Debe haber al menos una imagen asociada a la antigüedad.");
            }

            $this->_eliminarImagenAntiguedadBD($imagenAntiguedadDTO, $mysqli); // Eliminar la imagen de la base de datos

            $this->_reordenarImagenesAntiguedad($imagenAntiguedadDTO, $mysqli); // Reordenar las imágenes restantes

            $this->_eliminarArchivoStorage($imagenAntiguedadDTO->imaUrl); // Eliminar el archivo del sistema de archivos

            $mysqli->commit(); // Confirmar transacción

            Output::outputJson([], 204); // Retornar 204 No Content si la eliminación fue exitosa

        } catch (\Throwable $th) {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->rollback(); // Revertir transacción si hay error
            }
            // Manejo de excepciones
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof Model\CustomException) {
                Output::outputError($th->getCode(), $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
        }
    }

    private function _obtenerImagenAntiguedadDTO(int $imaId, ClaimDTO $claimDTO, mysqli $mysqli): ImagenAntiguedadDTO
    {
        $query = "SELECT imaId, imaUrl, imaAntId, imaOrden, imaNombreArchivo FROM imagenantiguedad WHERE imaId = $imaId";

        if ($claimDTO->usrTipoUsuario !== 'ST') {
            $query .= " AND EXISTS (SELECT 1 FROM antiguedad WHERE antId = imaAntId AND antUsrId = {$claimDTO->usrId})";
        }

        $resultado = $mysqli->query($query);
        if (!$resultado) {
            throw new mysqli_sql_exception("Error al buscar la imagen de antigüedad: " . $mysqli->error);
        }

        if ($resultado->num_rows === 0) {
            $msg = "No se encontró una imagen de antigüedad con ID: $imaId.";
            if ($claimDTO->usrTipoUsuario !== 'ST') {
                $msg .= " Asegúrese de que la imagen pertenece a una antigüedad del usuario con ID: {$claimDTO->usrId}.";
            }
            throw new Model\CustomException($msg, 404);
        }

        $imagenAntiguedadDTO = new ImagenAntiguedadDTO(mysqli_fetch_assoc($resultado));
        $resultado->free_result();
        return $imagenAntiguedadDTO;
    }

    private function _eliminarArchivoStorage(string $filePath): void
    {
        $absolutePath = dirname(__DIR__, 2) . $filePath;

        if (file_exists($absolutePath)) {
            if (!unlink($absolutePath)) {
                throw new Model\CustomException("Error al eliminar el archivo: $filePath", 500);
            }
        } else {
            throw new Model\CustomException("El archivo no existe: $filePath", 404);
        }
    }

    private function _eliminarImagenAntiguedadBD(ImagenAntiguedadDTO $imagenAntiguedadDTO, mysqli $mysqli): void
    {
        $query = "DELETE FROM imagenantiguedad WHERE imaId = {$imagenAntiguedadDTO->imaId}";
        if (!$mysqli->query($query)) {
            throw new mysqli_sql_exception("Error al eliminar la imagen de antigüedad: " . $mysqli->error);
        }
        if ($mysqli->affected_rows === 0) {
            throw new Model\CustomException(404, "No se encontró la imagen de antigüedad con ID: {$imagenAntiguedadDTO->imaId}");
        }
    }

    private function _reordenarImagenesAntiguedad(ImagenAntiguedadDTO $imagenAntiguedadDTO, mysqli $mysqli): void
    {
        if ($this->imagenesAntiguedadValidacionService::MAX_FILES != $imagenAntiguedadDTO->imaOrden) {
            $query = "UPDATE imagenantiguedad SET imaOrden = imaOrden - 1 WHERE imaOrden > {$imagenAntiguedadDTO->imaOrden} AND imaAntId = {$imagenAntiguedadDTO->antId}";
            if (!$mysqli->query($query)) {
                throw new mysqli_sql_exception("Error al actualizar el orden de las imágenes de antigüedad: " . $mysqli->error);
            }
        }
    }
}
