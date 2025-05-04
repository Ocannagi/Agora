<?php

use Utilidades\Output;
use Utilidades\Input;

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
        $this->securityService->requireLogin(null);
        return parent::get(query: "SELECT perId, perDescripcion FROM periodo WHERE perFechaBaja is NULL", classDTO: "PeriodoDTO");
    }

    public function getPeriodosById($id)
    {
        settype($id, 'integer');
        $this->securityService->requireLogin(null);
        return parent::getById(query: "SELECT perId, perDescripcion FROM periodo WHERE perId = $id AND perFechaBaja is NULL", classDTO: "PeriodoDTO");
    }

    public function postPeriodos()
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);

            $mysqli = $this->dbConnection->conectarBD();
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error()) {
                Output::outputError(400, "El formato de datos es incorrecto");
            }
            if (empty($data)) {
                Output::outputError(400, "No se recibieron datos para crear el periodo");
            }

            $this->periodosValidacionService->validarType(className: "PeriodoCreacionDTO", datos: $data);

            $periodoCreacionDTO = new PeriodoCreacionDTO($data);
            Input::trimStringDatos($periodoCreacionDTO);

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
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function patchPeriodos($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);
            settype($id, 'integer');
            $mysqli = $this->dbConnection->conectarBD();
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error()) {
                Output::outputError(400, "El formato de datos es incorrecto");
            }
            if (empty($data)) {
                Output::outputError(400, "No se recibieron datos para modificar el periodo");
            }

            $data['perId'] = $id;

            $this->periodosValidacionService->validarType(className: "PeriodoDTO", datos: $data);
            $periodoDTO = new PeriodoDTO($data);
            Input::trimStringDatos($periodoDTO);

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
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function deletePeriodos($id)
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);
            settype($id, 'integer');
            return parent::delete(queryBusqueda: "SELECT 1 FROM periodo WHERE perId=$id AND perFechaBaja IS NULL", queryBajaLogica: "UPDATE periodo SET perFechaBaja = CURRENT_TIMESTAMP() WHERE perId=$id");

        } catch (\Throwable $th) {
            if ($th instanceof InvalidArgumentException) {
                Output::outputError(400, $th->getMessage());
            } elseif ($th instanceof mysqli_sql_exception) {
                Output::outputError(500, "Error en la base de datos: " . $th->getMessage());
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

}