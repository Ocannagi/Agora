<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class PeriodosController extends BaseController
{
    private ValidacionServiceBase $periodosValidacionService;
    private ISecurity $securityService;

    private static $instancia = null; // La única instancia de la clase

    /** El orden de las dependencias debe ser el mismo que en inyectarDependencias en api.php  */
    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $periodosValidacionService)
    {
        parent::__construct($dbConnection);
        $this->periodosValidacionService = $periodosValidacionService;
        $this->securityService = $securityService;
    }

    // Método público para obtener la instancia única
    public static function getInstancia(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $periodosValidacionService): PeriodosController
    {
        if (self::$instancia === null) {
            self::$instancia = new self($dbConnection, $securityService, $periodosValidacionService); // Crea la instancia si no existe
        }
        return self::$instancia;
    }

    // Método para evitar la clonación del objeto
    private function __clone() {}

    public function getPeriodos()
    {
        try {
            $this->securityService->requireLogin(null);
            return parent::get(query: "SELECT perId, perDescripcion FROM periodo WHERE perFechaBaja is NULL", classDTO: "PeriodoDTO");
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

    public function getPeriodosById($id)
    {
        try {
            settype($id, 'integer');
            $this->securityService->requireLogin(null);
            return parent::getById(query: "SELECT perId, perDescripcion FROM periodo WHERE perId = $id AND perFechaBaja is NULL", classDTO: "PeriodoDTO");
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

    public function postPeriodos()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());

            $data = Input::getArrayBody(msgEntidad: "el periodo");

            $this->periodosValidacionService->validarType(className: "PeriodoCreacionDTO", datos: $data);

            $periodoCreacionDTO = new PeriodoCreacionDTO($data);

            $this->periodosValidacionService->validarInput($mysqli, $periodoCreacionDTO);

            $periodoCreacionDTO->perDescripcion = Input::cadaPalabraMayuscula($periodoCreacionDTO->perDescripcion);

            Input::escaparDatos($periodoCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($periodoCreacionDTO);

            return parent::post(query: "INSERT INTO periodo (perDescripcion) VALUES ($periodoCreacionDTO->perDescripcion)", link: $mysqli);
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

    public function patchPeriodos($id)
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            settype($id, 'integer');
            
            $data = Input::getArrayBody(msgEntidad: "el periodo");

            $data['perId'] = $id;

            $this->periodosValidacionService->validarType(className: "PeriodoDTO", datos: $data);
            $periodoDTO = new PeriodoDTO($data);

            $this->periodosValidacionService->validarInput($mysqli, $periodoDTO);

            $periodoDTO->perDescripcion = Input::cadaPalabraMayuscula($periodoDTO->perDescripcion);

            Input::escaparDatos($periodoDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($periodoDTO);

            $query = "UPDATE periodo SET perDescripcion = $periodoDTO->perDescripcion WHERE perId = $periodoDTO->perId AND perFechaBaja IS NULL";

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

    public function deletePeriodos($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::soporteTecnicoToArray());
            settype($id, 'integer');
            return parent::delete(queryBusqueda: "SELECT 1 FROM periodo WHERE perId=$id AND perFechaBaja IS NULL", queryBajaLogica: "UPDATE periodo SET perFechaBaja = CURRENT_TIMESTAMP() WHERE perId=$id");
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
        }
    }
}
