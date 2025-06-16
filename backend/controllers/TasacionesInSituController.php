<?php

use Utilidades\Output;
use Utilidades\Input;
use Model\CustomException;

class TasacionesInSituController extends BaseController
{

    use TraitGetByIdInterno;

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


    public function getTasacionesInSitu()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(null);


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




    public function postTasacionesInSitu()
    {
        $mysqli = $this->dbConnection->conectarBD();
        try {
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::solicitanteTasacionToArray());
            $data = Input::getArrayBody(msgEntidad: "la tasación In Situ");

            $this->tasacionesInSituValidacionService->validarType(className: TasacionInSituCreacionDTO::class, datos: $data);
            $tasacionInSituCreacionDTO = new TasacionInSituCreacionDTO($data);

            $this->tasacionesInSituValidacionService->validarInput($mysqli, $tasacionInSituCreacionDTO, $claimDTO);
            Input::escaparDatos($tasacionInSituCreacionDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($tasacionInSituCreacionDTO);

            $query = "INSERT INTO tasacioninsitu (tisTadId, tisDomTasId, tisFechaTasInSituProvisoria)
                      VALUES ({$tasacionInSituCreacionDTO->tasacionDigital->tadId}, {$tasacionInSituCreacionDTO->domicilio->domId}, '{$tasacionInSituCreacionDTO->tisFechaTasInSituProvisoria}')";

            return parent::post($query, $mysqli);
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


    public function patchTasacionesInSitu(int $id)
    {
        /*DE MOMENTO NO SE PUEDE MODIFICAR LA FECHA PROVISORIA. SOLO SE PUEDE RECHAZAR, QUE LUEGO EL USARIO DESESTIME Y VUELVA A GENERAR UNA NUEVA PRESTACION*/

        $mysqli = $this->dbConnection->conectarBD();
        try {
            settype($id, 'int');
            $claimDTO = $this->securityService->requireLogin(tipoUsurio: TipoUsuarioEnum::tasadorToArray());
            $data = Input::getArrayBody(msgEntidad: "la tasación In Situ");


            $tasacionInSituFS = $this->getByIdInterno(
                query: "SELECT  tisFechaTasInSituSolicitada
                    FROM tasacioninsitu
                    WHERE tisId = $id
                    AND tisFechaBaja IS NULL",
                classDTO: TasacionInSituDTO::class,
                linkExterno: $mysqli
            );

            if ($tasacionInSituFS === null) {
                Output::outputError(404, "No se encontró la tasación in situ con ID: $id");
            }

            $data['tisId'] = $id; // Aseguramos que el ID esté en los datos para la validación y creación del DTO.
            $data['tisFechaTasInSituSolicitada'] = $tasacionInSituFS->tisFechaTasInSituSolicitada; // Mantenemos la fecha solicitada si existe.

            $this->tasacionesInSituValidacionService->validarType(className: TasacionInSituDTO::class, datos: $data);
            $tasacionInSituDTO = new TasacionInSituDTO($data);

            if(isset($tasacionInSituDTO->tisPrecioInSitu)) {
                $tasacionInSituDTO->tisPrecioInSitu = Input::redondearNumero($tasacionInSituDTO->tisPrecioInSitu, 2);
            }

            $this->tasacionesInSituValidacionService->validarInput($mysqli, $tasacionInSituDTO, $claimDTO);
            Input::escaparDatos($tasacionInSituDTO, $mysqli);
            Input::agregarComillas_ConvertNULLtoString($tasacionInSituDTO);

            $query = "UPDATE tasacioninsitu
                      SET tisFechaTasInSituRealizada = '{$tasacionInSituDTO->tisFechaTasInSituRealizada}',
                          tisFechaTasInSituRechazada = '{$tasacionInSituDTO->tisFechaTasInSituRechazada}',
                          tisObservacionesInSitu = {$tasacionInSituDTO->tisObservacionesInSitu},
                          tisPrecioInSitu = {$tasacionInSituDTO->tisPrecioInSitu}
                      WHERE tisId = {$id}";

            return parent::patch($query, $mysqli);
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
