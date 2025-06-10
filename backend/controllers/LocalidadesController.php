<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class LocalidadesController extends BaseController
{
    private ValidacionServiceBase $localidadesValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $localidadesValidacionService)
    {
        parent::__construct($dbConnection);
        $this->localidadesValidacionService = $localidadesValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $localidadesValidacionService): LocalidadesController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $localidadesValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getLocalidades()
    {
        try {
            $this->securityService->requireLogin(null);

            $query =   "SELECT locId, locDescripcion, provId, provDescripcion
                    FROM localidad
                    INNER JOIN provincia ON locProvId = provId
                    WHERE locFechaBaja is NULL
                    ORDER BY locId";

            return parent::get(query: $query, classDTO: "LocalidadDTO");
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function getLocalidadesById($id)
    {
        try {
            settype($id, 'integer');
            $this->securityService->requireLogin(null);

            $query =   "SELECT locId, locDescripcion, provId, provDescripcion
                    FROM localidad
                    INNER JOIN provincia ON locProvId = provId
                    WHERE locId = $id AND locFechaBaja is NULL";

            return parent::getById(query: $query, classDTO: "LocalidadDTO");
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function postLocalidades()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);

            $data = Input::getArrayBody(msgEntidad: "la localidad");

            $this->localidadesValidacionService->validarType(className: "LocalidadCreacionDTO", datos: $data);
            $localidadCreacionDTO = new LocalidadCreacionDTO($data);

            $this->localidadesValidacionService->validarInput($mysqli, $localidadCreacionDTO);
            $localidadCreacionDTO->locDescripcion = Input::cadaPalabraMayuscula($localidadCreacionDTO->locDescripcion);
            Input::escaparDatos($localidadCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($localidadCreacionDTO);

            $locProvId = $localidadCreacionDTO->provincia->provId;

            $query =   "INSERT INTO localidad (locDescripcion, locProvId)
                        VALUES ($localidadCreacionDTO->locDescripcion, $locProvId)";

            return parent::post(query: $query, link: $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->close();
            }
        }
    }

    public function patchLocalidades($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);
            settype($id, 'integer');
            
            $data = Input::getArrayBody(msgEntidad: "la localidad");

            $data['locId'] = $id;

            $this->localidadesValidacionService->validarType(className: "LocalidadDTO", datos: $data);
            $localidadDTO = new LocalidadDTO($data);

            $this->localidadesValidacionService->validarInput($mysqli, $localidadDTO);

            $localidadDTO->locDescripcion = Input::cadaPalabraMayuscula($localidadDTO->locDescripcion);

            Input::escaparDatos($localidadDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($localidadDTO);

            $locProvId = $localidadDTO->provincia->provId;

            $query =   "UPDATE localidad
                        SET locDescripcion = $localidadDTO->locDescripcion,
                            locProvId = $locProvId
                        WHERE locId = $localidadDTO->locId
                        AND locFechaBaja IS NULL";

            return parent::patch(query: $query, link: $mysqli);
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->close();
            }
        }
    }

    public function deleteLocalidades($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);
            settype($id, 'integer');

            return parent::delete(queryBusqueda: "SELECT 1 FROM localidad WHERE locId=$id AND locFechaBaja IS NULL", queryBajaLogica: "UPDATE localidad SET locFechaBaja = CURRENT_TIMESTAMP() WHERE locId=$id");
        } catch (\Throwable $th) {
            if ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } elseif ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }
}
