<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class TasacionesInSituController extends BaseController
{
    private ValidacionServiceBase $tasacionesInSituValidacionService;
    private ISecurity $securityService;

    private static $instancia = null;

    private function __construct(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $tasacionesInSituValidacionService)
    {
        parent::__construct($dbConnection);
        $this->securityService = $securityService;
        $this->tasacionesInSituValidacionService = $tasacionesInSituValidacionService;
    }

    public static function getInstance(IDbConnection $dbConnection, ISecurity $securityService, ValidacionServiceBase $tasacionesInSituValidacionService): TasacionesInSituController
    {
        if (self::$instancia === null) {
            self::$instancia = new TasacionesInSituController($dbConnection, $securityService, $tasacionesInSituValidacionService);
        }
        return self::$instancia;
    }

    private function __clone() {}




    public function postTasacionesInSitu()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::solicitanteTasacionToArray());
            $data = Input::getArrayBody(msgEntidad: "la tasación In Situ");

            $this->tasacionesInSituValidacionService->validarType(className: TasacionInSituCreacionDTO::class, datos: $data);
            $tasacionInSituCreacionDTO = new TasacionInSituCreacionDTO($data);


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





}