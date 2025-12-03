<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class LocalidadesController extends BaseController
{
    private ValidacionServiceBase $localidadesValidacionService;
    private ISecurity $securityService;

      use traitGetPaginado;

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


 public function getLocalidadesPaginado($paginado)
    {
        $mysqli = $this->dbConnection->conectarBD();
        $query = "SELECT locId, locProvId, provDescripcion, locDescripcion FROM localidad INNER JOIN provincia ON localidad.locProvId = provincia.provId WHERE locFechaBaja is NULL";
        
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            
            $this->getPaginado($paginado, $mysqli, "localidad", "locFechaBaja is NULL", "obtener el total de localidades para paginado", $query, LocalidadDTO::class);
           
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
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) { // Verificar si la conexión fue establecida
                $mysqli->close(); // Cerrar la conexión a la base de datos
            }
        }
    }






    /** SECCION DE MÉTODOS CON getLocalidadesByParams */

    public function getLocalidadesByParams($params)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            if (is_array($params)) {
                if(array_key_exists('provId', $params) && array_key_exists('locDescripcion', $params)) {
                    $provId = null;
                    $locDescripcion = null;

                    if (Input::esNotNullVacioBlanco($params['provId'])) {
                        settype($params['provId'], 'integer');
                        $provId = $params['provId'];
                    }
                    if (Input::esNotNullVacioBlanco($params['locDescripcion'])) {
                        settype($params['locDescripcion'], 'string');
                        $locDescripcion = $params['locDescripcion'];
                    }

                    return $this->getLocalidadesByFiltros($mysqli, $provId, $locDescripcion);

                } else {
                    throw new InvalidArgumentException("No se enviaron los parámetros necesarios");
                }

            } else {
                throw new InvalidArgumentException("Los parámetros deben ser un array asociativo.");
            }
        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } elseif ($th instanceof CustomException) {
                Output::outputError($th->getCode(), "Error personalizado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        } finally {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // Cierra la conexión a la base de datos si se creó en este método.
                $mysqli->close();
            }
        }
    }

    private function getLocalidadesByFiltros(mysqli $mysqli, ?int $provId, ?string $locDescripcion)
    {
        try {
            //$this->securityService->requireLogin(null);

            $whereClauses = [];
            if (!is_null($provId)) {
                $whereClauses[] = "locProvId = $provId";
            }
            if (!is_null($locDescripcion)) {
                $locDescripcion = $mysqli->real_escape_string($locDescripcion);
                $whereClauses[] = "locDescripcion LIKE '%$locDescripcion%'";
            }

            $whereSQL = "";
            if (count($whereClauses) > 0) {
                $whereSQL = " AND " . implode(" AND ", $whereClauses);
            }

            $query =   "SELECT locId, locDescripcion, provId, provDescripcion
                    FROM localidad
                    INNER JOIN provincia ON locProvId = provId
                    WHERE locFechaBaja is NULL" . $whereSQL . "
                    ORDER BY locId";


            return parent::get(query: $query, classDTO: LocalidadDTO::class);
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

    /** SECCION DE MÉTODOS CRUD */

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
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());

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
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
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
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
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
