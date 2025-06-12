<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class DomiciliosController extends BaseController
{
    private ValidacionServiceBase $domiciliosValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $domiciliosValidacionService)
    {
        parent::__construct($dbConnection);
        $this->domiciliosValidacionService = $domiciliosValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $domiciliosValidacionService): DomiciliosController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $domiciliosValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getDomicilios()
    {
        try {
            $this->securityService->requireLogin(null);

            $query =  "SELECT domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto,
                    locId, locDescripcion, provId, provDescripcion
                    FROM domicilio
                    INNER JOIN localidad ON domLocId = locId
                    INNER JOIN provincia ON locProvId = provId
                    WHERE domFechaBaja is NULL
                    ORDER BY domId";

            return parent::get(query: $query, classDTO: "DomicilioDTO");
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

    public function getDomiciliosById($id)
    {
        try {
            settype($id, 'integer');
            $this->securityService->requireLogin(null);

            $query =  "SELECT domId, domCPA, domCalleRuta, domNroKm, domPiso, domDepto,
                    locId, locDescripcion, provId, provDescripcion
                    FROM domicilio
                    INNER JOIN localidad ON domLocId = locId
                    INNER JOIN provincia ON locProvId = provId
                    WHERE domId = $id AND domFechaBaja is NULL";

            return parent::getById(query: $query, classDTO: "DomicilioDTO");
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

    public function postDomicilios()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(null);
            $data = Input::getArrayBody(msgEntidad: 'el domicilio');

            $this->domiciliosValidacionService->validarType(className: "DomicilioCreacionDTO", datos: $data);
            $domicilioCreacionDTO = new DomicilioCreacionDTO($data);

            $this->domiciliosValidacionService->validarInput($mysqli, $domicilioCreacionDTO);
            Input::escaparDatos($domicilioCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($domicilioCreacionDTO);

            $domLocId = $domicilioCreacionDTO->localidad->locId;

            $query = "INSERT INTO domicilio (domCPA, domCalleRuta, domNroKm, domPiso, domDepto, domLocId)
                      VALUES ($domicilioCreacionDTO->domCPA, $domicilioCreacionDTO->domCalleRuta, $domicilioCreacionDTO->domNroKm,
                              $domicilioCreacionDTO->domPiso, $domicilioCreacionDTO->domDepto, $domLocId)";

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
                // Cierra la conexión a la base de datos si fue creada
                $mysqli->close();
            }
        }
    }

    public function patchDomicilios($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(null);
            settype($id, 'integer');
            
            $data = Input::getArrayBody(msgEntidad: 'el domicilio');

            $data['domId'] = $id;

            $this->domiciliosValidacionService->validarType(className: "DomicilioCreacionDTO", datos: $data);
            $domicilioDTO = new DomicilioDTO($data);

            $this->domiciliosValidacionService->validarInput($mysqli, $domicilioDTO);
            Input::escaparDatos($domicilioDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($domicilioDTO);

            $domLocId = $domicilioDTO->localidad->locId;

            $query = "UPDATE domicilio
                      SET domCPA = $domicilioDTO->domCPA,
                          domCalleRuta = $domicilioDTO->domCalleRuta,
                          domNroKm = $domicilioDTO->domNroKm,
                          domPiso = $domicilioDTO->domPiso,
                          domDepto = $domicilioDTO->domDepto,
                          domLocId = $domLocId
                      WHERE domId = $domicilioDTO->domId
                      AND domFechaBaja is NULL";

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
                // Cierra la conexión a la base de datos si fue creada
                $mysqli->close();
            }
        }
    }

    public function deleteDomicilios($id)
    {
        try {
            $this->securityService->requireLogin(TipoUsuarioEnum::soporteTecnicoToArray());
            settype($id, 'integer');

            $queryBusqueda = "SELECT 1
                              FROM domicilio
                              WHERE domId = $id AND domFechaBaja IS NULL";

            $queryBajaLogica = "UPDATE domicilio
                                SET domFechaBaja = CURRENT_TIMESTAMP()
                                WHERE domId = $id";

            return parent::delete(queryBusqueda: $queryBusqueda, queryBajaLogica: $queryBajaLogica);
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
