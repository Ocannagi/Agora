<?php

use Utilidades\Output;
use Utilidades\Input;

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
        $this->securityService->requireLogin(null);

        $query =   "SELECT locId, locDescripcion, provId, provDescripcion
                    FROM localidad
                    INNER JOIN provincia ON locProvId = provId
                    WHERE locFechaBaja is NULL
                    ORDER BY locId";

        return parent::get(query: $query, classDTO: "LocalidadDTO");
    }

    public function getLocalidadesById($id)
    {
        settype($id, 'integer');
        $this->securityService->requireLogin(null);

        $query =   "SELECT locId, locDescripcion, provId, provDescripcion
                    FROM localidad
                    INNER JOIN provincia ON locProvId = provId
                    WHERE locId = $id AND locFechaBaja is NULL";

        return parent::getById(query: $query, classDTO: "LocalidadDTO");
    }

    public function postLocalidades()
    {
        try {
            $this->securityService->requireLogin(tipoUsurio: ['ST']);

            $mysqli = $this->dbConnection->conectarBD();
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error()) {
                Output::outputError(400, "El formato de datos es incorrecto");
            }
            if (empty($data)) {
                Output::outputError(400, "No se recibieron datos para crear la localidad");
            }

            $this->localidadesValidacionService->validarType(className: "LocalidadCreacionDTO", datos: $data);
            $localidadCreacionDTO = new LocalidadCreacionDTO($data);
            Input::trimStringDatos($localidadCreacionDTO);
            
            
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
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }

    public function patchLocalidades($id)
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
                Output::outputError(400, "No se recibieron datos para modificar la localidad");
            }

            $data['locId'] = $id;

            $this->localidadesValidacionService->validarType(className: "LocalidadDTO", datos: $data);
            $localidadDTO = new LocalidadDTO($data);
            Input::trimStringDatos($localidadDTO);

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
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
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
            } else {
                Output::outputError(500, "Error inesperado: " . $th->getMessage() . ". Trace: " . $th->getTraceAsString());
            }
        }
    }


}